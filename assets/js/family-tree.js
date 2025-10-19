class FamilyTree {
    constructor(containerId, data) {
        this.container = document.getElementById(containerId);
        this.data = data;
        this.margin = { top: 50, right: 120, bottom: 50, left: 120 };
        this.width = 1200 - this.margin.left - this.margin.right;
        this.height = 800 - this.margin.top - this.margin.bottom;

        this.init();
    }

    init() {
        this.container.innerHTML = '';

        const svg = d3.select(this.container)
            .append('svg')
            .attr('width', this.width + this.margin.left + this.margin.right)
            .attr('height', this.height + this.margin.top + this.margin.bottom)
            .append('g')
            .attr('transform', `translate(${this.margin.left},${this.margin.top})`);

        this.renderTree(svg);
    }

    renderTree(svg) {
        // Build hierarchy
        const root = this.buildHierarchy();
        const treeLayout = d3.tree().size([this.height, this.width]);

        const treeData = treeLayout(root);

        // Draw links
        svg.selectAll('.link')
            .data(treeData.links())
            .enter()
            .append('path')
            .attr('class', 'link')
            .attr('d', d3.linkHorizontal()
                .x(d => d.y)
                .y(d => d.x)
            )
            .style('fill', 'none')
            .style('stroke', '#ccc')
            .style('stroke-width', 2);

        // Draw nodes
        const nodes = svg.selectAll('.node')
            .data(treeData.descendants())
            .enter()
            .append('g')
            .attr('class', 'node')
            .attr('transform', d => `translate(${d.y},${d.x})`);

        // Add circles
        nodes.append('circle')
            .attr('r', 25)
            .style('fill', d => this.getNodeColor(d.data))
            .style('stroke', '#fff')
            .style('stroke-width', 3)
            .on('click', (event, d) => this.showMemberDetails(d.data));

        // Add labels
        nodes.append('text')
            .attr('dy', '.35em')
            .attr('x', d => d.children ? -30 : 30)
            .style('text-anchor', d => d.children ? 'end' : 'start')
            .text(d => d.data.firstName + ' ' + d.data.lastName)
            .style('font-size', '12px')
            .style('font-family', 'Arial, sans-serif')
            .style('cursor', 'pointer')
            .on('click', (event, d) => this.showMemberDetails(d.data));
    }

    buildHierarchy() {
        const nodeMap = new Map();

        // Create all nodes
        this.data.forEach(member => {
            nodeMap.set(member.id, {
                ...member,
                children: []
            });
        });

        // Build parent-child relationships
        const rootNodes = [];
        nodeMap.forEach((node, id) => {
            if (node.parent1 || node.parent2) {
                if (node.parent1 && nodeMap.has(node.parent1)) {
                    nodeMap.get(node.parent1).children.push(node);
                }
                if (node.parent2 && nodeMap.has(node.parent2)) {
                    nodeMap.get(node.parent2).children.push(node);
                }
            } else {
                rootNodes.push(node);
            }
        });

        // Use first root node or create a dummy root
        let rootNode = rootNodes.length > 0 ? rootNodes[0] : this.data[0];

        return d3.hierarchy(rootNode);
    }

    getNodeColor(member) {
        if (member.gender === 'male') {
            return member.deathDate ? '#2C5282' : '#4A90E2';
        } else if (member.gender === 'female') {
            return member.deathDate ? '#822727' : '#E53E3E';
        } else {
            return member.deathDate ? '#22543D' : '#38A169';
        }
    }

    showMemberDetails(member) {
        const detailsHtml = `
            <div class="member-details-modal">
                <div class="modal-content">
                    <h3>${member.firstName} ${member.lastName}</h3>
                    <p><strong>Gender:</strong> ${member.gender || 'Not specified'}</p>
                    <p><strong>Born:</strong> ${member.birthDate || 'Unknown'}</p>
                    ${member.deathDate ? `<p><strong>Died:</strong> ${member.deathDate}</p>` : ''}
                    <button onclick="this.parentElement.parentElement.remove()">Close</button>
                    <button onclick="editMember(${member.id})">Edit</button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', detailsHtml);
    }
}

// Global functions
function editMember(memberId) {
    window.location.href = `/edit-member?id=${memberId}`;
}

// Initialize tree when D3 is loaded
function initializeFamilyTree(containerId, data) {
    if (typeof d3 !== 'undefined') {
        new FamilyTree(containerId, data);
    } else {
        console.error('D3.js not loaded');
    }
}