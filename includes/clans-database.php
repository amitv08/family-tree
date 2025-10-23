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

        if (empty($data['clan_name'])) {
            return new WP_Error('missing_name', 'Clan name is required');
        }
        if (empty($data['locations']) || !is_array($data['locations']) || count($data['locations']) === 0) {
            return new WP_Error('missing_locations', 'At least one location is required');
        }
        if (empty($data['surnames']) || !is_array($data['surnames']) || count($data['surnames']) === 0) {
            return new WP_Error('missing_surnames', 'At least one surname is required');
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

        // Attach locations and surnames to each clan
        foreach ($clans as $clan) {
            // Get locations as simple array of strings
            $locations = $wpdb->get_col($wpdb->prepare(
                "SELECT location_name FROM $locations_table WHERE clan_id = %d",
                $clan->id
            ));
            $clan->locations = $locations;

            // Get surnames as simple array of strings
            $surnames = $wpdb->get_col($wpdb->prepare(
                "SELECT last_name FROM $surnames_table WHERE clan_id = %d",
                $clan->id
            ));
            $clan->surnames = $surnames;
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

        $clan->locations = $wpdb->get_results($wpdb->prepare("SELECT id, location_name FROM $locations WHERE clan_id = %d", $id), OBJECT);
        $clan->surnames  = $wpdb->get_results($wpdb->prepare("SELECT id, last_name FROM $surnames WHERE clan_id = %d", $id), OBJECT);

        return $clan;
    }

    public static function update_clan($id, $data) {
        global $wpdb;
        $clans = $wpdb->prefix . 'family_clans';
        $locations = $wpdb->prefix . 'clan_locations';
        $surnames = $wpdb->prefix . 'clan_surnames';

        $now = current_time('mysql');

        $wpdb->update($clans, array(
            'clan_name'   => sanitize_text_field($data['clan_name']),
            'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
            'origin_year' => !empty($data['origin_year']) ? intval($data['origin_year']) : null,
            'updated_by'  => get_current_user_id(),
            'updated_at'  => $now
        ), array('id' => $id), array('%s','%s','%d','%d','%s'), array('%d'));

        // Replace related data: delete old and insert new
        $wpdb->delete($locations, array('clan_id' => $id));
        $wpdb->delete($surnames, array('clan_id' => $id));

        if (!empty($data['locations']) && is_array($data['locations'])) {
            foreach ($data['locations'] as $loc) {
                $loc_name = sanitize_text_field($loc);
                if ($loc_name === '') continue;
                $wpdb->insert($locations, array(
                    'clan_id' => $id,
                    'location_name' => $loc_name,
                    'created_by' => get_current_user_id(),
                    'created_at' => $now
                ), array('%d','%s','%d','%s'));
            }
        }

        if (!empty($data['surnames']) && is_array($data['surnames'])) {
            foreach ($data['surnames'] as $sn) {
                $sn_name = sanitize_text_field($sn);
                if ($sn_name === '') continue;
                $wpdb->insert($surnames, array(
                    'clan_id' => $id,
                    'last_name' => $sn_name,
                    'created_by' => get_current_user_id(),
                    'created_at' => $now
                ), array('%d','%s','%d','%s'));
            }
        }

        return true;
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