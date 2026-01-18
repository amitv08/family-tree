<?php
/**
 * Family Tree Plugin - Browse Members Page
 * List all family members with management options
 * Updated with professional design system
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

use FamilyTree\Repositories\MemberRepository;
use FamilyTree\Repositories\ClanRepository;

$can_manage = current_user_can('manage_family') || current_user_can('family_super_admin');
$member_repo = new MemberRepository();
$clan_repo = new ClanRepository();

$members = $member_repo->get_members(1000, 0, true); // Include deleted for admin view

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Members'],
];
$page_title = '👥 Family Members';
$page_actions = current_user_can('edit_family_members') 
    ? '<a href="/add-member" class="btn btn-primary btn-sm">➕ Add Member</a>' 
    : '';

ob_start();
?>

<!-- Search/Filter Bar -->
<div style="display: flex; gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl); flex-wrap: wrap;">
    <div class="form-group" style="flex: 1; min-width: 250px; margin: 0;">
        <input 
            type="text" 
            id="memberSearch" 
            placeholder="🔍 Search by name..."
            style="width: 100%;"
        >
    </div>

    <div style="display: flex; align-items: flex-end; gap: var(--spacing-md);">
        <button id="clearSearch" class="btn btn-outline btn-sm">Clear</button>
    </div>
</div>

<?php if (empty($members)): ?>
    <!-- Empty State -->
    <div class="alert alert-info">
        <div class="alert-icon">📋</div>
        <div class="alert-content">
            <div class="alert-title">No Members Yet</div>
            <p class="alert-message">
                Start by adding your first family member to build your family tree.
            </p>
            <?php if (current_user_can('edit_family_members')): ?>
                <a href="/add-member" class="btn btn-primary" style="margin-top: var(--spacing-lg);">
                    ➕ Add Your First Member
                </a>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Stats Bar -->
    <div class="grid grid-3" style="margin-bottom: var(--spacing-2xl);">
        <div class="stat-card">
            <div class="stat-card-icon">👥</div>
            <div class="stat-card-value"><?php echo count($members); ?></div>
            <p class="stat-card-label">Total Members</p>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon">🟢</div>
            <div class="stat-card-value">
                <?php
                $living = 0;
                foreach ($members as $m) {
                    if (empty($m->death_date) && empty($m->is_deleted)) {
                        $living++;
                    }
                }
                echo $living;
                ?>
            </div>
            <p class="stat-card-label">Living</p>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon">⚫</div>
            <div class="stat-card-value">
                <?php
                $deceased = 0;
                foreach ($members as $m) {
                    if (!empty($m->death_date) && empty($m->is_deleted)) {
                        $deceased++;
                    }
                }
                echo $deceased;
                ?>
            </div>
            <p class="stat-card-label">Deceased</p>
        </div>
    </div>

    <!-- Members Table -->
    <div style="overflow-x: auto;">
        <table class="table" id="membersTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Clan</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                    <tr class="member-row" data-member-id="<?php echo intval($m->id); ?>" data-member-name="<?php echo esc_attr(strtolower($m->first_name . ' ' . $m->last_name)); ?>" style="<?php echo !empty($m->is_deleted) ? 'opacity: 0.5;' : ''; ?>">
                        <td>
                            <strong><?php echo esc_html($m->first_name . ' ' . $m->last_name); ?></strong>
                            <?php if (!empty($m->is_deleted)): ?>
                                <span class="badge badge-warning">🗑️ Deleted</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $gender_display = match($m->gender) {
                                'Male' => '♂️ Male',
                                'Female' => '♀️ Female',
                                'Other' => '⚧️ Other',
                                default => '-'
                            };
                            echo esc_html($gender_display);
                            ?>
                        </td>
                        <td><?php echo esc_html($m->birth_date ?: '-'); ?></td>
                        <td><?php echo esc_html($clan_repo->get_clan_name($m->clan_id)); ?></td>
                        <td>
                            <?php if (!empty($m->death_date)): ?>
                                <span class="badge badge-danger">⚫ Deceased</span>
                            <?php elseif (empty($m->is_deleted)): ?>
                                <span class="badge badge-success">🟢 Living</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" style="gap: var(--spacing-sm);">
                                <a href="/view-member?id=<?php echo intval($m->id); ?>" class="btn btn-sm btn-outline">
                                    👁️ View
                                </a>
                                <?php if (!$m->is_deleted && current_user_can('edit_family_members')): ?>
                                    <a href="/edit-member?id=<?php echo intval($m->id); ?>" class="btn btn-sm btn-outline">
                                        ✏️ Edit
                                    </a>
                                <?php endif; ?>
                                <?php if ($can_manage): ?>
                                    <?php if (!$m->is_deleted): ?>
                                        <button class="btn btn-sm btn-danger btn-delete-member" data-id="<?php echo intval($m->id); ?>">
                                            🗑️ Delete
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success btn-restore-member" data-id="<?php echo intval($m->id); ?>">
                                            ↩️ Restore
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    // Search functionality
    $('#memberSearch').on('keyup', function() {
        var query = $(this).val().toLowerCase();
        
        if (!query) {
            $('.member-row').show();
            return;
        }

        $('.member-row').each(function() {
            var memberName = $(this).data('member-name');
            if (memberName.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Clear search
    $('#clearSearch').on('click', function() {
        $('#memberSearch').val('').trigger('keyup').focus();
    });

    // Soft delete
    $(document).on('click', '.btn-delete-member', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this member? This can be restored later.')) return;

        var id = $(this).data('id');
        var btn = $(this);

        btn.prop('disabled', true);
        var originalText = btn.html();
        btn.html('<span class="loading-spinner"></span>');

        $.post(family_tree.ajax_url, {
            action: 'soft_delete_member',
            nonce: family_tree.nonce,
            member_id: id
        }, function(res) {
            if (res.success) {
                showToast('Member deleted successfully', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('Error: ' + (res.data || 'Unable to delete'), 'error');
                btn.prop('disabled', false).html(originalText);
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).html(originalText);
        });
    });

    // Restore
    $(document).on('click', '.btn-restore-member', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        var btn = $(this);

        btn.prop('disabled', true);
        var originalText = btn.html();
        btn.html('<span class="loading-spinner"></span>');

        $.post(family_tree.ajax_url, {
            action: 'restore_member',
            nonce: family_tree.nonce,
            member_id: id
        }, function(res) {
            if (res.success) {
                showToast('Member restored successfully', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('Error: ' + (res.data || 'Unable to restore'), 'error');
                btn.prop('disabled', false).html(originalText);
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).html(originalText);
        });
    });
});
</script>

<?php
$page_content = ob_get_clean();
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>