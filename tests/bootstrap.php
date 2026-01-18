<?php
/**
 * PHPUnit Bootstrap File
 */

// Load Composer autoloader first
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Check if WordPress test environment is available
$wpTestsDir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib';
$wpFunctionsFile = $wpTestsDir . '/includes/functions.php';

// If WordPress test files don't exist, skip WordPress setup (for unit tests)
if (!file_exists($wpFunctionsFile)) {
    echo "WordPress test environment not available. Running basic unit tests only.\n";
    return;
}

// Load WordPress test environment for integration tests
define('WP_TESTS_DIR', $wpTestsDir);

// Load WordPress test functions
require_once WP_TESTS_DIR . '/includes/functions.php';

// Load the plugin
function _manually_load_plugin() {
    require dirname(__DIR__) . '/family-tree.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Load WordPress
require WP_TESTS_DIR . '/includes/bootstrap.php';