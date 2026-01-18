<?php
/**
 * Member Controller - Handles member-related AJAX requests
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Controllers;

use FamilyTree\Config;
use FamilyTree\Repositories\MarriageRepository;
use FamilyTree\Repositories\MemberRepository;
use FamilyTree\Validators\MemberValidator;

if (!defined('ABSPATH')) exit;

class MemberController extends BaseController {
    private MarriageRepository $marriage_repository;
    private MemberRepository $member_repository;

    public function __construct() {
        $this->marriage_repository = new MarriageRepository();
        $this->member_repository = new MemberRepository();
    }
    /**
     * Add a new family member
     */
    public function add(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $data = $_POST;

        // Validate first
        $validation_errors = MemberValidator::validate($data);
        if (!empty($validation_errors)) {
            $this->error(implode(' ', $validation_errors));
            return;
        }

        $member_id = $this->member_repository->add($data);

        if ($member_id) {
            // Handle marriage data if marital status is married, divorced, or widowed
            $this->handle_marriage_save($member_id, $data);

            $this->success(['message' => 'Member added successfully', 'member_id' => $member_id]);
        } else {
            $this->error('Failed to add member');
        }
    }

    /**
     * Get family members
     */
    public function get_members(): void {
        $this->verify_nonce();
        
        $limit = $this->get_post_int('limit', 50);
        $offset = $this->get_post_int('offset', 0);
        
        $members = $this->member_repository->get_members($limit, $offset);
        
        error_log('Family Tree: get_members returning ' . count($members) . ' members');
        
        $this->success(['members' => $members]);
    }

    /**
     * Update an existing family member
     */
    public function update(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $id = $this->get_post_int('member_id');

        // Validate first
        $validation_errors = MemberValidator::validate($_POST, $id);
        if (!empty($validation_errors)) {
            $this->error(implode(' ', $validation_errors));
            return;
        }

        $ok = $this->member_repository->update($id, $_POST);

        if ($ok) {
            // Handle marriage data if marital status is married, divorced, or widowed
            $this->handle_marriage_save($id, $_POST);

            $this->success(['message' => 'Member updated successfully']);
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

        $ok = $this->member_repository->soft_delete($id);

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

        $ok = $this->member_repository->restore($id);

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

        $results = $this->member_repository->search($query, 20);
        wp_send_json($results ?: []);
    }

    /**
     * Handle marriage save/update when member is saved
     *
     * @param int $member_id Member ID
     * @param array $data Form data
     * @return void
     */
    private function handle_marriage_save(int $member_id, array $data): void {
        $marital_status = $data['marital_status'] ?? 'unmarried';

        // Only process if marital status requires marriage details
        if (!in_array($marital_status, ['married', 'divorced', 'widowed'])) {
            return;
        }

        // Check if spouse name is provided
        $spouse_name = $data['spouse_name'] ?? '';
        if (empty($spouse_name)) {
            return; // No spouse name, skip marriage save
        }

        // Get member data to determine gender
        $member = $this->member_repository->find($member_id);
        if (!$member) {
            return;
        }

        // Prepare marriage data
        $marriage_data = [
            'marriage_date' => $data['marriage_date'] ?? null,
            'marriage_location' => $data['marriage_location'] ?? null,
            'marriage_status' => $marital_status,
            'notes' => $data['marriage_notes'] ?? null,
        ];

        // Set husband/wife based on member gender
        $gender = strtolower($member->gender ?? 'other');
        if ($gender === 'male') {
            $marriage_data['husband_id'] = $member_id;
            $marriage_data['wife_name'] = $spouse_name;
        } elseif ($gender === 'female') {
            $marriage_data['wife_id'] = $member_id;
            $marriage_data['husband_name'] = $spouse_name;
        } else {
            // For 'other' or unknown gender, default to husband
            $marriage_data['husband_id'] = $member_id;
            $marriage_data['wife_name'] = $spouse_name;
        }

        // Add divorce date if status is divorced
        if ($marital_status === 'divorced') {
            $marriage_data['divorce_date'] = $data['divorce_date'] ?? null;
        }

        // Check if updating existing marriage or creating new one
        $existing_marriage_id = isset($data['existing_marriage_id']) ? intval($data['existing_marriage_id']) : 0;

        if ($existing_marriage_id > 0) {
            // Update existing marriage
            $this->marriage_repository->update($existing_marriage_id, $marriage_data);
        } else {
            // Check if there's already a marriage for this member
            $existing_marriages = $this->marriage_repository->get_marriages_for_member($member_id);
            if (!empty($existing_marriages)) {
                // Update the latest marriage
                $latest = end($existing_marriages);
                $this->marriage_repository->update($latest->id, $marriage_data);
            } else {
                // Create new marriage
                $this->marriage_repository->add($marriage_data);
            }
        }
    }
}
