<?php
/**
 * Member Repository - Database operations for family members
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Repositories;

use FamilyTree\Config;

if (!defined('ABSPATH')) exit;

class MemberRepository extends BaseRepository {
    /**
     * Get table name
     */
    protected function get_table_name(): string {
        return Config::get_table_name(Config::TABLE_MEMBERS);
    }

    /**
     * Get members (excluding deleted by default)
     *
     * @param int $limit Limit
     * @param int $offset Offset
     * @param bool $include_deleted Include soft-deleted members
     * @return array
     */
    public function get_members(int $limit = 1000, int $offset = 0, bool $include_deleted = false): array {
        if ($include_deleted) {
            return $this->all($limit, $offset);
        }

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE COALESCE(is_deleted,0)=0 ORDER BY last_name ASC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
        return $results ?: [];
    }

    /**
     * Add a new member
     *
     * @param array $data Member data
     * @return int|false Member ID or false on failure
     */
    public function add(array $data): int|false {
        $now = $this->get_current_timestamp();

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
            'created_by' => $this->get_current_user_id(),
            'created_at' => $now,
            'updated_by' => $this->get_current_user_id(),
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

        $formats = array_fill(0, count($insert), '%s');
        $result = $this->wpdb->insert($this->table, $insert, $formats);

        if ($result === false) {
            error_log('MemberRepository::add failed: ' . $this->wpdb->last_error);
            return false;
        }

        return intval($this->wpdb->insert_id);
    }

    /**
     * Update a member
     *
     * @param int $id Member ID
     * @param array $data Member data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $now = $this->get_current_timestamp();

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
            'updated_by' => $this->get_current_user_id(),
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

        $result = $this->wpdb->update($this->table, $update, ['id' => $id]);

        if ($result === false) {
            error_log('MemberRepository::update failed: ' . $this->wpdb->last_error);
            return false;
        }

        return true;
    }

    /**
     * Soft delete a member
     *
     * @param int $id Member ID
     * @return bool
     */
    public function soft_delete(int $id): bool {
        $now = $this->get_current_timestamp();
        $result = $this->wpdb->update(
            $this->table,
            [
                'is_deleted' => 1,
                'updated_by' => $this->get_current_user_id(),
                'updated_at' => $now
            ],
            ['id' => $id]
        );
        return $result !== false;
    }

    /**
     * Restore a soft-deleted member
     *
     * @param int $id Member ID
     * @return bool
     */
    public function restore(int $id): bool {
        $now = $this->get_current_timestamp();
        $result = $this->wpdb->update(
            $this->table,
            [
                'is_deleted' => 0,
                'updated_by' => $this->get_current_user_id(),
                'updated_at' => $now
            ],
            ['id' => $id]
        );
        return $result !== false;
    }

    /**
     * Search members
     *
     * @param string $query Search query
     * @param int $limit Limit results
     * @return array
     */
    public function search(string $query, int $limit = 20): array {
        $q = '%' . $this->wpdb->esc_like($query) . '%';

        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT id, first_name, last_name, birth_date FROM {$this->table}
             WHERE (first_name LIKE %s OR last_name LIKE %s)
             AND is_deleted = 0
             ORDER BY last_name, first_name
             LIMIT %d",
            $q,
            $q,
            $limit
        ));

        return $results ?: [];
    }

    /**
     * Get tree data (for visualization)
     *
     * @return array
     */
    public function get_tree_data(): array {
        $clans_table = Config::get_table_name(Config::TABLE_CLANS);

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
            FROM {$this->table} m
            LEFT JOIN {$clans_table} c ON m.clan_id = c.id
            WHERE COALESCE(m.is_deleted,0)=0
            ORDER BY m.last_name, m.first_name
        ";

        $results = $this->wpdb->get_results($sql);
        return $results ?: [];
    }
}
