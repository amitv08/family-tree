<?php
/**
 * Family Tree Plugin - Tree View Page
 * Interactive D3.js visualization with professional design
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

// Fetch all member data for tree
$members = FamilyTreeDatabase::get_tree_data();

// Build clan list for filter dropdown
$clans = [];
foreach ($members as $m) {
    if (!empty($m->clan_id)) {
        $clans[$m->clan_id] = $m->clan_name ?: 'Unnamed Clan';
    }
}

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Tree View'],
];
$page_title = 'Family Tree Visualization';
$page_actions = '
    <button id="zoomIn" class="btn btn-outline btn-sm" title="Zoom in">
        🔍 +
    </button>
    <button id="zoomOut" class="btn btn-outline btn-sm" title="Zoom out">
        🔍 −
    </button>
    <button id="resetView" class="btn btn-outline btn-sm" title="Reset zoom and pan">
        🔄 Reset
    </button>
    <a href="/family-dashboard" class="btn btn-outline btn-sm">
        ← Back
    </a>
';

ob_start();
?>

<!-- Filter Panel -->
<div style="display: flex; gap: var(--spacing-lg); margin-bottom: var(--spacing-xl); flex-wrap: wrap;">
    <div class="form-group" style="flex: 1; min-width: 250px; margin: 0;">
        <label class="form-label" for="clanFilter" style="margin-bottom: var(--spacing-sm);">
            🏰 Filter by Clan
        </label>
        <select id="clanFilter" style="width: 100%;">
            <option value="">All Clans</option>
            <?php foreach ($clans as $id => $name): ?>
                <option value="<?php echo esc_attr($id); ?>">
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="display: flex; align-items: flex-end; gap: var(--spacing-md);">
        <button id="toggleInfo" class="btn btn-secondary btn-sm">
            ℹ️ Info
        </button>
    </div>
</div>

<!-- Tree Container -->
<div style="
    background: var(--color-bg-white);
    border: 2px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    height: 600px;
    position: relative;
    box-shadow: var(--shadow-base);
    margin-bottom: var(--spacing-xl);
">
    <!-- Empty State -->
    <?php if (empty($members)): ?>
        <div style="
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            flex-direction: column;
            gap: var(--spacing-lg);
            color: var(--color-text-secondary);
        ">
            <div style="font-size: 3rem;">🌳</div>
            <div style="text-align: center;">
                <h3 style="color: var(--color-text-primary); margin: 0 0 var(--spacing-sm) 0;">
                    No Family Tree Data
                </h3>
                <p style="margin: 0; font-size: var(--font-size-sm);">
                    Add family members to see the interactive tree visualization
                </p>
                <a href="/add-member" class="btn btn-primary" style="margin-top: var(--spacing-lg);">
                    ➕ Add First Member
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- SVG Canvas for D3.js -->
        <svg id="familyTree" style="width: 100%; height: 100%;"></svg>
    <?php endif; ?>

    <!-- Loading Indicator -->
    <div id="treeLoading" style="
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        background: rgba(255,255,255,0.95);
        padding: var(--spacing-2xl);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
    ">
        <div class="loading-spinner" style="margin: 0 auto var(--spacing-lg) auto;"></div>
        <p style="margin: 0; color: var(--color-text-secondary);">Loading tree...</p>
    </div>

    <!-- Legend -->
    <div style="
        position: absolute;
        bottom: var(--spacing-lg);
        right: var(--spacing-lg);
        background: var(--color-bg-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-base);
        padding: var(--spacing-lg);
        font-size: var(--font-size-sm);
        max-width: 250px;
        box-shadow: var(--shadow-md);
    ">
        <strong style="display: block; margin-bottom: var(--spacing-md); color: var(--color-text-primary);">
            Legend
        </strong>
        <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
            <div style="
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: #4A90E2;
                border: 2px solid #2C5282;
            "></div>
            <span>Living Male</span>
        </div>
        <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
            <div style="
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: #E53E3E;
                border: 2px solid #822727;
            "></div>
            <span>Living Female</span>
        </div>
        <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
            <div style="
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: #38A169;
                border: 2px solid #22543D;
            "></div>
            <span>Living Other</span>
        </div>
        <div style="display: flex; align-items: center; gap: var(--spacing-md);">
            <div style="
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background: rgba(0,0,0,0.3);
                border: 2px solid rgba(0,0,0,0.5);
            "></div>
            <span>Deceased</span>
        </div>
    </div>
</div>

<!-- Info Panel (Hidden by default) -->
<div id="infoPanel" style="
    background: var(--color-info-light);
    border-left: 4px solid var(--color-info);
    border-radius: var(--radius-base);
    padding: var(--spacing-lg);
    color: var(--color-info);
    display: none;
    margin-bottom: var(--spacing-xl);
">
    <strong>💡 How to use the tree:</strong>
    <ul style="margin: var(--spacing-md) 0 0 var(--spacing-lg); padding-left: var(--spacing-lg);">
        <li>Use <strong>🔍 +/−</strong> buttons to zoom in/out</li>
        <li>Use <strong>mouse wheel</strong> to zoom smoothly</li>
        <li><strong>Drag</strong> to pan around the tree</li>
        <li><strong>Hover on nodes</strong> to see member details</li>
        <li>Use the <strong>Filter</strong> dropdown to show specific clans</li>
        <li>Click <strong>🔄 Reset</strong> to return to default view</li>
    </ul>
</div>

<!-- Statistics -->
<?php if (!empty($members)): ?>
    <div class="grid grid-3" style="margin-bottom: var(--spacing-xl);">
        <div class="stat-card">
            <div class="stat-card-icon">👥</div>
            <div class="stat-card-value"><?php echo count($members); ?></div>
            <p class="stat-card-label">Total Members</p>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon">🏰</div>
            <div class="stat-card-value"><?php echo count($clans); ?></div>
            <p class="stat-card-label">Clans</p>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon">👨‍👩‍👧‍👦</div>
            <div class="stat-card-value">
                <?php
                $generations = 1;
                $maxDepth = 0;
                function getMaxDepth($memberId, $members, $depth = 0) {
                    $max = $depth;
                    foreach ($members as $m) {
                        if ($m->parent1_id == $memberId || $m->parent2_id == $memberId) {
                            $childMax = getMaxDepth($m->id, $members, $depth + 1);
                            $max = max($max, $childMax);
                        }
                    }
                    return $max;
                }
                
                $membersArray = $members;
                foreach ($membersArray as $m) {
                    if (!$m->parent1_id && !$m->parent2_id) {
                        $depth = getMaxDepth($m->id, $membersArray);
                        $maxDepth = max($maxDepth, $depth);
                    }
                }
                echo $maxDepth + 1;
                ?>
            </div>
            <p class="stat-card-label">Generations</p>
        </div>
    </div>
<?php endif; ?>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    (function() {
        const fullData = <?php echo wp_json_encode($members); ?> || [];
        const svg = d3.select("#familyTree");
        const g = svg.append("g");

        const width = document.getElementById('familyTree')?.parentElement?.offsetWidth || 1200;
        const height = 600;

        const colorScale = d3.scaleOrdinal()
            .domain([...new Set(fullData.map(d => d.clan_id || 'none'))])
            .range(['#007cba', '#9c27b0', '#009688', '#f44336', '#ff9800']);

        // Zoom & Pan setup
        const zoom = d3.zoom()
            .scaleExtent([0.1, 3])  // Allow more zoom out and zoom in
            .on("zoom", e => g.attr("transform", e.transform));

        svg.call(zoom);

        // Zoom In button
        document.getElementById('zoomIn')?.addEventListener('click', () => {
            svg.transition()
                .duration(300)
                .call(zoom.scaleBy, 1.3);
        });

        // Zoom Out button
        document.getElementById('zoomOut')?.addEventListener('click', () => {
            svg.transition()
                .duration(300)
                .call(zoom.scaleBy, 0.7);
        });

        // Reset view button
        document.getElementById('resetView')?.addEventListener('click', () => {
            svg.transition()
                .duration(750)
                .call(zoom.transform, d3.zoomIdentity.translate(width / 4, 50).scale(1));
        });

        // Toggle info panel
        document.getElementById('toggleInfo')?.addEventListener('click', function() {
            const panel = document.getElementById('infoPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
            this.classList.toggle('btn-primary');
            this.classList.toggle('btn-secondary');
        });

        function buildTree(data) {
            if (data.length === 0) return;

            g.selectAll("*").remove();

            const membersMap = {};
            data.forEach(d => { 
                d.children = []; 
                membersMap[d.id] = d; 
            });

            const roots = [];
            data.forEach(d => {
                if (d.parent1_id && membersMap[d.parent1_id]) {
                    membersMap[d.parent1_id].children.push(d);
                } else if (d.parent2_id && membersMap[d.parent2_id]) {
                    membersMap[d.parent2_id].children.push(d);
                } else {
                    roots.push(d);
                }
            });

            if (roots.length === 0 && data.length > 0) {
                roots.push(data[0]);
            }

            const root = d3.hierarchy({ children: roots }, d => d.children);
            const treeLayout = d3.tree().size([height - 100, width - 200]);
            treeLayout(root);

            // Links
            g.selectAll(".link")
                .data(root.links())
                .join("path")
                .attr("class", "link")
                .attr("d", d3.linkVertical().x(d => d.x).y(d => d.y))
                .style("fill", "none")
                .style("stroke", "#bbb")
                .style("stroke-width", 1.5);

            // Nodes
            const node = g.selectAll(".node")
                .data(root.descendants().slice(1))
                .join("g")
                .attr("class", "node")
                .attr("transform", d => `translate(${d.x},${d.y})`);

            node.append("circle")
                .attr("r", 18)
                .style("stroke-width", 2)
                .style("cursor", "pointer")
                .style("stroke", d => colorScale(d.data.clan_id))
                .style("fill", d => {
                    if (d.data.death_date) return "rgba(0,0,0,0.3)";
                    if (d.data.gender === "Male") return "#4A90E2";
                    if (d.data.gender === "Female") return "#E53E3E";
                    return "#38A169";
                })
                .on("mouseover", function(e, d) {
                    // Show tooltip
                    const tooltip = document.createElement('div');
                    tooltip.id = 'treeTooltip';
                    tooltip.style.cssText = `
                        position: fixed;
                        top: ${e.clientY + 10}px;
                        left: ${e.clientX + 10}px;
                        background: rgba(0,0,0,0.85);
                        color: white;
                        padding: var(--spacing-md) var(--spacing-lg);
                        border-radius: var(--radius-sm);
                        font-size: var(--font-size-sm);
                        z-index: 1000;
                        pointer-events: none;
                    `;
                    tooltip.innerHTML = `
                        <strong>${d.data.first_name || ''} ${d.data.last_name || ''}</strong><br>
                        Gender: ${d.data.gender || '-'}<br>
                        Born: ${d.data.birth_date || '-'}<br>
                        ${d.data.death_date ? ('Died: ' + d.data.death_date + '<br>') : ''}
                        Clan: ${d.data.clan_name || 'None'}
                    `;
                    document.body.appendChild(tooltip);
                    
                    d3.select(this)
                        .transition()
                        .duration(200)
                        .attr("r", 24);
                })
                .on("mouseout", function(e, d) {
                    document.getElementById('treeTooltip')?.remove();
                    d3.select(this)
                        .transition()
                        .duration(200)
                        .attr("r", 18);
                });

            node.append("text")
                .attr("dy", 4)
                .style("font-size", "11px")
                .style("font-weight", "500")
                .style("text-anchor", "middle")
                .style("fill", "#333")
                .style("pointer-events", "none")
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

        // Handle resize
        window.addEventListener('resize', () => {
            const newWidth = document.getElementById('familyTree')?.parentElement?.offsetWidth || 1200;
            svg.attr("width", newWidth);
        });
    })();
</script>

<?php
$page_content = ob_get_clean();
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>