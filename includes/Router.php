<?php
/**
 * Router - Handles custom URL routing
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree;

if (!defined('ABSPATH')) exit;

class Router {
    /**
     * Route definitions
     *
     * @var array
     */
    private array $routes;

    /**
     * Constructor
     */
    public function __construct() {
        $this->routes = Config::ROUTES;
    }

    /**
     * Handle routing
     */
    public function handle(): void {
        error_log('Family Tree Router: handle() method called at ' . time());
        
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Debug output
        error_log('Family Tree Router: Handling request for URI: ' . $uri);
        
        // Check if we have a family_tree_route query var
        $route = get_query_var('family_tree_route');
        error_log('Family Tree Router: family_tree_route query var: ' . ($route ?: 'NOT SET'));
        if (!empty($route)) {
            error_log('Family Tree Router: Found route query var: ' . $route);
            $this->serve_template($route);
            return;
        }
        error_log('Family Tree Router: Parsed URI path: ' . $uri);
        
        foreach ($this->routes as $route => $template) {
            error_log('Family Tree Router: Checking route: ' . $route . ' -> ' . $template);
            if ($uri === $route || $uri === $route . '/') {
                error_log('Family Tree Router: Route matched! Loading template: ' . $template);
                $this->load_template($template, $route);
                exit; // Stop WordPress execution
            }
        }
        
        error_log('Family Tree Router: No route matched for: ' . $uri);
    }

    /**
     * Load a template with optional middleware
     *
     * @param string $template Template file path
     * @param string $route Route pattern
     */
    private function load_template(string $template, string $route): void {
        // Apply middleware based on route
        $this->apply_middleware($route);

        // Load the template
        $path = FAMILY_TREE_PATH . 'templates/' . $template;
        if (file_exists($path)) {
            include $path;
            exit;
        }

        wp_die('Template not found: ' . esc_html($template));
    }

    /**
     * Serve template for query var based routing
     */
    private function serve_template(string $route): void {
        // Look up the template for this route
        $template = Config::ROUTES[$route] ?? null;
        
        if ($template) {
            $this->load_template($template, $route);
        } else {
            wp_die('Route not found: ' . esc_html($route));
        }
    }

    /**
     * Apply middleware checks for routes
     *
     * @param string $route Route pattern
     */
    private function apply_middleware(string $route): void {
        // Public routes (no auth required)
        $public_routes = ['/family-login'];

        if (in_array($route, $public_routes)) {
            return;
        }

        // Admin panel requires special permission
        if ($route === '/family-admin') {
            if (!current_user_can(Config::CAP_MANAGE_FAMILY_USERS) && !current_user_can(Config::CAP_MANAGE_FAMILY)) {
                wp_die('Access denied. You do not have permission to access the admin panel.', 'Access Denied', ['response' => 403]);
            }
            return;
        }

        // Add/Edit members require edit permission
        $member_edit_routes = ['/add-member', '/edit-member'];
        if (in_array($route, $member_edit_routes)) {
            if (!current_user_can(Config::CAP_EDIT_FAMILY_MEMBERS)) {
                wp_die('Access denied. You do not have permission to manage family members.', 'Access Denied', ['response' => 403]);
            }
            return;
        }

        // Add/Edit clans require manage permission
        $clan_manage_routes = ['/add-clan', '/edit-clan'];
        if (in_array($route, $clan_manage_routes)) {
            if (!current_user_can(Config::CAP_MANAGE_CLANS)) {
                wp_die('Access denied. You do not have permission to manage clans.', 'Access Denied', ['response' => 403]);
            }
            return;
        }

        // All other routes require authentication
        if (!is_user_logged_in()) {
            wp_redirect('/family-login?redirect_to=' . urlencode($route));
            exit;
        }
    }

    /**
     * Redirect home to dashboard
     */
    public function redirect_home(): void {
        if (is_front_page() || is_home()) {
            wp_redirect('/family-dashboard');
            exit;
        }
    }
}
