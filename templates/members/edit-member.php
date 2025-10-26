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

            <div class="form-row form-row-3">
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

                <div class="form-group">
                    <label class="form-label" for="clan_surname_id">Surname</label>
                    <select id="clan_surname_id" name="clan_surname_id">
                        <option value="">-- Select Surname --</option>
                    </select>
                    <small class="form-help">Auto-filled from surname; editable</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label required">Gender</label>
                <div style="display: flex; gap: var(--spacing-lg); margin-top: var(--spacing-sm);">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="radio" name="gender" value="Male" required <?php echo ($member->gender == 'Male') ? 'checked' : ''; ?> style="margin-right: var(--spacing-xs);">
                        <span>♂️ Male</span>
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="radio" name="gender" value="Female" required <?php echo ($member->gender == 'Female') ? 'checked' : ''; ?> style="margin-right: var(--spacing-xs);">
                        <span>♀️ Female</span>
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="radio" name="gender" value="Other" required <?php echo ($member->gender == 'Other') ? 'checked' : ''; ?> style="margin-right: var(--spacing-xs);">
                        <span>⚧️ Other</span>
                    </label>
                </div>
                <small class="form-help">Gender is required for proper family tree relationships</small>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer; margin-top: var(--spacing-md);">
                    <input type="checkbox" id="is_adopted" name="is_adopted" value="1" <?php echo ($member->is_adopted ?? 0) ? 'checked' : ''; ?> style="margin-right: var(--spacing-sm);">
                    <span>🤝 This person is adopted</span>
                </label>
                <small class="form-help">Check if this member was adopted by the parents listed below</small>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="section">
            <h2 class="section-title">👤 Personal Information</h2>

            <div class="form-group">
                <label class="form-label required" for="first_name">First Name</label>
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    required
                    value="<?php echo esc_attr($member->first_name); ?>"
                    placeholder="e.g., Pramila"
                >
                <small class="form-help">Full name will be: First Name + Father's First Name + Clan Surname</small>
            </div>

            <!-- Hidden fields for auto-populated middle and last name -->
            <input type="hidden" id="middle_name" name="middle_name" value="<?php echo esc_attr($member->middle_name ?? ''); ?>">
            <input type="hidden" id="last_name" name="last_name" value="<?php echo esc_attr($member->last_name); ?>">

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="parent1_id">Father's Name</label>
                    <select id="parent1_id" name="parent1_id" class="select2-parent">
                        <option value="">-- Select Father --</option>
                        <?php foreach ($all_members as $m): ?>
                            <?php if ($m->id != $member_id && $m->gender === 'Male'): ?>
                                <option value="<?php echo intval($m->id); ?>" data-firstname="<?php echo esc_attr($m->first_name); ?>" <?php echo ($member->parent1_id == $m->id) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($m->first_name . ' ' . ($m->middle_name ? $m->middle_name . ' ' : '') . $m->last_name . ' (b. ' . ($m->birth_date ?: 'N/A') . ')'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-help">Father's first name will be used as middle name. Leave empty for root ancestors.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="parent2">Mother's Name</label>

                    <?php
                    // Determine initial value for mother combined dropdown
                    $initial_mother_value = '';
                    if (!empty($member->parent2_id)) {
                        $initial_mother_value = 'member_' . $member->parent2_id;
                    } elseif (!empty($member->parent2_name)) {
                        $initial_mother_value = 'text_' . $member->parent2_name;
                    }
                    ?>

                    <!-- Combined dropdown with tagging (Select2 with tags) -->
                    <select id="parent2_combined" name="parent2_combined" class="select2-tags">
                        <option value="">-- Select or type mother's name --</option>
                        <?php if (!empty($member->parent2_name) && empty($member->parent2_id)): ?>
                            <option value="text_<?php echo esc_attr($member->parent2_name); ?>" selected><?php echo esc_html($member->parent2_name); ?> (text)</option>
                        <?php endif; ?>
                        <?php foreach ($all_members as $m): ?>
                            <?php if ($m->id != $member_id && $m->gender === 'Female'): ?>
                                <option value="member_<?php echo intval($m->id); ?>" <?php echo ($member->parent2_id == $m->id) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($m->first_name . ' ' . ($m->middle_name ? $m->middle_name . ' ' : '') . $m->last_name . ' (b. ' . ($m->birth_date ?: 'N/A') . ')'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>

                    <!-- Hidden fields to store the actual values -->
                    <input type="hidden" id="parent2_id" name="parent2_id" value="<?php echo esc_attr($member->parent2_id ?? ''); ?>">
                    <input type="hidden" id="parent2_name" name="parent2_name" value="<?php echo esc_attr($member->parent2_name ?? ''); ?>">

                    <small class="form-help">Select from list (populated based on father's marriages) or type a new name</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="nickname">Nickname</label>
                <input
                    type="text"
                    id="nickname"
                    name="nickname"
                    value="<?php echo esc_attr($member->nickname ?? ''); ?>"
                    placeholder="e.g., Sonu"
                >
                <small class="form-help">Common name or nickname (optional)</small>
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
                    <?php
                    // Get marriages for this member to determine marital status
                    $marriages = FamilyTreeDatabase::get_marriages_for_member($member_id);
                    $latest_marriage = !empty($marriages) ? end($marriages) : null;
                    $current_status = 'unmarried';
                    if ($latest_marriage) {
                        $current_status = $latest_marriage->marriage_status ?? 'married';
                    }
                    ?>
                    <label class="form-label" for="marital_status">Marital Status</label>
                    <select id="marital_status" name="marital_status">
                        <option value="unmarried" <?php echo ($current_status === 'unmarried') ? 'selected' : ''; ?>>Unmarried</option>
                        <option value="married" <?php echo ($current_status === 'married') ? 'selected' : ''; ?>>Married</option>
                        <option value="divorced" <?php echo ($current_status === 'divorced') ? 'selected' : ''; ?>>Divorced</option>
                        <option value="widowed" <?php echo ($current_status === 'widowed') ? 'selected' : ''; ?>>Widowed</option>
                    </select>
                    <small class="form-help">Current marital status</small>
                </div>
            </div>
        </div>

        <!-- Multiple Marriages Section -->
        <div class="section">
            <h2 class="section-title">💍 Marriages</h2>
            <p class="section-description">Manage marriage details. You can add multiple marriages if applicable.</p>

            <!-- Maiden Name (for females only) -->
            <div class="form-group" id="maiden_name_group" style="display: <?php echo ($member->gender === 'Female') ? 'block' : 'none'; ?>;">
                <label class="form-label" for="maiden_name">Maiden Name (Birth Surname)</label>
                <input
                    type="text"
                    id="maiden_name"
                    name="maiden_name"
                    value="<?php echo esc_attr($member->maiden_name ?? ''); ?>"
                    placeholder="Surname before first marriage"
                >
                <small class="form-help">Birth surname before marriage (automatically shown for female members)</small>
            </div>

            <!-- Existing marriages will be loaded here via JavaScript -->
            <div id="marriages_container">
                <!-- Marriage entries will be populated via JavaScript -->
            </div>

            <button type="button" id="add_marriage_btn" class="btn btn-outline btn-sm">
                ➕ Add Marriage
            </button>

            <!-- Hidden data for existing marriages -->
            <script type="application/json" id="existing_marriages_data">
                <?php
                $marriages = FamilyTreeDatabase::get_marriages_for_member($member_id);
                $marriages_data = [];
                foreach ($marriages as $marriage) {
                    $spouse_name = '';
                    if ($marriage->husband_id == $member_id) {
                        // Current member is husband, show wife
                        if ($marriage->wife_id) {
                            $wife_middle = !empty($marriage->wife_middle_name) ? $marriage->wife_middle_name . ' ' : '';
                            $spouse_name = $marriage->wife_first_name . ' ' . $wife_middle . $marriage->wife_last_name;
                        } else {
                            $spouse_name = $marriage->wife_name ?? '';
                        }
                    } else {
                        // Current member is wife, show husband
                        if ($marriage->husband_id) {
                            $husband_middle = !empty($marriage->husband_middle_name) ? $marriage->husband_middle_name . ' ' : '';
                            $spouse_name = $marriage->husband_first_name . ' ' . $husband_middle . $marriage->husband_last_name;
                        } else {
                            $spouse_name = $marriage->husband_name ?? '';
                        }
                    }

                    $marriages_data[] = [
                        'id' => $marriage->id,
                        'spouse_name' => $spouse_name,
                        'marriage_date' => $marriage->marriage_date ?? '',
                        'marriage_location' => $marriage->marriage_location ?? '',
                        'marriage_status' => $marriage->marriage_status ?? 'married',
                        'divorce_date' => $marriage->divorce_date ?? '',
                        'notes' => $marriage->notes ?? ''
                    ];
                }
                echo json_encode($marriages_data);
                ?>
            </script>
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

    var marriageCounter = 0; // Counter for dynamic marriage entries
    var existingMarriagesMap = {}; // Track existing marriages by ID

    // =========================================
    // Initialize Select2
    // =========================================

    // Initialize Select2 for father dropdown
    $('#parent1_id').select2({
        placeholder: '--- Search and select father ---',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        language: {
            noResults: () => 'No male members found'
        }
    });

    // Initialize Select2 for mother dropdown with tags (allows custom text)
    $('#parent2_combined').select2({
        placeholder: '--- Select or type mother\'s name ---',
        allowClear: true,
        width: '100%',
        tags: true,
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') {
                return null;
            }
            return {
                id: 'text_' + term,
                text: term + ' (new name)',
                newTag: true
            }
        }
    });

    // Handle mother selection - store in hidden fields
    $('#parent2_combined').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue) {
            if (selectedValue.startsWith('member_')) {
                // Existing member selected
                var memberId = selectedValue.replace('member_', '');
                $('#parent2_id').val(memberId);
                $('#parent2_name').val('');
            } else if (selectedValue.startsWith('text_')) {
                // New text entered
                var name = selectedValue.replace('text_', '');
                $('#parent2_id').val('');
                $('#parent2_name').val(name);
            } else {
                // Direct text tag
                $('#parent2_id').val('');
                $('#parent2_name').val(selectedValue);
            }
        } else {
            // Cleared
            $('#parent2_id').val('');
            $('#parent2_name').val('');
        }
    });

    // =========================================
    // Auto-populate middle_name and last_name
    // =========================================

    // Auto-populate middle_name when father is selected
    $('#parent1_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var fatherFirstName = selectedOption.data('firstname') || '';
        var fatherId = $(this).val();

        // Set middle_name to father's first name
        $('#middle_name').val(fatherFirstName);
        console.log('Middle name auto-populated:', fatherFirstName);
        updateFullNamePreview();

        // Fetch father's marriages for smart mother selection (only if father changed)
        if (fatherId) {
            fetchAndPopulateMotherFromMarriages(fatherId);
        }
    });

    // Auto-populate last_name when clan surname is selected
    $('#clan_surname_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var surname = selectedOption.data('lastname') || '';

        // Set last_name to clan surname
        $('#last_name').val(surname);
        console.log('Last name auto-populated:', surname);
        updateFullNamePreview();
    });

    // Show/hide maiden name based on gender
    $('input[name="gender"]').on('change', function() {
        var gender = $(this).val();
        if (gender === 'Female') {
            $('#maiden_name_group').slideDown();
        } else {
            $('#maiden_name_group').slideUp();
        }
    });

    // Update full name preview
    function updateFullNamePreview() {
        var firstName = $('#first_name').val() || '';
        var middleName = $('#middle_name').val() || '';
        var lastName = $('#last_name').val() || '';

        var fullName = [firstName, middleName, lastName].filter(n => n).join(' ');
        if (fullName) {
            console.log('Full name preview:', fullName);
        }
    }

    // =========================================
    // Smart Mother Selection
    // =========================================

    // Fetch father's marriages and populate mother dropdown
    function fetchAndPopulateMotherFromMarriages(fatherId) {
        $.post(family_tree.ajax_url, {
            action: 'get_marriages_for_member',
            nonce: family_tree.nonce,
            member_id: fatherId
        }, function(res) {
            if (res.success && res.data.marriages && res.data.marriages.length > 0) {
                var marriages = res.data.marriages;
                populateMotherFromMarriages(marriages);

                if (marriages.length === 1) {
                    showToast('Mother auto-selected from father\'s marriage', 'info');
                } else {
                    showToast('Select mother from father\'s ' + marriages.length + ' marriages', 'info');
                }
            }
        }).fail(function() {
            console.log('Failed to fetch marriages for father');
        });
    }

    // Populate mother dropdown with wives from marriages
    function populateMotherFromMarriages(marriages) {
        // Clear existing options except the default
        $('#parent2_combined').find('option').not(':first').remove();

        // Add wives from marriages
        marriages.forEach(function(marriage, index) {
            var option;
            if (marriage.wife_id) {
                // Wife exists as member in system
                var wifeName = marriage.wife_first_name + ' ' +
                              (marriage.wife_middle_name ? marriage.wife_middle_name + ' ' : '') +
                              marriage.wife_last_name;
                var status = marriage.marriage_status ? ' (' + marriage.marriage_status + ')' : '';
                option = new Option(wifeName + status, 'member_' + marriage.wife_id, false, index === 0);
            } else if (marriage.wife_name) {
                // Wife is text-only (not in system)
                option = new Option(marriage.wife_name + ' (from marriage record)', 'text_' + marriage.wife_name, false, index === 0);
            }

            if (option) {
                $('#parent2_combined').append(option);
            }
        });

        // Trigger Select2 update
        $('#parent2_combined').trigger('change');

        // Auto-select first marriage if only one exists
        if (marriages.length === 1) {
            $('#parent2_combined').trigger('change');
        }
    }

    // =========================================
    // Dynamic Marriage Entries
    // =========================================

    // Load existing marriages on page load
    var existingMarriagesData = JSON.parse($('#existing_marriages_data').text() || '[]');
    existingMarriagesData.forEach(function(marriage, idx) {
        marriageCounter++;
        existingMarriagesMap[marriageCounter] = marriage.id; // Track existing marriage IDs
        addMarriageEntry(marriageCounter, marriage, true);
    });

    // Add a new marriage entry
    $('#add_marriage_btn').on('click', function() {
        marriageCounter++;
        addMarriageEntry(marriageCounter);
    });

    function addMarriageEntry(index, data = {}, isExisting = false) {
        var html = `
            <div class="marriage-entry" data-index="${index}" data-marriage-id="${data.id || ''}" style="border: 1px solid var(--color-border); padding: var(--spacing-md); margin-bottom: var(--spacing-md); border-radius: 4px; position: relative;">
                <button type="button" class="remove-marriage-btn" style="position: absolute; top: 10px; right: 10px; background: var(--color-error); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px; line-height: 1;">×</button>

                <h4 style="margin-bottom: var(--spacing-md);">Marriage #${index} ${isExisting ? '(Existing)' : '(New)'}</h4>

                ${isExisting ? '<input type="hidden" name="marriages[' + index + '][id]" value="' + (data.id || '') + '">' : ''}

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Spouse Name</label>
                        <input type="text" name="marriages[${index}][spouse_name]" placeholder="Full name of spouse" value="${escapeHtml(data.spouse_name || '')}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Marriage Date</label>
                        <input type="date" name="marriages[${index}][marriage_date]" value="${data.marriage_date || ''}">
                    </div>
                </div>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Marriage Location</label>
                        <input type="text" name="marriages[${index}][marriage_location]" placeholder="City, Country" value="${escapeHtml(data.marriage_location || '')}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Marriage Status</label>
                        <select name="marriages[${index}][marriage_status]" class="marriage-status-select">
                            <option value="married" ${data.marriage_status === 'married' ? 'selected' : ''}>Married</option>
                            <option value="divorced" ${data.marriage_status === 'divorced' ? 'selected' : ''}>Divorced</option>
                            <option value="widowed" ${data.marriage_status === 'widowed' ? 'selected' : ''}>Widowed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group divorce-date-field" style="display: ${data.marriage_status === 'divorced' ? 'block' : 'none'};">
                    <label class="form-label">Divorce Date</label>
                    <input type="date" name="marriages[${index}][divorce_date]" value="${data.divorce_date || ''}">
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="marriages[${index}][notes]" rows="2" placeholder="Additional details about this marriage...">${escapeHtml(data.notes || '')}</textarea>
                </div>
            </div>
        `;

        $('#marriages_container').append(html);

        // Add event listener for divorce date toggle
        $(`[name="marriages[${index}][marriage_status]"]`).on('change', function() {
            var divorceField = $(this).closest('.marriage-entry').find('.divorce-date-field');
            if ($(this).val() === 'divorced') {
                divorceField.slideDown();
            } else {
                divorceField.slideUp();
                divorceField.find('input').val('');
            }
        });
    }

    // Remove marriage entry
    $(document).on('click', '.remove-marriage-btn', function() {
        var marriageEntry = $(this).closest('.marriage-entry');
        var marriageId = marriageEntry.data('marriage-id');

        var message = 'Are you sure you want to remove this marriage entry?';
        if (marriageId) {
            message += ' This will permanently delete the marriage record from the database.';
        }

        if (confirm(message)) {
            // If existing marriage, delete from database
            if (marriageId) {
                $.post(family_tree.ajax_url, {
                    action: 'delete_marriage',
                    nonce: family_tree.nonce,
                    marriage_id: marriageId
                }, function(res) {
                    if (res.success) {
                        marriageEntry.slideUp(function() {
                            $(this).remove();
                            renumberMarriageEntries();
                        });
                        showToast('Marriage deleted successfully', 'success');
                    } else {
                        showToast('Failed to delete marriage: ' + (res.data || 'Unknown error'), 'error');
                    }
                }).fail(function() {
                    showToast('Connection error while deleting marriage', 'error');
                });
            } else {
                // Just remove from UI (not yet saved)
                marriageEntry.slideUp(function() {
                    $(this).remove();
                    renumberMarriageEntries();
                });
            }
        }
    });

    // Renumber marriage entries after deletion
    function renumberMarriageEntries() {
        $('.marriage-entry').each(function(idx) {
            var isExisting = $(this).data('marriage-id') ? '(Existing)' : '(New)';
            $(this).find('h4').text('Marriage #' + (idx + 1) + ' ' + isExisting);
        });
    }

    // =========================================
    // Clan Details Loading
    // =========================================

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

    // =========================================
    // Form Submission
    // =========================================

    // Form submission
    $('#editMemberForm').on('submit', function(e) {
        e.preventDefault();

        // Validation
        if (!$('#first_name').val().trim()) {
            showToast('First name is required', 'error');
            return;
        }

        // Check if gender is selected
        if (!$('input[name="gender"]:checked').val()) {
            showToast('Gender is required', 'error');
            return;
        }

        // Check if clan is selected
        if (!$('#clan_id').val()) {
            showToast('Clan is required', 'error');
            return;
        }

        // Validate parent selection
        var parent1 = $('#parent1_id').val();
        var parent2 = $('#parent2_id').val();
        var memberId = <?php echo $member_id; ?>;

        if (parent1 == memberId || parent2 == memberId) {
            showToast('A person cannot be their own parent', 'error');
            return;
        }

        if (parent1 && parent2 && parent1 === parent2) {
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
                // After member is updated, save marriages
                saveMarriages(memberId, function(success) {
                    if (success) {
                        showToast('Member and marriages updated successfully! 🎉', 'success');
                    } else {
                        showToast('Member updated but some marriages failed to save', 'warning');
                    }
                    setTimeout(() => {
                        window.location.href = '/view-member?id=<?php echo $member_id; ?>';
                    }, 1500);
                });
            } else {
                showToast('Error: ' + (res.data || 'Failed to update member'), 'error');
                btn.prop('disabled', false).html(originalText);
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).html(originalText);
        });
    });

    // Save marriages to database
    function saveMarriages(memberId, callback) {
        var marriages = [];
        var gender = $('input[name="gender"]:checked').val();

        // Collect marriage data from form
        $('.marriage-entry').each(function(idx) {
            var index = $(this).data('index');
            var marriageId = $(this).data('marriage-id');
            var spouse_name = $(this).find(`[name="marriages[${index}][spouse_name]"]`).val();

            if (spouse_name) { // Only save if spouse name is provided
                var marriageData = {
                    spouse_name: spouse_name,
                    marriage_date: $(this).find(`[name="marriages[${index}][marriage_date]"]`).val(),
                    marriage_location: $(this).find(`[name="marriages[${index}][marriage_location]"]`).val(),
                    marriage_status: $(this).find(`[name="marriages[${index}][marriage_status]"]`).val(),
                    divorce_date: $(this).find(`[name="marriages[${index}][divorce_date]"]`).val(),
                    notes: $(this).find(`[name="marriages[${index}][notes]"]`).val(),
                    marriage_order: idx + 1
                };

                // Set husband/wife based on member gender
                if (gender === 'Male') {
                    marriageData.husband_id = memberId;
                    marriageData.wife_name = spouse_name;
                } else if (gender === 'Female') {
                    marriageData.wife_id = memberId;
                    marriageData.husband_name = spouse_name;
                } else {
                    marriageData.husband_id = memberId;
                    marriageData.wife_name = spouse_name;
                }

                // Determine if update or add
                if (marriageId) {
                    marriageData.action = 'update_marriage';
                    marriageData.marriage_id = marriageId;
                } else {
                    marriageData.action = 'add_marriage';
                }

                marriageData.nonce = family_tree.nonce;
                marriages.push(marriageData);
            }
        });

        // If no marriages, callback immediately
        if (marriages.length === 0) {
            callback(true);
            return;
        }

        // Save each marriage
        var savePromises = [];
        marriages.forEach(function(marriage) {
            savePromises.push($.post(family_tree.ajax_url, marriage));
        });

        // Wait for all marriages to be saved
        $.when.apply($, savePromises).done(function() {
            callback(true);
        }).fail(function() {
            callback(false);
        });
    }

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
