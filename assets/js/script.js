// Family Tree Main JavaScript
jQuery(document).ready(function ($) {
    console.log('Family Tree plugin loaded successfully');

    // Global modal functions
    window.showAddMemberForm = function () {
        $('#add-member-modal').show();
    };

    window.closeModal = function () {
        $('.modal').hide();
    };

    // Close modal when clicking outside
    $(document).on('click', function (e) {
        if ($(e.target).hasClass('modal')) {
            closeModal();
        }
    });

    // Handle escape key to close modals
    $(document).on('keyup', function (e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Initialize any dynamic functionality
    initializeFamilyTree();
});

function initializeFamilyTree() {
    // This function can be extended for any initialization needed
    console.log('Initializing family tree functionality');
}

// Utility functions
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
        color: ${type === 'success' ? '#155724' : '#721c24'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
        border-radius: 4px;
        z-index: 10000;
        max-width: 300px;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Form validation
function validateMemberForm(formData) {
    const errors = [];

    if (!formData.first_name || formData.first_name.trim().length < 2) {
        errors.push('First name is required and must be at least 2 characters long.');
    }

    if (!formData.last_name || formData.last_name.trim().length < 2) {
        errors.push('Last name is required and must be at least 2 characters long.');
    }

    if (formData.birth_date && formData.death_date) {
        const birthDate = new Date(formData.birth_date);
        const deathDate = new Date(formData.death_date);

        if (deathDate < birthDate) {
            errors.push('Death date cannot be before birth date.');
        }
    }

    return errors;
}

// Date formatting utility
function formatDateForDisplay(dateString) {
    if (!dateString) return 'Unknown';

    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Tree utility functions
function buildFamilyHierarchy(members) {
    // Create a map of all members by ID
    const memberMap = new Map();
    members.forEach(member => {
        memberMap.set(member.id, {
            ...member,
            children: []
        });
    });

    // Build hierarchy
    const rootNodes = [];

    memberMap.forEach(member => {
        if (member.parent1_id || member.parent2_id) {
            // Add as child to parents
            if (member.parent1_id && memberMap.has(member.parent1_id)) {
                memberMap.get(member.parent1_id).children.push(member);
            }
            if (member.parent2_id && memberMap.has(member.parent2_id)) {
                memberMap.get(member.parent2_id).children.push(member);
            }
        } else {
            // Root node (no parents)
            rootNodes.push(member);
        }
    });

    return rootNodes;
}