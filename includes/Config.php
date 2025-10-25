<?php
/**
 * Configuration constants for Family Tree Plugin
 *
 * @package FamilyTree
 * @since 2.4.0
 */

namespace FamilyTree;

if (!defined('ABSPATH')) exit;

class Config {
    /**
     * Plugin version
     */
    public const VERSION = '2.6.0';

    /**
     * Database table names (without prefix)
     */
    public const TABLE_MEMBERS = 'family_members';
    public const TABLE_CLANS = 'family_clans';
    public const TABLE_CLAN_LOCATIONS = 'clan_locations';
    public const TABLE_CLAN_SURNAMES = 'clan_surnames';

    /**
     * User capabilities
     */
    public const CAP_MANAGE_CLANS = 'manage_clans';
    public const CAP_MANAGE_FAMILY = 'manage_family';
    public const CAP_MANAGE_FAMILY_USERS = 'manage_family_users';
    public const CAP_EDIT_FAMILY_MEMBERS = 'edit_family_members';
    public const CAP_DELETE_FAMILY_MEMBERS = 'delete_family_members';

    /**
     * User roles
     */
    public const ROLE_SUPER_ADMIN = 'family_super_admin';
    public const ROLE_ADMIN = 'family_admin';
    public const ROLE_EDITOR = 'family_editor';
    public const ROLE_VIEWER = 'family_viewer';

    /**
     * AJAX actions
     */
    public const AJAX_ADD_CLAN = 'add_clan';
    public const AJAX_UPDATE_CLAN = 'update_clan';
    public const AJAX_DELETE_CLAN = 'delete_clan';
    public const AJAX_GET_CLAN = 'get_clan';
    public const AJAX_GET_CLAN_DETAILS = 'get_clan_details';
    public const AJAX_GET_ALL_CLANS_SIMPLE = 'get_all_clans_simple';
    public const AJAX_ADD_FAMILY_MEMBER = 'add_family_member';
    public const AJAX_UPDATE_FAMILY_MEMBER = 'update_family_member';
    public const AJAX_DELETE_FAMILY_MEMBER = 'delete_family_member';
    public const AJAX_SOFT_DELETE_MEMBER = 'soft_delete_member';
    public const AJAX_RESTORE_MEMBER = 'restore_member';
    public const AJAX_SEARCH_MEMBERS_SELECT2 = 'search_members_select2';
    public const AJAX_CREATE_FAMILY_USER = 'create_family_user';
    public const AJAX_UPDATE_USER_ROLE = 'update_user_role';
    public const AJAX_DELETE_FAMILY_USER = 'delete_family_user';

    /**
     * Nonce names
     */
    public const NONCE_NAME = 'family_tree_nonce';

    /**
     * Routes
     */
    public const ROUTES = [
        '/family-dashboard' => 'dashboard.php',
        '/family-login' => 'login.php',
        '/family-admin' => 'admin-panel.php',
        '/add-member' => 'members/add-member.php',
        '/edit-member' => 'members/edit-member.php',
        '/browse-members' => 'members/browse-members.php',
        '/view-member' => 'members/view-member.php',
        '/add-clan' => 'clans/add-clan.php',
        '/edit-clan' => 'clans/edit-clan.php',
        '/browse-clans' => 'clans/browse-clans.php',
        '/view-clan' => 'clans/view-clan.php',
        '/family-tree' => 'tree-view.php',
    ];

    /**
     * Get table name with WordPress prefix
     *
     * @param string $table Table constant (e.g., self::TABLE_MEMBERS)
     * @return string Full table name with prefix
     */
    public static function get_table_name(string $table): string {
        global $wpdb;
        return $wpdb->prefix . $table;
    }
}
