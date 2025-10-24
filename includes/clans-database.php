<?php
if (!class_exists('FamilyTreeClanDatabase')) {

class FamilyTreeClanDatabase {

    public static function setup_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $clans_table = $wpdb->prefix . 'family_clans';
        $locations_table = $wpdb->prefix . 'clan_locations';
        $surnames_table = $wpdb->prefix . 'clan_surnames';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // clan table
        $sql1 = "CREATE TABLE IF NOT EXISTS $clans_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            origin_year SMALLINT(4) NULL,
            created_by MEDIUMINT(9) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // locations table
        $sql2 = "CREATE TABLE IF NOT EXISTS $locations_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_id MEDIUMINT(9) NOT NULL,
            location_name VARCHAR(150) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            created_by MEDIUMINT(9) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // surnames table
        $sql3 = "CREATE TABLE IF NOT EXISTS $surnames_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_id MEDIUMINT(9) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            created_by MEDIUMINT(9) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql1);
        dbDelta($sql2);
        dbDelta($sql3);

        // Ensure audit columns and FK constraints
        FamilyTreeDatabase::apply_schema_updates();
    }

    public static function add_clan($data) {
        global $wpdb;
        $clan_table = $wpdb->prefix . 'family_clans';
        $locations_table = $wpdb->prefix . 'clan_locations';
        $surnames_table = $wpdb->prefix . 'clan_surnames';

        // Validation: Required fields
        if (empty($data['clan_name'])) {
            return new WP_Error('missing_name', 'Clan name is required');
        }
        if (empty($data['locations']) || !is_array($data['locations']) || count($data['locations']) === 0) {
            return new WP_Error('missing_locations', 'At least one location is required');
        }
        if (empty($data['surnames']) || !is_array($data['surnames']) || count($data['surnames']) === 0) {
            return new WP_Error('missing_surnames', 'At least one surname is required');
        }

        // Validation: Field length limits (matching database schema)
        if (strlen($data['clan_name']) > 150) {
            return new WP_Error('name_too_long', 'Clan name is too long (maximum 150 characters)');
        }
        if (isset($data['description']) && strlen($data['description']) > 5000) {
            return new WP_Error('description_too_long', 'Description is too long (maximum 5,000 characters)');
        }
        if (isset($data['origin_year']) && ($data['origin_year'] < 1000 || $data['origin_year'] > date('Y'))) {
            return new WP_Error('invalid_year', 'Origin year must be between 1000 and current year');
        }

        $now = current_time('mysql');

        $inserted = $wpdb->insert($clan_table, array(
            'clan_name'   => sanitize_text_field($data['clan_name']),
            'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
            'origin_year' => !empty($data['origin_year']) ? intval($data['origin_year']) : null,
            'created_by'  => get_current_user_id(),
            'created_at'  => $now,
            'updated_by'  => get_current_user_id(),
            'updated_at'  => $now
        ), array('%s','%s','%d','%d','%s','%d','%s'));

        if ($inserted === false) {
            error_log('FamilyTreeClanDatabase::add_clan error: ' . $wpdb->last_error);
            return new WP_Error('db_error', $wpdb->last_error);
        }

        $clan_id = $wpdb->insert_id;
        if (!$clan_id) {
            error_log('FamilyTreeClanDatabase::add_clan - no insert id');
            return new WP_Error('db_no_id', 'No insert id returned');
        }

        // Insert locations
        foreach ($data['locations'] as $loc) {
            // Validate that each location is a string
            if (!is_string($loc) && !is_numeric($loc)) {
                error_log('Invalid location data type in add_clan: ' . gettype($loc));
                continue;
            }
            $loc_name = sanitize_text_field($loc);
            if ($loc_name === '') continue;
            $wpdb->insert($locations_table, array(
                'clan_id' => $clan_id,
                'location_name' => $loc_name,
                'is_primary' => 0,
                'created_by' => get_current_user_id(),
                'created_at' => $now
            ), array('%d','%s','%d','%d','%s'));
        }

        // Insert surnames
        foreach ($data['surnames'] as $sn) {
            // Validate that each surname is a string
            if (!is_string($sn) && !is_numeric($sn)) {
                error_log('Invalid surname data type in add_clan: ' . gettype($sn));
                continue;
            }
            $sn_name = sanitize_text_field($sn);
            if ($sn_name === '') continue;
            $wpdb->insert($surnames_table, array(
                'clan_id' => $clan_id,
                'last_name' => $sn_name,
                'is_primary' => 0,
                'created_by' => get_current_user_id(),
                'created_at' => $now
            ), array('%d','%s','%d','%d','%s'));
        }

        return $clan_id;
    }

    public static function get_all_clans() {
        global $wpdb;
        $clans_table = $wpdb->prefix . 'family_clans';
        $locations_table = $wpdb->prefix . 'clan_locations';
        $surnames_table = $wpdb->prefix . 'clan_surnames';

        $clans = $wpdb->get_results("SELECT * FROM $clans_table ORDER BY clan_name ASC");

        // Return empty array if no clans
        if (!$clans) {
            return [];
        }

        // Attach locations and surnames to each clan
        foreach ($clans as $clan) {
            // Get locations as simple array of strings (ensure always array, never false/null)
            $locations = $wpdb->get_col($wpdb->prepare(
                "SELECT location_name FROM $locations_table WHERE clan_id = %d",
                $clan->id
            ));
            $clan->locations = is_array($locations) ? $locations : [];

            // Get surnames as simple array of strings (ensure always array, never false/null)
            $surnames = $wpdb->get_col($wpdb->prepare(
                "SELECT last_name FROM $surnames_table WHERE clan_id = %d",
                $clan->id
            ));
            $clan->surnames = is_array($surnames) ? $surnames : [];
        }

        return $clans;
    }

    public static function get_clan($id) {
        global $wpdb;
        $clans = $wpdb->prefix . 'family_clans';
        $locations = $wpdb->prefix . 'clan_locations';
        $surnames = $wpdb->prefix . 'clan_surnames';

        $clan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $clans WHERE id = %d", $id));
        if (!$clan) return null;

        // Get locations (ensure always array, never false/null)
        $clan_locations = $wpdb->get_results($wpdb->prepare("SELECT id, location_name FROM $locations WHERE clan_id = %d", $id), OBJECT);
        $clan->locations = is_array($clan_locations) ? $clan_locations : [];

        // Get surnames (ensure always array, never false/null)
        $clan_surnames = $wpdb->get_results($wpdb->prepare("SELECT id, last_name FROM $surnames WHERE clan_id = %d", $id), OBJECT);
        $clan->surnames = is_array($clan_surnames) ? $clan_surnames : [];

        return $clan;
    }

    public static function update_clan($id, $data) {
        global $wpdb;
        $clans = $wpdb->prefix . 'family_clans';
        $locations = $wpdb->prefix . 'clan_locations';
        $surnames = $wpdb->prefix . 'clan_surnames';

        $now = current_time('mysql');

        // Update clan basic info
        $wpdb->update($clans, array(
            'clan_name'   => sanitize_text_field($data['clan_name']),
            'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
            'origin_year' => !empty($data['origin_year']) ? intval($data['origin_year']) : null,
            'updated_by'  => get_current_user_id(),
            'updated_at'  => $now
        ), array('id' => $id), array('%s','%s','%d','%d','%s'), array('%d'));

        // Smart update for locations (preserves member references)
        self::smart_update_related_data(
            $id,
            $locations,
            'location_name',
            isset($data['locations']) && is_array($data['locations']) ? $data['locations'] : [],
            $now
        );

        // Smart update for surnames (preserves member references)
        self::smart_update_related_data(
            $id,
            $surnames,
            'last_name',
            isset($data['surnames']) && is_array($data['surnames']) ? $data['surnames'] : [],
            $now
        );

        return true;
    }

    /**
     * Smart update for related data (locations/surnames)
     * Preserves existing records and their IDs to maintain member references
     *
     * @param int $clan_id Clan ID
     * @param string $table Table name (with prefix)
     * @param string $name_field Field name (location_name or last_name)
     * @param array $new_values New values from form
     * @param string $now Current timestamp
     */
    private static function smart_update_related_data($clan_id, $table, $name_field, $new_values, $now) {
        global $wpdb;

        // Sanitize new values
        $new_values = array_filter(array_map(function($val) {
            if (!is_string($val) && !is_numeric($val)) {
                error_log('Invalid data type in smart_update_related_data: ' . gettype($val));
                return null;
            }
            $sanitized = sanitize_text_field($val);
            return $sanitized !== '' ? $sanitized : null;
        }, $new_values));

        // Get existing records
        $existing = $wpdb->get_results($wpdb->prepare(
            "SELECT id, {$name_field} FROM {$table} WHERE clan_id = %d",
            $clan_id
        ), OBJECT);

        $existing_values = [];
        $existing_ids = [];
        foreach ($existing as $record) {
            $existing_values[] = $record->{$name_field};
            $existing_ids[$record->{$name_field}] = $record->id;
        }

        // Determine what to add, keep, and remove
        $to_add = array_diff($new_values, $existing_values);      // New items to insert
        $to_keep = array_intersect($new_values, $existing_values); // Existing items to keep
        $to_remove = array_diff($existing_values, $new_values);   // Old items to delete

        // Delete removed items
        foreach ($to_remove as $value) {
            if (isset($existing_ids[$value])) {
                $wpdb->delete($table, array('id' => $existing_ids[$value]));
            }
        }

        // Add new items
        foreach ($to_add as $value) {
            $wpdb->insert($table, array(
                'clan_id' => $clan_id,
                $name_field => $value,
                'created_by' => get_current_user_id(),
                'created_at' => $now
            ), array('%d', '%s', '%d', '%s'));
        }

        // Items in $to_keep remain unchanged (preserving their IDs and member references)
    }

    public static function delete_clan($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_clans';
        $locations = $wpdb->prefix . 'clan_locations';
        $surnames = $wpdb->prefix . 'clan_surnames';
        $wpdb->delete($locations, array('clan_id' => $id));
        $wpdb->delete($surnames, array('clan_id' => $id));
        return $wpdb->delete($table, array('id' => intval($id)));
    }
}
} // class_exists guard
?>