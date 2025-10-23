<?php
/**
 * Plugin Name: Family Tree
 * Description: Complete family tree management system with clans and members.
 * Version: 2.3.1
 * Author: Amit Vengsarkar
 */

if (!defined('ABSPATH'))
    exit;

// -------------------------------------------------------------
// Constants
// -------------------------------------------------------------
define('FAMILY_TREE_URL', plugin_dir_url(__FILE__));
define('FAMILY_TREE_PATH', plugin_dir_path(__FILE__));

// -------------------------------------------------------------
// Load critical classes early (needed during activation)
// -------------------------------------------------------------
require_once FAMILY_TREE_PATH . 'includes/database.php';
require_once FAMILY_TREE_PATH . 'includes/clans-database.php';
require_once FAMILY_TREE_PATH . 'includes/roles.php';

// -------------------------------------------------------------
// Main Plugin Class
// -------------------------------------------------------------
class FamilyTreePlugin
{
    public function __construct()
    {
        // Register activation hook early
        register_activation_hook(__FILE__, [$this, 'activate']);

        // Init hooks
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Include dependent files (redundant require_once safe)
        $this->include_files();

        // AJAX Handlers
        add_action('wp_ajax_add_clan', [$this, 'ajax_add_clan']);
        add_action('wp_ajax_update_clan', [$this, 'ajax_update_clan']);
        add_action('wp_ajax_delete_clan', [$this, 'ajax_delete_clan']);
        add_action('wp_ajax_get_clan', [$this, 'ajax_get_clan']);
        add_action('wp_ajax_get_clan_details', [$this, 'ajax_get_clan_details']);
        add_action('wp_ajax_get_all_clans_simple', [$this, 'ajax_get_all_clans_simple']);

        add_action('wp_ajax_add_family_member', [$this, 'ajax_add_family_member']);
        add_action('wp_ajax_update_family_member', [$this, 'ajax_update_family_member']);
        add_action('wp_ajax_delete_family_member', [$this, 'ajax_delete_family_member']);

        // Routes
        add_action('template_redirect', [$this, 'handle_routes']);
        add_action('template_redirect', [$this, 'redirect_home_to_dashboard']);

        // inside constructor (add these)
        add_action('wp_ajax_soft_delete_member', [$this, 'ajax_soft_delete_member']);
        add_action('wp_ajax_restore_member', [$this, 'ajax_restore_member']);
        add_action('wp_ajax_search_members_select2', [$this, 'ajax_search_members_select2']);

    }

    // -------------------------------------------------------------
    // Include dependent files
    // -------------------------------------------------------------
    private function include_files()
    {
        require_once FAMILY_TREE_PATH . 'includes/database.php';
        require_once FAMILY_TREE_PATH . 'includes/roles.php';
        require_once FAMILY_TREE_PATH . 'includes/shortcodes.php';
        require_once FAMILY_TREE_PATH . 'includes/clans-database.php';
    }

    // -------------------------------------------------------------
    // Activation Hook
    // -------------------------------------------------------------
    public function activate()
    {
        // Ensure all classes are loaded during activation
        require_once FAMILY_TREE_PATH . 'includes/database.php';
        require_once FAMILY_TREE_PATH . 'includes/clans-database.php';
        require_once FAMILY_TREE_PATH . 'includes/roles.php';

        ob_start();

        // Create / update tables
        FamilyTreeDatabase::setup_tables();
        FamilyTreeClanDatabase::setup_tables();
        FamilyTreeDatabase::migrate_members_add_clan();
        FamilyTreeDatabase::apply_schema_updates();
        FamilyTreeRoles::setup_roles();

        flush_rewrite_rules();

        // Grant super admin privileges to default admin
        $admin = get_user_by('email', get_option('admin_email'));
        if ($admin) {
            $admin->add_role('family_super_admin');
        }

        ob_end_clean();
    }

    // -------------------------------------------------------------
    // Init Hook
    // -------------------------------------------------------------
    public function init()
    {
        // Reserved for future initializations
    }

    // -------------------------------------------------------------
    // Redirect home to dashboard
    // -------------------------------------------------------------
    public function redirect_home_to_dashboard()
    {
        if (is_front_page() || is_home()) {
            wp_redirect('/family-dashboard');
            exit;
        }
    }

    // -------------------------------------------------------------
    // Template Routing
    // -------------------------------------------------------------
    public function handle_routes()
    {
        $uri = $_SERVER['REQUEST_URI'];

        // Dashboard & Login
        if (strpos($uri, '/family-dashboard') !== false)
            $this->load_template('dashboard.php');
        elseif (strpos($uri, '/family-login') !== false)
            $this->load_template('login.php');

        // Members
        elseif (strpos($uri, '/add-member') !== false)
            $this->load_template('members/add-member.php');
        elseif (strpos($uri, '/edit-member') !== false)
            $this->load_template('members/edit-member.php');
        elseif (strpos($uri, '/browse-members') !== false)
            $this->load_template('members/browse-members.php');
        elseif (strpos($uri, '/view-member') !== false)
            $this->load_template('members/view-member.php');

        // Clans
        elseif (strpos($uri, '/browse-clans') !== false)
            $this->load_template('clans/browse-clans.php');
        elseif (strpos($uri, '/add-clan') !== false)
            $this->load_template('clans/add-clan.php');
        elseif (strpos($uri, '/edit-clan') !== false)
            $this->load_template('clans/edit-clan.php');
        elseif (strpos($uri, '/view-clan') !== false)
            $this->load_template('clans/view-clan.php');

        // Tree
        elseif (strpos($uri, '/family-tree') !== false)
            $this->load_template('tree-view.php');
    }

