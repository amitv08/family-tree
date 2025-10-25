<?php
/**
 * Family Tree Plugin - View Member Page
 * Display detailed information about a family member
 * Updated with professional design system
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$member = FamilyTreeDatabase::get_member($member_id);

if (!$member) {
    wp_die('Member not found.');
}

$can_manage = current_user_can('manage_family') || current_user_can('family_super_admin');

// Build full name with middle name if available
$full_name = $member->first_name;
if (!empty($member->middle_name)) {
    $full_name .= ' ' . $member->middle_name;
}
$full_name .= ' ' . $member->last_name;

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Members', 'url' => '/browse-members'],
    ['label' => $full_name],
];
$page_title = '👤 ' . $full_name;
$page_actions = '
    ' . (current_user_can('edit_family_members') ? '
    <a href="/edit-member?id=' . intval($member->id) . '" class="btn btn-primary btn-sm">
        ✏️ Edit
    </a>
    ' : '') . '
    <a href="/browse-members" class="btn btn-outline btn-sm">
        ← Back to Members
    </a>
';

ob_start();
?>

<!-- Header Section -->
<div class="card" style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; border: none; margin-bottom: var(--spacing-2xl);">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: var(--spacing-xl);">
            <div style="flex: 1;">
                <h2 style="color: white; margin: 0 0 var(--spacing-md) 0; font-size: var(--font-size-2xl);">
                    👤 <?php echo esc_html($full_name); ?>
                    <?php if (!empty($member->nickname)): ?>
                        <span style="font-weight: normal; font-size: var(--font-size-lg);">(<?php echo esc_html($member->nickname); ?>)</span>
                    <?php endif; ?>
                    <?php if (!empty($member->maiden_name)): ?>
                        <span style="font-weight: normal; font-size: var(--font-size-md); opacity: 0.9;">née <?php echo esc_html($member->maiden_name); ?></span>
                    <?php endif; ?>
                </h2>
                <?php if ($member->birth_date): ?>
                    <p style="margin: var(--spacing-sm) 0; opacity: 0.95;">
                        <strong>Born:</strong> <?php echo esc_html(date('M j, Y', strtotime($member->birth_date))); ?>
                    </p>
                <?php endif; ?>
                <?php if ($member->death_date): ?>
                    <p style="margin: var(--spacing-sm) 0; opacity: 0.95;">
                        <strong>Died:</strong> <?php echo esc_html(date('M j, Y', strtotime($member->death_date))); ?>
                    </p>
                <?php endif; ?>
                <?php if ($member->gender): ?>
                    <p style="margin: var(--spacing-sm) 0; opacity: 0.95;">
                        <strong>Gender:</strong> 
                        <?php
                        echo $member->gender === 'Male' ? '♂️ Male' : 
                             ($member->gender === 'Female' ? '♀️ Female' : '⚧️ Other');
                        ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php if ($member->photo_url): ?>
                <div style="text-align: center;">
                    <img src="<?php echo esc_url($member->photo_url); ?>" alt="<?php echo esc_attr($member->first_name); ?>" style="width: 120px; height: 150px; object-fit: cover; border-radius: var(--radius-lg); border: 4px solid white;">
                </div>
            <?php else: ?>
                <div style="font-size: 4rem; line-height: 1;">👤</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-2">
    <!-- Left Column -->
    <div>
        <!-- Personal Details -->
        <div class="card" style="margin-bottom: var(--spacing-xl);">
            <div class="card-header">
                <h3 style="margin: 0;">ℹ️ Personal Details</h3>
            </div>
            <div class="card-body">
                <dl style="margin: 0;">
                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Full Name:
                    </dt>
                    <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                        <?php echo esc_html($full_name); ?>
                    </dd>

                    <?php if ($member->gender): ?>
                        <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                            Gender:
                        </dt>
                        <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                            <?php echo esc_html($member->gender); ?>
                        </dd>
                    <?php endif; ?>

                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Status:
                    </dt>
                    <dd style="margin: 0 0 var(--spacing-lg) 0;">
                        <?php if ($member->death_date): ?>
                            <span class="badge badge-danger">⚫ Deceased</span>
                        <?php else: ?>
                            <span class="badge badge-success">🟢 Living</span>
                        <?php endif; ?>
                        <?php if (!empty($member->is_adopted)): ?>
                            <span class="badge badge-info" style="margin-left: var(--spacing-sm);">🤝 Adopted</span>
                        <?php endif; ?>
                    </dd>

                    <?php if (!empty($member->nickname)): ?>
                        <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                            Nickname:
                        </dt>
                        <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                            <?php echo esc_html($member->nickname); ?>
                        </dd>
                    <?php endif; ?>

                    <?php if (!empty($member->maiden_name)): ?>
                        <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                            Maiden Name (Birth Surname):
                        </dt>
                        <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                            <?php echo esc_html($member->maiden_name); ?>
                        </dd>
                    <?php endif; ?>

                    <?php if ($member->is_deleted): ?>
                        <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                            Record:
                        </dt>
                        <dd style="margin: 0; color: var(--color-text-secondary);">
                            <span class="badge badge-warning">🗑️ Deleted (can be restored)</span>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Family Details -->
        <div class="card" style="margin-bottom: var(--spacing-xl);">
            <div class="card-header">
                <h3 style="margin: 0;">👨‍👩‍👧‍👦 Family Details</h3>
            </div>
            <div class="card-body">
                <dl style="margin: 0;">
                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Clan:
                    </dt>
                    <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                        <?php
                        $clan_name = FamilyTreeDatabase::get_clan_name($member->clan_id);
                        echo $clan_name ? '<strong>' . esc_html($clan_name) . '</strong>' : '<em>Not assigned</em>';
                        ?>
                    </dd>

                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Father:
                    </dt>
                    <dd style="margin: 0 0 var(--spacing-lg) 0;">
                        <?php if ($member->parent1_id): ?>
                            <?php $p1 = FamilyTreeDatabase::get_member($member->parent1_id); ?>
                            <a href="/view-member?id=<?php echo intval($p1->id); ?>" style="color: var(--color-primary); text-decoration: underline;">
                                <?php echo esc_html($p1->first_name . ' ' . $p1->last_name); ?>
                            </a>
                        <?php else: ?>
                            <em style="color: var(--color-text-light);">Not recorded</em>
                        <?php endif; ?>
                    </dd>

                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Mother:
                    </dt>
                    <dd style="margin: 0; color: var(--color-text-secondary);">
                        <?php if (!empty($member->parent2_name)): ?>
                            <?php echo esc_html($member->parent2_name); ?>
                        <?php elseif ($member->parent2_id): ?>
                            <?php $p2 = FamilyTreeDatabase::get_member($member->parent2_id); ?>
                            <?php if ($p2): ?>
                                <a href="/view-member?id=<?php echo intval($p2->id); ?>" style="color: var(--color-primary); text-decoration: underline;">
                                    <?php echo esc_html($p2->first_name . ' ' . $p2->last_name); ?>
                                </a>
                            <?php else: ?>
                                <em style="color: var(--color-text-light);">Not recorded</em>
                            <?php endif; ?>
                        <?php else: ?>
                            <em style="color: var(--color-text-light);">Not recorded</em>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Biography -->
        <?php if (!empty($member->biography)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;">📖 Biography</h3>
                </div>
                <div class="card-body">
                    <p style="margin: 0; color: var(--color-text-secondary); line-height: var(--line-height-relaxed);">
                        <?php echo nl2br(esc_html($member->biography)); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Life Events -->
        <div class="card" style="margin-bottom: var(--spacing-xl);">
            <div class="card-header">
                <h3 style="margin: 0;">📅 Life Events</h3>
            </div>
            <div class="card-body">
                <dl style="margin: 0;">
                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Birth:
                    </dt>
                    <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                        <?php echo $member->birth_date ? esc_html(date('M j, Y', strtotime($member->birth_date))) : '<em>Not recorded</em>'; ?>
                    </dd>

                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Death:
                    </dt>
                    <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                        <?php echo $member->death_date ? esc_html(date('M j, Y', strtotime($member->death_date))) : '<em>Still living</em>'; ?>
                    </dd>

                    <?php if ($member->marriage_date): ?>
                        <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                            Marriage:
                        </dt>
                        <dd style="margin: 0; color: var(--color-text-secondary);">
                            <?php echo esc_html(date('M j, Y', strtotime($member->marriage_date))); ?>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Location Information -->
        <div class="card" style="margin-bottom: var(--spacing-xl);">
            <div class="card-header">
                <h3 style="margin: 0;">📍 Location</h3>
            </div>
            <div class="card-body">
                <dl style="margin: 0;">
                    <?php if ($member->address): ?>
                        <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                            Address:
                        </dt>
                        <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                            <?php echo esc_html($member->address); ?>
                        </dd>
                    <?php endif; ?>

                    <?php $location_parts = array_filter([$member->city, $member->state, $member->country]); ?>
                    <?php if ($location_parts): ?>
                        <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                            Location:
                        </dt>
                        <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                            <?php echo esc_html(implode(', ', $location_parts)); ?>
                        </dd>
                    <?php endif; ?>

                    <?php if ($member->postal_code): ?>
                        <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                            Postal Code:
                        </dt>
                        <dd style="margin: 0; color: var(--color-text-secondary);">
                            <?php echo esc_html($member->postal_code); ?>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Actions -->
        <?php if ($can_manage): ?>
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;">⚙️ Actions</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group" style="flex-direction: column;">
                        <?php if (!$member->is_deleted): ?>
                            <button class="btn btn-danger btn-delete-member" data-id="<?php echo intval($member->id); ?>" style="width: 100%;">
                                🗑️ Delete Member
                            </button>
                        <?php else: ?>
                            <button class="btn btn-success btn-restore-member" data-id="<?php echo intval($member->id); ?>" style="width: 100%;">
                                ↩️ Restore Member
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?php echo esc_url(plugins_url('assets/js/members.js', FAMILY_TREE_PATH . 'family-tree.php')); ?>"></script>

<?php
$page_content = ob_get_clean();
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>