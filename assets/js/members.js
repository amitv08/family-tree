jQuery(document).ready(function($){
    
    // Soft delete
    $(document).on('click', '.btn-delete-member', function(e){
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this member? This can be restored later.')) return;
        
        var id = $(this).data('id');
        var btn = $(this);
        
        // Show loading
        btn.prop('disabled', true);
        var originalText = btn.text();
        btn.html('<span class="loading-spinner"></span>');
        
        $.post(family_tree.ajax_url, { 
            action: 'soft_delete_member', 
            nonce: family_tree.nonce, 
            member_id: id 
        }, function(res){
            if (res.success) {
                showToast('Member deleted successfully', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('Error: ' + (res.data || 'Unable to delete'), 'error');
                btn.prop('disabled', false).text(originalText);
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).text(originalText);
        });
    });

    // Restore
    $(document).on('click', '.btn-restore-member', function(e){
        e.preventDefault();
        
        var id = $(this).data('id');
        var btn = $(this);
        
        btn.prop('disabled', true);
        var originalText = btn.text();
        btn.html('<span class="loading-spinner"></span>');
        
        $.post(family_tree.ajax_url, { 
            action: 'restore_member', 
            nonce: family_tree.nonce, 
            member_id: id 
        }, function(res){
            if (res.success) {
                showToast('Member restored successfully', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('Error: ' + (res.data || 'Unable to restore'), 'error');
                btn.prop('disabled', false).text(originalText);
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).text(originalText);
        });
    });
});