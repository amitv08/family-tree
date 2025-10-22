<?php
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$member = FamilyTreeDatabase::get_member($member_id);
if (!$member) wp_die('Member not found');
wp_head();
$can_manage = current_user_can('manage_family') || current_user_can('family_super_admin');
?>
<!doctype html><html><head><?php wp_head(); ?></head><body <?php body_class('family-tree-page'); ?>>
<nav class="top-menu"><a href="/family-dashboard">🏠 Dashboard</a><a href="/browse-members" class="active">Members</a><a href="/browse-clans">Clans</a></nav>
<div class="dashboard-container">
<h2><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?><?php if (!empty($member->is_deleted)) echo ' <em>(deleted)</em>'; ?></h2>
<div class="clan-details">
    <p><strong>Clan:</strong> <?php echo esc_html(FamilyTreeDatabase::get_clan_name($member->clan_id)); ?></p>
    <p><strong>Location:</strong> <?php echo esc_html(FamilyTreeDatabase::get_clan_location_name($member->clan_location_id)); ?></p>
    <p><strong>Surname:</strong> <?php echo esc_html(FamilyTreeDatabase::get_clan_surname_name($member->clan_surname_id)); ?></p>
    <p><strong>Parents:</strong>
        <?php
        $p1 = $member->parent1_id ? FamilyTreeDatabase::get_member($member->parent1_id) : null;
        $p2 = $member->parent2_id ? FamilyTreeDatabase::get_member($member->parent2_id) : null;
        echo $p1 ? esc_html($p1->first_name.' '.$p1->last_name) : '-';
        echo ', ';
        echo $p2 ? esc_html($p2->first_name.' '.$p2->last_name) : '-';
        ?>
    </p>
</div>
<div style="margin-top:20px;">
    <?php if ($can_manage) {
        if (empty($member->is_deleted)) echo '<button class="btn btn-danger btn-delete-member" data-id="'.intval($member->id).'">Delete</button> ';
        else echo '<button class="btn btn-restore-member" data-id="'.intval($member->id).'">Restore</button> ';
    } ?>
    <a href="/browse-members" class="btn btn-secondary">Back</a>
</div>
</div>
<script src="<?php echo esc_url( plugins_url('assets/js/members.js', __DIR__ . '/../../../') ); ?>"></script>
<?php wp_footer(); ?></body></html>
