<?php
if (!class_exists('FamilyTreeDatabase')) {

class FamilyTreeDatabase {

    /**
     * Create base tables if missing and run schema updates
     */
    public static function setup_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $members_table = $wpdb->prefix . 'family_members';
        $clans_table = $wpdb->prefix . 'family_clans';
        $locations_table = $wpdb->prefix . 'clan_locations';
        $surnames_table = $wpdb->prefix . 'clan_surnames';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // create clan tables (minimal columns; apply_schema_updates will add audit cols)
        $sql_clans = "CREATE TABLE IF NOT EXISTS $clans_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            origin_year SMALLINT(4) NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_clans);

        $sql_locations = "CREATE TABLE IF NOT EXISTS $locations_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_id MEDIUMINT(9) NOT NULL,
            location_name VARCHAR(150) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_locations);

        $sql_surnames = "CREATE TABLE IF NOT EXISTS $surnames_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_id MEDIUMINT(9) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            is_primary TINYINT(1) DEFAULT 0,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_surnames);

        // Members table (create basic columns — migration ensures the exact structure)
        $sql_members = "CREATE TABLE IF NOT EXISTS $members_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            clan_id MEDIUMINT(9) DEFAULT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            birth_date DATE DEFAULT NULL,
            death_date DATE DEFAULT NULL,
            gender VARCHAR(20) DEFAULT NULL,
            photo_url VARCHAR(255) DEFAULT NULL,
            biography TEXT NULL,
            parent1_id MEDIUMINT(9) DEFAULT NULL,
            parent2_id MEDIUMINT(9) DEFAULT NULL,
            created_by MEDIUMINT(9) DEFAULT NULL,
            updated_by MEDIUMINT(9) DEFAULT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_deleted TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            marriage_date DATE DEFAULT NULL,
            address TEXT NULL,
            city VARCHAR(100) DEFAULT NULL,
            state VARCHAR(100) DEFAULT NULL,
            country VARCHAR(100) DEFAULT NULL,
            postal_code VARCHAR(20) DEFAULT NULL,
            clan_location_id MEDIUMINT(9) DEFAULT NULL,
            clan_surname_id MEDIUMINT(9) DEFAULT NULL,
            user_id MEDIUMINT(9) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_members);

        // ensure columns & keys are present & correct
        self::apply_schema_updates();
    }

    /**
     * Apply safe schema updates: add missing columns, set nullable where required, attempt FK adds.
     */
    public static function apply_schema_updates() {
        global $wpdb;

        $members_table = $wpdb->prefix . 'family_members';
        $clans_table = $wpdb->prefix . 'family_clans';
        $locations_table = $wpdb->prefix . 'clan_locations';
        $surnames_table = $wpdb->prefix . 'clan_surnames';
        $prefix = $wpdb->prefix;

        // helper to list columns
        $get_cols = function($table) use ($wpdb) {
            $cols = [];
            $rows = $wpdb->get_results("SHOW COLUMNS FROM $table", ARRAY_A);
            if ($rows) foreach ($rows as $r) $cols[] = $r['Field'];
            return $cols;
        };

        // Ensure members table has all columns exactly as your structure (only add missing ones)
        $cols = $get_cols($members_table);

        $maybe_add = function($col_sql) use ($wpdb) {
            // $col_sql is a string like "ADD COLUMN colname TYPE ..."
            global $members_table;
            $wpdb->query("ALTER TABLE {$members_table} {$col_sql}");
        };

        // Add missing columns with same definitions as your posted schema
        $to_add = [
            "ADD COLUMN clan_id MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN first_name VARCHAR(100) NOT NULL",
            "ADD COLUMN last_name VARCHAR(100) NOT NULL",
            "ADD COLUMN birth_date DATE DEFAULT NULL",
            "ADD COLUMN death_date DATE DEFAULT NULL",
            "ADD COLUMN gender VARCHAR(20) DEFAULT NULL",
            "ADD COLUMN photo_url VARCHAR(255) DEFAULT NULL",
            "ADD COLUMN biography TEXT NULL",
            "ADD COLUMN parent1_id MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN parent2_id MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN created_by MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN updated_by MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP",
            "ADD COLUMN is_deleted TINYINT(1) DEFAULT 0",
            "ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
            "ADD COLUMN marriage_date DATE DEFAULT NULL",
            "ADD COLUMN address TEXT NULL",
            "ADD COLUMN city VARCHAR(100) DEFAULT NULL",
            "ADD COLUMN state VARCHAR(100) DEFAULT NULL",
            "ADD COLUMN country VARCHAR(100) DEFAULT NULL",
            "ADD COLUMN postal_code VARCHAR(20) DEFAULT NULL",
            "ADD COLUMN clan_location_id MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN clan_surname_id MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN user_id MEDIUMINT(9) DEFAULT NULL",
        ];

        foreach ($to_add as $definition) {
            // parse column name from definition
            preg_match('/ADD COLUMN\s+`?([a-z0-9_]+)`?\s+/i', $definition, $m);
            $col = isset($m[1]) ? $m[1] : null;
            if ($col && !in_array($col, $cols)) {
                // run safe alteration
                $wpdb->query("ALTER TABLE {$members_table} {$definition}");
                // refresh column list
                $cols = $get_cols($members_table);
            }
        }

        // Ensure columns are NULLABLE where needed (so FK with ON DELETE SET NULL works)
        // We'll explicitly set clan_id, parent1_id, parent2_id, clan_location_id, clan_surname_id to NULLABLE
        $nullable_cols = ['clan_id','parent1_id','parent2_id','clan_location_id','clan_surname_id','user_id'];
        foreach ($nullable_cols as $nc) {
            if (in_array($nc, $cols)) {
                // modify to NULL without changing type (fetch current type)
                $col_info = $wpdb->get_row("SHOW COLUMNS FROM {$members_table} WHERE Field = '{$nc}'", ARRAY_A);
                if ($col_info) {
                    $type = $col_info['Type'];
                    // build modify statement; allow DEFAULT NULL
                    $wpdb->query("ALTER TABLE {$members_table} MODIFY {$nc} {$type} NULL");
                }
            }
        }

        // Run similar checks for other tables' audit columns
        $tables = [$clans_table, $locations_table, $surnames_table];
        foreach ($tables as $t) {
            $cols2 = $get_cols($t);
            if (!in_array('created_by', $cols2)) $wpdb->query("ALTER TABLE {$t} ADD COLUMN created_by MEDIUMINT(9) DEFAULT NULL");
            if (!in_array('updated_by', $cols2)) $wpdb->query("ALTER TABLE {$t} ADD COLUMN updated_by MEDIUMINT(9) DEFAULT NULL");
            if (!in_array('updated_at', $cols2)) $wpdb->query("ALTER TABLE {$t} ADD COLUMN updated_at DATETIME DEFAULT NULL");
            if (!in_array('created_at', $cols2)) $wpdb->query("ALTER TABLE {$t} ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        }

        // Attempt to add foreign keys — check existence first to avoid duplicates.
        // Creating FKs can fail on some hosts; we log errors but continue.
        try {
            $dbName = DB_NAME;
            $exists_fk = function($table, $name) use ($wpdb, $dbName) {
                $sql = $wpdb->prepare("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=%s AND TABLE_NAME=%s AND CONSTRAINT_NAME=%s", $dbName, $table, $name);
                $cnt = $wpdb->get_var($sql);
                return intval($cnt) > 0;
            };

            // members.clan_id -> clans.id
            if (!$exists_fk($wpdb->prefix.'family_members','fk_members_clan')) {
                $wpdb->query("ALTER TABLE {$wpdb->prefix}family_members ADD CONSTRAINT fk_members_clan FOREIGN KEY (clan_id) REFERENCES {$wpdb->prefix}family_clans(id) ON DELETE SET NULL ON UPDATE CASCADE");
            }
            // clan_location
            if (!$exists_fk($wpdb->prefix.'family_members','fk_members_clan_location')) {
                $wpdb->query("ALTER TABLE {$wpdb->prefix}family_members ADD CONSTRAINT fk_members_clan_location FOREIGN KEY (clan_location_id) REFERENCES {$wpdb->prefix}clan_locations(id) ON DELETE SET NULL ON UPDATE CASCADE");
            }
            // clan_surname
            if (!$exists_fk($wpdb->prefix.'family_members','fk_members_clan_surname')) {
                $wpdb->query("ALTER TABLE {$wpdb->prefix}family_members ADD CONSTRAINT fk_members_clan_surname FOREIGN KEY (clan_surname_id) REFERENCES {$wpdb->prefix}clan_surnames(id) ON DELETE SET NULL ON UPDATE CASCADE");
            }
            // parent1 & parent2 (self-referential)
            if (!$exists_fk($wpdb->prefix.'family_members','fk_members_parent1')) {
                $wpdb->query("ALTER TABLE {$wpdb->prefix}family_members ADD CONSTRAINT fk_members_parent1 FOREIGN KEY (parent1_id) REFERENCES {$wpdb->prefix}family_members(id) ON DELETE SET NULL ON UPDATE CASCADE");
            }
            if (!$exists_fk($wpdb->prefix.'family_members','fk_members_parent2')) {
                $wpdb->query("ALTER TABLE {$wpdb->prefix}family_members ADD CONSTRAINT fk_members_parent2 FOREIGN KEY (parent2_id) REFERENCES {$wpdb->prefix}family_members(id) ON DELETE SET NULL ON UPDATE CASCADE");
            }
        } catch (Exception $e) {
            error_log('apply_schema_updates: FK creation error: ' . $e->getMessage());
        }
    }

        /**
     * Set default clan for existing members (keeps backward compatibility)
     */
    public static function migrate_members_add_clan() {
        global $wpdb;
        $members_table = $wpdb->prefix . 'family_members';
        $clans_table   = $wpdb->prefix . 'family_clans';

        $default_clan_id = $wpdb->get_var("SELECT id FROM $clans_table LIMIT 1");
        if ($default_clan_id) {
            $wpdb->query($wpdb->prepare("UPDATE $members_table SET clan_id = %d WHERE clan_id IS NULL OR clan_id = ''", $default_clan_id));
        }
    }

        // --- Helpers used by templates (browse-members, tree-view, etc.) ---

    public static function get_clan_name($clan_id) {
        if (empty($clan_id)) return '';
        global $wpdb;
        $table = $wpdb->prefix . 'family_clans';
        $name = $wpdb->get_var($wpdb->prepare("SELECT clan_name FROM $table WHERE id = %d", $clan_id));
        return $name ?: '';
    }

    public static function get_tree_data() {
        global $wpdb;
        $members_table = $wpdb->prefix . 'family_members';
        $clans_table   = $wpdb->prefix . 'family_clans';

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
            WHERE COALESCE(m.is_deleted,0)=0
            ORDER BY m.last_name, m.first_name
        ";
        return $wpdb->get_results($sql) ?: [];
    }

    /* -------------------------
     * CRUD: members (supports full schema)
     * ------------------------- */

    public static function add_member($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        $now = current_time('mysql');

        // Prepare input with sanitization
        $insert = [
            'clan_id' => !empty($data['clan_id']) ? intval($data['clan_id']) : null,
            'first_name' => isset($data['first_name']) ? sanitize_text_field($data['first_name']) : '',
            'last_name' => isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '',
            'birth_date' => !empty($data['birth_date']) ? sanitize_text_field($data['birth_date']) : null,
            'death_date' => !empty($data['death_date']) ? sanitize_text_field($data['death_date']) : null,
            'marriage_date' => !empty($data['marriage_date']) ? sanitize_text_field($data['marriage_date']) : null,
            'gender' => isset($data['gender']) ? sanitize_text_field($data['gender']) : null,
            'photo_url' => isset($data['photo_url']) ? esc_url_raw($data['photo_url']) : null,
            'biography' => isset($data['biography']) ? sanitize_textarea_field($data['biography']) : null,
            'parent1_id' => isset($data['parent1_id']) && $data['parent1_id'] !== '' ? intval($data['parent1_id']) : null,
            'parent2_id' => isset($data['parent2_id']) && $data['parent2_id'] !== '' ? intval($data['parent2_id']) : null,
            'created_by' => get_current_user_id() ?: null,
            'created_at' => $now,
            'updated_by' => get_current_user_id() ?: null,
            'updated_at' => $now,
            'is_deleted' => 0,
            'address' => isset($data['address']) ? sanitize_textarea_field($data['address']) : null,
            'city' => isset($data['city']) ? sanitize_text_field($data['city']) : null,
            'state' => isset($data['state']) ? sanitize_text_field($data['state']) : null,
            'country' => isset($data['country']) ? sanitize_text_field($data['country']) : null,
            'postal_code' => isset($data['postal_code']) ? sanitize_text_field($data['postal_code']) : null,
            'clan_location_id' => isset($data['clan_location_id']) && $data['clan_location_id'] !== '' ? intval($data['clan_location_id']) : null,
            'clan_surname_id' => isset($data['clan_surname_id']) && $data['clan_surname_id'] !== '' ? intval($data['clan_surname_id']) : null,
            'user_id' => isset($data['user_id']) && $data['user_id'] !== '' ? intval($data['user_id']) : null,
        ];

        $formats = array_fill(0, count($insert), '%s'); // wpdb will coerce where needed
        $res = $wpdb->insert($table, $insert, $formats);
        if ($res === false) {
            error_log('add_member failed: ' . $wpdb->last_error);
            return false;
        }
        return intval($wpdb->insert_id);
    }

    public static function update_member($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        $now = current_time('mysql');

        $update = [
            'clan_id' => !empty($data['clan_id']) ? intval($data['clan_id']) : null,
            'first_name' => isset($data['first_name']) ? sanitize_text_field($data['first_name']) : '',
            'last_name' => isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '',
            'birth_date' => !empty($data['birth_date']) ? sanitize_text_field($data['birth_date']) : null,
            'death_date' => !empty($data['death_date']) ? sanitize_text_field($data['death_date']) : null,
            'marriage_date' => !empty($data['marriage_date']) ? sanitize_text_field($data['marriage_date']) : null,
            'gender' => isset($data['gender']) ? sanitize_text_field($data['gender']) : null,
            'photo_url' => isset($data['photo_url']) ? esc_url_raw($data['photo_url']) : null,
            'biography' => isset($data['biography']) ? sanitize_textarea_field($data['biography']) : null,
            'parent1_id' => isset($data['parent1_id']) && $data['parent1_id'] !== '' ? intval($data['parent1_id']) : null,
            'parent2_id' => isset($data['parent2_id']) && $data['parent2_id'] !== '' ? intval($data['parent2_id']) : null,
            'updated_by' => get_current_user_id() ?: null,
            'updated_at' => $now,
            'address' => isset($data['address']) ? sanitize_textarea_field($data['address']) : null,
            'city' => isset($data['city']) ? sanitize_text_field($data['city']) : null,
            'state' => isset($data['state']) ? sanitize_text_field($data['state']) : null,
            'country' => isset($data['country']) ? sanitize_text_field($data['country']) : null,
            'postal_code' => isset($data['postal_code']) ? sanitize_text_field($data['postal_code']) : null,
            'clan_location_id' => isset($data['clan_location_id']) && $data['clan_location_id'] !== '' ? intval($data['clan_location_id']) : null,
            'clan_surname_id' => isset($data['clan_surname_id']) && $data['clan_surname_id'] !== '' ? intval($data['clan_surname_id']) : null,
            'user_id' => isset($data['user_id']) && $data['user_id'] !== '' ? intval($data['user_id']) : null,
        ];

        $res = $wpdb->update($table, $update, ['id' => intval($id)]);
        if ($res === false) {
            error_log('update_member failed: ' . $wpdb->last_error);
            return false;
        }
        return true;
    }

    // Soft delete / restore helpers
    public static function soft_delete_member($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        $now = current_time('mysql');
        $res = $wpdb->update($table, ['is_deleted' => 1, 'updated_by' => get_current_user_id(), 'updated_at' => $now], ['id' => intval($id)]);
        return $res !== false;
    }

    public static function restore_member($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        $now = current_time('mysql');
        $res = $wpdb->update($table, ['is_deleted' => 0, 'updated_by' => get_current_user_id(), 'updated_at' => $now], ['id' => intval($id)]);
        return $res !== false;
    }

    // Get single or multiple members
    public static function get_member($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($id)));
    }

    public static function get_members($limit = 1000, $offset = 0, $include_deleted = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        if ($include_deleted) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY last_name ASC LIMIT %d OFFSET %d", $limit, $offset));
        } else {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE COALESCE(is_deleted,0)=0 ORDER BY last_name ASC LIMIT %d OFFSET %d", $limit, $offset));
        }
    }

    // Clan & helpers (used by templates)
    public static function get_all_clans_simple() {
        global $wpdb;
        $table = $wpdb->prefix . 'family_clans';
        return $wpdb->get_results("SELECT id, clan_name FROM $table ORDER BY clan_name ASC");
    }

    public static function get_clan_locations($clan_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'clan_locations';
        return $wpdb->get_results($wpdb->prepare("SELECT id, location_name FROM $table WHERE clan_id = %d ORDER BY location_name ASC", intval($clan_id)));
    }

    public static function get_clan_surnames($clan_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'clan_surnames';
        return $wpdb->get_results($wpdb->prepare("SELECT id, last_name FROM $table WHERE clan_id = %d ORDER BY last_name ASC", intval($clan_id)));
    }
}
} // class_exists guard
?>