<?php
/**
 * Family Tree Plugin - Admin Panel
 * User management and system settings with professional design
 */

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

$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/family-dashboard'],
    ['label' => 'Admin Panel'],
];
$page_title = '⚙️ Administration Panel';
$page_actions = '
    <a href="/family-dashboard" class="btn btn-outline btn-sm">
        ← Back to Dashboard
    </a>
';

ob_start();
?>

<!-- Admin Tabs Navigation -->
<div style="
    display: flex;
    gap: var(--spacing-lg);
    border-bottom: 2px solid var(--color-border);
    margin-bottom: var(--spacing-2xl);
    flex-wrap: wrap;
">
    <button class="tab-button active" data-tab="users" style="
        padding: var(--spacing-md) var(--spacing-lg);
        border: none;
        background: none;
        cursor: pointer;
        font-size: var(--font-size-base);
        color: var(--color-text-secondary);
        border-bottom: 3px solid transparent;
        transition: all var(--transition-fast);
        font-weight: var(--font-weight-medium);
    ">
        👤 User Management
    </button>
    <button class="tab-button" data-tab="members" style="
        padding: var(--spacing-md) var(--spacing-lg);
        border: none;
        background: none;
        cursor: pointer;
        font-size: var(--font-size-base);
        color: var(--color-text-secondary);
        border-bottom: 3px solid transparent;
        transition: all var(--transition-fast);
        font-weight: var(--font-weight-medium);
    ">
        👥 Members Overview
    </button>
    <button class="tab-button" data-tab="settings" style="
        padding: var(--spacing-md) var(--spacing-lg);
        border: none;
        background: none;
        cursor: pointer;
        font-size: var(--font-size-base);
        color: var(--color-text-secondary);
        border-bottom: 3px solid transparent;
        transition: all var(--transition-fast);
        font-weight: var(--font-weight-medium);
    ">
        ⚙️ Settings
    </button>
</div>

