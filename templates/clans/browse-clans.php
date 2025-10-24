<?php
/**
 * Family Tree Plugin - Browse Clans Page
 * List all family clans with management options
 * Updated with professional design system
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

if (!current_user_can('manage_clans')) {
    wp_die('You do not have permission to manage clans.');
}

$clans = FamilyTreeClanDatabase::get_all_clans();

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Clans'],
];
$page_title = '🏰 Family Clans';
$page_actions = '<a href="/add-clan" class="btn btn-primary btn-sm">➕ Add New Clan</a>';

ob_start();
?>

<?php if (empty($clans)): ?>
    <!-- Empty State -->
    <div class="alert alert-info">
        <div class="alert-icon">📋</div>
        <div class="alert-content">
            <div class="alert-title">No Clans Yet</div>
            <p class="alert-message">
                Start by creating your first family clan. This will help organize members into family groups.
            </p>
            <a href="/add-clan" class="btn btn-primary" style="margin-top: var(--spacing-lg);">
                ➕ Create Your First Clan
            </a>
        </div>
    </div>

<?php else: ?>
    <!-- Stats Bar -->
    <div class="grid grid-3" style="margin-bottom: var(--spacing-2xl);">
        <div class="stat-card">
            <div class="stat-card-icon">🏰</div>
            <div class="stat-card-value"><?php echo count($clans); ?></div>
            <p class="stat-card-label">Total Clans</p>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon">📍</div>
            <div class="stat-card-value">
                <?php
                $total_locations = 0;
                foreach ($clans as $c) {
                    if (isset($c->locations) && is_array($c->locations)) {
                        $total_locations += count($c->locations);
                    }
                }
                echo $total_locations;
                ?>
            </div>
            <p class="stat-card-label">Locations</p>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon">🔤</div>
            <div class="stat-card-value">
                <?php
                $total_surnames = 0;
                foreach ($clans as $c) {
                    if (isset($c->surnames) && is_array($c->surnames)) {
                        $total_surnames += count($c->surnames);
                    }
                }
                echo $total_surnames;
                ?>
            </div>
            <p class="stat-card-label">Surnames</p>
        </div>
    </div>

    <!-- Clans Grid -->
    <div class="grid grid-2" style="margin-bottom: var(--spacing-2xl);">
        <?php foreach ($clans as $clan): ?>
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 style="margin: 0; color: var(--color-primary);">
                            🏰 <?php echo esc_html($clan->clan_name); ?>
                        </h3>
                        <?php if (!empty($clan->origin_year)): ?>
                            <small style="color: var(--color-text-light);">
                                Founded: <?php echo esc_html((string)$clan->origin_year); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <?php if (!empty($clan->description)): ?>
                        <p style="color: var(--color-text-secondary); margin-bottom: var(--spacing-lg);">
                            <?php echo esc_html(wp_trim_words($clan->description, 30)); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Locations -->
                    <?php if (isset($clan->locations) && is_array($clan->locations) && !empty($clan->locations)): ?>
                        <div style="margin-bottom: var(--spacing-lg);">
                            <strong style="display: block; margin-bottom: var(--spacing-sm); color: var(--color-text-primary); font-size: var(--font-size-sm);">
                                📍 Locations:
                            </strong>
                            <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-sm);">
                                <?php foreach ($clan->locations as $location): ?>
                                    <?php if (is_string($location) || is_numeric($location)): ?>
                                        <span class="badge badge-primary">
                                            <?php echo esc_html((string)$location); ?>
                                        </span>
                                    <?php elseif (is_object($location) && isset($location->location_name)): ?>
                                        <span class="badge badge-primary">
                                            <?php echo esc_html($location->location_name); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Surnames -->
                    <?php if (isset($clan->surnames) && is_array($clan->surnames) && !empty($clan->surnames)): ?>
                        <div style="margin-bottom: var(--spacing-lg);">
                            <strong style="display: block; margin-bottom: var(--spacing-sm); color: var(--color-text-primary); font-size: var(--font-size-sm);">
                                🔤 Surnames:
                            </strong>
                            <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-sm);">
                                <?php foreach ($clan->surnames as $surname): ?>
                                    <?php if (is_string($surname) || is_numeric($surname)): ?>
                                        <span class="badge badge-secondary">
                                            <?php echo esc_html((string)$surname); ?>
                                        </span>
                                    <?php elseif (is_object($surname) && isset($surname->last_name)): ?>
                                        <span class="badge badge-secondary">
                                            <?php echo esc_html($surname->last_name); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Members Count -->
                    <?php
                    global $wpdb;
                    $member_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}family_members WHERE clan_id = %d AND is_deleted = 0",
                        $clan->id
                    ));
                    ?>
                    <div style="padding-top: var(--spacing-lg); border-top: 1px solid var(--color-border); margin-top: var(--spacing-lg);">
                        <small style="color: var(--color-text-light);">
                            👥 <?php echo $member_count; ?> member<?php echo $member_count !== 1 ? 's' : ''; ?> in this clan
                        </small>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="/view-clan?id=<?php echo intval($clan->id); ?>" class="btn btn-outline btn-sm">
                        👁️ View
                    </a>
                    <a href="/edit-clan?id=<?php echo intval($clan->id); ?>" class="btn btn-primary btn-sm">
                        ✏️ Edit
                    </a>
                    <button class="btn btn-danger btn-sm delete-clan" data-id="<?php echo intval($clan->id); ?>" data-name="<?php echo esc_attr($clan->clan_name); ?>">
                        🗑️ Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<!-- Delete Clan Modal -->
<div id="deleteClanModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 style="margin: 0;">Delete Clan</h2>
            <button class="modal-close" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete the clan "<strong id="deleteClanName"></strong>"?</p>
            <div class="alert alert-danger" style="margin-top: var(--spacing-lg);">
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                    <div class="alert-title">Warning</div>
                    <p class="alert-message" style="margin: 0;">
                        This will NOT delete members in this clan, only the clan itself. Members will remain in the database but unlinked from any clan.
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button id="confirmDelete" class="btn btn-danger">🗑️ Delete Clan</button>
            <button id="cancelDelete" class="btn btn-outline">Cancel</button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let clanToDelete = null;

    // Delete clan
    $('.delete-clan').on('click', function() {
        clanToDelete = $(this).data('id');
        const clanName = $(this).data('name');
        $('#deleteClanName').text(clanName);
        $('#deleteClanModal').addClass('active');
    });

    // Close modal
    $('.modal-close, #cancelDelete').on('click', function() {
        $('#deleteClanModal').removeClass('active');
        clanToDelete = null;
    });

    // Confirm delete
    $('#confirmDelete').on('click', function() {
        if (!clanToDelete) return;

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="loading-spinner"></span>');

        $.post(family_tree.ajax_url, {
            action: 'delete_clan',
            nonce: family_tree.nonce,
            id: clanToDelete
        }, function(response) {
            if (response.success) {
                showToast('Clan deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error: ' + (response.data || 'Failed to delete'), 'error');
                btn.prop('disabled', false).html(originalText);
                $('#deleteClanModal').removeClass('active');
            }
        }).fail(function() {
            showToast('Connection error. Please try again.', 'error');
            btn.prop('disabled', false).html(originalText);
            $('#deleteClanModal').removeClass('active');
        });
    });

    // Close modal on background click
    $('#deleteClanModal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
        }
    });
});
</script>

<?php
$page_content = ob_get_clean();
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>