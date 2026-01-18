<?php
/**
 * PHPUnit Bootstrap File
 */

// Check if we're running integration tests or unit tests
$testSuite = getenv('PHPUNIT_TESTSUITE') ?: '';

// For unit tests, we don't need WordPress test environment
if ($testSuite === 'Unit Tests' || strpos($testSuite, 'Unit') !== false) {
    // Simple bootstrap for unit tests - just load Composer autoloader
    $autoloader = dirname(__DIR__) . '/vendor/autoload.php';
    if (file_exists($autoloader)) {
        require_once $autoloader;
    }
    return;
}

// Load WordPress test environment for integration tests
define('WP_TESTS_DIR', getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib');

// Check if WordPress test files exist
if (!file_exists(WP_TESTS_DIR . '/includes/functions.php')) {
    echo "WordPress test libraries not found at " . WP_TESTS_DIR . ". Skipping WordPress-dependent tests.\n";
    return;
}

// Load WordPress test functions
require_once WP_TESTS_DIR . '/includes/functions.php';

// Load the plugin
function _manually_load_plugin() {
    require dirname(__DIR__) . '/family-tree.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Load WordPress
require WP_TESTS_DIR . '/includes/bootstrap.php';