<?php
if (!is_user_logged_in() || !current_user_can('manage_family')) {
    wp_redirect('/family-login');
    exit;
}

// Get all family users
$family_users = get_users(array(
    'meta_query' => array(
        array(
            'key' => 'wp_capabilities',
            'value' => 'family',
            'compare' => 'LIKE'
        )
    )
));

$current_user_id = get_current_user_id();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f0f1;
            padding: 20px;
        }

        .family-admin-panel {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #007cba;
            color: white;
        }

        .btn-primary:hover {
            background: #005a87;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #6c757d;
            color: #6c757d;
        }

        .btn-outline:hover {
            background: #6c757d;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab-button {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-button.active {
            border-bottom-color: #007cba;
            color: #007cba;
            font-weight: 600;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .tab-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
            margin-bottom: 30px;
        }

        .tab-section h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        input, select, textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #007cba;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .users-table tr:hover {
            background: #f8f9fa;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .role-admin {
            background: #dc3545;
            color: white;
        }

        .role-editor {
            background: #fd7e14;
            color: white;
        }

        .role-viewer {
            background: #6c757d;
            color: white;
        }

        .user-actions {
            display: flex;
            gap: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #007cba;
            margin: 10px 0;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .admin-tabs {
                flex-wrap: wrap;
            }
            
            .users-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <div class="family-admin-panel">
        <div class="admin-header">
            <h1>Family Tree Administration</h1>
            <div class="admin-actions">
                <a href="/family-dashboard" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline">Logout</a>
            </div>
        </div>

        <div class="admin-tabs">
            <button class="tab-button active" data-tab="users">User Management</button>
            <button class="tab-button" data-tab="members">Family Members</button>
            <button class="tab-button" data-tab="settings">Settings</button>
        </div>

        <div class="tab-content active" id="users-tab">
            <div class="tab-section">
                <h3>Create New User</h3>
                <form id="create-user-form" class="user-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required minlength="6">
                            <small>Minimum 6 characters</small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">User Role *</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="family_admin">Family Admin</option>
                            <option value="family_editor">Family Editor</option>
                            <option value="family_viewer">Family Viewer</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Create User</button>
                </form>
            </div>

            <div class="tab-section">
                <h3>Existing Users</h3>
                <div class="users-list">
                    <?php if ($family_users): ?>
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($family_users as $user): 
                                    $roles = $user->roles;
                                    $primary_role = !empty($roles) ? $roles[0] : 'No role';
                                    $role_class = '';
                                    if (strpos($primary_role, 'admin') !== false) $role_class = 'role-admin';
                                    elseif (strpos($primary_role, 'editor') !== false) $role_class = 'role-editor';
                                    elseif (strpos($primary_role, 'viewer') !== false) $role_class = 'role-viewer';
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($user->user_login); ?></td>
                                        <td><?php echo esc_html($user->display_name); ?></td>
                                        <td><?php echo esc_html($user->user_email); ?></td>
                                        <td>
                                            <select class="role-select <?php echo $role_class; ?>" data-user-id="<?php echo $user->ID; ?>" style="padding: 4px 8px; border-radius: 4px; border: none; color: white; font-weight: 500;">
                                                <option value="family_admin" <?php selected($primary_role, 'family_admin'); ?>>Admin</option>
                                                <option value="family_editor" <?php selected($primary_role, 'family_editor'); ?>>Editor</option>
                                                <option value="family_viewer" <?php selected($primary_role, 'family_viewer'); ?>>Viewer</option>
                                            </select>
                                        </td>
                                        <td class="user-actions">
                                            <?php if ($user->ID != $current_user_id): ?>
                                                <button class="btn btn-danger btn-sm delete-user" data-user-id="<?php echo $user->ID; ?>" data-username="<?php echo esc_attr($user->user_login); ?>">
                                                    Delete
                                                </button>
                                            <?php else: ?>
                                                <span style="color: #666; font-size: 12px;">Current User</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No family users found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tab-content" id="members-tab">
            <div class="tab-section">
                <h3>Family Members Overview</h3>
                <?php
                $members = FamilyTreeDatabase::get_members();
                $member_count = $members ? count($members) : 0;
                ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Total Members</h4>
                        <div class="stat-number"><?php echo $member_count; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>With Parents</h4>
                        <div class="stat-number">
                            <?php
                            $with_parents = 0;
                            if ($members) {
                                foreach ($members as $member) {
                                    if ($member->parent1_id || $member->parent2_id) {
                                        $with_parents++;
                                    }
                                }
                            }
                            echo $with_parents;
                            ?>
                        </div>
                    </div>
                </div>

                <div class="admin-actions">
                    <a href="/add-member" class="btn btn-primary">Add New Member</a>
                    <a href="/browse-members" class="btn btn-secondary">Browse Members</a>
                    <a href="/family-tree" class="btn btn-outline">View Tree</a>
                </div>
            </div>
        </div>

        <div class="tab-content" id="settings-tab">
            <div class="tab-section">
                <h3>Family Tree Settings</h3>
                <div class="settings-info">
                    <p><strong>User Registration:</strong>
                        <?php echo get_option('users_can_register') ? 'Enabled' : 'Disabled'; ?></p>
                    <p><em>To enable user registration, go to Settings → General in WordPress admin.</em></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm User Deletion</h3>
                <button class="close-modal">&times;</button>
            </div>
            <p>Are you sure you want to delete user "<span id="delete-username"></span>"?</p>
            <p style="color: #dc3545; font-size: 14px; margin-top: 10px;">
                <strong>Warning:</strong> This action cannot be undone.
            </p>
            <div class="modal-actions">
                <button id="confirm-delete" class="btn btn-danger">Delete User</button>
                <button id="cancel-delete" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <div id="admin-messages"></div>

    <script>
        jQuery(document).ready(function ($) {
            // Tab functionality
            $('.tab-button').on('click', function () {
                var tabId = $(this).data('tab');

                $('.tab-button').removeClass('active');
                $(this).addClass('active');

                $('.tab-content').removeClass('active');
                $('#' + tabId + '-tab').addClass('active');
            });

            // Create user form
            $('#create-user-form').on('submit', function (e) {
                e.preventDefault();

                // Validate passwords match
                var password = $('#password').val();
                var confirmPassword = $('#confirm_password').val();
                
                if (password !== confirmPassword) {
                    $('#admin-messages').html('<div class="message error">Passwords do not match.</div>');
                    return;
                }

                if (password.length < 6) {
                    $('#admin-messages').html('<div class="message error">Password must be at least 6 characters long.</div>');
                    return;
                }

                var formData = $(this).serializeArray();
                var data = {};

                formData.forEach(function (field) {
                    data[field.name] = field.value;
                });

                data.action = 'create_family_user';
                data.nonce = family_tree.nonce;

                // Show loading state
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.text();
                submitBtn.text('Creating...').prop('disabled', true);

                $.post(family_tree.ajax_url, data, function (response) {
                    if (response.success) {
                        $('#admin-messages').html('<div class="message success">' + response.data + '</div>');
                        $('#create-user-form')[0].reset();
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        $('#admin-messages').html('<div class="message error">' + response.data + '</div>');
                    }
                    
                    submitBtn.text(originalText).prop('disabled', false);
                });
            });

            // Role change functionality
            $('.role-select').on('change', function() {
                var userId = $(this).data('user-id');
                var newRole = $(this).val();
                var select = $(this);

                // Update visual appearance
                select.removeClass('role-admin role-editor role-viewer');
                if (newRole === 'family_admin') select.addClass('role-admin');
                else if (newRole === 'family_editor') select.addClass('role-editor');
                else if (newRole === 'family_viewer') select.addClass('role-viewer');

                $.post(family_tree.ajax_url, {
                    action: 'update_user_role',
                    user_id: userId,
                    new_role: newRole,
                    nonce: family_tree.nonce
                }, function(response) {
                    if (response.success) {
                        $('#admin-messages').html('<div class="message success">' + response.data + '</div>');
                    } else {
                        $('#admin-messages').html('<div class="message error">' + response.data + '</div>');
                        // Revert the select on error
                        location.reload();
                    }
                });
            });

            // Delete user functionality
            var userToDelete = null;

            $('.delete-user').on('click', function() {
                userToDelete = $(this).data('user-id');
                var username = $(this).data('username');
                $('#delete-username').text(username);
                $('#delete-user-modal').show();
            });

            $('.close-modal, #cancel-delete').on('click', function() {
                $('#delete-user-modal').hide();
                userToDelete = null;
            });

            $('#confirm-delete').on('click', function() {
                if (!userToDelete) return;

                $.post(family_tree.ajax_url, {
                    action: 'delete_family_user',
                    user_id: userToDelete,
                    nonce: family_tree.nonce
                }, function(response) {
                    if (response.success) {
                        $('#admin-messages').html('<div class="message success">' + response.data + '</div>');
                        $('#delete-user-modal').hide();
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        $('#admin-messages').html('<div class="message error">' + response.data + '</div>');
                        $('#delete-user-modal').hide();
                    }
                    userToDelete = null;
                });
            });
        });
    </script>

    <?php wp_footer(); ?>
</body>

</html>