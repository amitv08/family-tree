<?php
/**
 * Main Plugin Class - Bootstraps the plugin with modern architecture
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree;

use FamilyTree\Controllers\ClanController;
use FamilyTree\Controllers\MemberController;
use FamilyTree\Controllers\MarriageController;
use FamilyTree\Controllers\UserController;

if (!defined('ABSPATH')) exit;

class Plugin {
    /**
     * Router instance
     *
     * @var Router
     */
    private Router $router;

    /**
     * Controller instances
     *
     * @var array
     */
    private array $controllers = [];

    /**
     * Constructor - Initialize the plugin
     */
    public function __construct() {
        error_log('Family Tree Plugin: Constructor called at ' . time());
        
        // Initialize router
        $this->router = new Router();

        // Initialize controllers
        $this->init_controllers();

        // Register hooks
        $this->register_hooks();
    }

    /**
     * Initialize controller instances
     */
    private function init_controllers(): void {
        $this->controllers['clan'] = new ClanController();
        $this->controllers['member'] = new MemberController();
        $this->controllers['marriage'] = new MarriageController();
        $this->controllers['user'] = new UserController();
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks(): void {
        error_log('Family Tree Plugin: Registering hooks');
        
        // Plugin initialization
        add_action('init', [$this, 'init']);
        
        // Routing
        add_action('parse_request', [$this, 'handle_routing']);
        add_action('template_redirect', [$this->router, 'redirect_home']);
        
        // Admin hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Plugin activation hook
     */
    public static function activate(): void {
        // Ensure database classes are loaded
        require_once FAMILY_TREE_PATH . 'includes/database.php';
        require_once FAMILY_TREE_PATH . 'includes/clans-database.php';
        require_once FAMILY_TREE_PATH . 'includes/roles.php';

        ob_start();

        // Create/update tables
        \FamilyTreeDatabase::setup_tables();
        \FamilyTreeClanDatabase::setup_tables();
        \FamilyTreeDatabase::migrate_members_add_clan();
        \FamilyTreeDatabase::apply_schema_updates();

        // Phase 2: Migrate existing marriage_date data to marriages table
        \FamilyTreeDatabase::migrate_existing_marriages();

        \FamilyTreeRoles::setup_roles();

        flush_rewrite_rules();

        // Grant super admin to site admin
        $admin = get_user_by('email', get_option('admin_email'));
        if ($admin) {
            $admin->add_role(Config::ROLE_SUPER_ADMIN);
        }

        ob_end_clean();
    }

    /**
     * Init hook
     */
    public function init(): void {
        // Add query vars
        add_filter('query_vars', [$this, 'add_query_vars']);
        
        // Add rewrite rules for custom routes
        $this->add_rewrite_rules();
        
        // Register AJAX hooks
        $this->register_ajax_hooks();
        
        // Flush rewrite rules if needed (only on activation)
        if (get_option('family_tree_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('family_tree_flush_rewrite_rules');
        }
    }

    /**
     * Add custom query vars
     */
    public function add_query_vars(array $vars): array {        error_log('Family Tree Plugin: Adding query vars');        $vars[] = 'family_tree_route';
        return $vars;
    }

    /**
     * Add rewrite rules for custom routes
     */
    private function add_rewrite_rules(): void {
        global $wp_rewrite;
        if (!$wp_rewrite) {
            return; // Rewrite system not initialized yet
        }
        
        foreach (Config::ROUTES as $route => $template) {
            add_rewrite_rule(
                '^' . trim($route, '/') . '/?$',
                'index.php?family_tree_route=' . $route,
                'top'
            );
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts(): void {
        // Main stylesheet
        wp_enqueue_style(
            'family-tree-style',
            FAMILY_TREE_URL . 'assets/css/style.css',
            [],
            Config::VERSION
        );

        // Select2
        wp_enqueue_style(
            'select2-style',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css'
        );
        wp_enqueue_script(
            'select2',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
            ['jquery']
        );

        // Plugin scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'family-tree-main',
            FAMILY_TREE_URL . 'assets/js/script.js',
            ['jquery'],
            Config::VERSION,
            true
        );
        wp_enqueue_script(
            'family-tree-clans',
            FAMILY_TREE_URL . 'assets/js/clans.js',
            ['jquery'],
            Config::VERSION,
            true
        );
        wp_enqueue_script(
            'family-tree-members',
            FAMILY_TREE_URL . 'assets/js/members.js',
            ['jquery', 'select2'],
            Config::VERSION,
            true
        );
        wp_enqueue_script(
            'family-tree-marriages',
            FAMILY_TREE_URL . 'assets/js/marriages.js',
            ['jquery'],
            Config::VERSION,
            true
        );

        // Localize script
        wp_localize_script('family-tree-members', 'family_tree', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(Config::NONCE_NAME),
        ]);
    }

    /**
     * Handle custom routing
     */
    public function handle_routing(): void {
        $this->router->handle();
    }

    /**
     * Register AJAX hooks
     */
    private function register_ajax_hooks(): void {
        error_log('Family Tree Plugin: Registering AJAX hooks');
        
        // Member AJAX endpoints
        add_action('wp_ajax_add_family_member', [$this->controllers['member'], 'add']);
        add_action('wp_ajax_nopriv_add_family_member', [$this->controllers['member'], 'add']);
        add_action('wp_ajax_get_family_members', [$this->controllers['member'], 'get_members']);
        add_action('wp_ajax_nopriv_get_family_members', [$this->controllers['member'], 'get_members']);
        add_action('wp_ajax_update_family_member', [$this->controllers['member'], 'update']);
        add_action('wp_ajax_nopriv_update_family_member', [$this->controllers['member'], 'update']);
        add_action('wp_ajax_delete_family_member', [$this->controllers['member'], 'delete']);
        add_action('wp_ajax_nopriv_delete_family_member', [$this->controllers['member'], 'delete']);
        add_action('wp_ajax_get_member_details', [$this->controllers['member'], 'get_details']);
        add_action('wp_ajax_nopriv_get_member_details', [$this->controllers['member'], 'get_details']);
        
        // Clan AJAX endpoints
        add_action('wp_ajax_' . Config::AJAX_ADD_CLAN, [$this->controllers['clan'], 'add']);
        add_action('wp_ajax_nopriv_' . Config::AJAX_ADD_CLAN, [$this->controllers['clan'], 'add']);
        add_action('wp_ajax_' . Config::AJAX_GET_ALL_CLANS_SIMPLE, [$this->controllers['clan'], 'get_clans']);
        add_action('wp_ajax_nopriv_' . Config::AJAX_GET_ALL_CLANS_SIMPLE, [$this->controllers['clan'], 'get_clans']);
        add_action('wp_ajax_' . Config::AJAX_UPDATE_CLAN, [$this->controllers['clan'], 'update']);
        add_action('wp_ajax_nopriv_' . Config::AJAX_UPDATE_CLAN, [$this->controllers['clan'], 'update']);
        add_action('wp_ajax_' . Config::AJAX_DELETE_CLAN, [$this->controllers['clan'], 'delete']);
        add_action('wp_ajax_nopriv_' . Config::AJAX_DELETE_CLAN, [$this->controllers['clan'], 'delete']);
        add_action('wp_ajax_' . Config::AJAX_GET_CLAN_DETAILS, [$this->controllers['clan'], 'get_details']);
        add_action('wp_ajax_nopriv_' . Config::AJAX_GET_CLAN_DETAILS, [$this->controllers['clan'], 'get_details']);
        
        // Marriage AJAX endpoints
        add_action('wp_ajax_add_family_marriage', [$this->controllers['marriage'], 'add']);
        add_action('wp_ajax_nopriv_add_family_marriage', [$this->controllers['marriage'], 'add']);
        add_action('wp_ajax_get_family_marriages', [$this->controllers['marriage'], 'get_marriages']);
        add_action('wp_ajax_nopriv_get_family_marriages', [$this->controllers['marriage'], 'get_marriages']);
        add_action('wp_ajax_update_family_marriage', [$this->controllers['marriage'], 'update']);
        add_action('wp_ajax_nopriv_update_family_marriage', [$this->controllers['marriage'], 'update']);
        add_action('wp_ajax_delete_family_marriage', [$this->controllers['marriage'], 'delete']);
        add_action('wp_ajax_nopriv_delete_family_marriage', [$this->controllers['marriage'], 'delete']);
        
        // User AJAX endpoints
        add_action('wp_ajax_add_family_user', [$this->controllers['user'], 'add']);
        add_action('wp_ajax_nopriv_add_family_user', [$this->controllers['user'], 'add']);
        add_action('wp_ajax_get_family_users', [$this->controllers['user'], 'get_users']);
        add_action('wp_ajax_nopriv_get_family_users', [$this->controllers['user'], 'get_users']);
        add_action('wp_ajax_update_family_user', [$this->controllers['user'], 'update']);
        add_action('wp_ajax_nopriv_update_family_user', [$this->controllers['user'], 'update']);
        add_action('wp_ajax_delete_family_user', [$this->controllers['user'], 'delete']);
        add_action('wp_ajax_nopriv_delete_family_user', [$this->controllers['user'], 'delete']);
        
        // Heartbeat for testing
        add_action('wp_ajax_heartbeat', [$this, 'heartbeat']);
        add_action('wp_ajax_nopriv_heartbeat', [$this, 'heartbeat']);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu(): void {
        add_menu_page(
            'Family Tree Admin',
            'Family Tree',
            Config::CAP_MANAGE_FAMILY_TREE,
            'family-tree-admin',
            [$this, 'admin_page'],
            'dashicons-networking',
            30
        );
    }

    /**
     * Heartbeat endpoint for testing
     */
    public function heartbeat(): void {
        wp_send_json_success(['status' => 'alive', 'timestamp' => time()]);
    }

    /**
     * Admin page callback
     */
    public function admin_page(): void {
        require_once FAMILY_TREE_PATH . 'templates/admin-panel.php';
    }

    /**
     * Configure nonce lifetime for security
     *
     * @param int $life Nonce lifetime in seconds
     * @return int Modified lifetime
     */
    public function configure_nonce_lifetime(int $life): int {
        // Shorter lifetime for sensitive delete operations
        if (isset($_POST['action']) && in_array($_POST['action'], [
            Config::AJAX_DELETE_CLAN,
            Config::AJAX_DELETE_FAMILY_MEMBER,
            Config::AJAX_DELETE_FAMILY_USER,
            Config::AJAX_SOFT_DELETE_MEMBER,
            Config::AJAX_DELETE_MARRIAGE
        ])) {
            return 2 * HOUR_IN_SECONDS;
        }

        // Default 12 hours for other operations
        return 12 * HOUR_IN_SECONDS;
    }
}
