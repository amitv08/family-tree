<?php
/**
 * PHPUnit Bootstrap File
 */

// Load WordPress test environment
define('WP_TESTS_DIR', getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib');

// Load WordPress test functions
require_once WP_TESTS_DIR . '/includes/functions.php';

// Load the plugin
function _manually_load_plugin() {
    require dirname(__DIR__) . '/family-tree.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Load WordPress
require WP_TESTS_DIR . '/includes/bootstrap.php';