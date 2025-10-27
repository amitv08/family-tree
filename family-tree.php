<?php
/**
 * Plugin Name: Family Tree
 * Description: Complete family tree management system with clans and members.
 * Version: 3.5.0
 * Author: Amit Vengsarkar
 *
 * @package FamilyTree
 * @since 2.6.0
 */

if (!defined('ABSPATH')) exit;

// -------------------------------------------------------------
// Constants
// -------------------------------------------------------------
define('FAMILY_TREE_URL', plugin_dir_url(__FILE__));
define('FAMILY_TREE_PATH', plugin_dir_path(__FILE__));

// -------------------------------------------------------------
// Autoloader
// -------------------------------------------------------------
require_once FAMILY_TREE_PATH . 'includes/Autoloader.php';

$autoloader = new \FamilyTree\Autoloader();
$autoloader->register();

// -------------------------------------------------------------
// Legacy Support - Load old database classes for backward compatibility
// -------------------------------------------------------------
require_once FAMILY_TREE_PATH . 'includes/database.php';
require_once FAMILY_TREE_PATH . 'includes/clans-database.php';
require_once FAMILY_TREE_PATH . 'includes/roles.php';
require_once FAMILY_TREE_PATH . 'includes/shortcodes.php';

// -------------------------------------------------------------
// Initialize Plugin
// -------------------------------------------------------------
use FamilyTree\Plugin;

$family_tree_plugin = new Plugin();

// -------------------------------------------------------------
// Activation Hook
// -------------------------------------------------------------
register_activation_hook(__FILE__, [Plugin::class, 'activate']);
