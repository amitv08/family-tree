<?php
/**
 * User Controller - Handles user management AJAX requests
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Controllers;

use FamilyTree\Config;

if (!defined('ABSPATH')) exit;

class UserController extends BaseController {
    /**
     * Create a new family user
     */
    public function create(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_MANAGE_FAMILY_USERS);

        $username = $this->get_post('username');
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $first_name = $this->get_post('first_name');
        $last_name = $this->get_post('last_name');
        $role = $this->get_post('role');

        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            $this->error('Username, email, and password are required');
            return;
        }

        if (!is_email($email)) {
            $this->error('Invalid email address');
            return;
        }

        // Password strength validation
        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters');
            return;
        }

        // Check for password complexity
        $has_lowercase = preg_match('/[a-z]/', $password);
        $has_uppercase = preg_match('/[A-Z]/', $password);
        $has_number = preg_match('/\d/', $password);
        $has_special = preg_match('/[@$!%*?&#^()_\-+=\[\]{}|\\:;"\'<>,.\/~`]/', $password);

        if (!$has_lowercase || !$has_uppercase || !$has_number || !$has_special) {
            $this->error('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character');
            return;
        }

        if (!in_array($role, [Config::ROLE_ADMIN, Config::ROLE_EDITOR, Config::ROLE_VIEWER])) {
            $this->error('Invalid role selected');
            return;
        }

        // Check if username exists
        if (username_exists($username)) {
            $this->error('Username already exists');
            return;
        }

        // Check if email exists
        if (email_exists($email)) {
            $this->error('Email already exists');
            return;
        }

        // Create user
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            $this->error($user_id->get_error_message());
            return;
        }

        // Update user meta
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => trim($first_name . ' ' . $last_name) ?: $username,
            'role' => $role
        ]);

        $this->success('User created successfully');
    }

    /**
     * Update user role
     */
    public function update_role(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_MANAGE_FAMILY_USERS);

        $user_id = $this->get_post_int('user_id');
        $new_role = $this->get_post('new_role');

        if (!$user_id) {
            $this->error('Invalid user ID');
            return;
        }

        if (!in_array($new_role, [Config::ROLE_ADMIN, Config::ROLE_EDITOR, Config::ROLE_VIEWER])) {
            $this->error('Invalid role');
            return;
        }

        $user = get_user_by('ID', $user_id);
        if (!$user) {
            $this->error('User not found');
            return;
        }

        // Remove old family roles
        $user->remove_role(Config::ROLE_ADMIN);
        $user->remove_role(Config::ROLE_EDITOR);
        $user->remove_role(Config::ROLE_VIEWER);

        // Add new role
        $user->add_role($new_role);

        $this->success('Role updated successfully');
    }

    /**
     * Delete a family user
     */
    public function delete(): void {
        $this->verify_nonce();
        $this->verify_capability(Config::CAP_MANAGE_FAMILY_USERS);

        $user_id = $this->get_post_int('user_id');

        if (!$user_id) {
            $this->error('Invalid user ID');
            return;
        }

        // Prevent deleting yourself
        if ($user_id == get_current_user_id()) {
            $this->error('You cannot delete your own account');
            return;
        }

        $user = get_user_by('ID', $user_id);
        if (!$user) {
            $this->error('User not found');
            return;
        }

        // Delete user
        require_once(ABSPATH . 'wp-admin/includes/user.php');
        $result = wp_delete_user($user_id);

        if (!$result) {
            $this->error('Failed to delete user');
            return;
        }

        $this->success('User deleted successfully');
    }
}