<style>
    .tab-button.active {
        color: var(--color-primary) !important;
        border-bottom-color: var(--color-primary) !important;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
</style>

<!-- ===== TAB 1: USER MANAGEMENT ===== -->
<div class="tab-content active" id="users-tab">
    <!-- Create User Section -->
    <div class="section">
        <h2 class="section-title">➕ Create New User</h2>
        
        <div class="container container-sm">
            <form id="createUserForm" class="form">
                <!-- Username & Email -->
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label required" for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="e.g., john_smith">
                        <small class="form-help">Minimum 3 characters. No spaces.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="email">Email Address</label>
                        <input type="email" id="email" name="email" required placeholder="john@example.com">
                    </div>
                </div>

                <!-- Name Fields -->
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="John">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Smith">
                    </div>
                </div>

                <!-- Password -->
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label required" for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Min. 8 characters">
                        <small class="form-help">Minimum 8 characters recommended</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label class="form-label required" for="role">User Role</label>
                    <select id="role" name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="family_admin">👑 Admin - Full access to manage members & users</option>
                        <option value="family_editor">✏️ Editor - Can add & edit family members</option>
                        <option value="family_viewer">👁️ Viewer - Read-only access</option>
                    </select>
                    <small class="form-help">Choose the access level for this user</small>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        ➕ Create User
                    </button>
                </div>

                <!-- Message -->
                <div id="createUserMessage" style="margin-top: var(--spacing-lg);"></div>
            </form>
        </div>
    </div>

    <!-- Existing Users Section -->
    <div class="section">
        <h2 class="section-title">👥 Existing Users</h2>
        
        <?php if (empty($family_users)): ?>
            <div class="alert alert-info">
                <div class="alert-icon">ℹ️</div>
                <div class="alert-content">
                    <div class="alert-title">No users yet</div>
                    <p class="alert-message">Create your first user above.</p>
                </div>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table">
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
                            $role_display = ucfirst(str_replace('family_', '', $primary_role));
                        ?>
                            <tr>
                                <td>
                                    <strong>@<?php echo esc_html($user->user_login); ?></strong>
                                </td>
                                <td><?php echo esc_html($user->display_name); ?></td>
                                <td>
                                    <a href="mailto:<?php echo esc_html($user->user_email); ?>">
                                        <?php echo esc_html($user->user_email); ?>
                                    </a>
                                </td>
                                <td>
                                    <select class="role-select" data-user-id="<?php echo $user->ID; ?>" style="
                                        padding: var(--spacing-sm) var(--spacing-md);
                                        border-radius: var(--radius-sm);
                                        border: none;
                                        font-weight: var(--font-weight-medium);
                                        background: var(--color-primary);
                                        color: white;
                                        cursor: pointer;
                                    ">
                                        <option value="family_admin" <?php selected($primary_role, 'family_admin'); ?>>Admin</option>
                                        <option value="family_editor" <?php selected($primary_role, 'family_editor'); ?>>Editor</option>
                                        <option value="family_viewer" <?php selected($primary_role, 'family_viewer'); ?>>Viewer</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="btn-group" style="gap: var(--spacing-sm);">
                                        <?php if ($user->ID != $current_user_id): ?>
                                            <button class="btn btn-sm btn-danger delete-user" data-user-id="<?php echo $user->ID; ?>" data-username="<?php echo esc_attr($user->user_login); ?>">
                                                🗑️ Delete
                                            </button>
                                        <?php else: ?>
                                            <span style="
                                                color: var(--color-text-light);
                                                font-size: var(--font-size-sm);
                                                padding: var(--spacing-sm) var(--spacing-md);
                                            ">
                                                👤 You
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== TAB 2: MEMBERS OVERVIEW ===== -->
<div class="tab-content" id="members-tab">
    <div class="section">
        <h2 class="section-title">📊 Family Members Overview</h2>
        
        <?php
        $members = FamilyTreeDatabase::get_members();
        $member_count = $members ? count($members) : 0;
        $with_parents = 0;
        $with_birthdates = 0;
        $with_photos = 0;
        
        if ($members) {
            foreach ($members as $member) {
                if ($member->parent1_id || $member->parent2_id) $with_parents++;
                if ($member->birth_date) $with_birthdates++;
                if ($member->photo_url) $with_photos++;
            }
        }
        ?>

        <!-- Stats Grid -->
        <div class="grid grid-4" style="margin-bottom: var(--spacing-2xl);">
            <div class="stat-card">
                <div class="stat-card-icon">👥</div>
                <div class="stat-card-value"><?php echo $member_count; ?></div>
                <p class="stat-card-label">Total Members</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon">👨‍👩‍👧</div>
                <div class="stat-card-value"><?php echo $with_parents; ?></div>
                <p class="stat-card-label">With Parents Linked</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon">🎂</div>
                <div class="stat-card-value"><?php echo $with_birthdates; ?></div>
                <p class="stat-card-label">With Birth Dates</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon">📸</div>
                <div class="stat-card-value"><?php echo $with_photos; ?></div>
                <p class="stat-card-label">With Photos</p>
            </div>
        </div>

        <!-- Progress Indicators -->
        <div style="
            background: var(--color-bg-white);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
        ">
            <h3 style="color: var(--color-text-primary); margin: 0 0 var(--spacing-lg) 0;">
                📈 Data Completeness
            </h3>

            <?php if ($member_count > 0): ?>
                <div style="margin-bottom: var(--spacing-lg);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                        <span style="font-weight: var(--font-weight-medium); color: var(--color-text-primary);">
                            Members with Parents
                        </span>
                        <span style="color: var(--color-primary); font-weight: var(--font-weight-semibold);">
                            <?php echo round(($with_parents / $member_count) * 100); ?>%
                        </span>
                    </div>
                    <div style="
                        background: var(--color-bg-light);
                        border-radius: var(--radius-full);
                        height: 8px;
                        overflow: hidden;
                    ">
                        <div style="
                            background: var(--color-primary);
                            height: 100%;
                            width: <?php echo round(($with_parents / $member_count) * 100); ?>%;
                            transition: width 0.3s ease;
                        "></div>
                    </div>
                </div>

                <div style="margin-bottom: var(--spacing-lg);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                        <span style="font-weight: var(--font-weight-medium); color: var(--color-text-primary);">
                            Members with Birth Dates
                        </span>
                        <span style="color: var(--color-primary); font-weight: var(--font-weight-semibold);">
                            <?php echo round(($with_birthdates / $member_count) * 100); ?>%
                        </span>
                    </div>
                    <div style="
                        background: var(--color-bg-light);
                        border-radius: var(--radius-full);
                        height: 8px;
                        overflow: hidden;
                    ">
                        <div style="
                            background: var(--color-success);
                            height: 100%;
                            width: <?php echo round(($with_birthdates / $member_count) * 100); ?>%;
                            transition: width 0.3s ease;
                        "></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                        <span style="font-weight: var(--font-weight-medium); color: var(--color-text-primary);">
                            Members with Photos
                        </span>
                        <span style="color: var(--color-primary); font-weight: var(--font-weight-semibold);">
                            <?php echo round(($with_photos / $member_count) * 100); ?>%
                        </span>
                    </div>
                    <div style="
                        background: var(--color-bg-light);
                        border-radius: var(--radius-full);
                        height: 8px;
                        overflow: hidden;
                    ">
                        <div style="
                            background: var(--color-warning);
                            height: 100%;
                            width: <?php echo round(($with_photos / $member_count) * 100); ?>%;
                            transition: width 0.3s ease;
                        "></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <div class="alert-icon">ℹ️</div>
                    <div class="alert-content">
                        <p class="alert-message" style="margin: 0;">
                            No members in the database yet.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="btn-group">
            <a href="/add-member" class="btn btn-primary">
                ➕ Add Member
            </a>
            <a href="/browse-members" class="btn btn-secondary">
                📋 Browse All Members
            </a>
            <a href="/family-tree" class="btn btn-outline">
                🌳 View Tree
            </a>
        </div>
    </div>
</div>

<!-- ===== TAB 3: SETTINGS ===== -->
<div class="tab-content" id="settings-tab">
    <div class="section">
        <h2 class="section-title">⚙️ System Settings</h2>
        
        <div style="
            background: var(--color-bg-white);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
        ">
            <div style="margin-bottom: var(--spacing-lg);">
                <h3 style="color: var(--color-text-primary); margin: 0 0 var(--spacing-md) 0;">
                    👥 User Registration
                </h3>
                <p style="color: var(--color-text-secondary); margin: 0 0 var(--spacing-md) 0; font-size: var(--font-size-sm);">
                    Current Status: 
                    <strong style="color: <?php echo get_option('users_can_register') ? 'var(--color-success)' : 'var(--color-danger)'; ?>;">
                        <?php echo get_option('users_can_register') ? '✅ Enabled' : '❌ Disabled'; ?>
                    </strong>
                </p>
                <p style="color: var(--color-text-light); font-size: var(--font-size-sm); margin: 0;">
                    To enable/disable user registration, go to WordPress Admin → Settings → General → Membership
                </p>
            </div>

            <hr style="border: none; border-top: 1px solid var(--color-border); margin: var(--spacing-lg) 0;">

            <div style="margin-bottom: var(--spacing-lg);">
                <h3 style="color: var(--color-text-primary); margin: 0 0 var(--spacing-md) 0;">
                    🔐 Security
                </h3>
                <ul style="margin: 0; padding-left: var(--spacing-xl); font-size: var(--font-size-sm); color: var(--color-text-secondary);">
                    <li style="margin-bottom: var(--spacing-sm);">All AJAX requests are protected with nonce tokens</li>
                    <li style="margin-bottom: var(--spacing-sm);">User permissions are validated for all operations</li>
                    <li style="margin-bottom: var(--spacing-sm);">Database queries use prepared statements</li>
                    <li>Data is sanitized and escaped on output</li>
                </ul>
            </div>

            <hr style="border: none; border-top: 1px solid var(--color-border); margin: var(--spacing-lg) 0;">

            <div>
                <h3 style="color: var(--color-text-primary); margin: 0 0 var(--spacing-md) 0;">
                    ℹ️ System Information
                </h3>
                <dl style="display: grid; grid-template-columns: 150px 1fr; gap: var(--spacing-lg); font-size: var(--font-size-sm);">
                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary);">Plugin Version:</dt>
                    <dd style="margin: 0; color: var(--color-text-secondary);">2.3</dd>
                    
                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary);">WordPress Version:</dt>
                    <dd style="margin: 0; color: var(--color-text-secondary);"><?php echo esc_html(get_bloginfo('version')); ?></dd>
                    
                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary);">Site URL:</dt>
                    <dd style="margin: 0; color: var(--color-text-secondary);"><?php echo esc_url(site_url()); ?></dd>
                    
                    <dt style="font-weight: var(--font-weight-semibold); color: var(--color-text-primary);">Database Prefix:</dt>
                    <dd style="margin: 0; color: var(--color-text-secondary);"><?php global $wpdb; echo esc_html($wpdb->prefix); ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div id="deleteUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 style="margin: 0;">Delete User</h2>
            <button class="modal-close" type="button">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete the user "<strong id="deleteUsername"></strong>"?</p>
            <div class="alert alert-danger" style="margin-top: var(--spacing-lg);">
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                    <div class="alert-title">This cannot be undone</div>
                    <p class="alert-message" style="margin: 0;">This action is permanent. Please proceed with caution.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button id="confirmDelete" class="btn btn-danger">🗑️ Delete User</button>
            <button id="cancelDelete" class="btn btn-outline">Cancel</button>
        </div>
    </div>
