<?php
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$member = FamilyTreeDatabase::get_member($member_id);
if (!$member)
    wp_die('Member not found');

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

<body <?php body_class(); ?>></body>
<nav class="top-menu">
    <a href="/family-dashboard">🏠 Dashboard</a>
    <a href="/browse-members" class="active">👨‍👩‍👧 Members</a>
    <a href="/browse-clans">🏰 Clans</a>
</nav>

<div class="dashboard-container">
    <h2><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></h2>

    <div class="clan-details">
        <h3>Clan Details</h3>
        <p><strong>Clan:</strong> <?php echo esc_html(FamilyTreeDatabase::get_clan_name($member->clan_id)); ?></p>
        <p><strong>Clan Location:</strong>
            <?php echo esc_html(FamilyTreeDatabase::get_clan_location_name($member->clan_location_id)); ?></p>
        <p><strong>Clan Surname:</strong>
            <?php echo esc_html(FamilyTreeDatabase::get_clan_surname_name($member->clan_surname_id)); ?></p>
    </div>

    <h3>Personal Info</h3>
    <p><strong>Birth:</strong> <?php echo esc_html($member->birth_date ?: '-'); ?></p>
    <p><strong>Death:</strong> <?php echo esc_html($member->death_date ?: '-'); ?></p>
    <p><strong>Gender:</strong> <?php echo esc_html($member->gender ?: '-'); ?></p>
    <div style="margin-top:20px;">
        <a href="/edit-member?id=<?php echo intval($member_id); ?>" class="btn btn-primary">Edit</a>
        <a href="/browse-members" class="btn btn-secondary">Back</a>
    </div>
</div>

<?php wp_footer(); ?>
</body>

</html>