<?php
/**
 * Plugin Name: Family Tree
 * Description: Complete family tree management system with clans and members
 * Version: 2.1
 * Author: Amit Vengsarkar
 */

if (!defined('ABSPATH')) exit;

// -------------------------------------------------------------
// Constants
// -------------------------------------------------------------
define('FAMILY_TREE_URL', plugin_dir_url(__FILE__));
define('FAMILY_TREE_PATH', plugin_dir_path(__FILE__));

// -------------------------------------------------------------
// Main Plugin Class
// -------------------------------------------------------------
class FamilyTreePlugin
{
    public function __construct()
    {
        // Activation and setup
        register_activation_hook(__FILE__, [$this, 'activate']);
        add_action('init', [$this, 'init']);

        // Scripts & assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Include dependent files
        $this->include_files();

        // Member AJAX handlers
        add_action('wp_ajax_add_family_member', [$this, 'ajax_add_family_member']);
        add_action('wp_ajax_update_family_member', [$this, 'ajax_update_family_member']);
        add_action('wp_ajax_delete_family_member', [$this, 'ajax_delete_family_member']);
        add_action('wp_ajax_get_tree_data', [$this, 'ajax_get_tree_data']);
        add_action('wp_ajax_debug_tree_data', [$this, 'ajax_debug_tree_data']);

        // User management AJAX
        add_action('wp_ajax_create_family_user', [$this, 'ajax_create_family_user']);
        add_action('wp_ajax_update_user_role', [$this, 'ajax_update_user_role']);
        add_action('wp_ajax_delete_family_user', [$this, 'ajax_delete_family_user']);

        // Clan AJAX handlers
        add_action('wp_ajax_add_clan', [$this, 'ajax_add_clan']);
        add_action('wp_ajax_update_clan', [$this, 'ajax_update_clan']);
        add_action('wp_ajax_delete_clan', [$this, 'ajax_delete_clan']);
        add_action('wp_ajax_get_clan', [$this, 'ajax_get_clan']);

        // NEW: Clan–Member relationship AJAX
        add_action('wp_ajax_get_clan_details', [$this, 'ajax_get_clan_details']);
        add_action('wp_ajax_get_all_clans_simple', [$this, 'ajax_get_all_clans_simple']);

        // Routes
        add_action('template_redirect', [$this, 'handle_routes']);
        add_action('template_redirect', [$this, 'redirect_home_to_dashboard']);
    }

    // ---------------------------------------------------------
    // Include all helper files
    // ---------------------------------------------------------
    private function include_files()
    {
        require_once FAMILY_TREE_PATH . 'includes/database.php';
        require_once FAMILY_TREE_PATH . 'includes/roles.php';
        require_once FAMILY_TREE_PATH . 'includes/shortcodes.php';
        require_once FAMILY_TREE_PATH . 'includes/clans-database.php';
    }

    // ---------------------------------------------------------
    // Activation
    // ---------------------------------------------------------
    public function activate()
    {
        ob_start();
        FamilyTreeDatabase::setup_tables();
        FamilyTreeRoles::setup_roles();
        FamilyTreeClanDatabase::setup_tables();

        // ensure member table has clan columns
        FamilyTreeDatabase::migrate_members_add_clan();

        flush_rewrite_rules();

        // Make admin a super admin
        $admin = get_user_by('email', get_option('admin_email'));
        if ($admin) {
            $admin->add_role('family_super_admin');
        }
        ob_end_clean();
    }

    // ---------------------------------------------------------
    // Init and routing
    // ---------------------------------------------------------
    public function init()
    {
        // Placeholder for future initialization
    }

    public function redirect_home_to_dashboard()
    {
        if (is_front_page() || is_home()) {
            wp_redirect('/family-dashboard');
            exit;
        }
    }

