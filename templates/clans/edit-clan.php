<?php
/**
 * Family Tree Plugin - Edit Clan Page
 * Modify an existing family clan
 * Updated with professional design system
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

if (!current_user_can('manage_clans')) {
    wp_die('You do not have permission to manage clans.');
}

$clan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clan = FamilyTreeClanDatabase::get_clan($clan_id);

if (!$clan) {
    wp_die('Clan not found.');
}

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Clans', 'url' => '/browse-clans'],
    ['label' => $clan->clan_name . ' (Edit)'],
];
$page_title = '🏰 Edit Clan: ' . $clan->clan_name;
$page_actions = '
    <a href="/view-clan?id=' . intval($clan->id) . '" class="btn btn-outline btn-sm">
        👁️ View Clan
    </a>
    <a href="/browse-clans" class="btn btn-outline btn-sm">
        ← Back to Clans
    </a>
';

ob_start();
?>

<div class="container container-sm">
    <form id="editClanForm" class="form">
        <input type="hidden" name="clan_id" value="<?php echo intval($clan->id); ?>">

        <!-- Basic Information Section -->
        <div class="section">
            <h2 class="section-title">Basic Information</h2>
            
            <div class="form-group">
                <label class="form-label required" for="clan_name">Clan Name</label>
                <input 
                    type="text" 
                    id="clan_name" 
                    name="clan_name" 
                    required 
                    value="<?php echo esc_attr($clan->clan_name); ?>"
                    placeholder="e.g., House of Windsor"
                    minlength="2"
                    maxlength="150"
                >
                <small class="form-help">The primary name for this family clan</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="origin_year">Origin Year</label>
                <input 
                    type="number" 
                    id="origin_year" 
                    name="origin_year" 
                    min="1000" 
                    max="<?php echo date('Y'); ?>"
                    value="<?php echo esc_attr($clan->origin_year ?: ''); ?>"
                    placeholder="e.g., 1850"
                >
                <small class="form-help">When this clan was founded (optional)</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea 
                    id="description" 
                    name="description" 
                    placeholder="Tell the story of this clan..."
                    maxlength="1000"
                ><?php echo esc_textarea($clan->description ?: ''); ?></textarea>
                <small class="form-help">Optional. Share details about this clan's history.</small>
            </div>
        </div>

        <!-- Locations Section -->
        <div class="section">
            <h2 class="section-title">Primary Locations</h2>
            <p class="section-description">Where did this clan primarily live?</p>
            
            <div class="form-group">
                <label class="form-label required" for="clanLocations">Add Location</label>
                <input 
                    type="text" 
                    id="clanLocations" 
                    placeholder="e.g., Scotland, London, New York"
                >
                <small class="form-help">Type location and press Enter to add</small>
            </div>

            <div id="locationTags" class="tag-container" style="min-height: 44px;">
                <!-- Tags will appear here -->
            </div>

            <div id="locationError" class="error-message" style="display: none; margin-top: var(--spacing-md);">
                ❌ At least one location is required
            </div>
        </div>

        <!-- Surnames Section -->
        <div class="section">
            <h2 class="section-title">Family Surnames</h2>
            <p class="section-description">What surnames are associated with this clan?</p>
            
            <div class="form-group">
                <label class="form-label required" for="clanSurnames">Add Surname</label>
                <input 
                    type="text" 
                    id="clanSurnames" 
                    placeholder="e.g., MacDonald, Smith, Johnson"
                >
                <small class="form-help">Type surname and press Enter to add</small>
            </div>

            <div id="surnameTags" class="tag-container" style="min-height: 44px;">
                <!-- Tags will appear here -->
            </div>

            <div id="surnameError" class="error-message" style="display: none; margin-top: var(--spacing-md);">
                ❌ At least one surname is required
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                💾 Update Clan
            </button>
            <a href="/browse-clans" class="btn btn-outline btn-lg">
                Cancel
            </a>
        </div>

        <!-- Message Area -->
        <div id="formMessage" style="margin-top: var(--spacing-lg);"></div>
    </form>
</div>

<script>
jQuery(function($) {
    console.log("Edit Clan form initialized");

    // Tag management
    let locations = <?php echo json_encode($clan->locations ?: []); ?>;
    let surnames = <?php echo json_encode($clan->surnames ?: []); ?>;

    // Render tags
    function renderTags(arr, containerId) {
        const container = $('#' + containerId);
        container.empty();
        
        if (arr.length === 0) {
            return;
        }

        arr.forEach((tag, index) => {
            container.append(`
                <span class="tag">
                    ${escapeHtml(tag)}
                    <button type="button" class="tag-remove" data-index="${index}" data-type="${containerId}">×</button>
                </span>
            `);
        });
    }

    // Add tag on Enter key
    function setupTagInput(inputId, arr, containerId) {
        const input = $('#' + inputId);
        
        input.on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = $(this).val().trim();
                
                if (!val) return;
                if (arr.includes(val)) {
                    showToast('This item is already added', 'warning');
                    return;
                }
                if (val.length > 100) {
                    showToast('Item is too long (max 100 characters)', 'error');
                    return;
                }

                arr.push(val);
                renderTags(arr, containerId);
                $(this).val('');
                $('#' + containerId.replace('Tags', 'Error')).hide();
            }
        });
    }

    // Remove tag
    $(document).on('click', '.tag-remove', function(e) {
        e.preventDefault();
        const index = $(this).data('index');
        const type = $(this).data('type');
        
        if (type === 'locationTags') {
            locations.splice(index, 1);
            renderTags(locations, 'locationTags');
        } else if (type === 'surnameTags') {
            surnames.splice(index, 1);
            renderTags(surnames, 'surnameTags');
        }
    });

    // Setup tag inputs
    setupTagInput('clanLocations', locations, 'locationTags');
    setupTagInput('clanSurnames', surnames, 'surnameTags');

    // Initial render
    renderTags(locations, 'locationTags');
    renderTags(surnames, 'surnameTags');

    // Form submission
    $('#editClanForm').on('submit', function(e) {
        e.preventDefault();

        // Validation
        let hasErrors = false;

        if (!$('#clan_name').val().trim()) {
            showToast('Clan name is required', 'error');
            $('#clan_name').addClass('error');
            hasErrors = true;
        } else {
            $('#clan_name').removeClass('error');
        }

        if (locations.length === 0) {
            $('#locationError').show();
            hasErrors = true;
        } else {
            $('#locationError').hide();
        }

        if (surnames.length === 0) {
            $('#surnameError').show();
            hasErrors = true;
        } else {
            $('#surnameError').hide();
        }

        if (hasErrors) {
            showToast('Please fill in all required fields', 'error');
            return;
        }

        // Submit
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="loading-spinner"></span> Updating...');

        const data = {
            action: 'update_clan',
            nonce: family_tree.nonce,
            clan_id: $('[name="clan_id"]').val(),
            clan_name: $('#clan_name').val(),
            description: $('#description').val(),
            origin_year: $('#origin_year').val(),
            locations: locations,
            surnames: surnames
        };

        $.post(family_tree.ajax_url, data, function(response) {
            if (response.success) {
                showToast('Clan updated successfully! 🎉', 'success');
                setTimeout(() => {
                    window.location.href = '/browse-clans';
                }, 1200);
            } else {
                showToast('Error: ' + (response.data || 'Failed to update clan'), 'error');
                btn.prop('disabled', false).html(originalText);
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).html(originalText);
        });
    });

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
});
</script>

<?php
$page_content = ob_get_clean();
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>