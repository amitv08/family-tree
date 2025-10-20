<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}


// Fetch all member data
$members = FamilyTreeDatabase::get_tree_data();

// Build clan list for dropdown
$clans = [];
foreach ($members as $m) {
    if (!empty($m->clan_id)) {
        $clans[$m->clan_id] = $m->clan_name ?: 'Unnamed Clan';
    }
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Tree</title>

    <?php wp_head(); ?>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        nav.top-menu {
            display: flex;
            justify-content: center;
            gap: 30px;
            background: #007cba;
            color: white;
            padding: 14px 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        nav.top-menu a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: color 0.2s ease;
        }

        nav.top-menu a:hover {
            color: #dff0ff;
        }

        .tree-wrapper {
            width: 100%;
            height: calc(100vh - 60px);
            background: #f8f9fa;
            position: relative;
            overflow: hidden;
        }

        svg {
            width: 100%;
            height: 100%;
            cursor: grab;
        }

        .node circle {
            stroke-width: 2px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .node circle:hover {
            transform: scale(1.15);
        }

        .node text {
            font-size: 12px;
            font-weight: 500;
            text-anchor: middle;
            fill: #333;
            pointer-events: none;
        }

        .link {
            fill: none;
            stroke: #bbb;
            stroke-width: 1.6px;
        }

        .tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.75);
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .filter-panel {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            padding: 10px 15px;
            font-size: 13px;
        }

        .filter-panel select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            font-size: 13px;
        }

        /* Clan color palette */
        .clan-color-1 circle { stroke: #007cba; fill: #e3f2fd; }
        .clan-color-2 circle { stroke: #9c27b0; fill: #f3e5f5; }
        .clan-color-3 circle { stroke: #009688; fill: #e0f2f1; }
        .clan-color-4 circle { stroke: #f44336; fill: #ffebee; }
        .clan-color-5 circle { stroke: #ff9800; fill: #fff3e0; }

    </style>
</head>

<body <?php body_class(); ?>>

    <!-- Global navigation -->
    <nav class="top-menu">
        <a href="/family-dashboard">🏠 Dashboard</a>
        <a href="/browse-members">👨‍👩‍👧 Members</a>
        <a href="/browse-clans">🏰 Clans</a>
        <a href="/family-tree" class="active">🌳 Tree View</a>
    </nav>

    <div class="tree-wrapper">
        <div class="filter-panel">
            <label for="clanFilter"><strong>Filter by Clan:</strong></label><br>
            <select id="clanFilter">
                <option value="">All Clans</option>
                <?php foreach ($clans as $id => $name): ?>
                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="tooltip" id="tooltip"></div>
        <svg id="familyTree"></svg>
    </div>

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        (function() {
            const fullData = <?php echo wp_json_encode($members); ?> || [];
            const svg = d3.select("#familyTree");
            const g = svg.append("g");
            const tooltip = d3.select("#tooltip");

            const width = window.innerWidth;
            const height = window.innerHeight - 80;

            const colorScale = d3.scaleOrdinal()
                .domain([...new Set(fullData.map(d => d.clan_id || 'none'))])
                .range(['#007cba', '#9c27b0', '#009688', '#f44336', '#ff9800']);

            // Zoom & Pan setup
            const zoom = d3.zoom()
                .scaleExtent([0.2, 2])
                .on("zoom", e => g.attr("transform", e.transform));
            svg.call(zoom);

            function buildTree(data) {
                g.selectAll("*").remove();

                const membersMap = {};
                data.forEach(d => { d.children = []; membersMap[d.id] = d; });

                const roots = [];
                data.forEach(d => {
                    if (d.parent1_id && membersMap[d.parent1_id]) membersMap[d.parent1_id].children.push(d);
                    else if (d.parent2_id && membersMap[d.parent2_id]) membersMap[d.parent2_id].children.push(d);
                    else roots.push(d);
                });

                const root = d3.hierarchy({ children: roots }, d => d.children);
                const treeLayout = d3.tree().size([width - 200, height - 100]);
                treeLayout(root);

                // Links
                g.selectAll(".link")
                    .data(root.links())
                    .join("path")
                    .attr("class", "link")
                    .attr("d", d3.linkVertical().x(d => d.x).y(d => d.y));

                // Nodes
                const node = g.selectAll(".node")
                    .data(root.descendants().slice(1))
                    .join("g")
                    .attr("class", "node")
                    .attr("transform", d => `translate(${d.x},${d.y})`);

                node.append("circle")
                    .attr("r", 18)
                    .style("stroke", d => colorScale(d.data.clan_id))
                    .style("fill", d => d3.color(colorScale(d.data.clan_id)).brighter(1.5))
                    .on("mouseover", function(e, d) {
                        tooltip.style("opacity", 1)
                            .html(`<strong>${d.data.first_name || ''} ${d.data.last_name || ''}</strong><br>
                                   Gender: ${d.data.gender || '-'}<br>
                                   Clan: ${d.data.clan_name || 'None'}`)
                            .style("left", (e.pageX + 12) + "px")
                            .style("top", (e.pageY - 28) + "px");
                    })
                    .on("mouseout", () => tooltip.style("opacity", 0));

                node.append("text")
                    .attr("dy", 4)
                    .text(d => `${d.data.first_name || ''} ${d.data.last_name || ''}`);
            }

            // Initial render
            buildTree(fullData);

            // Clan filter logic
            d3.select("#clanFilter").on("change", function() {
                const val = this.value;
                if (!val) {
                    buildTree(fullData);
                } else {
                    const filtered = fullData.filter(d => String(d.clan_id) === val);
                    buildTree(filtered);
                }
            });
        })();
    </script>

    <?php wp_footer(); ?>
</body>
</html>