</div>

<!-- Messages -->
<div id="adminMessages" style="margin-top: var(--spacing-xl);"></div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.tab-button').on('click', function() {
        const tabId = $(this).data('tab');
        
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        
        $('.tab-content').removeClass('active');
        $('#' + tabId + '-tab').addClass('active');
    });

    // Create user form
    $('#createUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();
        
        if (password !== confirmPassword) {
            showToast('Passwords do not match', 'error');
            return;
        }
        
        if (password.length < 6) {
            showToast('Password must be at least 6 characters', 'error');
            return;
        }
        
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="loading-spinner"></span> Creating...');
        
        const data = {
            action: 'create_family_user',
            nonce: family_tree.nonce,
            username: $('#username').val(),
            email: $('#email').val(),
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
            password: password,
            role: $('#role').val()
        };
        
        $.post(family_tree.ajax_url, data, function(response) {
            if (response.success) {
                showToast('User created successfully! 🎉', 'success');
                $('#createUserForm')[0].reset();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Error: ' + (response.data || 'Failed to create user'), 'error');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Change user role
    $('.role-select').on('change', function() {
        const userId = $(this).data('user-id');
        const newRole = $(this).val();
        const select = $(this);
        
        $.post(family_tree.ajax_url, {
            action: 'update_user_role',
            nonce: family_tree.nonce,
            user_id: userId,
            new_role: newRole
        }, function(response) {
            if (response.success) {
                showToast('Role updated successfully', 'success');
            } else {
                showToast('Error: ' + (response.data || 'Failed to update'), 'error');
                location.reload();
            }
        });
    });

    // Delete user
    let userToDelete = null;
    
    $('.delete-user').on('click', function() {
        userToDelete = $(this).data('user-id');
        const username = $(this).data('username');
        $('#deleteUsername').text(username);
        $('#deleteUserModal').addClass('active');
    });

    $('.modal-close, #cancelDelete').on('click', function() {
        $('#deleteUserModal').removeClass('active');
        userToDelete = null;
    });

    $('#confirmDelete').on('click', function() {
        if (!userToDelete) return;
        
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="loading-spinner"></span>');
        
        $.post(family_tree.ajax_url, {
            action: 'delete_family_user',
            nonce: family_tree.nonce,
            user_id: userToDelete
        }, function(response) {
            if (response.success) {
                showToast('User deleted successfully', 'success');
                $('#deleteUserModal').removeClass('active');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error: ' + (response.data || 'Failed to delete'), 'error');
                btn.prop('disabled', false).html(originalText);
                $('#deleteUserModal').removeClass('active');
            }
        });
    });

    // Close modal on background click
    $('#deleteUserModal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
        }
    });
});
</script>

<?php
$page_content = ob_get_clean();
include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>