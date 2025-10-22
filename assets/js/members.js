jQuery(document).ready(function($){
    // Soft delete
    $(document).on('click', '.btn-delete-member', function(e){
        e.preventDefault();
        if (!confirm('Are you sure you want to delete (soft) this member? This can be restored later.')) return;
        var id = $(this).data('id');
        $.post(family_tree.ajax_url, { action: 'soft_delete_member', nonce: family_tree.nonce, member_id: id }, function(res){
            if (res.success) location.reload();
            else alert('Error: ' + (res.data || 'Unable to delete'));
        });
    });

    // Restore
    $(document).on('click', '.btn-restore-member', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $.post(family_tree.ajax_url, { action: 'restore_member', nonce: family_tree.nonce, member_id: id }, function(res){
            if (res.success) location.reload();
            else alert('Error: ' + (res.data || 'Unable to restore'));
        });
    });
});