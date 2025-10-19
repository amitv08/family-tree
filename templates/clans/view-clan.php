<?php
$clan_id = intval($_GET['id']);
$clan = FamilyTreeClanDatabase::get_clan($clan_id);
if (!$clan)
    wp_die('Clan not found.');
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Browse Family Members</title>
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

        .browse-members {
            max-width: 1200px;
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

    <!-- 🔹 Global Top Menu -->
    <nav class="top-menu">
        <a href="/family-dashboard">🏠 Dashboard</a>
        <a href="/browse-members">👨‍👩‍👧 Members</a>
        <a href="/browse-clans" class="active">🏰 Clans</a>
    </nav>

    <div class="clan-container">
        <h2><?php echo esc_html($clan->clan_name); ?></h2>
        <div class="clan-details">
            <p><strong>Origin Year:</strong> <?php echo esc_html($clan->origin_year ?: 'Unknown'); ?></p>
            <p><strong>Description:</strong><br><?php echo wpautop(esc_html($clan->description)); ?></p>

            <h3>Locations</h3>
            <ul>
                <?php foreach ($clan->locations as $loc)
                    echo "<li>" . esc_html($loc) . "</li>"; ?>
            </ul>

            <h3>Surnames</h3>
            <ul>
                <?php foreach ($clan->surnames as $sn)
                    echo "<li>" . esc_html($sn) . "</li>"; ?>
            </ul>
        </div>
        <div class="form-actions">
            <a href="/edit-clan?id=<?php echo $clan->id; ?>" class="btn btn-primary">Edit Clan</a>
            <a href="/browse-clans" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>

</html>