    // ---------------------------------------------------------
    // Template routing
    // ---------------------------------------------------------
    public function handle_routes()
    {
        $uri = $_SERVER['REQUEST_URI'];

        // Dashboard & Admin
        if (strpos($uri, '/family-dashboard') !== false) {
            $this->load_template('dashboard.php');
        } elseif (strpos($uri, '/family-login') !== false) {
            $this->load_template('login.php');
        } elseif (strpos($uri, '/family-admin') !== false) {
            if (!is_user_logged_in() || !current_user_can('manage_family')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('admin-panel.php');
        }

        // Member routes
        if (strpos($uri, '/add-member') !== false) {
            if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('members/add-member.php');
        }

        if (strpos($uri, '/edit-member') !== false) {
            if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('members/edit-member.php');
        }

        if (strpos($uri, '/browse-members') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('members/browse-members.php');
        }

        if (strpos($uri, '/view-member') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('members/view-member.php');
        }

        // Tree view
        if (strpos($uri, '/family-tree') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('tree-view.php');
        }

        // Clan routes
        if (strpos($uri, '/browse-clans') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans/browse-clans.php');
        }

        if (strpos($uri, '/add-clan') !== false) {
            if (!is_user_logged_in() || !current_user_can('manage_clans')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans/add-clan.php');
        }

        if (strpos($uri, '/edit-clan') !== false) {
            if (!is_user_logged_in() || !current_user_can('manage_clans')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans/edit-clan.php');
        }

        if (strpos($uri, '/view-clan') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans/view-clan.php');
        }
    }

    private function load_template($template)
    {
        $path = FAMILY_TREE_PATH . 'templates/' . $template;
        if (file_exists($path)) {
            include $path;
            exit;
        } else {
            wp_die('Template not found: ' . esc_html($template));
        }
    }

    // ---------------------------------------------------------
    // Scripts and Styles
    // ---------------------------------------------------------
    public function enqueue_scripts()
    {
        wp_enqueue_style(
            'family-tree-style',
            FAMILY_TREE_URL . 'assets/css/style.css',
            [],
            '2.1'
        );

        wp_enqueue_script('jquery');

        wp_enqueue_script(
            'family-tree-script',
            FAMILY_TREE_URL . 'assets/Js/script.js',
            ['jquery'],
            '2.1',
            true
        );

        // Clan & Member integration JS
        wp_enqueue_script(
            'family-clan-script',
            FAMILY_TREE_URL . 'assets/js/clans.js',
            ['jquery'],
            '2.1',
            true
        );

        wp_enqueue_script(
            'family-members-script',
            FAMILY_TREE_URL . 'assets/js/members.js',
            ['jquery'],
            '2.1',
            true
        );

        wp_localize_script('family-members-script', 'family_tree', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('family_tree_nonce'),
        ]);
    }

    // ---------------------------------------------------------
    // AJAX: Member CRUD (unchanged except clan fields handled in DB)
    // ---------------------------------------------------------
    public function ajax_add_family_member()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        if (!current_user_can('edit_family_members')) wp_send_json_error('Insufficient permissions');

        $data = [
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name'  => sanitize_text_field($_POST['last_name']),
            'birth_date' => sanitize_text_field($_POST['birth_date']),
            'death_date' => sanitize_text_field($_POST['death_date']),
            'gender'     => sanitize_text_field($_POST['gender']),
            'biography'  => sanitize_textarea_field($_POST['biography']),
            'clan_id'          => intval($_POST['clan_id']),
            'clan_location_id' => intval($_POST['clan_location_id']),
            'clan_surname_id'  => intval($_POST['clan_surname_id'])
        ];

        $result = FamilyTreeDatabase::add_member($data);
        $result ? wp_send_json_success('Member added successfully') : wp_send_json_error('Failed to add member');
    }

    public function ajax_update_family_member()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        if (!current_user_can('edit_family_members')) wp_send_json_error('Insufficient permissions');

        $id = intval($_POST['member_id']);
        $data = [
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name'  => sanitize_text_field($_POST['last_name']),
            'birth_date' => sanitize_text_field($_POST['birth_date']),
            'death_date' => sanitize_text_field($_POST['death_date']),
            'gender'     => sanitize_text_field($_POST['gender']),
            'biography'  => sanitize_textarea_field($_POST['biography']),
            'clan_id'          => intval($_POST['clan_id']),
            'clan_location_id' => intval($_POST['clan_location_id']),
            'clan_surname_id'  => intval($_POST['clan_surname_id'])
        ];

        $ok = FamilyTreeDatabase::update_member($id, $data);
        $ok ? wp_send_json_success('Member updated successfully') : wp_send_json_error('Failed to update member');
    }

