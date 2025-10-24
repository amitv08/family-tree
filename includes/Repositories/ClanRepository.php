<?php
/**
 * Clan Repository - Database operations for clans
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Repositories;

use FamilyTree\Config;

if (!defined('ABSPATH')) exit;

class ClanRepository extends BaseRepository {
    /**
     * Get table name
     */
    protected function get_table_name(): string {
        return Config::get_table_name(Config::TABLE_CLANS);
    }

    /**
     * Get all clans with locations and surnames
     *
     * @return array
     */
    public function get_all_with_details(): array {
        $locations_table = Config::get_table_name(Config::TABLE_CLAN_LOCATIONS);
        $surnames_table = Config::get_table_name(Config::TABLE_CLAN_SURNAMES);

        $clans = $this->wpdb->get_results("SELECT * FROM {$this->table} ORDER BY clan_name ASC");

        if (!$clans) {
            return [];
        }

        foreach ($clans as $clan) {
            // Get locations as array of strings
            $locations = $this->wpdb->get_col($this->wpdb->prepare(
                "SELECT location_name FROM {$locations_table} WHERE clan_id = %d",
                $clan->id
            ));
            $clan->locations = is_array($locations) ? $locations : [];

            // Get surnames as array of strings
            $surnames = $this->wpdb->get_col($this->wpdb->prepare(
                "SELECT last_name FROM {$surnames_table} WHERE clan_id = %d",
                $clan->id
            ));
            $clan->surnames = is_array($surnames) ? $surnames : [];
        }

        return $clans;
    }

    /**
     * Get single clan with details (as objects)
     *
     * @param int $id Clan ID
     * @return object|null
     */
    public function get_with_details(int $id): ?object {
        $locations_table = Config::get_table_name(Config::TABLE_CLAN_LOCATIONS);
        $surnames_table = Config::get_table_name(Config::TABLE_CLAN_SURNAMES);

        $clan = $this->find($id);
        if (!$clan) return null;

        // Get locations as objects
        $clan_locations = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT id, location_name FROM {$locations_table} WHERE clan_id = %d",
            $id
        ), OBJECT);
        $clan->locations = is_array($clan_locations) ? $clan_locations : [];

        // Get surnames as objects
        $clan_surnames = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT id, last_name FROM {$surnames_table} WHERE clan_id = %d",
            $id
        ), OBJECT);
        $clan->surnames = is_array($clan_surnames) ? $clan_surnames : [];

        return $clan;
    }

    /**
     * Get all clans (simple list with id and name)
     *
     * @return array
     */
    public function get_all_simple(): array {
        $results = $this->wpdb->get_results(
            "SELECT id, clan_name FROM {$this->table} ORDER BY clan_name ASC"
        );
        return $results ?: [];
    }

    /**
     * Get clan name by ID
     *
     * @param int $clan_id Clan ID
     * @return string Clan name or empty string
     */
    public function get_clan_name(int $clan_id): string {
        if (empty($clan_id)) return '';

        $name = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT clan_name FROM {$this->table} WHERE id = %d",
            $clan_id
        ));

        return $name ?: '';
    }

    /**
     * Get locations for a clan
     *
     * @param int $clan_id Clan ID
     * @return array
     */
    public function get_locations(int $clan_id): array {
        $locations_table = Config::get_table_name(Config::TABLE_CLAN_LOCATIONS);
        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT id, location_name FROM {$locations_table} WHERE clan_id = %d ORDER BY location_name ASC",
            $clan_id
        ));
        return $results ?: [];
    }

    /**
     * Get surnames for a clan
     *
     * @param int $clan_id Clan ID
     * @return array
     */
    public function get_surnames(int $clan_id): array {
        $surnames_table = Config::get_table_name(Config::TABLE_CLAN_SURNAMES);
        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT id, last_name FROM {$surnames_table} WHERE clan_id = %d ORDER BY last_name ASC",
            $clan_id
        ));
        return $results ?: [];
    }
}
