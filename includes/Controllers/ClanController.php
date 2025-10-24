<?php
/**
 * Clan Controller - Handles clan-related AJAX requests
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Controllers;

use FamilyTree\Config;

if (!defined('ABSPATH')) exit;

class ClanController extends BaseController {
    /**
     * Add a new clan
     */
    public function add(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_MANAGE_CLANS);

        $result = \FamilyTreeClanDatabase::add_clan($_POST);

        if (is_wp_error($result)) {
            $this->error($result->get_error_message());
        } else {
            $this->success('Clan added successfully');
        }
    }

    /**
     * Update an existing clan
     */
    public function update(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_MANAGE_CLANS);

        $clan_id = $this->get_post_int('clan_id');
        $ok = \FamilyTreeClanDatabase::update_clan($clan_id, $_POST);

        if ($ok) {
            $this->success('Clan updated successfully');
        } else {
            $this->error('Failed to update clan');
        }
    }

    /**
     * Delete a clan
     */
    public function delete(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_MANAGE_CLANS);

        $id = $this->get_post_int('id');
        \FamilyTreeClanDatabase::delete_clan($id);
        $this->success('Clan deleted');
    }

    /**
     * Get clan details (locations and surnames)
     */
    public function get_details(): void {
        $this->verify_nonce();
        $this->verify_logged_in();

        $clan_id = $this->get_post_int('clan_id');

        if (!$clan_id) {
            $this->error('Invalid clan id');
        }

        global $wpdb;
        $locations = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, location_name FROM {$wpdb->prefix}clan_locations WHERE clan_id = %d",
                $clan_id
            )
        );
        $surnames = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, last_name FROM {$wpdb->prefix}clan_surnames WHERE clan_id = %d",
                $clan_id
            )
        );

        $this->success([
            'locations' => $locations,
            'surnames' => $surnames
        ]);
    }

    /**
     * Get all clans (simple list)
     */
    public function get_all_simple(): void {
        $this->verify_nonce();
        $this->verify_logged_in();

        global $wpdb;
        $table = $wpdb->prefix . 'family_clans';
        $clans = $wpdb->get_results("SELECT id, clan_name FROM $table ORDER BY clan_name ASC");
        $this->success($clans);
    }
}
