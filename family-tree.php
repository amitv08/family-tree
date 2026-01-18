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

// Define plugin constants
define('FAMILY_TREE_PATH', plugin_dir_path(__FILE__));
define('FAMILY_TREE_URL', plugin_dir_url(__FILE__));

// Include the autoloader
require_once plugin_dir_path(__FILE__) . 'includes/Autoloader.php';

// Initialize the autoloader
$autoloader = new FamilyTree\Autoloader();
$autoloader->register();

// Initialize the plugin
$family_tree_plugin = new FamilyTree\Plugin();

// Activation hook
register_activation_hook(__FILE__, array($family_tree_plugin, 'activate'));
