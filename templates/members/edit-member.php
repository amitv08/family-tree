<?php
/**
 * Family Tree Plugin - Edit Member Page
 * Edit an existing family member with full details
 * Updated with professional design system
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

if (!current_user_can('edit_family_members')) {
    wp_die('You do not have permission to edit members.');
}

// Get member ID from URL
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$member_id) {
    wp_die('Invalid member ID');
}

// Fetch the member data
$member = FamilyTreeDatabase::get_member($member_id);
if (!$member) {
    wp_die('Member not found');
}

$clans = FamilyTreeDatabase::get_all_clans_simple();
$all_members = FamilyTreeDatabase::get_members(2000, 0);

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Members', 'url' => '/browse-members'],
    ['label' => 'Edit Member'],
];
$page_title = '✏️ Edit Family Member: ' . esc_html($member->first_name . ' ' . $member->last_name);
$page_actions = '<a href="/view-member?id=' . $member_id . '" class="btn btn-outline btn-sm">👁️ View Member</a>
                 <a href="/browse-members" class="btn btn-outline btn-sm">← Back to Members</a>';

ob_start();
?>

<div class="container container-lg">
    <form id="editMemberForm" class="form">
        <input type="hidden" name="member_id" value="<?php echo intval($member_id); ?>">

        <!-- Clan Information Section -->
        <div class="section">
            <h2 class="section-title">🏰 Clan Information</h2>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label required" for="clan_id">Clan</label>
                    <select id="clan_id" name="clan_id" required>
                        <option value="">-- Select Clan --</option>
                        <?php foreach ($clans as $c): ?>
                            <option value="<?php echo intval($c->id); ?>" <?php echo ($member->clan_id == $c->id) ? 'selected' : ''; ?>>
                                <?php echo esc_html($c->clan_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-help">Which family clan does this member belong to?</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="clan_location_id">Location</label>
                    <select id="clan_location_id" name="clan_location_id">
                        <option value="">-- Select Location --</option>
                    </select>
                    <small class="form-help">Primary location for this clan member</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="clan_surname_id">Surname</label>
                <select id="clan_surname_id" name="clan_surname_id">
                    <option value="">-- Select Surname --</option>
                </select>
                <small class="form-help">Auto-filled from surname; editable</small>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="section">
            <h2 class="section-title">👤 Personal Information</h2>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label required" for="first_name">First Name</label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        required
                        value="<?php echo esc_attr($member->first_name); ?>"
                        placeholder="e.g., John"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label required" for="last_name">Last Name</label>
                    <input
                        type="text"
                        id="last_name_input"
                        name="last_name"
                        required
                        value="<?php echo esc_attr($member->last_name); ?>"
                        placeholder="e.g., Smith"
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Gender</label>
                <div style="display: flex; gap: var(--spacing-lg); margin-top: var(--spacing-sm);">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="radio" name="gender" value="Male" <?php echo ($member->gender == 'Male') ? 'checked' : ''; ?> style="margin-right: var(--spacing-xs);">
                        <span>♂️ Male</span>
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="radio" name="gender" value="Female" <?php echo ($member->gender == 'Female') ? 'checked' : ''; ?> style="margin-right: var(--spacing-xs);">
                        <span>♀️ Female</span>
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="radio" name="gender" value="Other" <?php echo ($member->gender == 'Other') ? 'checked' : ''; ?> style="margin-right: var(--spacing-xs);">
                        <span>⚧️ Other</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="photo_url">Photo URL</label>
                <input
                    type="url"
                    id="photo_url"
                    name="photo_url"
                    value="<?php echo esc_attr($member->photo_url ?: ''); ?>"
                    placeholder="https://example.com/photo.jpg"
                >
            </div>
        </div>

        <!-- Family Relationships Section -->
        <div class="section">
            <h2 class="section-title">👨‍👩‍👧‍👦 Family Relationships</h2>
            <p class="section-description">Leave parents empty for root ancestors (family founders)</p>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="parent1_id">Father (Parent 1)</label>
                    <select id="parent1_id" name="parent1_id">
                        <option value="">-- None --</option>
                        <?php foreach ($all_members as $m): ?>
                            <?php if ($m->id != $member_id && $m->gender === 'Male'): // Only show males, not self ?>
                                <option value="<?php echo intval($m->id); ?>" <?php echo ($member->parent1_id == $m->id) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($m->first_name . ' ' . $m->last_name . ' (b. ' . ($m->birth_date ?: 'N/A') . ')'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-help">Only male members shown. Leave empty for ancestors without recorded father.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="parent2_name">Mother Name (Parent 2)</label>
                    <input
                        type="text"
                        id="parent2_name"
                        name="parent2_name"
                        value="<?php echo esc_attr($member->parent2_name ?? ''); ?>"
                        placeholder="e.g., Mary Smith"
                    >
                    <small class="form-help">Optional. Enter mother's name as text for now.</small>
                </div>
            </div>
        </div>

        <!-- Life Events Section -->
        <div class="section">
            <h2 class="section-title">📅 Life Events</h2>

            <div class="form-row form-row-3">
                <div class="form-group">
                    <label class="form-label" for="birth_date">Birth Date</label>
                    <input type="date" id="birth_date" name="birth_date" value="<?php echo esc_attr($member->birth_date ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="death_date">Death Date</label>
                    <input type="date" id="death_date" name="death_date" value="<?php echo esc_attr($member->death_date ?? ''); ?>">
                    <small class="form-help">Leave empty if still living</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="marriage_date">Marriage Date</label>
                    <input type="date" id="marriage_date" name="marriage_date" value="<?php echo esc_attr($member->marriage_date ?? ''); ?>">
                </div>
            </div>
        </div>

        <!-- Location Information Section -->
        <div class="section">
            <h2 class="section-title">📍 Location Information</h2>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <input
                    type="text"
                    id="address"
                    name="address"
                    value="<?php echo esc_attr($member->address ?? ''); ?>"
                    placeholder="Street address"
                >
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo esc_attr($member->city ?? ''); ?>" placeholder="e.g., London">
                </div>
                <div class="form-group">
                    <label class="form-label" for="state">State/Province</label>
                    <input type="text" id="state" name="state" value="<?php echo esc_attr($member->state ?? ''); ?>" placeholder="e.g., England">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="country">Country</label>
                    <input type="text" id="country" name="country" value="<?php echo esc_attr($member->country ?? ''); ?>" placeholder="e.g., United Kingdom">
                </div>
                <div class="form-group">
                    <label class="form-label" for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?php echo esc_attr($member->postal_code ?? ''); ?>" placeholder="e.g., SW1A 1AA">
                </div>
            </div>
        </div>

        <!-- Biography Section -->
        <div class="section">
            <h2 class="section-title">📖 Biography</h2>
            <div class="form-group">
                <label class="form-label" for="biography">About This Person</label>
                <textarea
                    id="biography"
                    name="biography"
                    placeholder="Share interesting facts, achievements, and memories about this family member..."
                ><?php echo esc_textarea($member->biography ?? ''); ?></textarea>
                <small class="form-help">Optional. Write a short biography or notes about this person</small>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                💾 Update Member
            </button>
            <a href="/view-member?id=<?php echo $member_id; ?>" class="btn btn-outline btn-lg">
                Cancel
            </a>
        </div>

        <!-- Message Area -->
        <div id="formMessage" style="margin-top: var(--spacing-lg);"></div>
    </form>
</div>

<script>
jQuery(function($) {
    console.log("Edit Member form initialized for member ID: <?php echo $member_id; ?>");

    // Initialize Select2 for parent dropdowns
    $('#parent1_id, #parent2_id').select2({
        placeholder: '--- Search and select ---',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        language: {
            noResults: () => 'No members found'
        }
    });

    // Load clan details when clan selected
    function loadClanDetails(clanId, preSelectedLocationId, preSelectedSurnameId) {
        if (!clanId) {
            $('#clan_location_id').html('<option value="">-- Select Location --</option>');
            $('#clan_surname_id').html('<option value="">-- Select Surname --</option>');
            return;
        }

        $.post(family_tree.ajax_url, {
            action: 'get_clan_details',
            nonce: family_tree.nonce,
            clan_id: clanId
        }, function(res) {
            if (res.success) {
                var locs = res.data.locations || [];
                var surnames = res.data.surnames || [];

                var htmlL = '<option value="">-- Select Location --</option>';
                locs.forEach(function(l) {
                    var selected = (preSelectedLocationId && l.id == preSelectedLocationId) ? ' selected' : '';
                    htmlL += '<option value="' + l.id + '"' + selected + '>' + escapeHtml(l.location_name) + '</option>';
                });
                $('#clan_location_id').html(htmlL);

                var htmlS = '<option value="">-- Select Surname --</option>';
                surnames.forEach(function(s) {
                    var selected = (preSelectedSurnameId && s.id == preSelectedSurnameId) ? ' selected' : '';
                    htmlS += '<option value="' + s.id + '"' + selected + ' data-lastname="' + escapeHtml(s.last_name) + '">' + escapeHtml(s.last_name) + '</option>';
                });
                $('#clan_surname_id').html(htmlS);
            } else {
                showToast('Error loading clan details: ' + res.data, 'error');
            }
        });
    }

    // Load initial clan details with pre-selected values
    var initialClanId = $('#clan_id').val();
    var initialLocationId = <?php echo $member->clan_location_id ? intval($member->clan_location_id) : 'null'; ?>;
    var initialSurnameId = <?php echo $member->clan_surname_id ? intval($member->clan_surname_id) : 'null'; ?>;

    if (initialClanId) {
        loadClanDetails(initialClanId, initialLocationId, initialSurnameId);
    }

    $('#clan_id').on('change', function() {
        loadClanDetails($(this).val(), null, null);
    });

    // Auto-fill last name when surname selected
    $('#clan_surname_id').on('change', function() {
        var sel = $(this).find('option:selected');
        var ln = sel.data('lastname') || '';
        if (ln) {
            $('#last_name_input').val(ln);
        }
    });

    // Form submission
    $('#editMemberForm').on('submit', function(e) {
        e.preventDefault();

        // Validation
        if (!$('#first_name').val().trim()) {
            showToast('First name is required', 'error');
            return;
        }

        if (!$('#last_name_input').val().trim()) {
            showToast('Last name is required', 'error');
            return;
        }

        // Check for circular reference (person as their own parent)
        var memberId = <?php echo $member_id; ?>;
        var parent1 = $('#parent1_id').val();
        var parent2 = $('#parent2_id').val();

        if (parent1 == memberId || parent2 == memberId) {
            showToast('A person cannot be their own parent', 'error');
            return;
        }

        if (parent1 && parent1 === parent2) {
            showToast('Mother and Father cannot be the same person', 'error');
            return;
        }

        // Validate dates
        var birthDate = $('#birth_date').val();
        var deathDate = $('#death_date').val();

        if (birthDate && deathDate) {
            if (new Date(deathDate) < new Date(birthDate)) {
                showToast('Death date cannot be before birth date', 'error');
                return;
            }
        }

        // Submit
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="loading-spinner"></span> Updating...');

        var data = $(this).serializeArray();
        data.push(
            {name: 'action', value: 'update_family_member'},
            {name: 'nonce', value: family_tree.nonce}
        );

        $.post(family_tree.ajax_url, data, function(res) {
            if (res.success) {
                showToast('Member updated successfully! 🎉', 'success');
                setTimeout(() => {
                    window.location.href = '/view-member?id=<?php echo $member_id; ?>';
                }, 1200);
            } else {
                showToast('Error: ' + (res.data || 'Failed to update member'), 'error');
                btn.prop('disabled', false).html(originalText);
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).html(originalText);
        });
    });

    // Helper function
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
