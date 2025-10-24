<?php
/**
 * Family Tree Plugin - View Clan Page
 * Display detailed information about a family clan
 * Updated with professional design system
 */

if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$clan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clan = FamilyTreeClanDatabase::get_clan($clan_id);

if (!$clan) {
    wp_die('Clan not found.');
}

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Clans', 'url' => '/browse-clans'],
    ['label' => $clan->clan_name],
];
$page_title = '🏰 ' . $clan->clan_name;
$page_actions = '
    <a href="/edit-clan?id=' . intval($clan->id) . '" class="btn btn-primary btn-sm">
        ✏️ Edit Clan
    </a>
    <a href="/browse-clans" class="btn btn-outline btn-sm">
        ← Back to Clans
    </a>
';

ob_start();
?>

<!-- Header Section -->
<div class="card" style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; border: none; margin-bottom: var(--spacing-2xl);">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2 style="color: white; margin: 0 0 var(--spacing-md) 0; font-size: var(--font-size-2xl);">
                    🏰 <?php echo esc_html($clan->clan_name); ?>
                </h2>
                <?php if (!empty($clan->origin_year)): ?>
                    <p style="margin: 0; opacity: 0.9;">
                        <strong>Founded:</strong> <?php echo esc_html((string)$clan->origin_year); ?>
                    </p>
                <?php endif; ?>
            </div>
            <div style="text-align: right; font-size: 3rem;">
                🏰
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="grid grid-2">
    <!-- Left Column -->
    <div>
        <!-- Description Section -->
        <?php if (!empty($clan->description)): ?>
            <div class="card" style="margin-bottom: var(--spacing-xl);">
                <div class="card-header">
                    <h3 style="margin: 0;">📖 About This Clan</h3>
                </div>
                <div class="card-body">
                    <p style="margin: 0; color: var(--color-text-secondary); line-height: var(--line-height-relaxed);">
                        <?php echo nl2br(esc_html($clan->description)); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Locations Section -->
        <?php if (isset($clan->locations) && is_array($clan->locations) && !empty($clan->locations)): ?>
            <div class="card" style="margin-bottom: var(--spacing-xl);">
                <div class="card-header">
                    <h3 style="margin: 0;">📍 Primary Locations</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md);">
                        <?php foreach ($clan->locations as $location): ?>
                            <?php
                            // Handle both object (from get_clan) and string (from get_all_clans)
                            $location_name = is_object($location) && isset($location->location_name)
                                ? $location->location_name
                                : (is_string($location) ? $location : '');
                            ?>
                            <?php if (!empty($location_name)): ?>
                                <div style="
                                    flex: 1;
                                    min-width: 150px;
                                    padding: var(--spacing-lg);
                                    background: var(--color-bg-light);
                                    border-radius: var(--radius-base);
                                    border-left: 4px solid var(--color-primary);
                                ">
                                    <strong style="display: block; color: var(--color-text-primary); margin-bottom: var(--spacing-xs);">
                                        📍 <?php echo esc_html($location_name); ?>
                                    </strong>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Surnames Section -->
        <?php if (isset($clan->surnames) && is_array($clan->surnames) && !empty($clan->surnames)): ?>
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;">🔤 Family Surnames</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md);">
                        <?php foreach ($clan->surnames as $surname): ?>
                            <?php
                            // Handle both object (from get_clan) and string (from get_all_clans)
                            $surname_name = is_object($surname) && isset($surname->last_name)
                                ? $surname->last_name
                                : (is_string($surname) ? $surname : '');
                            ?>
                            <?php if (!empty($surname_name)): ?>
                                <span class="badge badge-primary" style="padding: var(--spacing-md) var(--spacing-lg); font-size: var(--font-size-sm);">
                                    <?php echo esc_html($surname_name); ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column - Statistics & Actions -->
    <div>
        <!-- Quick Stats -->
        <div class="card" style="margin-bottom: var(--spacing-xl);">
            <div class="card-header">
                <h3 style="margin: 0;">📊 Quick Stats</h3>
            </div>
            <div class="card-body">
                <?php
                global $wpdb;
                $table = $wpdb->prefix . 'family_members';
                $total_members = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE clan_id = %d AND is_deleted = 0",
                    $clan->id
                ));
                
                $living_members = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE clan_id = %d AND is_deleted = 0 AND death_date IS NULL",
                    $clan->id
                ));
                
                $deceased_members = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE clan_id = %d AND is_deleted = 0 AND death_date IS NOT NULL",
                    $clan->id
                ));
                
                $with_birthdates = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE clan_id = %d AND is_deleted = 0 AND birth_date IS NOT NULL",
                    $clan->id
                ));
                ?>

                <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: var(--spacing-lg); border-bottom: 1px solid var(--color-border);">
                        <span style="color: var(--color-text-secondary);">Total Members:</span>
                        <strong style="font-size: var(--font-size-lg); color: var(--color-primary);">
                            <?php echo $total_members; ?>
                        </strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: var(--spacing-lg); border-bottom: 1px solid var(--color-border);">
                        <span style="color: var(--color-text-secondary);">
                            <span style="color: var(--color-success);">●</span> Living:
                        </span>
                        <strong style="font-size: var(--font-size-lg);">
                            <?php echo $living_members; ?>
                        </strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: var(--spacing-lg); border-bottom: 1px solid var(--color-border);">
                        <span style="color: var(--color-text-secondary);">
                            <span style="color: var(--color-danger);">●</span> Deceased:
                        </span>
                        <strong style="font-size: var(--font-size-lg);">
                            <?php echo $deceased_members; ?>
                        </strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--color-text-secondary);">With Birth Dates:</span>
                        <strong style="font-size: var(--font-size-lg);">
                            <?php echo $with_birthdates; ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Info -->
        <div class="alert alert-info" style="margin-bottom: var(--spacing-xl);">
            <div class="alert-icon">ℹ️</div>
            <div class="alert-content">
                <div class="alert-title">Clan Overview</div>
                <p class="alert-message" style="margin: 0;">
                    This clan has <strong><?php echo $total_members; ?></strong> members in the family tree. 
                    <?php if ($total_members == 0): ?>
                        Start by <a href="/add-member" style="color: var(--color-info); text-decoration: underline;">adding members</a> to this clan.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Related Data -->
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">🔗 Related Information</h3>
            </div>
            <div class="card-body">
                <dl style="margin: 0;">
                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Total Locations:
                    </dt>
                    <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                        <?php echo isset($clan->locations) && is_array($clan->locations) ? count($clan->locations) : 0; ?>
                    </dd>

                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Total Surnames:
                    </dt>
                    <dd style="margin: 0 0 var(--spacing-lg) 0; color: var(--color-text-secondary);">
                        <?php echo isset($clan->surnames) && is_array($clan->surnames) ? count($clan->surnames) : 0; ?>
                    </dd>

                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: var(--spacing-sm);">
                        Clan ID:
                    </dt>
                    <dd style="margin: 0; color: var(--color-text-secondary); font-family: monospace; font-size: var(--font-size-sm);">
                        <?php echo intval($clan->id); ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Members Section -->
