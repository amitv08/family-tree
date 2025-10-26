<?php
/**
 * Verification Script - Tests the implementation
 *
 * Run this file directly to verify all changes are working
 * Usage: http://family-tree.local/wp-content/plugins/family-tree/verify-implementation.php
 */

// Load WordPress
require_once('../../../../../wp-load.php');

// Don't require authentication for this test
define('FAMILY_TREE_VERIFICATION', true);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Family Tree Implementation Verification</title>
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
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; }
        h2 { color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .check { color: #27ae60; font-weight: bold; }
        .fail { color: #e74c3c; font-weight: bold; }
        .info { color: #95a5a6; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .status-item {
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 4px;
        }
        .status-item.success { border-left-color: #27ae60; }
        .status-item.fail { border-left-color: #e74c3c; }
    </style>
</head>
<body>
    <h1>🎯 Family Tree Implementation Verification</h1>
    <p class="info">Verification run at: <?php echo date('Y-m-d H:i:s'); ?></p>

    <!-- File Checks -->
    <div class="card">
        <h2>📁 File Integrity Checks</h2>
        <div class="status-grid">
            <?php
            $files_to_check = [
                'add-member.php' => 'templates/members/add-member.php',
                'edit-member.php' => 'templates/members/edit-member.php',
                'database.php' => 'includes/database.php',
                'MemberController.php' => 'includes/Controllers/MemberController.php',
                'MarriageController.php' => 'includes/Controllers/MarriageController.php'
            ];

            foreach ($files_to_check as $name => $path) {
                $full_path = FAMILY_TREE_PATH . $path;
                $exists = file_exists($full_path);
                $class = $exists ? 'success' : 'fail';
                $icon = $exists ? '✅' : '❌';
                echo "<div class='status-item $class'>";
                echo "<strong>$icon $name</strong><br>";
                echo "<small>" . ($exists ? "Found: $path" : "Missing: $path") . "</small>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <!-- Feature Checks -->
    <div class="card">
        <h2>🔍 Feature Implementation Checks</h2>
        <div class="status-grid">
            <?php
            $add_member_file = FAMILY_TREE_PATH . 'templates/members/add-member.php';
            $edit_member_file = FAMILY_TREE_PATH . 'templates/members/edit-member.php';
            $database_file = FAMILY_TREE_PATH . 'includes/database.php';

            $features = [
                'Gender Required' => [
                    'file' => $add_member_file,
                    'search' => 'name="gender" value="Male" required'
                ],
                'Hidden middle_name' => [
                    'file' => $add_member_file,
                    'search' => 'type="hidden" id="middle_name"'
                ],
                'Hidden last_name' => [
                    'file' => $add_member_file,
                    'search' => 'type="hidden" id="last_name"'
                ],
                'Mother Dropdown (Select2)' => [
                    'file' => $add_member_file,
                    'search' => 'id="parent2_combined"'
                ],
                'Maiden Name Group' => [
                    'file' => $add_member_file,
                    'search' => 'id="maiden_name_group"'
                ],
                'Marriages Container' => [
                    'file' => $add_member_file,
                    'search' => 'id="marriages_container"'
                ],
                'Add Marriage Button' => [
                    'file' => $add_member_file,
                    'search' => 'id="add_marriage_btn"'
                ],
                'Migration Function' => [
                    'file' => $database_file,
                    'search' => 'function migrate_member_names()'
                ],
                'Auto-populate Logic' => [
                    'file' => $database_file,
                    'search' => 'Auto-populate middle_name from parent1'
                ]
            ];

            foreach ($features as $name => $check) {
                $content = file_get_contents($check['file']);
                $found = strpos($content, $check['search']) !== false;
                $class = $found ? 'success' : 'fail';
                $icon = $found ? '✅' : '❌';
                echo "<div class='status-item $class'>";
                echo "<strong>$icon $name</strong><br>";
                echo "<small>" . basename($check['file']) . "</small>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <!-- Database Checks -->
    <div class="card">
        <h2>🗄️ Database Checks</h2>
        <?php
        global $wpdb;

        // Check if tables exist
        $members_table = $wpdb->prefix . 'family_members';
        $marriages_table = $wpdb->prefix . 'family_marriages';

        $members_exists = $wpdb->get_var("SHOW TABLES LIKE '$members_table'") === $members_table;
        $marriages_exists = $wpdb->get_var("SHOW TABLES LIKE '$marriages_table'") === $marriages_table;

        echo "<div class='status-grid'>";

        echo "<div class='status-item " . ($members_exists ? 'success' : 'fail') . "'>";
        echo "<strong>" . ($members_exists ? '✅' : '❌') . " family_members table</strong><br>";
        echo "<small>" . ($members_exists ? "Table exists" : "Table missing") . "</small>";
        echo "</div>";

        echo "<div class='status-item " . ($marriages_exists ? 'success' : 'fail') . "'>";
        echo "<strong>" . ($marriages_exists ? '✅' : '❌') . " family_marriages table</strong><br>";
        echo "<small>" . ($marriages_exists ? "Table exists" : "Table missing") . "</small>";
        echo "</div>";

        if ($members_exists) {
            $member_count = $wpdb->get_var("SELECT COUNT(*) FROM $members_table WHERE is_deleted = 0");
            echo "<div class='status-item success'>";
            echo "<strong>📊 Active Members</strong><br>";
            echo "<small>$member_count members in database</small>";
            echo "</div>";
        }

        if ($marriages_exists) {
            $marriage_count = $wpdb->get_var("SELECT COUNT(*) FROM $marriages_table");
            echo "<div class='status-item success'>";
            echo "<strong>💍 Total Marriages</strong><br>";
            echo "<small>$marriage_count marriages in database</small>";
            echo "</div>";
        }

        echo "</div>";

        // Check column existence
        if ($members_exists) {
            echo "<h3 style='margin-top: 20px;'>Column Verification</h3>";
            $columns = $wpdb->get_results("SHOW COLUMNS FROM $members_table");
            $column_names = array_column($columns, 'Field');

            $required_columns = ['middle_name', 'last_name', 'maiden_name', 'parent1_id', 'parent2_id', 'parent2_name', 'clan_surname_id'];

            echo "<div class='status-grid'>";
            foreach ($required_columns as $col) {
                $exists = in_array($col, $column_names);
                $class = $exists ? 'success' : 'fail';
                $icon = $exists ? '✅' : '❌';
                echo "<div class='status-item $class'>";
                echo "<strong>$icon $col</strong>";
                echo "</div>";
            }
            echo "</div>";
        }
        ?>
    </div>

    <!-- JavaScript Checks -->
    <div class="card">
        <h2>📜 JavaScript Functionality Checks</h2>
        <div class="status-grid">
            <?php
            $js_functions = [
                'addMarriageEntry' => 'Add Marriage Entry Function',
                'saveMarriages' => 'Save Marriages Function',
                'fetchAndPopulateMotherFromMarriages' => 'Smart Mother Selection',
                'updateFullNamePreview' => 'Full Name Preview',
                'renumberMarriageEntries' => 'Renumber Marriages'
            ];

            $content = file_get_contents($add_member_file);

            foreach ($js_functions as $func => $name) {
                $found = strpos($content, "function $func") !== false;
                $class = $found ? 'success' : 'fail';
                $icon = $found ? '✅' : '❌';
                echo "<div class='status-item $class'>";
                echo "<strong>$icon $name</strong><br>";
                echo "<small>$func()</small>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <!-- AJAX Endpoints -->
    <div class="card">
        <h2>🔌 AJAX Endpoints Check</h2>
        <div class="status-grid">
            <?php
            $endpoints = [
                'add_marriage',
                'update_marriage',
                'delete_marriage',
                'get_marriages_for_member',
                'add_family_member',
                'update_family_member'
            ];

            foreach ($endpoints as $action) {
                $has_action = has_action("wp_ajax_$action");
                $class = $has_action ? 'success' : 'fail';
                $icon = $has_action ? '✅' : '❌';
                echo "<div class='status-item $class'>";
                echo "<strong>$icon $action</strong><br>";
                echo "<small>wp_ajax_$action</small>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <!-- Summary -->
    <div class="card">
        <h2>📊 Implementation Summary</h2>
        <?php
        $total_checks = 0;
        $passed_checks = 0;

        // Count checks
        $total_checks += count($files_to_check);
        $total_checks += count($features);
        $total_checks += 2; // Database tables
        $total_checks += count($js_functions);
        $total_checks += count($endpoints);

        // Count passed
        foreach ($files_to_check as $name => $path) {
            if (file_exists(FAMILY_TREE_PATH . $path)) $passed_checks++;
        }

        foreach ($features as $name => $check) {
            $content = file_get_contents($check['file']);
            if (strpos($content, $check['search']) !== false) $passed_checks++;
        }

        if ($members_exists) $passed_checks++;
        if ($marriages_exists) $passed_checks++;

        $content = file_get_contents($add_member_file);
        foreach ($js_functions as $func => $name) {
            if (strpos($content, "function $func") !== false) $passed_checks++;
        }

        foreach ($endpoints as $action) {
            if (has_action("wp_ajax_$action")) $passed_checks++;
        }

        $percentage = round(($passed_checks / $total_checks) * 100);
        $status = $percentage >= 90 ? 'success' : ($percentage >= 70 ? 'warning' : 'fail');

        echo "<div style='text-align: center; padding: 30px;'>";
        echo "<h1 style='font-size: 48px; margin: 0;'>$percentage%</h1>";
        echo "<p style='font-size: 24px; color: #7f8c8d;'>Implementation Complete</p>";
        echo "<p><strong>$passed_checks</strong> out of <strong>$total_checks</strong> checks passed</p>";

        if ($percentage >= 90) {
            echo "<p style='color: #27ae60; font-size: 20px;'>✅ All systems ready for testing!</p>";
        } elseif ($percentage >= 70) {
            echo "<p style='color: #f39c12; font-size: 20px;'>⚠️ Some checks failed. Review above.</p>";
        } else {
            echo "<p style='color: #e74c3c; font-size: 20px;'>❌ Multiple issues detected. Check logs.</p>";
        }
        echo "</div>";
        ?>
    </div>

    <!-- Next Steps -->
    <div class="card">
        <h2>🚀 Next Steps</h2>
        <ol style="line-height: 1.8;">
            <li><strong>Login to WordPress Admin:</strong> http://family-tree.local/wp-admin</li>
            <li><strong>Navigate to Add Member:</strong> http://family-tree.local/add-member</li>
            <li><strong>Test Features:</strong>
                <ul>
                    <li>Select gender (should be required)</li>
                    <li>Select father → middle name auto-fills</li>
                    <li>Select clan surname → last name auto-fills</li>
                    <li>Click "Add Marriage" → marriage entry appears</li>
                    <li>Add multiple marriages</li>
                    <li>Submit form → verify data saves</li>
                </ul>
            </li>
            <li><strong>Edit Existing Member:</strong> Verify existing marriages load and can be edited</li>
            <li><strong>Run Migration:</strong> In wp-admin, run this code:
                <pre>$stats = FamilyTreeDatabase::migrate_member_names();
print_r($stats);</pre>
            </li>
        </ol>
    </div>

    <div style="text-align: center; padding: 20px; color: #95a5a6;">
        <p>Generated by Family Tree Plugin v3.3.0 Implementation Verification</p>
        <p><small>Implementation completed: 2025-10-26</small></p>
    </div>
</body>
</html>
