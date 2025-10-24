<?php
/**
 * Member Controller - Handles member-related AJAX requests
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Controllers;

use FamilyTree\Config;

if (!defined('ABSPATH')) exit;

class MemberController extends BaseController {
    /**
     * Add a new family member
     */
    public function add(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $data = $_POST;

        // Validate first
        $validation_errors = \FamilyTreeDatabase::validate_member_data($data);
        if (!empty($validation_errors)) {
            $this->error(implode(' ', $validation_errors));
            return;
        }

        $result = \FamilyTreeDatabase::add_member($data);

        if ($result) {
            $this->success('Member added successfully');
        } else {
            $this->error('Failed to add member');
        }
    }

    /**
     * Update an existing family member
     */
    public function update(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $id = $this->get_post_int('member_id');

        // Validate first
        $validation_errors = \FamilyTreeDatabase::validate_member_data($_POST, $id);
        if (!empty($validation_errors)) {
            $this->error(implode(' ', $validation_errors));
            return;
        }

        $ok = \FamilyTreeDatabase::update_member($id, $_POST);

        if ($ok) {
            $this->success('Member updated successfully');
        } else {
            $this->error('Failed to update member');
        }
    }

    /**
     * Delete a family member (hard delete)
     */
    public function delete(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_DELETE_FAMILY_MEMBERS);

        $member_id = $this->get_post_int('id');

        // Check if member has children
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        $has_children = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE (parent1_id = %d OR parent2_id = %d) AND is_deleted = 0",
            $member_id,
            $member_id
        ));

        if ($has_children > 0) {
            $this->error('Cannot delete this member because they have ' . $has_children . ' child(ren). Please use soft delete instead or reassign children first.');
            return;
        }

        // Proceed with hard delete
        $wpdb->delete($table, ['id' => $member_id]);
        $this->success('Member permanently deleted');
    }

    /**
     * Soft delete a member
     */
    public function soft_delete(): void {
        $this->verify_nonce();

        if (!current_user_can(Config::CAP_MANAGE_FAMILY) && !current_user_can(Config::ROLE_SUPER_ADMIN)) {
            $this->error('Insufficient permissions');
            return;
        }

        $id = $this->get_post_int('member_id');

        if (!$id) {
            $this->error('Invalid member id');
            return;
        }

        $ok = \FamilyTreeDatabase::soft_delete_member($id);

        if ($ok) {
            $this->success('Member soft-deleted');
        } else {
            $this->error('Failed to delete member');
        }
    }

    /**
     * Restore a soft-deleted member
     */
    public function restore(): void {
        $this->verify_nonce();

        if (!current_user_can(Config::CAP_MANAGE_FAMILY) && !current_user_can(Config::ROLE_SUPER_ADMIN)) {
            $this->error('Insufficient permissions');
            return;
        }

        $id = $this->get_post_int('member_id');

        if (!$id) {
            $this->error('Invalid member id');
            return;
        }

        $ok = \FamilyTreeDatabase::restore_member($id);

        if ($ok) {
            $this->success('Member restored');
        } else {
            $this->error('Failed to restore member');
        }
    }

    /**
     * Search members for Select2 dropdown
     */
    public function search_select2(): void {
        $this->verify_nonce();
        $this->verify_logged_in();

        $query = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

        if (strlen($query) < 1) {
            wp_send_json([]);
            return;
        }

        $results = \FamilyTreeDatabase::search_members($query, 20);
        wp_send_json($results ?: []);
    }
}
