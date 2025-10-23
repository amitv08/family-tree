<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$current_user = wp_get_current_user();
$members = FamilyTreeDatabase::get_members();
$member_count = $members ? count($members) : 0;

$breadcrumbs = [];
$page_title = 'Dashboard';
$page_actions = '';

ob_start();
?>

<!-- Welcome Section -->
<div class="card" style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; border: none; margin-bottom: var(--spacing-2xl);">
    <div class="card-body">
        <h2 style="color: white; margin: 0 0 var(--spacing-md) 0;">Welcome back, <?php echo esc_html($current_user->display_name); ?> 👋</h2>
        <p style="margin: 0; opacity: 0.9;">Manage and explore your family history in one place</p>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-2" style="margin-bottom: var(--spacing-3xl);">
    <!-- Members Stat -->
    <div class="stat-card">
        <div class="stat-card-icon">👥</div>
        <div class="stat-card-value"><?php echo $member_count; ?></div>
        <p class="stat-card-label">Family Members</p>
        <?php if ($member_count > 0): ?>
            <small style="color: var(--color-text-light);">
                <?php
                $genders = ['male' => 0, 'female' => 0, 'other' => 0];
                foreach ($members as $m) {
                    $genders[$m->gender] = ($genders[$m->gender] ?? 0) + 1;
                }
                echo '♂ ' . $genders['male'] . ' • ♀ ' . $genders['female'];
                if ($genders['other'] > 0) echo ' • ⚧ ' . $genders['other'];
                ?>
            </small>
        <?php endif; ?>
    </div>

    <!-- Role Stat -->
    <div class="stat-card">
        <div class="stat-card-icon">🔑</div>
        <div class="stat-card-value">
            <?php echo ucfirst(str_replace('family_', '', $current_user->roles[0] ?? 'Viewer')); ?>
        </div>
        <p class="stat-card-label">Your Role</p>
    </div>
</div>

<!-- Status Section -->
<div class="section">
    <h2 class="section-title">Your Access Level</h2>
    <div class="alert alert-info">
        <div class="alert-icon">ℹ️</div>
        <div class="alert-content">
            <div class="alert-title">
                <?php
                if (current_user_can('manage_family')) {
                    echo 'Full Administrator Access';
                } elseif (current_user_can('edit_family_members')) {
                    echo 'Editor Access';
                } else {
                    echo 'Viewer Access';
                }
                ?>
            </div>
            <p class="alert-message" style="margin: 0;">
                <?php
                if (current_user_can('manage_family')) {
                    echo 'You have full access to manage users, edit family members, and view the entire family tree.';
                } elseif (current_user_can('edit_family_members')) {
                    echo 'You can add, edit, and view family members in the family tree.';
                } else {
                    echo 'You can view the family tree and family member details.';
                }
                ?>
            </p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="section">
    <h2 class="section-title">Quick Actions</h2>
    <div class="btn-group">
        <a href="/family-tree" class="btn btn-lg btn-primary">
            <span>🌲</span> View Family Tree
        </a>
        <a href="/browse-members" class="btn btn-lg btn-secondary">
            <span>👥</span> Browse Members
        </a>
        <?php if (current_user_can('edit_family_members')): ?>
            <a href="/add-member" class="btn btn-lg btn-success">
                <span>➕</span> Add Member
            </a>
        <?php endif; ?>
        <?php if (current_user_can('manage_family')): ?>
            <a href="/family-admin" class="btn btn-lg btn-outline">
                <span>⚙️</span> Admin Panel
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Tree Stats -->
<?php if ($member_count > 0): ?>
    <div class="section">
        <h2 class="section-title">Tree Health</h2>
        <div class="grid grid-3">
            <div class="stat-card">
                <div class="stat-card-icon">✅</div>
                <div class="stat-card-value">
                    <?php
                    $complete = 0;
                    foreach ($members as $m) {
                        if ($m->birth_date && ($m->parent1_id || $m->parent2_id)) {
                            $complete++;
                        }
                    }
                    echo round((($complete / $member_count) * 100)) . '%';
                    ?>
                </div>
                <p class="stat-card-label">Profiles Complete</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon">🎂</div>
                <div class="stat-card-value">
                    <?php
                    $with_dates = 0;
                    foreach ($members as $m) {
                        if ($m->birth_date) $with_dates++;
                    }
                    echo round((($with_dates / $member_count) * 100)) . '%';
                    ?>
                </div>
                <p class="stat-card-label">With Birth Dates</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon">📸</div>
                <div class="stat-card-value">
                    <?php
                    $with_photos = 0;
                    foreach ($members as $m) {
                        if ($m->photo_url) $with_photos++;
                    }
                    echo round((($with_photos / $member_count) * 100)) . '%';
                    ?>
                </div>
                <p class="stat-card-label">With Photos</p>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Empty State -->
    <div class="section">
        <div class="alert alert-warning">
            <div class="alert-icon">📝</div>
            <div class="alert-content">
                <div class="alert-title">Get Started</div>
                <p class="alert-message" style="margin: var(--spacing-md) 0 0 0;">
                    Your family tree is empty. Start by adding your first family member to create a lasting record of your family history.
                </p>
                <?php if (current_user_can('edit_family_members')): ?>
                    <a href="/add-member" class="btn btn-primary" style="margin-top: var(--spacing-lg);">
                        ➕ Add Your First Member
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$page_content = ob_get_clean();
$page_title = 'Dashboard';
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>