    private function load_template($template)
    {
        $path = FAMILY_TREE_PATH . 'templates/' . $template;
        if (file_exists($path)) {
            include $path;
            exit;
        }
        wp_die('Template not found: ' . esc_html($template));
    }

    // -------------------------------------------------------------
    // Enqueue CSS/JS
    // -------------------------------------------------------------
    public function enqueue_scripts()
    {
        wp_enqueue_style('family-tree-style', FAMILY_TREE_URL . 'assets/css/style.css', [], '2.3');

        // Add Select2
        wp_enqueue_style('select2-style', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery']);

        wp_enqueue_script('jquery');
        wp_enqueue_script('family-tree-main', FAMILY_TREE_URL . 'assets/js/script.js', ['jquery'], '2.3', true);
        wp_enqueue_script('family-tree-clans', FAMILY_TREE_URL . 'assets/js/clans.js', ['jquery'], '2.3', true);
        wp_enqueue_script('family-tree-members', FAMILY_TREE_URL . 'assets/js/members.js', ['jquery', 'select2'], '2.3', true);

        wp_localize_script('family-tree-members', 'family_tree', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('family_tree_nonce'),
        ]);
    }

    // -------------------------------------------------------------
    // AJAX: Clan and Member CRUD
    // -------------------------------------------------------------
    public function ajax_add_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        $result = FamilyTreeClanDatabase::add_clan($_POST);
        is_wp_error($result) ? wp_send_json_error($result->get_error_message()) : wp_send_json_success('Clan added successfully');
    }

    public function ajax_update_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        $ok = FamilyTreeClanDatabase::update_clan(intval($_POST['clan_id']), $_POST);
        $ok ? wp_send_json_success('Clan updated successfully') : wp_send_json_error('Failed to update clan');
    }

    public function ajax_delete_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        FamilyTreeClanDatabase::delete_clan(intval($_POST['id']));
        wp_send_json_success('Clan deleted');
    }

    public function ajax_get_clan_details()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        global $wpdb;
        $cid = intval($_POST['clan_id']);
        if (!$cid)
            wp_send_json_error('Invalid clan id');

        $locations = $wpdb->get_results($wpdb->prepare("SELECT id, location_name FROM {$wpdb->prefix}clan_locations WHERE clan_id = %d", $cid));
        $surnames = $wpdb->get_results($wpdb->prepare("SELECT id, last_name FROM {$wpdb->prefix}clan_surnames WHERE clan_id = %d", $cid));
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

public function ajax_add_family_member() {
    check_ajax_referer('family_tree_nonce', 'nonce');
    $data = $_POST;
    
    // Validate first
    $validation_errors = FamilyTreeDatabase::validate_member_data($data);
    if (!empty($validation_errors)) {
        wp_send_json_error(implode(' ', $validation_errors));
        return;
    }
    
    $result = FamilyTreeDatabase::add_member($data);
    $result ? wp_send_json_success('Member added successfully') : wp_send_json_error('Failed to add member');
}

public function ajax_update_family_member() {
    check_ajax_referer('family_tree_nonce', 'nonce');
    $id = intval($_POST['member_id']);
    
    // Validate first
    $validation_errors = FamilyTreeDatabase::validate_member_data($_POST, $id);
    if (!empty($validation_errors)) {
        wp_send_json_error(implode(' ', $validation_errors));
        return;
    }
    
    $ok = FamilyTreeDatabase::update_member($id, $_POST);
    $ok ? wp_send_json_success('Member updated successfully') : wp_send_json_error('Failed to update member');
}

    public function ajax_delete_family_member()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        global $wpdb;
        $table = $wpdb->prefix . 'family_members';
        $wpdb->delete($table, ['id' => intval($_POST['id'])]);
        wp_send_json_success('Member deleted');
    }
    public function ajax_soft_delete_member()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        if (!current_user_can('manage_family') && !current_user_can('family_super_admin')) {
            wp_send_json_error('Insufficient permissions');
        }
        $id = intval($_POST['member_id']);
        if (!$id)
            wp_send_json_error('Invalid member id');
        $ok = FamilyTreeDatabase::soft_delete_member($id);
        $ok ? wp_send_json_success('Member soft-deleted') : wp_send_json_error('Failed to delete member');
    }

    public function ajax_search_members_select2()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        $query = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

        if (strlen($query) < 1) {
            wp_send_json([]);
        }

        $results = FamilyTreeDatabase::search_members($query, 20);
        wp_send_json($results ?: []);
    }

    public function ajax_restore_member()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');
        if (!current_user_can('manage_family') && !current_user_can('family_super_admin')) {
            wp_send_json_error('Insufficient permissions');
        }
        $id = intval($_POST['member_id']);
        if (!$id)
            wp_send_json_error('Invalid member id');
        $ok = FamilyTreeDatabase::restore_member($id);
        $ok ? wp_send_json_success('Member restored') : wp_send_json_error('Failed to restore member');
    }
}

// -------------------------------------------------------------
// Initialize Plugin
// -------------------------------------------------------------
new FamilyTreePlugin();
