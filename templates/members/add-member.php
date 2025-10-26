<?php
/**
 * Family Tree Plugin - Add Member Page
 * Create a new family member with full details
 * Updated with professional design system
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

if (!current_user_can('edit_family_members')) {
    wp_die('You do not have permission to add members.');
}

$clans = FamilyTreeDatabase::get_all_clans_simple();
$all_members = FamilyTreeDatabase::get_members(2000, 0);

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Members', 'url' => '/browse-members'],
    ['label' => 'Add Member'],
];
$page_title = '➕ Add Family Member';
$page_actions = '<a href="/browse-members" class="btn btn-outline btn-sm">← Back to Members</a>';

ob_start();
?>

<div class="container container-lg">
    <form id="addMemberForm" class="form">
        <!-- Clan Information Section -->
        <div class="section">
            <h2 class="section-title">🏰 Clan Information</h2>

            <div class="form-row form-row-3">
                <div class="form-group">
                    <label class="form-label required" for="clan_id">Clan</label>
                    <select id="clan_id" name="clan_id" required>
                        <option value="">-- Select Clan --</option>
                        <?php foreach ($clans as $c): ?>
                            <option value="<?php echo intval($c->id); ?>">
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
                        <input type="radio" name="gender" value="Male" required style="margin-right: var(--spacing-xs);">
                        <span>♂️ Male</span>
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="radio" name="gender" value="Female" required style="margin-right: var(--spacing-xs);">
                        <span>♀️ Female</span>
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="radio" name="gender" value="Other" required style="margin-right: var(--spacing-xs);">
                        <span>⚧️ Other</span>
                    </label>
                </div>
                <small class="form-help">Gender is required for proper family tree relationships</small>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer; margin-top: var(--spacing-md);">
                    <input type="checkbox" id="is_adopted" name="is_adopted" value="1" style="margin-right: var(--spacing-sm);">
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
                    placeholder="e.g., Pramila"
                >
                <small class="form-help">Full name will be: First Name + Father's First Name + Clan Surname</small>
            </div>

            <!-- Hidden fields for auto-populated middle and last name -->
            <input type="hidden" id="middle_name" name="middle_name">
            <input type="hidden" id="last_name" name="last_name">

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="parent1_id">Father's Name</label>
                    <select id="parent1_id" name="parent1_id" class="select2-parent">
                        <option value="">-- Select Father --</option>
                        <?php foreach ($all_members as $m): ?>
                            <?php if ($m->gender === 'Male'): ?>
                                <option value="<?php echo intval($m->id); ?>" data-firstname="<?php echo esc_attr($m->first_name); ?>">
                                    <?php echo esc_html($m->first_name . ' ' . ($m->middle_name ? $m->middle_name . ' ' : '') . $m->last_name . ' (b. ' . ($m->birth_date ?: 'N/A') . ')'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-help">Father's first name will be used as middle name. Leave empty for root ancestors.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="parent2">Mother's Name</label>

                    <!-- Combined dropdown with tagging (Select2 with tags) -->
                    <select id="parent2_combined" name="parent2_combined" class="select2-tags">
                        <option value="">-- Select or type mother's name --</option>
                        <?php foreach ($all_members as $m): ?>
                            <?php if ($m->gender === 'Female'): ?>
                                <option value="member_<?php echo intval($m->id); ?>">
                                    <?php echo esc_html($m->first_name . ' ' . ($m->middle_name ? $m->middle_name . ' ' : '') . $m->last_name . ' (b. ' . ($m->birth_date ?: 'N/A') . ')'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>

                    <!-- Hidden fields to store the actual values -->
                    <input type="hidden" id="parent2_id" name="parent2_id">
                    <input type="hidden" id="parent2_name" name="parent2_name">

                    <small class="form-help">Select from list (populated based on father's marriages) or type a new name</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="nickname">Nickname</label>
                <input
                    type="text"
                    id="nickname"
                    name="nickname"
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
                    <input type="date" id="birth_date" name="birth_date">
                </div>

                <div class="form-group">
                    <label class="form-label" for="death_date">Death Date</label>
                    <input type="date" id="death_date" name="death_date">
                    <small class="form-help">Leave empty if still living</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="marital_status">Marital Status</label>
                    <select id="marital_status" name="marital_status">
                        <option value="unmarried">Unmarried</option>
                        <option value="married">Married</option>
                        <option value="divorced">Divorced</option>
                        <option value="widowed">Widowed</option>
                    </select>
                    <small class="form-help">Current marital status</small>
                </div>
            </div>
        </div>

        <!-- Multiple Marriages Section -->
        <div class="section">
            <h2 class="section-title">💍 Marriages</h2>
            <p class="section-description">Add marriage details. You can add multiple marriages if applicable.</p>

            <!-- Maiden Name (for females only) -->
            <div class="form-group" id="maiden_name_group" style="display: none;">
                <label class="form-label" for="maiden_name">Maiden Name (Birth Surname)</label>
                <input
                    type="text"
                    id="maiden_name"
                    name="maiden_name"
                    placeholder="Surname before first marriage"
                >
                <small class="form-help">Birth surname before marriage (automatically shown for female members)</small>
            </div>

            <!-- Container for marriage entries -->
            <div id="marriages_container">
                <!-- Marriage entries will be added here dynamically -->
            </div>

            <button type="button" id="add_marriage_btn" class="btn btn-outline btn-sm">
                ➕ Add Marriage
            </button>
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
                    placeholder="Street address"
                >
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="city">City</label>
                    <input type="text" id="city" name="city" placeholder="e.g., London">
                </div>
                <div class="form-group">
                    <label class="form-label" for="state">State/Province</label>
                    <input type="text" id="state" name="state" placeholder="e.g., England">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="country">Country</label>
                    <input type="text" id="country" name="country" placeholder="e.g., United Kingdom">
                </div>
                <div class="form-group">
                    <label class="form-label" for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" placeholder="e.g., SW1A 1AA">
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
                ></textarea>
                <small class="form-help">Optional. Write a short biography or notes about this person</small>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                ➕ Add Member
            </button>
            <a href="/browse-members" class="btn btn-outline btn-lg">
                Cancel
            </a>
        </div>

        <!-- Message Area -->
        <div id="formMessage" style="margin-top: var(--spacing-lg);"></div>
    </form>
</div>

<script>
jQuery(function($) {
    console.log("Add Member form initialized");

    var marriageCounter = 0; // Counter for dynamic marriage entries

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

    // Auto-populate middle_name when father is selected
    $('#parent1_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var fatherFirstName = selectedOption.data('firstname') || '';

        // Set middle_name to father's first name
        $('#middle_name').val(fatherFirstName);

        // Show preview in console for debugging
        console.log('Middle name auto-populated:', fatherFirstName);
        updateFullNamePreview();

        // Fetch father's marriages for smart mother selection
        var fatherId = $(this).val();
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

        // Show preview in console for debugging
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
            $('#maiden_name').val(''); // Clear value if hidden
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

    // Add a new marriage entry
    $('#add_marriage_btn').on('click', function() {
        marriageCounter++;
        addMarriageEntry(marriageCounter);
    });

    function addMarriageEntry(index, data = {}) {
        var html = `
            <div class="marriage-entry" data-index="${index}" style="border: 1px solid var(--color-border); padding: var(--spacing-md); margin-bottom: var(--spacing-md); border-radius: 4px; position: relative;">
                <button type="button" class="remove-marriage-btn" style="position: absolute; top: 10px; right: 10px; background: var(--color-error); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px; line-height: 1;">×</button>

                <h4 style="margin-bottom: var(--spacing-md);">Marriage #${index}</h4>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Spouse Name</label>
                        <input type="text" name="marriages[${index}][spouse_name]" placeholder="Full name of spouse" value="${data.spouse_name || ''}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Marriage Date</label>
                        <input type="date" name="marriages[${index}][marriage_date]" value="${data.marriage_date || ''}">
                    </div>
                </div>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Marriage Location</label>
                        <input type="text" name="marriages[${index}][marriage_location]" placeholder="City, Country" value="${data.marriage_location || ''}">
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
                    <textarea name="marriages[${index}][notes]" rows="2" placeholder="Additional details about this marriage...">${data.notes || ''}</textarea>
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
        if (confirm('Are you sure you want to remove this marriage entry?')) {
            $(this).closest('.marriage-entry').slideUp(function() {
                $(this).remove();
                renumberMarriageEntries();
            });
        }
    });

    // Renumber marriage entries after deletion
    function renumberMarriageEntries() {
        $('.marriage-entry').each(function(idx) {
            $(this).find('h4').text('Marriage #' + (idx + 1));
        });
    }

    // Load clan details when clan selected
    function loadClanDetails(clanId) {
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
                    htmlL += '<option value="' + l.id + '">' + escapeHtml(l.location_name) + '</option>';
                });
                $('#clan_location_id').html(htmlL);

                var htmlS = '<option value="">-- Select Surname --</option>';
                surnames.forEach(function(s) {
                    htmlS += '<option value="' + s.id + '" data-lastname="' + escapeHtml(s.last_name) + '">' + escapeHtml(s.last_name) + '</option>';
                });
                $('#clan_surname_id').html(htmlS);
            } else {
                showToast('Error loading clan details: ' + res.data, 'error');
            }
        });
    }

    $('#clan_id').on('change', function() {
        loadClanDetails($(this).val());
    });

    // Note: clan_surname_id change handler already defined above in auto-populate logic

    // Form submission
    $('#addMemberForm').on('submit', function(e) {
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
        var parent1 = $('#parent2_id').val();
        var parent2 = $('#parent2_id').val();

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
        btn.prop('disabled', true).html('<span class="loading-spinner"></span> Adding...');

        var data = $(this).serializeArray();
        data.push(
            {name: 'action', value: 'add_family_member'},
            {name: 'nonce', value: family_tree.nonce}
        );

        $.post(family_tree.ajax_url, data, function(res) {
            if (res.success) {
                // After member is created, add marriages if any
                var memberId = res.data.member_id;
                saveMarriages(memberId, function(success) {
                    if (success) {
                        showToast('Member and marriages added successfully! 🎉', 'success');
                    } else {
                        showToast('Member added but some marriages failed to save', 'warning');
                    }
                    setTimeout(() => {
                        window.location.href = '/browse-members';
                    }, 1500);
                });
            } else {
                showToast('Error: ' + (res.data || 'Failed to add member'), 'error');
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
            marriage.action = 'add_marriage';
            marriage.nonce = family_tree.nonce;
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