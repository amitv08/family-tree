<?php
/**
 * Plugin Name: Family Tree
 * Description: Complete family tree management system
 * Version: 2.0
 * Author: Amit Vengsarkar
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FAMILY_TREE_URL', plugin_dir_url(__FILE__));
define('FAMILY_TREE_PATH', plugin_dir_path(__FILE__));

class FamilyTreePlugin
{

    public function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Include files
        $this->include_files();

        // AJAX handlers
        add_action('wp_ajax_add_family_member', array($this, 'ajax_add_family_member'));
        add_action('wp_ajax_get_tree_data', array($this, 'ajax_get_tree_data'));
        add_action('wp_ajax_debug_tree_data', array($this, 'ajax_debug_tree_data'));
        add_action('wp_ajax_create_family_user', array($this, 'ajax_create_family_user'));
        add_action('wp_ajax_update_family_member', array($this, 'ajax_update_family_member'));
        add_action('wp_ajax_delete_family_member', array($this, 'ajax_delete_family_member'));
        // Add these to the constructor in family-tree.php
        add_action('wp_ajax_update_user_role', array($this, 'ajax_update_user_role'));
        add_action('wp_ajax_delete_family_user', array($this, 'ajax_delete_family_user'));

        // Handle Clans routes
        add_action('wp_ajax_add_clan', array($this, 'ajax_add_clan'));
        add_action('wp_ajax_update_clan', array($this, 'ajax_update_clan'));
        add_action('wp_ajax_delete_clan', array($this, 'ajax_delete_clan'));
        add_action('wp_ajax_get_clan', array($this, 'ajax_get_clan'));


        // Handle custom routes
        add_action('template_redirect', array($this, 'handle_routes'));

        // Set dashboard as home page
        add_action('template_redirect', array($this, 'redirect_home_to_dashboard'));
    }

    private function include_files()
    {
        require_once FAMILY_TREE_PATH . 'includes/database.php';
        require_once FAMILY_TREE_PATH . 'includes/roles.php';
        require_once FAMILY_TREE_PATH . 'includes/shortcodes.php';
        require_once FAMILY_TREE_PATH . 'includes/clans-database.php';
    }

    public function activate()
    {
        ob_start();
        FamilyTreeDatabase::setup_tables();
        FamilyTreeRoles::setup_roles();
        FamilyTreeClanDatabase::setup_tables();
        flush_rewrite_rules();

        // Make admin a super admin
        $admin = get_user_by('email', get_option('admin_email'));
        if ($admin) {
            $admin->add_role('family_super_admin');
        }
        ob_end_clean();
    }

    public function init()
    {
        // Simple initialization - no complex rewrite rules
    }

    public function redirect_home_to_dashboard()
    {
        // If accessing the site root, redirect to dashboard
        if (is_front_page() || is_home()) {
            wp_redirect('/family-dashboard');
            exit;
        }
    }

    public function handle_routes()
    {
        $request_uri = $_SERVER['REQUEST_URI'];

        // Handle our custom routes
        if (strpos($request_uri, '/family-dashboard') !== false) {
            $this->load_template('dashboard.php');
            exit;
        }

        if (strpos($request_uri, '/family-login') !== false) {
            $this->load_template('login.php');
            exit;
        }

        if (strpos($request_uri, '/family-admin') !== false) {
            if (!is_user_logged_in() || !current_user_can('manage_family')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('admin-panel.php');
            exit;
        }

        if (strpos($request_uri, '/add-member') !== false) {
            if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('add-member.php');
            exit;
        }

        if (strpos($request_uri, '/browse-members') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('browse-members.php');
            exit;
        }

        if (strpos($request_uri, '/family-tree') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('tree-view.php');
            exit;
        }
        // In the handle_routes method, add:
        if (strpos($request_uri, '/edit-member') !== false) {
            if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('edit-member.php');
            exit;
        }

        // Clan management route
        // ================== CLAN ROUTES ==================
        if (strpos($request_uri, '/browse-clans') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans/browse-clans.php');
            exit;
        }

        if (strpos($request_uri, '/add-clan') !== false) {
            if (!is_user_logged_in() || !current_user_can('manage_clans')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans/add-clan.php');
            exit;
        }

        if (strpos($request_uri, '/edit-clan') !== false) {
            if (!is_user_logged_in() || !current_user_can('manage_clans')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans/edit-clan.php');
            exit;
        }

        if (strpos($request_uri, '/view-clan') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans/view-clan.php');
            exit;
        }

    }

    private function load_template($template)
    {
        $template_path = FAMILY_TREE_PATH . 'templates/' . $template;
        if (file_exists($template_path)) {
            include $template_path;
            exit;
        } else {
            wp_die('Template not found: ' . $template);
        }
    }

    public function enqueue_scripts()
    {
        // Core style
        wp_enqueue_style(
            'family-tree-style',
            FAMILY_TREE_URL . 'assets/css/style.css',
            [],
            '1.0'
        );

        // ✅ Load WordPress-bundled jQuery before everything else
        wp_enqueue_script('jquery');

        // Main plugin JS (if any)
        wp_enqueue_script(
            'family-tree-script',
            FAMILY_TREE_URL . 'assets/Js/script.js',
            ['jquery'],
            '1.0',
            true
        );

        // ✅ Clan script (lower-case folder name)
        wp_enqueue_script(
            'family-clan-script',
            FAMILY_TREE_URL . 'assets/js/clans.js',
            ['jquery'],
            '1.0',
            true
        );

        // Make AJAX + nonce available to all scripts
        wp_localize_script('family-clan-script', 'family_tree', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('family_tree_nonce'),
        ]);
    }



    // Add this to your FamilyTreePlugin class in family-tree.php

    public function ajax_add_family_member()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('edit_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        // Handle file upload
        $photo_url = '';
        if (!empty($_FILES['photo']['name'])) {
            $upload = wp_handle_upload($_FILES['photo'], array('test_form' => false));
            if (isset($upload['url'])) {
                $photo_url = $upload['url'];
            }
        }

        $data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'birth_date' => sanitize_text_field($_POST['birth_date']),
            'death_date' => sanitize_text_field($_POST['death_date']),
            'gender' => sanitize_text_field($_POST['gender']),
            'photo_url' => $photo_url,
            'biography' => sanitize_textarea_field($_POST['biography']),
            'parent1_id' => !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null
        );

        $result = FamilyTreeDatabase::add_member($data);

        if ($result) {
            wp_send_json_success('Member added successfully');
        } else {
            wp_send_json_error('Failed to add member');
        }
    }

    public function ajax_get_tree_data()
    {
        if (!is_user_logged_in()) {
            wp_send_json_error('Authentication required');
        }

        $data = FamilyTreeDatabase::get_tree_data();
        wp_send_json_success($data);
    }

    public function ajax_debug_tree_data()
    {
        if (!is_user_logged_in()) {
            wp_send_json_error('Authentication required');
        }

        $data = FamilyTreeDatabase::get_tree_data();

        // Send raw data for debugging
        wp_send_json_success([
            'data' => $data,
            'count' => count($data),
            'sample' => $data[0] ?? 'No data',
            'debug' => 'Tree data debug response'
        ]);
    }

    public function ajax_create_family_user()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('manage_family_users')) {
            wp_send_json_error('Insufficient permissions');
        }

        // Validate passwords
        if ($_POST['password'] !== $_POST['confirm_password']) {
            wp_send_json_error('Passwords do not match');
        }

        if (strlen($_POST['password']) < 6) {
            wp_send_json_error('Password must be at least 6 characters long');
        }

        $user_data = array(
            'username' => sanitize_user($_POST['username']),
            'email' => sanitize_email($_POST['email']),
            'password' => $_POST['password'], // Include password
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'role' => sanitize_text_field($_POST['role'])
        );

        $result = FamilyTreeRoles::create_user($user_data);

        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    // Add these methods to your FamilyTreePlugin class in family-tree.php

    public function ajax_update_user_role()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('manage_family')) {
            wp_send_json_error('Insufficient permissions');
        }

        $user_id = intval($_POST['user_id']);
        $new_role = sanitize_text_field($_POST['new_role']);

        // Validate role
        $valid_roles = ['family_admin', 'family_editor', 'family_viewer'];
        if (!in_array($new_role, $valid_roles)) {
            wp_send_json_error('Invalid role specified');
        }

        // Get the user
        $user = get_user_by('id', $user_id);
        if (!$user) {
            wp_send_json_error('User not found');
        }

        // Update user role
        $user->set_role($new_role);

        wp_send_json_success('User role updated successfully');
    }

    public function ajax_delete_family_user()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('manage_family')) {
            wp_send_json_error('Insufficient permissions');
        }

        $user_id = intval($_POST['user_id']);
        $current_user_id = get_current_user_id();

        // Prevent users from deleting themselves
        if ($user_id == $current_user_id) {
            wp_send_json_error('You cannot delete your own account');
        }

        // Check if user exists and has family role
        $user = get_user_by('id', $user_id);
        if (!$user) {
            wp_send_json_error('User not found');
        }

        $user_roles = $user->roles;
        $is_family_user = false;
        foreach ($user_roles as $role) {
            if (strpos($role, 'family_') === 0) {
                $is_family_user = true;
                break;
            }
        }

        if (!$is_family_user) {
            wp_send_json_error('User is not a family user');
        }

        // Delete the user
        if (!function_exists('wp_delete_user')) {
            require_once(ABSPATH . 'wp-admin/includes/user.php');
        }

        $result = wp_delete_user($user_id);

        if ($result) {
            wp_send_json_success('User deleted successfully');
        } else {
            wp_send_json_error('Failed to delete user');
        }
    }
    // Add these to your FamilyTreePlugin class in family-tree.php

    public function ajax_update_family_member()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('edit_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $member_id = intval($_POST['member_id']);

        // Validate required fields
        if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
            wp_send_json_error('First name and last name are required');
        }

        $data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'birth_date' => sanitize_text_field($_POST['birth_date']),
            'death_date' => sanitize_text_field($_POST['death_date']),
            'gender' => sanitize_text_field($_POST['gender']),
            'photo_url' => esc_url_raw($_POST['photo_url']),
            'biography' => sanitize_textarea_field($_POST['biography']),
            'parent1_id' => !empty($_POST['parent1_id']) ? intval($_POST['parent1_id']) : null,
            'parent2_id' => !empty($_POST['parent2_id']) ? intval($_POST['parent2_id']) : null
        );

        error_log('Updating member ' . $member_id . ' with data: ' . print_r($data, true));

        $result = FamilyTreeDatabase::update_member($member_id, $data);

        if ($result) {
            wp_send_json_success('Member updated successfully');
        } else {
            wp_send_json_error('Failed to update member. Please try again.');
        }
    }

    public function ajax_delete_family_member()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('delete_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $member_id = intval($_POST['member_id']);

        error_log('Deleting member: ' . $member_id);

        $result = FamilyTreeDatabase::delete_member($member_id);

        if ($result) {
            wp_send_json_success('Member deleted successfully');
        } else {
            wp_send_json_error('Failed to delete member. Please try again.');
        }
    }

    public function ajax_add_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('manage_clans')) {
            wp_send_json_error('Insufficient permissions.');
        }

        // Coerce inputs
        $clan_name = isset($_POST['clan_name']) ? sanitize_text_field(wp_unslash($_POST['clan_name'])) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
        $origin_year = isset($_POST['origin_year']) && $_POST['origin_year'] !== '' ? intval($_POST['origin_year']) : null;
        $locations = isset($_POST['locations']) ? (array) $_POST['locations'] : array();
        $surnames = isset($_POST['surnames']) ? (array) $_POST['surnames'] : array();

        $data = array(
            'clan_name' => $clan_name,
            'description' => $description,
            'origin_year' => $origin_year,
            'locations' => $locations,
            'surnames' => $surnames
        );

        $res = FamilyTreeClanDatabase::add_clan($data);

        if (is_wp_error($res)) {
            wp_send_json_error($res->get_error_message());
        } elseif ($res) {
            wp_send_json_success(array('id' => $res, 'message' => 'Clan created successfully.'));
        } else {
            wp_send_json_error('Failed to create clan.');
        }
    }

    public function ajax_update_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('manage_clans')) {
            wp_send_json_error('Insufficient permissions.');
        }

        $clan_id = isset($_POST['clan_id']) ? intval($_POST['clan_id']) : 0;
        $clan_name = isset($_POST['clan_name']) ? sanitize_text_field(wp_unslash($_POST['clan_name'])) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
        $origin_year = isset($_POST['origin_year']) && $_POST['origin_year'] !== '' ? intval($_POST['origin_year']) : null;
        $locations = isset($_POST['locations']) ? (array) $_POST['locations'] : array();
        $surnames = isset($_POST['surnames']) ? (array) $_POST['surnames'] : array();

        if (!$clan_id || empty($clan_name) || empty($locations) || empty($surnames)) {
            wp_send_json_error('Missing or invalid data.');
        }

        $data = array(
            'clan_name' => $clan_name,
            'description' => $description,
            'origin_year' => $origin_year,
            'locations' => $locations,
            'surnames' => $surnames
        );

        $ok = FamilyTreeClanDatabase::update_clan($clan_id, $data);

        if ($ok) {
            wp_send_json_success('Clan updated successfully.');
        } else {
            wp_send_json_error('Failed to update clan.');
        }
    }

    public function ajax_delete_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('manage_clans')) {
            wp_send_json_error('Insufficient permissions.');
        }

        $id = isset($_POST['clan_id']) ? intval($_POST['clan_id']) : 0;
        if (!$id)
            wp_send_json_error('Invalid clan ID.');

        $result = FamilyTreeClanDatabase::delete_clan($id);

        if ($result) {
            wp_send_json_success('Clan deleted successfully.');
        } else {
            wp_send_json_error('Failed to delete clan.');
        }
    }

    public function ajax_get_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Not logged in.');
        }

        $id = isset($_POST['clan_id']) ? intval($_POST['clan_id']) : 0;
        if (!$id)
            wp_send_json_error('Invalid clan id.');

        $clan = FamilyTreeClanDatabase::get_clan($id);
        if ($clan) {
            wp_send_json_success($clan);
        } else {
            wp_send_json_error('Clan not found.');
        }
    }

}

new FamilyTreePlugin();
?>