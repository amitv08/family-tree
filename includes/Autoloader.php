<?php
/**
 * PSR-4 Autoloader for Family Tree Plugin
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree;

if (!defined('ABSPATH')) exit;

class Autoloader {
    /**
     * Base namespace
     */
    private const NAMESPACE_PREFIX = 'FamilyTree\\';

    /**
     * Base directory
     */
    private string $base_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $this->base_dir = FAMILY_TREE_PATH . 'includes/';
    }

    /**
     * Register the autoloader
     */
    public function register(): void {
        spl_autoload_register([$this, 'load_class']);
    }

    /**
     * Load a class file
     *
     * @param string $class The fully-qualified class name
     * @return void
     */
    private function load_class(string $class): void {
        // Check if class uses our namespace
        if (strpos($class, self::NAMESPACE_PREFIX) !== 0) {
            return;
        }

        // Remove namespace prefix
        $relative_class = substr($class, strlen(self::NAMESPACE_PREFIX));

        // Convert namespace separators to directory separators
        $file = $this->base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // If file exists, load it
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
