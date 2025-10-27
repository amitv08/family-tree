<?php
/**
 * Check for members missing required clan location or surname
 *
 * Run this file directly to see which members need data filled in
 * Usage: http://family-tree.local/wp-content/plugins/family-tree/check-missing-data.php
 */

// Load WordPress
require_once('../../../../../wp-load.php');

// Check authentication
if (!current_user_can('manage_options')) {
    die('Access denied. Please login as administrator.');
}

global $wpdb;
$members_table = $wpdb->prefix . 'family_members';

// Find members without clan_location_id
$missing_location = $wpdb->get_results("
    SELECT id, first_name, last_name, clan_id, clan_location_id, clan_surname_id
    FROM $members_table
    WHERE is_deleted = 0
    AND (clan_location_id IS NULL OR clan_location_id = 0)
    ORDER BY id
");

// Find members without clan_surname_id
$missing_surname = $wpdb->get_results("
    SELECT id, first_name, last_name, clan_id, clan_location_id, clan_surname_id
    FROM $members_table
    WHERE is_deleted = 0
    AND (clan_surname_id IS NULL OR clan_surname_id = 0)
    ORDER BY id
");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Check Missing Data - Family Tree</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; }
        h2 { color: #3498db; margin-top: 30px; }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        .alert-success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 Check Missing Data - Family Tree</h1>
        <p style="color: #6c757d;">Checking for members without required clan location or surname...</p>

        <?php if (empty($missing_location) && empty($missing_surname)): ?>
            <div class="alert alert-success">
                <strong>✅ All Good!</strong><br>
                All members have clan location and surname assigned. No migration needed!
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>⚠️ Missing Data Found</strong><br>
                Some members are missing required clan location or surname. These need to be filled in.
            </div>
        <?php endif; ?>

        <!-- Missing Locations -->
        <?php if (!empty($missing_location)): ?>
            <h2>📍 Members Missing Clan Location (<?php echo count($missing_location); ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Clan ID</th>
                        <th>Location ID</th>
                        <th>Surname ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($missing_location as $member): ?>
                        <tr>
                            <td><?php echo intval($member->id); ?></td>
                            <td><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></td>
                            <td><?php echo $member->clan_id ? intval($member->clan_id) : '<em>None</em>'; ?></td>
                            <td><strong style="color:#dc3545;">Missing</strong></td>
                            <td><?php echo $member->clan_surname_id ? intval($member->clan_surname_id) : '<em>Missing</em>'; ?></td>
                            <td><a href="/edit-member?id=<?php echo intval($member->id); ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Missing Surnames -->
        <?php if (!empty($missing_surname)): ?>
            <h2>📝 Members Missing Clan Surname (<?php echo count($missing_surname); ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Clan ID</th>
                        <th>Location ID</th>
                        <th>Surname ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($missing_surname as $member): ?>
                        <tr>
                            <td><?php echo intval($member->id); ?></td>
                            <td><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></td>
                            <td><?php echo $member->clan_id ? intval($member->clan_id) : '<em>None</em>'; ?></td>
                            <td><?php echo $member->clan_location_id ? intval($member->clan_location_id) : '<em>Missing</em>'; ?></td>
                            <td><strong style="color:#dc3545;">Missing</strong></td>
                            <td><a href="/edit-member?id=<?php echo intval($member->id); ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="alert alert-info">
            <h3 style="margin-top:0;">💡 How to Fix</h3>
            <p><strong>Option 1: Edit Members Manually</strong></p>
            <p>Click "Edit" links in the table above to edit each member and assign location/surname.</p>

            <p style="margin-top:20px;"><strong>Option 2: Run Auto-Assignment Migration</strong></p>
            <p>If your clans have a primary location and surname, you can auto-assign them to members:</p>
            <ol>
                <li>Make sure each clan has a primary location and surname marked</li>
                <li>Create a migration script to auto-assign primary location/surname to members</li>
                <li>Run the migration</li>
            </ol>

            <p style="margin-top:20px;"><strong>Why This is Important:</strong></p>
            <p>Clan location and surname are now <strong>required fields</strong> for better data traceability. Members without these cannot be edited until the fields are filled in.</p>
        </div>

        <!-- Actions -->
        <div style="margin-top:30px;">
            <a href="/browse-members" class="btn">View All Members</a>
            <a href="javascript:location.reload()" class="btn btn-success">Refresh Check</a>
        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #95a5a6;">
        <p>Family Tree Plugin v3.4.0 - Data Check Tool</p>
    </div>
</body>
</html>
