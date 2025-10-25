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

        // Marriages table (Phase 2 - Multiple marriages support)
        $marriages_table = $wpdb->prefix . 'family_marriages';
        $sql_marriages = "CREATE TABLE IF NOT EXISTS $marriages_table (
            id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            husband_id MEDIUMINT(9) DEFAULT NULL,
            husband_name VARCHAR(200) DEFAULT NULL,
            wife_id MEDIUMINT(9) DEFAULT NULL,
            wife_name VARCHAR(200) DEFAULT NULL,
            marriage_date DATE DEFAULT NULL,
            marriage_location VARCHAR(200) DEFAULT NULL,
            marriage_order TINYINT DEFAULT 1,
            marriage_status VARCHAR(20) DEFAULT 'married',
            divorce_date DATE DEFAULT NULL,
            end_date DATE DEFAULT NULL,
            end_reason VARCHAR(100) DEFAULT NULL,
            notes TEXT NULL,
            created_by MEDIUMINT(9) DEFAULT NULL,
            updated_by MEDIUMINT(9) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_marriages);

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
            "ADD COLUMN middle_name VARCHAR(100) DEFAULT NULL COMMENT 'Middle name or initial'",
            "ADD COLUMN last_name VARCHAR(100) NOT NULL",
            "ADD COLUMN birth_date DATE DEFAULT NULL",
            "ADD COLUMN death_date DATE DEFAULT NULL",
            "ADD COLUMN gender VARCHAR(20) DEFAULT NULL",
            "ADD COLUMN photo_url VARCHAR(255) DEFAULT NULL",
            "ADD COLUMN biography TEXT NULL",
            "ADD COLUMN parent1_id MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN parent2_id MEDIUMINT(9) DEFAULT NULL",
            "ADD COLUMN parent2_name VARCHAR(200) DEFAULT NULL",
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
            // Phase 1 additions - October 2025
            "ADD COLUMN is_adopted TINYINT(1) DEFAULT 0 COMMENT 'Is this person adopted?'",
            "ADD COLUMN maiden_name VARCHAR(100) DEFAULT NULL COMMENT 'Birth surname before marriage'",
            "ADD COLUMN nickname VARCHAR(100) DEFAULT NULL COMMENT 'Common name or nickname'",
            // Phase 2 additions - Multiple marriages support
            "ADD COLUMN parent_marriage_id MEDIUMINT(9) DEFAULT NULL COMMENT 'Which marriage produced this child'",
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
        $nullable_cols = ['clan_id','parent1_id','parent2_id','clan_location_id','clan_surname_id','user_id','parent_marriage_id'];
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

            // Phase 2 - Marriages table foreign keys
            // parent_marriage_id -> marriages.id
            if (!$exists_fk($wpdb->prefix.'family_members','fk_members_parent_marriage')) {
                $wpdb->query("ALTER TABLE {$wpdb->prefix}family_members ADD CONSTRAINT fk_members_parent_marriage FOREIGN KEY (parent_marriage_id) REFERENCES {$wpdb->prefix}family_marriages(id) ON DELETE SET NULL ON UPDATE CASCADE");
            }

            // marriages.husband_id -> members.id
            if (!$exists_fk($wpdb->prefix.'family_marriages','fk_marriage_husband')) {
                $wpdb->query("ALTER TABLE {$wpdb->prefix}family_marriages ADD CONSTRAINT fk_marriage_husband FOREIGN KEY (husband_id) REFERENCES {$wpdb->prefix}family_members(id) ON DELETE SET NULL ON UPDATE CASCADE");
            }

            // marriages.wife_id -> members.id
            if (!$exists_fk($wpdb->prefix.'family_marriages','fk_marriage_wife')) {
                $wpdb->query("ALTER TABLE {$wpdb->prefix}family_marriages ADD CONSTRAINT fk_marriage_wife FOREIGN KEY (wife_id) REFERENCES {$wpdb->prefix}family_members(id) ON DELETE SET NULL ON UPDATE CASCADE");
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
            'middle_name' => isset($data['middle_name']) ? sanitize_text_field($data['middle_name']) : null,
            'last_name' => isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '',
            'birth_date' => !empty($data['birth_date']) ? sanitize_text_field($data['birth_date']) : null,
            'death_date' => !empty($data['death_date']) ? sanitize_text_field($data['death_date']) : null,
            'marriage_date' => !empty($data['marriage_date']) ? sanitize_text_field($data['marriage_date']) : null,
            'gender' => isset($data['gender']) ? sanitize_text_field($data['gender']) : null,
            'photo_url' => isset($data['photo_url']) ? esc_url_raw($data['photo_url']) : null,
            'biography' => isset($data['biography']) ? sanitize_textarea_field($data['biography']) : null,
            'parent1_id' => isset($data['parent1_id']) && $data['parent1_id'] !== '' ? intval($data['parent1_id']) : null,
            'parent2_id' => isset($data['parent2_id']) && $data['parent2_id'] !== '' ? intval($data['parent2_id']) : null,
            'parent2_name' => isset($data['parent2_name']) ? sanitize_text_field($data['parent2_name']) : null,
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
            // Phase 1 additions
            'is_adopted' => isset($data['is_adopted']) ? intval($data['is_adopted']) : 0,
            'maiden_name' => isset($data['maiden_name']) ? sanitize_text_field($data['maiden_name']) : null,
            'nickname' => isset($data['nickname']) ? sanitize_text_field($data['nickname']) : null,
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
            'middle_name' => isset($data['middle_name']) ? sanitize_text_field($data['middle_name']) : null,
            'last_name' => isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '',
            'birth_date' => !empty($data['birth_date']) ? sanitize_text_field($data['birth_date']) : null,
            'death_date' => !empty($data['death_date']) ? sanitize_text_field($data['death_date']) : null,
            'marriage_date' => !empty($data['marriage_date']) ? sanitize_text_field($data['marriage_date']) : null,
            'gender' => isset($data['gender']) ? sanitize_text_field($data['gender']) : null,
            'photo_url' => isset($data['photo_url']) ? esc_url_raw($data['photo_url']) : null,
            'biography' => isset($data['biography']) ? sanitize_textarea_field($data['biography']) : null,
            'parent1_id' => isset($data['parent1_id']) && $data['parent1_id'] !== '' ? intval($data['parent1_id']) : null,
            'parent2_id' => isset($data['parent2_id']) && $data['parent2_id'] !== '' ? intval($data['parent2_id']) : null,
            'parent2_name' => isset($data['parent2_name']) ? sanitize_text_field($data['parent2_name']) : null,
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
            // Phase 1 additions
            'is_adopted' => isset($data['is_adopted']) ? intval($data['is_adopted']) : 0,
            'maiden_name' => isset($data['maiden_name']) ? sanitize_text_field($data['maiden_name']) : null,
            'nickname' => isset($data['nickname']) ? sanitize_text_field($data['nickname']) : null,
        ];

        $res = $wpdb->update($table, $update, ['id' => intval($id)]);
        if ($res === false) {
            error_log('update_member failed: ' . $wpdb->last_error);
            return false;
        }
        return true;
    }

    public static function search_members($query, $limit = 20) {
    global $wpdb;
    $table = $wpdb->prefix . 'family_members';
    $q = '%' . $wpdb->esc_like($query) . '%';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT id, first_name, last_name, birth_date FROM $table 
         WHERE (first_name LIKE %s OR last_name LIKE %s)
         AND is_deleted = 0
         ORDER BY last_name, first_name
         LIMIT %d",
        $q, $q, $limit
    ));
}

