/**
 * Family Tree - Marriages JavaScript
 * Handles CRUD operations for marriages with modal UI
 */

jQuery(document).ready(function($){

    // Add Marriage - Open Modal
    $(document).on('click', '.btn-add-marriage', function(e){
        e.preventDefault();
        var memberId = $(this).data('member-id');

        // Set member ID in hidden field
        $('#current_member_id').val(memberId);

        // Open modal in add mode
        openMarriageModal('add');
    });

    // Edit Marriage - Open Modal with Data
    $(document).on('click', '.btn-edit-marriage', function(e){
        e.preventDefault();

        var marriageId = $(this).data('marriage-id');

        // Fetch marriage details first
        $.post(family_tree.ajax_url, {
            action: 'get_marriage_details',
            nonce: family_tree.nonce,
            marriage_id: marriageId
        }, function(res){
            if (!res.success) {
                showToast('Error: ' + (res.data || 'Unable to load marriage details'), 'error');
                return;
            }

            var marriage = res.data.marriage;

            // Open modal in edit mode with pre-filled data
            openMarriageModal('edit', {
                id: marriage.id,
                spouse_name: getSpouseNameFromMarriage(marriage),
                marriage_date: marriage.marriage_date,
                marriage_location: marriage.marriage_location,
                marriage_status: marriage.marriage_status,
                marriage_order: marriage.marriage_order,
                divorce_date: marriage.divorce_date,
                end_date: marriage.end_date,
                end_reason: marriage.end_reason,
                notes: marriage.notes
            });
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
        });
    });

    // Delete Marriage
    $(document).on('click', '.btn-delete-marriage', function(e){
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this marriage? This action cannot be undone.')) {
            return;
        }

        var marriageId = $(this).data('marriage-id');
        var btn = $(this);

        // Show loading
        btn.prop('disabled', true);
        var originalHtml = btn.html();
        btn.html('<span class="loading-spinner"></span>');

        $.post(family_tree.ajax_url, {
            action: 'delete_marriage',
            nonce: family_tree.nonce,
            marriage_id: marriageId
        }, function(res){
            if (res.success) {
                var message = (res.data && res.data.message) || 'Marriage deleted successfully';
                showToast(message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('Error: ' + (res.data || 'Unable to delete marriage'), 'error');
                btn.prop('disabled', false).html(originalHtml);
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).html(originalHtml);
        });
    });

    // Helper function to extract spouse name from marriage object
    function getSpouseNameFromMarriage(marriage) {
        var currentMemberId = $('#current_member_id').val();

        // Determine which spouse name to show
        if (marriage.husband_id == currentMemberId) {
            // Current member is husband, show wife
            if (marriage.wife_id) {
                var wife_middle = marriage.wife_middle_name ? marriage.wife_middle_name + ' ' : '';
                return marriage.wife_first_name + ' ' + wife_middle + marriage.wife_last_name;
            } else {
                return marriage.wife_name || '';
            }
        } else {
            // Current member is wife, show husband
            if (marriage.husband_id) {
                var husband_middle = marriage.husband_middle_name ? marriage.husband_middle_name + ' ' : '';
                return marriage.husband_first_name + ' ' + husband_middle + marriage.husband_last_name;
            } else {
                return marriage.husband_name || '';
            }
        }
    }
});

// Save Marriage Function (called from modal)
function saveMarriage() {
    var marriageId = jQuery('#marriage_id').val();
    var isEdit = marriageId && marriageId !== '';

    var memberId = jQuery('#current_member_id').val();
    var memberGender = jQuery('#current_member_gender').val();
    var spouseName = jQuery('#spouse_name').val();

    if (!spouseName) {
        showToast('Please enter spouse name', 'error');
        return;
    }

    // Prepare data
    var data = {
        action: isEdit ? 'update_marriage' : 'add_marriage',
        nonce: family_tree.nonce,
        marriage_date: jQuery('#marriage_date').val(),
        marriage_location: jQuery('#marriage_location').val(),
        marriage_status: jQuery('#marriage_status').val(),
        marriage_order: jQuery('#marriage_order').val() || 1,
        divorce_date: jQuery('#divorce_date').val(),
        end_date: jQuery('#end_date').val(),
        end_reason: jQuery('#end_reason').val(),
        notes: jQuery('#notes').val()
    };

    // Add marriage ID if editing
    if (isEdit) {
        data.marriage_id = marriageId;
    }

    // Determine husband/wife based on member gender
    if (memberGender && memberGender.toLowerCase() === 'male') {
        data.husband_id = memberId;
        data.wife_name = spouseName;
    } else if (memberGender && memberGender.toLowerCase() === 'female') {
        data.wife_id = memberId;
        data.husband_name = spouseName;
    } else {
        // Default to husband if gender unknown
        data.husband_id = memberId;
        data.wife_name = spouseName;
    }

    // Show loading
    var saveBtn = jQuery('#saveMarriageBtn');
    var originalText = jQuery('#saveMarriageBtnText').text();
    saveBtn.prop('disabled', true);
    jQuery('#saveMarriageBtnText').text('Saving...');

    // Submit
    jQuery.post(family_tree.ajax_url, data, function(res){
        if (res.success) {
            var message = (res.data && res.data.message) || (isEdit ? 'Marriage updated successfully' : 'Marriage added successfully');
            showToast(message, 'success');
            closeMarriageModal();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Error: ' + (res.data || 'Unable to save marriage'), 'error');
            saveBtn.prop('disabled', false);
            jQuery('#saveMarriageBtnText').text(originalText);
        }
    }).fail(function() {
        showToast('Connection error. Please try again.', 'error');
        saveBtn.prop('disabled', false);
        jQuery('#saveMarriageBtnText').text(originalText);
    });
}
