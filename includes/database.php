<?php
if (!class_exists('FamilyTreeDatabase')) {

class FamilyTreeDatabase {

    // Existing code may be longer — this file replaces/extends your existing DB class.
    // Make sure to merge in any other methods you have that aren't shown below.

    public static function setup_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $members_table = $wpdb->prefix . 'family_members';
        $clans_table = $wpdb->prefix . 'family_clans';
        $locations_table = $wpdb->prefix . 'clan_locations';
        $surnames_table = $wpdb->prefix . 'clan_surnames';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // If your existing family_members table is already created by your plugin,
        // we will ALTER it to add clan columns if they do not exist.
        // Defensive: create base members if absent (your plugin likely already has it).
        $sql_members = "CREATE TABLE IF NOT EXISTS $members_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            user_id MEDIUMINT(9) NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            birth_date DATE NULL,
            death_date DATE NULL,
            gender VARCHAR(20) NULL,
            photo_url VARCHAR(255) NULL,
            biography TEXT NULL,
            parent1_id MEDIUMINT(9) NULL,
            parent2_id MEDIUMINT(9) NULL,
            created_by MEDIUMINT(9) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql_members);

        // Add clan related columns to members table if missing
        $cols = $wpdb->get_results("SHOW COLUMNS FROM $members_table", ARRAY_A);
        $col_names = array_column($cols, 'Field');

        if (!in_array('clan_id', $col_names)) {
            $wpdb->query("ALTER TABLE $members_table ADD COLUMN clan_id MEDIUMINT(9) NULL AFTER biography");
        }
        if (!in_array('clan_location_id', $col_names)) {
            $wpdb->query("ALTER TABLE $members_table ADD COLUMN clan_location_id MEDIUMINT(9) NULL AFTER clan_id");
        }
        if (!in_array('clan_surname_id', $col_names)) {
            $wpdb->query("ALTER TABLE $members_table ADD COLUMN clan_surname_id MEDIUMINT(9) NULL AFTER clan_location_id");
        }

        // Ensure clan tables exist (if you have clan setup elsewhere, dbDelta is safe)
        $sql_clans = "CREATE TABLE IF NOT EXISTS $clans_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            origin_year SMALLINT(4) NULL,
            created_by MEDIUMINT(9) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_clans);

        $sql_locations = "CREATE TABLE IF NOT EXISTS $locations_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_id MEDIUMINT(9) NOT NULL,
            location_name VARCHAR(150) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_locations);

        $sql_surnames = "CREATE TABLE IF NOT EXISTS $surnames_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_id MEDIUMINT(9) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_surnames);
    }

    /**
     * Migration helper: set a default clan for existing members if none present.
     * Call this once after activation if needed.
     */
    public static function migrate_members_add_clan() {
        global $wpdb;
        $members_table = $wpdb->prefix . 'family_members';
        $clans_table   = $wpdb->prefix . 'family_clans';

        // pick a default clan (first row) if any
        $default_clan_id = $wpdb->get_var("SELECT id FROM $clans_table LIMIT 1");
        if ($default_clan_id) {
            $wpdb->query($wpdb->prepare("UPDATE $members_table SET clan_id = %d WHERE clan_id IS NULL OR clan_id = ''", $default_clan_id));
        }
    }

    // ------------------------
    // Member CRUD (simplified)
    // ------------------------

    public static function add_member($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';

        // Basic validation omitted here — caller should validate
        $inserted = $wpdb->insert($table, array(
            'user_id' => isset($data['user_id']) ? intval($data['user_id']) : null,
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'birth_date' => !empty($data['birth_date']) ? sanitize_text_field($data['birth_date']) : null,
            'death_date' => !empty($data['death_date']) ? sanitize_text_field($data['death_date']) : null,
            'gender' => isset($data['gender']) ? sanitize_text_field($data['gender']) : null,
            'photo_url' => isset($data['photo_url']) ? esc_url_raw($data['photo_url']) : null,
            'biography' => isset($data['biography']) ? sanitize_textarea_field($data['biography']) : null,
            'parent1_id' => isset($data['parent1_id']) ? intval($data['parent1_id']) : null,
            'parent2_id' => isset($data['parent2_id']) ? intval($data['parent2_id']) : null,
            'created_by' => get_current_user_id(),
            'clan_id' => isset($data['clan_id']) ? intval($data['clan_id']) : null,
            'clan_location_id' => isset($data['clan_location_id']) ? intval($data['clan_location_id']) : null,
            'clan_surname_id' => isset($data['clan_surname_id']) ? intval($data['clan_surname_id']) : null
        ), array(
            '%d','%s','%s','%s','%s','%s','%s','%d','%d','%d','%d','%d','%d','%d'
        ));

        if ($inserted === false) {
            error_log('add_member failed: ' . $wpdb->last_error);
            return false;
        }
        return $wpdb->insert_id;
    }

    public static function update_member($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';

        $result = $wpdb->update($table, array(
            'user_id' => isset($data['user_id']) ? intval($data['user_id']) : null,
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'birth_date' => !empty($data['birth_date']) ? sanitize_text_field($data['birth_date']) : null,
            'death_date' => !empty($data['death_date']) ? sanitize_text_field($data['death_date']) : null,
            'gender' => isset($data['gender']) ? sanitize_text_field($data['gender']) : null,
            'photo_url' => isset($data['photo_url']) ? esc_url_raw($data['photo_url']) : null,
            'biography' => isset($data['biography']) ? sanitize_textarea_field($data['biography']) : null,
            'parent1_id' => isset($data['parent1_id']) ? intval($data['parent1_id']) : null,
            'parent2_id' => isset($data['parent2_id']) ? intval($data['parent2_id']) : null,
            'clan_id' => isset($data['clan_id']) ? intval($data['clan_id']) : null,
            'clan_location_id' => isset($data['clan_location_id']) ? intval($data['clan_location_id']) : null,
            'clan_surname_id' => isset($data['clan_surname_id']) ? intval($data['clan_surname_id']) : null
        ), array('id' => intval($id)), array(
            '%d','%s','%s','%s','%s','%s','%s','%d','%d','%d','%d','%d','%d','%d'
        ), array('%d'));

        if ($result === false) {
            error_log('update_member failed: ' . $wpdb->last_error);
            return false;
        }
        return true;
    }

    // Example simple get_member (include clan ids)
    public static function get_member($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        $member = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        return $member;
    }

    public static function get_members($limit = 100, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY last_name ASC LIMIT %d OFFSET %d", $limit, $offset));
    }

    // ------------------------
    // Helper getters for clan names
    // ------------------------
    public static function get_clan_name($clan_id) {
        if (!$clan_id) return '';
        global $wpdb;
        $table = $wpdb->prefix . 'family_clans';
        return $wpdb->get_var($wpdb->prepare("SELECT clan_name FROM $table WHERE id = %d", $clan_id));
    }

    public static function get_clan_location_name($location_id) {
        if (!$location_id) return '';
        global $wpdb;
        $table = $wpdb->prefix . 'clan_locations';
        return $wpdb->get_var($wpdb->prepare("SELECT location_name FROM $table WHERE id = %d", $location_id));
    }

    public static function get_clan_surname_name($surname_id) {
        if (!$surname_id) return '';
        global $wpdb;
        $table = $wpdb->prefix . 'clan_surnames';
        return $wpdb->get_var($wpdb->prepare("SELECT last_name FROM $table WHERE id = %d", $surname_id));
    }

    /**
 * Retrieve all family members for building the tree view.
 * Includes parent, clan, and basic relationship fields.
 */
public static function get_tree_data() {
    global $wpdb;

    $members_table = $wpdb->prefix . 'family_members';
    $clans_table   = $wpdb->prefix . 'family_clans';

    // Fetch members with minimal data for tree rendering
    $sql = "
        SELECT 
            m.id,
            m.first_name,
            m.last_name,
            m.gender,
            m.parent1_id,
            m.parent2_id,
            m.clan_id,
            c.clan_name
        FROM $members_table m
        LEFT JOIN $clans_table c ON m.clan_id = c.id
        ORDER BY m.last_name, m.first_name
    ";

    $results = $wpdb->get_results($sql);

    // Return data formatted for your JS tree builder
    return $results ?: [];
}

}
} // class_exists guard
?>