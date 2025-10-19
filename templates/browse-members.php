<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$members = FamilyTreeDatabase::get_members();
$member_count = $members ? count($members) : 0;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Browse Family Members</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; }
        
        .browse-members {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .search-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .filter-select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        
        .members-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .members-table th,
        .members-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .members-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .members-table tr:hover {
            background: #f8f9fa;
        }
        
        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            background: #e9ecef;
        }
        
        .member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #007cba, #0056b3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #007cba;
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #6c757d;
            color: #6c757d;
        }
        
        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #007cba;
        }
        
        .stat-label {
            font-size: 0.8em;
            color: #666;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="browse-members">
    <div class="page-header">
        <h1>Browse Family Members</h1>
        <a href="/family-dashboard" class="btn btn-outline">← Back to Dashboard</a>
    </div>

    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-number"><?php echo $member_count; ?></div>
            <div class="stat-label">Total Members</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">
                <?php
                $with_photos = 0;
                foreach ($members as $member) {
                    if ($member->photo_url) $with_photos++;
                }
                echo $with_photos;
                ?>
            </div>
            <div class="stat-label">With Photos</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">
                <?php
                $with_birthdates = 0;
                foreach ($members as $member) {
                    if ($member->birth_date) $with_birthdates++;
                }
                echo $with_birthdates;
                ?>
            </div>
            <div class="stat-label">With Birth Dates</div>
        </div>
    </div>

    <div class="search-filters">
        <div class="search-box">
            <input type="text" id="search-input" placeholder="Search by name..." onkeyup="filterMembers()">
        </div>
        <select class="filter-select" onchange="filterMembers()">
            <option value="">All Genders</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <select class="filter-select" onchange="filterMembers()">
            <option value="">All Status</option>
            <option value="alive">Living</option>
            <option value="deceased">Deceased</option>
        </select>
    </div>

    <table class="members-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Birth Date</th>
                <th>Status</th>
                <th>Parents</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="members-tbody">
            <?php foreach ($members as $member): ?>
            <?php
            $parent1 = $member->parent1_id ? FamilyTreeDatabase::get_member($member->parent1_id) : null;
            $parent2 = $member->parent2_id ? FamilyTreeDatabase::get_member($member->parent2_id) : null;
            $parents = [];
            if ($parent1) $parents[] = $parent1->first_name . ' ' . $parent1->last_name;
            if ($parent2) $parents[] = $parent2->first_name . ' ' . $parent2->last_name;
            
            $is_deceased = $member->death_date && strtotime($member->death_date) < time();
            ?>
            <tr class="member-row" 
                data-name="<?php echo strtolower($member->first_name . ' ' . $member->last_name); ?>"
                data-gender="<?php echo $member->gender; ?>"
                data-status="<?php echo $is_deceased ? 'deceased' : 'alive'; ?>">
                <td>
                    <div class="member-avatar">
                        <?php if ($member->photo_url): ?>
                            <img src="<?php echo esc_url($member->photo_url); ?>" alt="<?php echo esc_attr($member->first_name); ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder"><?php echo strtoupper(substr($member->first_name, 0, 1)); ?></div>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <strong><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></strong>
                </td>
                <td>
                    <?php 
                    $gender_icons = ['male' => '♂', 'female' => '♀', 'other' => '⚧'];
                    echo ($gender_icons[$member->gender] ?? '?') . ' ' . ucfirst($member->gender);
                    ?>
                </td>
                <td><?php echo $member->birth_date ? date('M j, Y', strtotime($member->birth_date)) : 'Unknown'; ?></td>
                <td>
                    <span style="color: <?php echo $is_deceased ? '#dc3545' : '#28a745'; ?>">
                        <?php echo $is_deceased ? 'Deceased' : 'Living'; ?>
                    </span>
                </td>
                <td>
                    <?php echo $parents ? implode(' & ', $parents) : 'No parents'; ?>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="/edit-member?id=<?php echo $member->id; ?>" class="btn btn-primary">Edit</a>
                        <a href="/family-tree" class="btn btn-outline">View in Tree</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if ($member_count === 0): ?>
    <div style="text-align: center; padding: 40px; color: #666;">
        <h3>No Family Members Yet</h3>
        <p>Start building your family tree by adding the first member.</p>
        <a href="/add-member" class="btn btn-primary">Add First Member</a>
    </div>
    <?php endif; ?>
</div>

<script>
function filterMembers() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const genderFilter = document.querySelector('select').value;
    const statusFilter = document.querySelectorAll('select')[1].value;
    const rows = document.querySelectorAll('.member-row');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const gender = row.getAttribute('data-gender');
        const status = row.getAttribute('data-status');
        
        const nameMatch = name.includes(searchTerm);
        const genderMatch = !genderFilter || gender === genderFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        
        if (nameMatch && genderMatch && statusMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php wp_footer(); ?>
</body>
</html>