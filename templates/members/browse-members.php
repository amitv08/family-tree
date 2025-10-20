<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}
if (!current_user_can('view_family_tree')) {
    wp_die('No permission.');
}
wp_head();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Clan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f0f1;
            padding: 20px;
        }

        .add-clan-page {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .page-header h1 {
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #545b62;
        }

        .btn-primary {
            background: #007cba;
            color: white;
        }

        .btn-primary:hover {
            background: #005a87;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .clan-form {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
        }

        .form-section h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.2em;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        input,
        select,
        textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #007cba;
        }

        input[type="file"] {
            padding: 8px;
            border: 2px dashed #e0e0e0;
            background: #fafafa;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        #form-message {
            margin-top: 20px;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .photo-preview {
            margin-top: 10px;
            text-align: center;
        }

        .photo-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }

        .required {
            color: #e53e3e;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <!-- 🔹 Global Top Menu -->
    <nav class="top-menu">
        <a href="/family-dashboard">🏠 Dashboard</a>
        <a href="/browse-members">👨‍👩‍👧 Members</a>
        <a href="/browse-clans" class="active">🏰 Clans</a>
    </nav>
    <div class="dashboard-container">
        <h2>Members</h2>
        <a href="/add-member" class="btn btn-primary">➕ Add Member</a>

        <table class="family-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Birth</th>
                    <th>Clan</th>
                    <th>Clan Location</th>
                    <th>Clan Surname</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $members = FamilyTreeDatabase::get_members(200, 0);
                if ($members) {
                    foreach ($members as $m) {
                        $clan_name = FamilyTreeDatabase::get_clan_name($m->clan_id);
                        $loc_name = FamilyTreeDatabase::get_clan_location_name($m->clan_location_id);
                        $sn_name = FamilyTreeDatabase::get_clan_surname_name($m->clan_surname_id);
                        echo '<tr>';
                        echo '<td>' . esc_html($m->first_name . ' ' . $m->last_name) . '</td>';
                        echo '<td>' . esc_html($m->birth_date ?: '-') . '</td>';
                        echo '<td>' . esc_html($clan_name ?: '-') . '</td>';
                        echo '<td>' . esc_html($loc_name ?: '-') . '</td>';
                        echo '<td>' . esc_html($sn_name ?: '-') . '</td>';
                        echo '<td>
                            <a href="/view-member?id=' . intval($m->id) . '" class="btn btn-outline">View</a>
                            <a href="/edit-member?id=' . intval($m->id) . '" class="btn btn-edit">Edit</a>
                        </td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No members found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php wp_footer(); ?>
</body>

</html>