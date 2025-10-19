<?php

class FamilyTreeRoles
{

    public static function setup_roles()
    {
        // Super Admin - Full access
        add_role('family_super_admin', 'Family Super Admin', array(
            'read' => true,
            'manage_clans' => true,
            'manage_family' => true,
            'manage_family_users' => true,
            'edit_family_members' => true,
            'delete_family_members' => true
        ));

        // Family Admin - Can manage members and users
        add_role('family_admin', 'Family Admin', array(
            'read' => true,
            'manage_clans' => true,
            'manage_family_users' => true,
            'edit_family_members' => true,
            'delete_family_members' => true
        ));

        // Family Editor - Can edit members
        add_role('family_editor', 'Family Editor', array(
            'read' => true,
            'edit_family_members' => true
        ));

        // Family Viewer - Read only
        add_role('family_viewer', 'Family Viewer', array(
            'read' => true
        ));

        // Add capabilities to WordPress admin
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_family');
            $admin->add_cap('manage_family_users');
            $admin->add_cap('edit_family_members');
            $admin->add_cap('delete_family_members');
            $admin->add_cap('manage_clans');
        }

        $role = get_role('family_admin');
        if ($role && !$role->has_cap('manage_clans')) {
            $role->add_cap('manage_clans');
        }
    }

    public static function create_user($user_data)
    {
        if (username_exists($user_data['username'])) {
            return array('success' => false, 'message' => 'Username already exists');
        }

        if (email_exists($user_data['email'])) {
            return array('success' => false, 'message' => 'Email already exists');
        }

        $user_id = wp_create_user(
            $user_data['username'],
            $user_data['password'], // Use provided password
            $user_data['email']
        );

        if (is_wp_error($user_id)) {
            return array('success' => false, 'message' => $user_id->get_error_message());
        }

        // Update user details
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'role' => $user_data['role'],
            'display_name' => $user_data['first_name'] . ' ' . $user_data['last_name']
        ));

        // Send notification email with password reset link
        wp_new_user_notification($user_id, null, 'user');

        return array('success' => true, 'message' => 'User created successfully. Password reset email sent.');
    }
}
?>