<div style="margin-top: var(--spacing-2xl);">
    <h2 class="section-title">👥 Members in This Clan</h2>
    
    <?php
    $members = $wpdb->get_results($wpdb->prepare(
        "SELECT id, first_name, last_name, birth_date, death_date, gender 
         FROM {$wpdb->prefix}family_members 
         WHERE clan_id = %d AND is_deleted = 0 
         ORDER BY last_name, first_name",
        $clan->id
    ));
    ?>

    <?php if (empty($members)): ?>
        <div class="alert alert-info">
            <div class="alert-icon">📋</div>
            <div class="alert-content">
                <p class="alert-message" style="margin: 0;">
                    No members in this clan yet. 
                    <a href="/add-member" style="color: var(--color-info); text-decoration: underline;">Add a member</a>
                </p>
            </div>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Birth Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></strong>
                            </td>
                            <td>
                                <?php
                                $gender_display = match($member->gender) {
                                    'Male' => '♂️ Male',
                                    'Female' => '♀️ Female',
                                    'Other' => '⚧️ Other',
                                    default => '-'
                                };
                                echo esc_html($gender_display);
                                ?>
                            </td>
                            <td>
                                <?php echo !empty($member->birth_date) ? esc_html($member->birth_date) : '-'; ?>
                            </td>
                            <td>
                                <?php if (!empty($member->death_date)): ?>
                                    <span class="badge badge-danger">⚫ Deceased</span>
                                <?php else: ?>
                                    <span class="badge badge-success">🟢 Living</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" style="gap: var(--spacing-sm);">
                                    <a href="/view-member?id=<?php echo intval($member->id); ?>" class="btn btn-sm btn-outline">
                                        View
                                    </a>
                                    <a href="/edit-member?id=<?php echo intval($member->id); ?>" class="btn btn-sm btn-outline">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$page_content = ob_get_clean();
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>