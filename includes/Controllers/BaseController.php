<?php
/**
 * Base Controller for AJAX handlers
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree\Controllers;

use FamilyTree\Config;

if (!defined('ABSPATH')) exit;

abstract class BaseController {
    /**
     * Verify AJAX nonce
     *
     * @param string $nonce_field Field name for nonce
     * @return void Dies if nonce is invalid
     */
    protected function verify_nonce(string $nonce_field = 'nonce'): void {
        check_ajax_referer(Config::NONCE_NAME, $nonce_field);
    }

    /**
     * Verify user capability
     *
     * @param string $capability Required capability
     * @return void Dies with error if user lacks capability
     */
    protected function verify_capability(string $capability): void {
        if (!current_user_can($capability)) {
            wp_send_json_error('You do not have permission to perform this action');
        }
    }

    /**
     * Verify user is logged in
     *
     * @return void Dies with error if user is not logged in
     */
    protected function verify_logged_in(): void {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to perform this action');
        }
    }

    /**
     * Get POST parameter with sanitization
     *
     * @param string $key Parameter key
     * @param mixed $default Default value if not set
     * @return mixed Sanitized value
     */
    protected function get_post(string $key, mixed $default = ''): mixed {
        return isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : $default;
    }

    /**
     * Get POST parameter as integer
     *
     * @param string $key Parameter key
     * @param int $default Default value if not set
     * @return int
     */
    protected function get_post_int(string $key, int $default = 0): int {
        return isset($_POST[$key]) ? intval($_POST[$key]) : $default;
    }

    /**
     * Get POST parameter as array
     *
     * @param string $key Parameter key
     * @param array $default Default value if not set
     * @return array
     */
    protected function get_post_array(string $key, array $default = []): array {
        return isset($_POST[$key]) && is_array($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Send JSON success response
     *
     * @param mixed $data Data to send
     * @return void
     */
    protected function success($data = null): void {
        wp_send_json_success($data);
    }

    /**
     * Send JSON error response
     *
     * @param string $message Error message
     * @return void
     */
    protected function error(string $message): void {
        wp_send_json_error($message);
    }
}
