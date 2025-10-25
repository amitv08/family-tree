/**
 * Family Tree - Marriages JavaScript
 * Handles CRUD operations for marriages
 */

jQuery(document).ready(function($){

    // Add Marriage
    $(document).on('click', '.btn-add-marriage', function(e){
        e.preventDefault();

        var memberId = $(this).data('member-id');
        var memberGender = $(this).data('member-gender'); // Optional: to pre-fill husband/wife

        // Simple prompt-based form (can be enhanced to modal later)
        var proceed = confirm('Add a new marriage for this member?');
        if (!proceed) return;

        // Collect data via prompts (temporary - should be modal form)
        var spouseName = prompt('Enter spouse name (or leave blank to select from members):');
        var marriageDate = prompt('Marriage date (YYYY-MM-DD or leave blank):');
        var marriageLocation = prompt('Marriage location (optional):');
        var marriageStatus = prompt('Status (married/divorced/widowed/annulled):', 'married');

        var data = {
            action: 'add_marriage',
            nonce: family_tree.nonce,
        };

        // Determine husband/wife based on member gender (simplified)
        // In a real modal, we'd have proper dropdowns
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

        if (marriageDate) data.marriage_date = marriageDate;
        if (marriageLocation) data.marriage_location = marriageLocation;
        if (marriageStatus) data.marriage_status = marriageStatus;

        // Submit
        $.post(family_tree.ajax_url, data, function(res){
            if (res.success) {
                showToast('Marriage added successfully', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('Error: ' + (res.data || 'Unable to add marriage'), 'error');
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
        });
    });

    // Edit Marriage
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
                showToast('Error: Unable to load marriage details', 'error');
                return;
            }

            var marriage = res.data.marriage;

            // Prompt for updates
            var marriageDate = prompt('Marriage date (YYYY-MM-DD):', marriage.marriage_date || '');
            var marriageLocation = prompt('Marriage location:', marriage.marriage_location || '');
            var marriageStatus = prompt('Status (married/divorced/widowed/annulled):', marriage.marriage_status || 'married');
            var divorceDate = null;
            var endDate = null;
            var endReason = null;
            var notes = null;

            if (marriageStatus === 'divorced') {
                divorceDate = prompt('Divorce date (YYYY-MM-DD or leave blank):', marriage.divorce_date || '');
            }

            if (marriageStatus === 'widowed') {
                endDate = prompt('End date (YYYY-MM-DD or leave blank):', marriage.end_date || '');
                endReason = prompt('End reason:', marriage.end_reason || 'death of spouse');
            }

            notes = prompt('Notes (optional):', marriage.notes || '');

            var data = {
                action: 'update_marriage',
                nonce: family_tree.nonce,
                marriage_id: marriageId,
                marriage_status: marriageStatus,
            };

            if (marriageDate) data.marriage_date = marriageDate;
            if (marriageLocation) data.marriage_location = marriageLocation;
            if (divorceDate) data.divorce_date = divorceDate;
            if (endDate) data.end_date = endDate;
            if (endReason) data.end_reason = endReason;
            if (notes) data.notes = notes;

            // Submit update
            $.post(family_tree.ajax_url, data, function(res){
                if (res.success) {
                    showToast('Marriage updated successfully', 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast('Error: ' + (res.data || 'Unable to update marriage'), 'error');
                }
            }).fail(function() {
                showToast('Connection error. Please try again.', 'error');
            });
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
                showToast('Marriage deleted successfully', 'success');
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
});
