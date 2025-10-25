<?php
/**
 * Marriage Repository - Database operations for marriages
 *
 * @package FamilyTree
 * @since 3.0.0
 */

namespace FamilyTree\Repositories;

use FamilyTree\Config;

if (!defined('ABSPATH')) exit;

class MarriageRepository extends BaseRepository {
    /**
     * Get table name
     */
    protected function get_table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'family_marriages';
    }

    /**
     * Add a new marriage
     *
     * @param array $data Marriage data
     * @return int|false Marriage ID or false on failure
     */
    public function add(array $data) {
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

        $res = $this->wpdb->insert($this->table, $insert);
        if ($res === false) {
            error_log('MarriageRepository::add failed: ' . $this->wpdb->last_error);
            return false;
        }
        return intval($this->wpdb->insert_id);
    }

    /**
     * Update an existing marriage
     *
     * @param int $id Marriage ID
     * @param array $data Marriage data
     * @return bool
     */
    public function update(int $id, array $data): bool {
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

        $res = $this->wpdb->update($this->table, $update, ['id' => $id]);
        if ($res === false) {
            error_log('MarriageRepository::update failed: ' . $this->wpdb->last_error);
            return false;
        }
        return true;
    }

    /**
     * Get all marriages for a specific member (as husband or wife)
     *
     * @param int $member_id Member ID
     * @return array
     */
    public function get_marriages_for_member(int $member_id): array {
        $members_table = Config::get_table_name(Config::TABLE_MEMBERS);

        $sql = "
            SELECT
                m.*,
                h.first_name as husband_first_name,
                h.middle_name as husband_middle_name,
                h.last_name as husband_last_name,
                w.first_name as wife_first_name,
                w.middle_name as wife_middle_name,
                w.last_name as wife_last_name
            FROM {$this->table} m
            LEFT JOIN {$members_table} h ON m.husband_id = h.id
            LEFT JOIN {$members_table} w ON m.wife_id = w.id
            WHERE m.husband_id = %d OR m.wife_id = %d
            ORDER BY m.marriage_date ASC, m.marriage_order ASC
        ";

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $member_id, $member_id)
        );

        return $results ?: [];
    }

    /**
     * Get all children from a specific marriage
     *
     * @param int $marriage_id Marriage ID
     * @return array
     */
    public function get_children_for_marriage(int $marriage_id): array {
        $members_table = Config::get_table_name(Config::TABLE_MEMBERS);

        $sql = "
            SELECT id, first_name, middle_name, last_name, birth_date, gender
            FROM {$members_table}
            WHERE parent_marriage_id = %d
            AND COALESCE(is_deleted, 0) = 0
            ORDER BY birth_date ASC
        ";

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $marriage_id)
        );

        return $results ?: [];
    }

    /**
     * Get all children for multiple marriages in one query (avoids N+1 problem)
     *
     * @param array $marriage_ids Array of marriage IDs
     * @return array Associative array keyed by marriage_id
     */
    public function get_children_for_marriages(array $marriage_ids): array {
        if (empty($marriage_ids)) {
            return [];
        }

        $members_table = Config::get_table_name(Config::TABLE_MEMBERS);

        // Create placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($marriage_ids), '%d'));

        $sql = "
            SELECT id, first_name, middle_name, last_name, birth_date, gender, parent_marriage_id
            FROM {$members_table}
            WHERE parent_marriage_id IN ($placeholders)
            AND COALESCE(is_deleted, 0) = 0
            ORDER BY parent_marriage_id, birth_date ASC
        ";

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, ...$marriage_ids)
        );

        // Group children by marriage_id
        $grouped = [];
        foreach ($results as $child) {
            $marriage_id = $child->parent_marriage_id;
            if (!isset($grouped[$marriage_id])) {
                $grouped[$marriage_id] = [];
            }
            $grouped[$marriage_id][] = $child;
        }

        return $grouped;
    }

    /**
     * Get a single marriage with spouse details
     *
     * @param int $id Marriage ID
     * @return object|null
     */
    public function get_marriage_with_details(int $id): ?object {
        $members_table = Config::get_table_name(Config::TABLE_MEMBERS);

        $sql = "
            SELECT
                m.*,
                h.first_name as husband_first_name,
                h.middle_name as husband_middle_name,
                h.last_name as husband_last_name,
                w.first_name as wife_first_name,
                w.middle_name as wife_middle_name,
                w.last_name as wife_last_name
            FROM {$this->table} m
            LEFT JOIN {$members_table} h ON m.husband_id = h.id
            LEFT JOIN {$members_table} w ON m.wife_id = w.id
            WHERE m.id = %d
        ";

        $result = $this->wpdb->get_row(
            $this->wpdb->prepare($sql, $id)
        );

        return $result ?: null;
    }

    /**
     * Get marriages with full details (including spouse names and children count)
     *
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function get_all_marriages(int $limit = 100, int $offset = 0): array {
        $members_table = Config::get_table_name(Config::TABLE_MEMBERS);

        $sql = "
            SELECT
                m.*,
                h.first_name as husband_first_name,
                h.middle_name as husband_middle_name,
                h.last_name as husband_last_name,
                w.first_name as wife_first_name,
                w.middle_name as wife_middle_name,
                w.last_name as wife_last_name,
                (SELECT COUNT(*) FROM {$members_table} WHERE parent_marriage_id = m.id AND COALESCE(is_deleted,0)=0) as children_count
            FROM {$this->table} m
            LEFT JOIN {$members_table} h ON m.husband_id = h.id
            LEFT JOIN {$members_table} w ON m.wife_id = w.id
            ORDER BY m.marriage_date DESC
            LIMIT %d OFFSET %d
        ";

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $limit, $offset)
        );

        return $results ?: [];
    }
}