public static function validate_member_data($data, $member_id = null) {
    $errors = [];

    // If editing, check against current ID
    $current_id = $member_id ? intval($member_id) : null;

    // Check: Required field lengths (matching database schema)
    if (isset($data['first_name']) && strlen($data['first_name']) > 100) {
        $errors[] = 'First name is too long (maximum 100 characters).';
    }
    if (isset($data['last_name']) && strlen($data['last_name']) > 100) {
        $errors[] = 'Last name is too long (maximum 100 characters).';
    }
    if (isset($data['gender']) && strlen($data['gender']) > 20) {
        $errors[] = 'Gender value is too long (maximum 20 characters).';
    }
    if (isset($data['photo_url']) && strlen($data['photo_url']) > 255) {
        $errors[] = 'Photo URL is too long (maximum 255 characters).';
    }

    // Check: Photo URL must be a valid image format
    if (!empty($data['photo_url'])) {
        $url = $data['photo_url'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $parsed_url = parse_url($url);

        if ($parsed_url && isset($parsed_url['path'])) {
            $path_info = pathinfo($parsed_url['path']);
            $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

            if (!empty($extension) && !in_array($extension, $allowed_extensions)) {
                $errors[] = 'Photo URL must be a valid image file (jpg, jpeg, png, gif, webp, bmp, or svg).';
            }
        }
    }
    if (isset($data['biography']) && strlen($data['biography']) > 10000) {
        $errors[] = 'Biography is too long (maximum 10,000 characters).';
    }
    if (isset($data['address']) && strlen($data['address']) > 500) {
        $errors[] = 'Address is too long (maximum 500 characters).';
    }
    if (isset($data['city']) && strlen($data['city']) > 100) {
        $errors[] = 'City is too long (maximum 100 characters).';
    }
    if (isset($data['state']) && strlen($data['state']) > 100) {
        $errors[] = 'State is too long (maximum 100 characters).';
    }
    if (isset($data['country']) && strlen($data['country']) > 100) {
        $errors[] = 'Country is too long (maximum 100 characters).';
    }
    if (isset($data['postal_code']) && strlen($data['postal_code']) > 20) {
        $errors[] = 'Postal code is too long (maximum 20 characters).';
    }

    $parent1_id = !empty($data['parent1_id']) ? intval($data['parent1_id']) : null;
    $parent2_id = !empty($data['parent2_id']) ? intval($data['parent2_id']) : null;

    // Check: Person cannot be their own parent
    if ($current_id && ($parent1_id == $current_id || $parent2_id == $current_id)) {
        $errors[] = 'A person cannot be their own parent.';
    }

    // Check: Parent 1 and Parent 2 must be different
    if ($parent1_id && $parent2_id && $parent1_id == $parent2_id) {
        $errors[] = 'Parent 1 and Parent 2 must be different people.';
    }
    
    // Check: Dates make sense
    if (!empty($data['birth_date']) && !empty($data['death_date'])) {
        $birth = strtotime($data['birth_date']);
        $death = strtotime($data['death_date']);
        
        if ($death < $birth) {
            $errors[] = 'Death date cannot be before birth date.';
        }
        
        $age = ($death - $birth) / (365.25 * 24 * 60 * 60);
        if ($age > 150) {
            $errors[] = 'Age seems unrealistic (over 150 years). Please verify dates.';
        }
    }
    
    // Check: Birth year is reasonable
    if (!empty($data['birth_date'])) {
        $birth_year = (int)date('Y', strtotime($data['birth_date']));
        $current_year = date('Y');
        
        if ($birth_year < 1800) {
            $errors[] = 'Birth year seems too old. Please verify.';
        }
        if ($birth_year > $current_year) {
            $errors[] = 'Birth year cannot be in the future.';
        }
    }
    
    return $errors;
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

    /* -------------------------
     * CRUD: Marriages (Phase 2 - Multiple marriages support)
     * ------------------------- */

    /**
     * Add a new marriage record
     */
    public static function add_marriage($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_marriages';
        $now = current_time('mysql');

        $insert = [
            'husband_id' => isset($data['husband_id']) && $data['husband_id'] !== '' ? intval($data['husband_id']) : null,
            'husband_name' => isset($data['husband_name']) ? sanitize_text_field($data['husband_name']) : null,
            'wife_id' => isset($data['wife_id']) && $data['wife_id'] !== '' ? intval($data['wife_id']) : null,
            'wife_name' => isset($data['wife_name']) ? sanitize_text_field($data['wife_name']) : null,
            'marriage_date' => !empty($data['marriage_date']) ? sanitize_text_field($data['marriage_date']) : null,
            'marriage_location' => isset($data['marriage_location']) ? sanitize_text_field($data['marriage_location']) : null,
            'marriage_order' => isset($data['marriage_order']) ? intval($data['marriage_order']) : 1,
            'marriage_status' => isset($data['marriage_status']) ? sanitize_text_field($data['marriage_status']) : 'married',
            'divorce_date' => !empty($data['divorce_date']) ? sanitize_text_field($data['divorce_date']) : null,
            'end_date' => !empty($data['end_date']) ? sanitize_text_field($data['end_date']) : null,
            'end_reason' => isset($data['end_reason']) ? sanitize_text_field($data['end_reason']) : null,
            'notes' => isset($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'created_by' => get_current_user_id() ?: null,
            'updated_by' => get_current_user_id() ?: null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $res = $wpdb->insert($table, $insert);
        if ($res === false) {
            error_log('add_marriage failed: ' . $wpdb->last_error);
            return false;
        }
        return intval($wpdb->insert_id);
    }

    /**
     * Update an existing marriage record
     */
    public static function update_marriage($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_marriages';
        $now = current_time('mysql');

        $update = [
            'husband_id' => isset($data['husband_id']) && $data['husband_id'] !== '' ? intval($data['husband_id']) : null,
            'husband_name' => isset($data['husband_name']) ? sanitize_text_field($data['husband_name']) : null,
            'wife_id' => isset($data['wife_id']) && $data['wife_id'] !== '' ? intval($data['wife_id']) : null,
            'wife_name' => isset($data['wife_name']) ? sanitize_text_field($data['wife_name']) : null,
            'marriage_date' => !empty($data['marriage_date']) ? sanitize_text_field($data['marriage_date']) : null,
            'marriage_location' => isset($data['marriage_location']) ? sanitize_text_field($data['marriage_location']) : null,
            'marriage_order' => isset($data['marriage_order']) ? intval($data['marriage_order']) : 1,
            'marriage_status' => isset($data['marriage_status']) ? sanitize_text_field($data['marriage_status']) : 'married',
            'divorce_date' => !empty($data['divorce_date']) ? sanitize_text_field($data['divorce_date']) : null,
            'end_date' => !empty($data['end_date']) ? sanitize_text_field($data['end_date']) : null,
            'end_reason' => isset($data['end_reason']) ? sanitize_text_field($data['end_reason']) : null,
            'notes' => isset($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'updated_by' => get_current_user_id() ?: null,
            'updated_at' => $now,
        ];

        $res = $wpdb->update($table, $update, ['id' => intval($id)]);
        if ($res === false) {
            error_log('update_marriage failed: ' . $wpdb->last_error);
            return false;
        }
        return true;
    }

    /**
     * Delete a marriage record
     */
    public static function delete_marriage($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_marriages';
        $res = $wpdb->delete($table, ['id' => intval($id)]);
        return $res !== false;
    }

    /**
     * Get a single marriage by ID
     */
    public static function get_marriage($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_marriages';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($id)));
    }

    /**
     * Get all marriages for a specific member (as husband or wife)
     */
    public static function get_marriages_for_member($member_id) {
        global $wpdb;
        $marriages_table = $wpdb->prefix . 'family_marriages';
        $members_table = $wpdb->prefix . 'family_members';

        $sql = "
            SELECT
                m.*,
                h.first_name as husband_first_name,
                h.last_name as husband_last_name,
                w.first_name as wife_first_name,
                w.last_name as wife_last_name
            FROM $marriages_table m
            LEFT JOIN $members_table h ON m.husband_id = h.id
            LEFT JOIN $members_table w ON m.wife_id = w.id
            WHERE m.husband_id = %d OR m.wife_id = %d
            ORDER BY m.marriage_date ASC, m.marriage_order ASC
        ";

        return $wpdb->get_results($wpdb->prepare($sql, intval($member_id), intval($member_id)));
    }

    /**
     * Get all children from a specific marriage
     */
    public static function get_children_for_marriage($marriage_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';

        $sql = "
            SELECT id, first_name, middle_name, last_name, birth_date, gender
            FROM $table
            WHERE parent_marriage_id = %d
            AND COALESCE(is_deleted, 0) = 0
            ORDER BY birth_date ASC
        ";

        return $wpdb->get_results($wpdb->prepare($sql, intval($marriage_id)));
    }

    /**
     * Migrate existing marriage_date data to marriages table
     * Called during plugin activation if needed
     */
    public static function migrate_existing_marriages() {
        global $wpdb;
        $members_table = $wpdb->prefix . 'family_members';
        $marriages_table = $wpdb->prefix . 'family_marriages';

        // Check if migration already happened by looking for any migrated marriages
        $migrated_count = $wpdb->get_var("SELECT COUNT(*) FROM $marriages_table WHERE notes LIKE '%Migrated from old%'");
        if ($migrated_count > 0) {
            error_log('migrate_existing_marriages: Migration already completed, skipping');
            return; // Already migrated
        }

        // Get all members with marriage_date set
        $members_with_marriage = $wpdb->get_results("
            SELECT id, first_name, last_name, gender, marriage_date
            FROM $members_table
            WHERE marriage_date IS NOT NULL
        ");

        if (empty($members_with_marriage)) {
            error_log('migrate_existing_marriages: No members with marriage_date found');
            return;
        }

        $migrated = 0;
        foreach ($members_with_marriage as $member) {
            // Determine husband/wife based on gender
            $data = [
                'marriage_date' => $member->marriage_date,
                'marriage_order' => 1,
                'marriage_status' => 'married',
                'notes' => 'Migrated from old marriage_date field',
                'created_by' => get_current_user_id() ?: null,
                'updated_by' => get_current_user_id() ?: null,
            ];

            if (strtolower($member->gender) === 'male') {
                $data['husband_id'] = $member->id;
            } elseif (strtolower($member->gender) === 'female') {
                $data['wife_id'] = $member->id;
            } else {
                // For unknown gender, store as husband_id
                $data['husband_id'] = $member->id;
            }

            $marriage_id = self::add_marriage($data);
            if ($marriage_id) {
                $migrated++;
                error_log("migrate_existing_marriages: Created marriage ID {$marriage_id} for member {$member->id}");
            }
        }

        error_log("migrate_existing_marriages: Successfully migrated {$migrated} marriages");
    }
}
} // class_exists guard
?>