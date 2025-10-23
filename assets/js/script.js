// Family Tree Main JavaScript
jQuery(document).ready(function ($) {
    console.log('Family Tree plugin loaded successfully');

    // Global notification system
    window.showToast = function(message, type = 'success', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease-out reverse';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    window.showLoadingSpinner = function(show = true) {
        if (show) {
            $('body').append(`
                <div id="global-loading" style="
                    position: fixed; top: 50%; left: 50%; 
                    transform: translate(-50%, -50%);
                    background: rgba(255,255,255,0.9);
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                    z-index: 9999;
                    text-align: center;
                ">
                    <div class="loading-spinner"></div>
                    <p style="margin: 10px 0 0 0; color: #333;">Loading...</p>
                </div>
            `);
        } else {
            $('#global-loading').remove();
        }
    };

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

    initializeFamilyTree();
});

function initializeFamilyTree() {
    console.log('Initializing family tree functionality');
}