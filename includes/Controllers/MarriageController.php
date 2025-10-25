<?php
/**
 * Marriage Controller - Handles marriage-related AJAX requests
 *
 * @package FamilyTree
 * @since 3.0.0
 */

namespace FamilyTree\Controllers;

use FamilyTree\Config;
use FamilyTree\Repositories\MarriageRepository;

if (!defined('ABSPATH')) exit;

class MarriageController extends BaseController {
    private MarriageRepository $repository;

    public function __construct() {
        $this->repository = new MarriageRepository();
    }

    /**
     * Add a new marriage
     */
    public function add(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $data = $_POST;

        // Validate required fields
        if (empty($data['husband_id']) && empty($data['husband_name'])) {
            $this->error('Either husband member or husband name must be provided');
            return;
        }

        if (empty($data['wife_id']) && empty($data['wife_name'])) {
            $this->error('Either wife member or wife name must be provided');
            return;
        }

        // Auto-calculate marriage_order if not provided
        if (empty($data['marriage_order'])) {
            $member_id = !empty($data['husband_id']) ? intval($data['husband_id']) : intval($data['wife_id']);
            $existing_marriages = $this->repository->get_marriages_for_member($member_id);
            $data['marriage_order'] = count($existing_marriages) + 1;
        }

        $result = $this->repository->add($data);

        if ($result) {
            $this->success('Marriage added successfully', ['marriage_id' => $result]);
        } else {
            $this->error('Failed to add marriage');
        }
    }

    /**
     * Update an existing marriage
     */
    public function update(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $id = $this->get_post_int('marriage_id');
        $data = $_POST;

        $ok = $this->repository->update($id, $data);

        if ($ok) {
            $this->success('Marriage updated successfully');
        } else {
            $this->error('Failed to update marriage');
        }
    }

    /**
     * Delete a marriage
     */
    public function delete(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_DELETE_FAMILY_MEMBERS);

        $marriage_id = $this->get_post_int('marriage_id');

        // Check if marriage has children
        $children = $this->repository->get_children_for_marriage($marriage_id);
        if (!empty($children)) {
            $this->error('Cannot delete marriage with children. Please unlink children first.');
            return;
        }

        $ok = $this->repository->delete($marriage_id);

        if ($ok) {
            $this->success('Marriage deleted successfully');
        } else {
            $this->error('Failed to delete marriage');
        }
    }

    /**
     * Get marriage details
     */
    public function get_details(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $marriage_id = $this->get_post_int('marriage_id');
        $marriage = $this->repository->find($marriage_id);

        if ($marriage) {
            $this->success('Marriage found', ['marriage' => $marriage]);
        } else {
            $this->error('Marriage not found');
        }
    }

    /**
     * Get all marriages for a member
     */
    public function get_marriages_for_member(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $member_id = $this->get_post_int('member_id');
        $marriages = $this->repository->get_marriages_for_member($member_id);

        // Get children for each marriage
        foreach ($marriages as &$marriage) {
            $marriage->children = $this->repository->get_children_for_marriage($marriage->id);
        }

        $this->success('Marriages retrieved', ['marriages' => $marriages]);
    }

    /**
     * Get children for a specific marriage
     */
    public function get_children_for_marriage(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_EDIT_FAMILY_MEMBERS);

        $marriage_id = $this->get_post_int('marriage_id');
        $children = $this->repository->get_children_for_marriage($marriage_id);

        $this->success('Children retrieved', ['children' => $children]);
    }
}
