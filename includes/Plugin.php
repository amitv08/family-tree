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
        // Core hooks
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Routing
        add_action('template_redirect', [$this->router, 'handle']);
        add_action('template_redirect', [$this->router, 'redirect_home']);

        // AJAX hooks for clans
        add_action('wp_ajax_' . Config::AJAX_ADD_CLAN, [$this->controllers['clan'], 'add']);
        add_action('wp_ajax_' . Config::AJAX_UPDATE_CLAN, [$this->controllers['clan'], 'update']);
        add_action('wp_ajax_' . Config::AJAX_DELETE_CLAN, [$this->controllers['clan'], 'delete']);
        add_action('wp_ajax_' . Config::AJAX_GET_CLAN_DETAILS, [$this->controllers['clan'], 'get_details']);
        add_action('wp_ajax_' . Config::AJAX_GET_ALL_CLANS_SIMPLE, [$this->controllers['clan'], 'get_all_simple']);

        // AJAX hooks for members
        add_action('wp_ajax_' . Config::AJAX_ADD_FAMILY_MEMBER, [$this->controllers['member'], 'add']);
        add_action('wp_ajax_' . Config::AJAX_UPDATE_FAMILY_MEMBER, [$this->controllers['member'], 'update']);
        add_action('wp_ajax_' . Config::AJAX_DELETE_FAMILY_MEMBER, [$this->controllers['member'], 'delete']);
        add_action('wp_ajax_' . Config::AJAX_SOFT_DELETE_MEMBER, [$this->controllers['member'], 'soft_delete']);
        add_action('wp_ajax_' . Config::AJAX_RESTORE_MEMBER, [$this->controllers['member'], 'restore']);
        add_action('wp_ajax_' . Config::AJAX_SEARCH_MEMBERS_SELECT2, [$this->controllers['member'], 'search_select2']);

        // AJAX hooks for users
        add_action('wp_ajax_' . Config::AJAX_CREATE_FAMILY_USER, [$this->controllers['user'], 'create']);
        add_action('wp_ajax_' . Config::AJAX_UPDATE_USER_ROLE, [$this->controllers['user'], 'update_role']);
        add_action('wp_ajax_' . Config::AJAX_DELETE_FAMILY_USER, [$this->controllers['user'], 'delete']);

        // Phase 2: AJAX hooks for marriages
        add_action('wp_ajax_' . Config::AJAX_ADD_MARRIAGE, [$this->controllers['marriage'], 'add']);
        add_action('wp_ajax_' . Config::AJAX_UPDATE_MARRIAGE, [$this->controllers['marriage'], 'update']);
        add_action('wp_ajax_' . Config::AJAX_DELETE_MARRIAGE, [$this->controllers['marriage'], 'delete']);
        add_action('wp_ajax_' . Config::AJAX_GET_MARRIAGE_DETAILS, [$this->controllers['marriage'], 'get_details']);
        add_action('wp_ajax_' . Config::AJAX_GET_MARRIAGES_FOR_MEMBER, [$this->controllers['marriage'], 'get_marriages_for_member']);
        add_action('wp_ajax_' . Config::AJAX_GET_CHILDREN_FOR_MARRIAGE, [$this->controllers['marriage'], 'get_children_for_marriage']);

        // Security: Configure nonce lifetime
        add_filter('nonce_life', [$this, 'configure_nonce_lifetime']);
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
        // Reserved for future use
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
