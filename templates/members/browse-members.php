<?php
if (!is_user_logged_in()) { wp_redirect('/family-login'); exit; }
wp_head();
$can_manage = current_user_can('manage_family') || current_user_can('family_super_admin');
$members = FamilyTreeDatabase::get_members(1000,0,true); // include deleted so admin can restore
?>
<!doctype html><html><head><?php wp_head(); ?></head><body <?php body_class('family-tree-page'); ?>>
<nav class="top-menu"><a href="/family-dashboard">🏠 Dashboard</a><a href="/browse-members" class="active">Members</a><a href="/browse-clans">Clans</a></nav>
<div class="dashboard-container">
<h2>Members</h2>
<a href="/add-member" class="btn btn-primary">➕ Add Member</a>
<table class="family-table"><thead><tr><th>Name</th><th>Birth</th><th>Clan</th><th>Actions</th></tr></thead><tbody>
<?php if ($members) {
    foreach($members as $m) {
        $deleted = !empty($m->is_deleted);
        echo '<tr'.($deleted?' style="opacity:0.5"':'').'>';
        echo '<td>'.esc_html($m->first_name.' '.$m->last_name).($deleted?' <em>(deleted)</em>':'').'</td>';
        echo '<td>'.esc_html($m->birth_date?:'-').'</td>';
        echo '<td>'.esc_html(FamilyTreeDatabase::get_clan_name($m->clan_id)).'</td>';
        echo '<td>';
        echo '<a href="/view-member?id='.intval($m->id).'" class="btn btn-outline">View</a> ';
        if (!$deleted) echo '<a href="/edit-member?id='.intval($m->id).'" class="btn btn-edit">Edit</a> ';
        if ($can_manage) {
            if (!$deleted) echo '<button class="btn btn-danger btn-delete-member" data-id="'.intval($m->id).'">Delete</button>';
            else echo '<button class="btn btn-restore btn-restore-member" data-id="'.intval($m->id).'">Restore</button>';
        }
        echo '</td></tr>';
    }
} else {
    echo '<tr><td colspan="4">No members found.</td></tr>';
} ?>
</tbody></table>
</div>
<script src="<?php echo esc_url( plugins_url('assets/js/members.js', __DIR__ . '/../../../') ); ?>"></script>
<?php wp_footer(); ?></body></html>
