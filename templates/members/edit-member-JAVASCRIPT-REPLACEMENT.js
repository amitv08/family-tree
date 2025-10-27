// REPLACEMENT JAVASCRIPT FOR edit-member.php
// This replaces the entire <script> section starting from jQuery(function($) {

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
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
});
