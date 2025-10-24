<?php
/**
 * Base Repository for database operations
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Repositories;

if (!defined('ABSPATH')) exit;

abstract class BaseRepository {
    /**
     * WordPress database object
     *
     * @var \wpdb
     */
    protected \wpdb $wpdb;

    /**
     * Table name (with prefix)
     *
     * @var string
     */
    protected string $table;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $this->get_table_name();
    }

    /**
     * Get the table name for this repository
     * Must be implemented by child classes
     *
     * @return string Table name with prefix
     */
    abstract protected function get_table_name(): string;

    /**
     * Get current user ID
     *
     * @return int
     */
    protected function get_current_user_id(): int {
        return get_current_user_id() ?: 0;
    }

    /**
     * Get current timestamp
     *
     * @return string MySQL formatted timestamp
     */
    protected function get_current_timestamp(): string {
        return current_time('mysql');
    }

    /**
     * Find record by ID
     *
     * @param int $id Record ID
     * @return object|null
     */
    public function find(int $id): ?object {
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id)
        );
        return $result ?: null;
    }

    /**
     * Get all records
     *
     * @param int $limit Limit number of results
     * @param int $offset Offset for pagination
     * @return array
     */
    public function all(int $limit = 1000, int $offset = 0): array {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
        return $results ?: [];
    }

    /**
     * Delete record by ID
     *
     * @param int $id Record ID
     * @return bool
     */
    public function delete(int $id): bool {
        return (bool) $this->wpdb->delete($this->table, ['id' => $id]);
    }

    /**
     * Count total records
     *
     * @return int
     */
    public function count(): int {
        return (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }
}
