<?php
if (!is_user_logged_in()) { wp_redirect('/family-login'); exit; }
if (!current_user_can('edit_family_members')) wp_die('You do not have permission to add members.');

// fetch lists
$clans = FamilyTreeDatabase::get_all_clans_simple();
$all_members = FamilyTreeDatabase::get_members(2000,0); // parent dropdowns
?>
<!doctype html>
<html><head><?php wp_head(); ?></head><body <?php body_class('family-tree-page'); ?>>
<nav class="top-menu"><a href="/family-dashboard">🏠 Dashboard</a><a href="/browse-members" class="active">Members</a><a href="/browse-clans">Clans</a></nav>
<div class="dashboard-container">
<h2>Add Member</h2>

<form id="addMemberForm">
    <div class="form-row">
        <label>Clan</label>
        <select name="clan_id" id="clan_id" required>
            <option value="">-- Select Clan --</option>
            <?php foreach($clans as $c): ?>
                <option value="<?php echo intval($c->id); ?>"><?php echo esc_html($c->clan_name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <label>Clan Location</label>
        <select name="clan_location_id" id="clan_location_id">
            <option value="">-- Select Location --</option>
        </select>
    </div>

    <div class="form-row">
        <label>Clan Surname</label>
        <select name="clan_surname_id" id="clan_surname_id">
            <option value="">-- Select Surname --</option>
        </select>
    </div>

    <div class="form-row">
        <label>Father (Parent 1)</label>
        <select name="parent1_id">
            <option value="">-- None --</option>
            <?php foreach($all_members as $m): ?>
                <option value="<?php echo intval($m->id); ?>"><?php echo esc_html($m->first_name . ' ' . $m->last_name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <label>Mother (Parent 2) <span style="color:red">*</span></label>
        <select name="parent2_id" required>
            <option value="">-- Select Mother --</option>
            <?php foreach($all_members as $m): ?>
                <option value="<?php echo intval($m->id); ?>"><?php echo esc_html($m->first_name . ' ' . $m->last_name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <label>First Name</label>
        <input type="text" name="first_name" required>
    </div>

    <div class="form-row">
        <label>Last Name</label>
        <input type="text" name="last_name" id="last_name_input" required>
        <small>Automatically set when clan surname selected; editable.</small>
    </div>

    <div class="form-row">
        <label>Birth Date</label>
        <input type="date" name="birth_date">
    </div>

    <div class="form-row">
        <label>Death Date</label>
        <input type="date" name="death_date">
    </div>

    <div class="form-row">
        <label>Marriage Date</label>
        <input type="date" name="marriage_date">
    </div>

    <div class="form-row">
        <label>Gender</label>
        <select name="gender">
            <option value="">-- Select --</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
    </div>

    <div class="form-row">
        <label>Photo URL</label>
        <input type="text" name="photo_url" placeholder="http://...">
    </div>

    <div class="form-row">
        <label>Address</label>
        <textarea name="address"></textarea>
    </div>

    <div class="form-row">
        <label>City</label><input type="text" name="city">
        <label>State</label><input type="text" name="state">
        <label>Country</label><input type="text" name="country">
        <label>Postal Code</label><input type="text" name="postal_code">
    </div>

    <div class="form-row">
        <label>Biography</label>
        <textarea name="biography"></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Add Member</button>
        <a href="/browse-members" class="btn">Cancel</a>
    </div>
</form>
</div>

<script>
jQuery(function($){
    // Populate clan-location & surname when clan selected
    function loadClanDetails(clanId, selectLocationId, selectSurnameId) {
        if (!clanId) {
            $('#clan_location_id').html('<option value="">-- Select Location --</option>');
            $('#clan_surname_id').html('<option value="">-- Select Surname --</option>');
            return;
        }
        $.post(family_tree.ajax_url, { action: 'get_clan_details', nonce: family_tree.nonce, clan_id: clanId }, function(res){
            if (res.success) {
                var locs = res.data.locations || [];
                var surnames = res.data.surnames || [];
                var htmlL = '<option value="">-- Select Location --</option>';
                locs.forEach(function(l){ htmlL += '<option value="'+l.id+'">'+l.location_name+'</option>'; });
                $('#clan_location_id').html(htmlL);
                var htmlS = '<option value="">-- Select Surname --</option>';
                surnames.forEach(function(s){ htmlS += '<option value="'+s.id+'" data-lastname="'+s.last_name+'">'+s.last_name+'</option>'; });
                $('#clan_surname_id').html(htmlS);
            } else {
                alert('Error loading clan details: ' + res.data);
            }
        });
    }

    $('#clan_id').on('change', function(){ loadClanDetails($(this).val()); });

    // When surname chosen, auto-fill last_name input (but allow edit)
    $('#clan_surname_id').on('change', function(){
        var sel = $(this).find('option:selected');
        var ln = sel.data('lastname') || '';
        if (ln) {
            $('#last_name_input').val(ln);
        }
    });

    $('#addMemberForm').on('submit', function(e){
        e.preventDefault();
        // mother required: ensure parent2_id selected
        if (!$('select[name="parent2_id"]').val()) {
            alert('Mother (Parent 2) is required.');
            return;
        }
        var data = $(this).serializeArray();
        data.push({name:'action', value:'add_family_member'}, {name:'nonce', value: family_tree.nonce});
        $.post(family_tree.ajax_url, data, function(res){
            if (res.success) {
                alert('Member added.');
                window.location.href = '/browse-members';
            } else {
                alert('Error: ' + (res.data||'Failed to add'));
            }
        });
    });
});
</script>

<?php wp_footer(); ?></body></html>