    // ---------------------------------------------------------
    // NEW: AJAX for Clan–Member linkage
    // ---------------------------------------------------------
    public function ajax_get_clan_details()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        global $wpdb;
        $cid = intval($_POST['clan_id']);
        if (!$cid) wp_send_json_error('Invalid clan id');

        $locations = $wpdb->get_results($wpdb->prepare("SELECT id, location_name FROM {$wpdb->prefix}clan_locations WHERE clan_id = %d", $cid));
        $surnames  = $wpdb->get_results($wpdb->prepare("SELECT id, last_name FROM {$wpdb->prefix}clan_surnames WHERE clan_id = %d", $cid));

        wp_send_json_success(['locations' => $locations, 'surnames' => $surnames]);
    }

    public function ajax_get_all_clans_simple()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        global $wpdb;
        $table = $wpdb->prefix . 'family_clans';
        $clans = $wpdb->get_results("SELECT id, clan_name FROM $table ORDER BY clan_name ASC");
        wp_send_json_success($clans);
    }

    // ---------------------------------------------------------
    // Remaining existing AJAX methods (unchanged)
    // ---------------------------------------------------------
    public function ajax_get_tree_data()
    {
        if (!is_user_logged_in()) wp_send_json_error('Authentication required');
        wp_send_json_success(FamilyTreeDatabase::get_tree_data());
    }

    public function ajax_debug_tree_data()
    {
        if (!is_user_logged_in()) wp_send_json_error('Authentication required');
        $data = FamilyTreeDatabase::get_tree_data();
        wp_send_json_success(['count' => count($data), 'sample' => $data[0] ?? 'No data']);
    }

    public function ajax_create_family_user()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        if (!current_user_can('manage_family_users')) wp_send_json_error('Insufficient permissions');

        if ($_POST['password'] !== $_POST['confirm_password']) wp_send_json_error('Passwords do not match');
        if (strlen($_POST['password']) < 6) wp_send_json_error('Password too short');

        $result = FamilyTreeRoles::create_user([
            'username' => sanitize_user($_POST['username']),
            'email'    => sanitize_email($_POST['email']),
            'password' => $_POST['password'],
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name'  => sanitize_text_field($_POST['last_name']),
            'role'       => sanitize_text_field($_POST['role'])
        ]);

        $result['success'] ? wp_send_json_success($result['message']) : wp_send_json_error($result['message']);
    }

    public function ajax_update_user_role()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        if (!current_user_can('manage_family')) wp_send_json_error('Insufficient permissions');

        $uid = intval($_POST['user_id']);
        $role = sanitize_text_field($_POST['new_role']);
        $valid = ['family_admin', 'family_editor', 'family_viewer'];

        if (!in_array($role, $valid)) wp_send_json_error('Invalid role');
        $user = get_user_by('id', $uid);
        if (!$user) wp_send_json_error('User not found');

        $user->set_role($role);
        wp_send_json_success('User role updated successfully');
    }

    public function ajax_delete_family_user()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        if (!current_user_can('manage_family')) wp_send_json_error('Insufficient permissions');
        $uid = intval($_POST['user_id']);
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user($uid);
        wp_send_json_success('User deleted');
    }
}

// -------------------------------------------------------------
// Initialize plugin
// -------------------------------------------------------------
new FamilyTreePlugin();
