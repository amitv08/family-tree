// Advanced Tree Visualization with D3.js
class FamilyTreeVisualization {
    constructor(containerId, data) {
        this.containerId = containerId;
        this.data = data;
        this.margin = { top: 20, right: 120, bottom: 20, left: 120 };
        this.width = 1200 - this.margin.right - this.margin.left;
        this.height = 800 - this.margin.top - this.margin.bottom;
        this.zoom = null;
        this.svg = null;
        this.treeLayout = null;

        this.init();
    }

    init() {
        this.setupSVG();
        this.setupZoom();
        this.render();
    }

    setupSVG() {
        this.svg = d3.select(`#${this.containerId}`)
            .append('svg')
            .attr('width', this.width + this.margin.right + this.margin.left)
            .attr('height', this.height + this.margin.top + this.margin.bottom)
            .append('g')
            .attr('transform', `translate(${this.margin.left},${this.margin.top})`);
    }

    setupZoom() {
        this.zoom = d3.zoom()
            .scaleExtent([0.1, 2])
            .on('zoom', (event) => {
                this.svg.attr('transform', event.transform);
            });

        d3.select(`#${this.containerId} svg`).call(this.zoom);
    }

    render() {
        if (!this.data || this.data.length === 0) {
            this.showNoDataMessage();
            return;
        }

        // Build hierarchy
        const root = this.buildHierarchy();
        this.treeLayout = d3.tree().size([this.height, this.width]);
        this.treeLayout(root);

        this.drawLinks(root);
        this.drawNodes(root);
    }

    buildHierarchy() {
        // Create a map of nodes
        const nodeMap = new Map();
        this.data.forEach(d => {
            nodeMap.set(d.id, { ...d, children: [] });
        });

        // Build children arrays
        const rootNodes = [];
        nodeMap.forEach(node => {
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

        // If no clear root, use the first node
        if (rootNodes.length === 0 && this.data.length > 0) {
            rootNodes.push(this.data[0]);
        }

        return d3.hierarchy(rootNodes[0]);
    }

    drawLinks(root) {
        const linkGenerator = d3.linkHorizontal()
            .x(d => d.y)
            .y(d => d.x);

        this.svg.selectAll('.link')
            .data(root.links())
            .enter()
            .append('path')
            .attr('class', 'link')
            .attr('d', linkGenerator)
            .style('fill', 'none')
            .style('stroke', '#ccc')
            .style('stroke-width', 2);
    }

    drawNodes(root) {
        const nodes = this.svg.selectAll('.node')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('class', 'node')
            .attr('transform', d => `translate(${d.y},${d.x})`);

        // Add circles
        nodes.append('circle')
            .attr('r', 10)
            .style('fill', d => this.getNodeColor(d.data))
            .style('stroke', '#fff')
            .style('stroke-width', 2);

        // Add labels
        nodes.append('text')
            .attr('dy', '.35em')
            .attr('x', d => d.children ? -13 : 13)
            .style('text-anchor', d => d.children ? 'end' : 'start')
            .text(d => d.data.firstName + ' ' + d.data.lastName)
            .style('font-size', '12px')
            .style('font-family', 'Arial, sans-serif');
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

    showNoDataMessage() {
        d3.select(`#${this.containerId}`).html(`
            <div class="no-data-message">
                <h3>No Family Data Available</h3>
                <p>Add some family members to see the interactive tree visualization.</p>
                <a href="/add-member" class="btn btn-primary">Add First Member</a>
            </div>
        `);
    }

    // Public methods
    updateData(newData) {
        this.data = newData;
        this.svg.selectAll('*').remove();
        this.render();
    }

    resetView() {
        d3.select(`#${this.containerId} svg`).call(
            this.zoom.transform,
            d3.zoomIdentity
        );
    }
}

// Global tree instance
let familyTree = null;

// Initialize advanced tree when D3 is available
function initializeAdvancedTree(containerId, data) {
    if (typeof d3 !== 'undefined') {
        familyTree = new FamilyTreeVisualization(containerId, data);
    } else {
        console.error('D3.js not loaded');
    }
}