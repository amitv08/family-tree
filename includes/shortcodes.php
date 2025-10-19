<?php

class FamilyTreeShortcodes
{

    public function __construct()
    {
        add_shortcode('family_dashboard', array($this, 'family_dashboard'));
        add_shortcode('family_login', array($this, 'family_login'));
        add_shortcode('family_admin', array($this, 'family_admin'));
        add_shortcode('add_family_member', array($this, 'add_family_member'));
        add_shortcode('family_tree_view', array($this, 'family_tree_view'));
    }

    public function family_dashboard($atts)
    {
        if (!is_user_logged_in()) {
            return $this->family_login($atts);
        }

        ob_start();
        ?>
        <div class="family-dashboard">
            <div class="dashboard-header">
                <h1>Family Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline">Logout</a>
                    <?php if (current_user_can('edit_family_members')): ?>
                        <a href="/add-member" class="btn btn-primary">Add Family Member</a>
                    <?php endif; ?>
                    <?php if (current_user_can('manage_family')): ?>
                        <a href="/family-admin" class="btn btn-secondary">Admin Panel</a>
                    <?php endif; ?>
                    <a href="/family-tree" class="btn btn-info">Tree View</a>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="members-section">
                    <h2>Family Members</h2>
                    <div class="members-grid">
                        <?php
                        $members = FamilyTreeDatabase::get_members();
                        if ($members):
                            foreach ($members as $member):
                                $parents = array();
                                if ($member->parent1_id) {
                                    $parent1 = FamilyTreeDatabase::get_member($member->parent1_id);
                                    if ($parent1)
                                        $parents[] = $parent1->first_name . ' ' . $parent1->last_name;
                                }
                                if ($member->parent2_id) {
                                    $parent2 = FamilyTreeDatabase::get_member($member->parent2_id);
                                    if ($parent2)
                                        $parents[] = $parent2->first_name . ' ' . $parent2->last_name;
                                }
                                ?>
                                <div class="member-card">
                                    <div class="member-photo">
                                        <?php if ($member->photo_url): ?>
                                            <img src="<?php echo esc_url($member->photo_url); ?>"
                                                alt="<?php echo esc_attr($member->first_name); ?>">
                                        <?php else: ?>
                                            <div class="photo-placeholder">
                                                <?php echo strtoupper(substr($member->first_name, 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="member-details">
                                        <h3><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></h3>
                                        <p><strong>Born:</strong>
                                            <?php echo $member->birth_date ? date('M j, Y', strtotime($member->birth_date)) : 'Unknown'; ?>
                                        </p>
                                        <?php if ($member->death_date): ?>
                                            <p><strong>Died:</strong> <?php echo date('M j, Y', strtotime($member->death_date)); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($parents)): ?>
                                            <p><strong>Parents:</strong> <?php echo implode(' & ', $parents); ?></p>
                                        <?php endif; ?>
                                        <?php if ($member->biography): ?>
                                            <p class="biography"><?php echo wp_trim_words(esc_html($member->biography), 20); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; else: ?>
                            <div class="no-members">
                                <p>No family members added yet.</p>
                                <?php if (current_user_can('edit_family_members')): ?>
                                    <a href="/add-member" class="btn btn-primary">Add First Member</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function family_login($atts)
    {
        if (is_user_logged_in()) {
            return $this->family_dashboard($atts);
        }

        ob_start();
        ?>
        <div class="family-login-page">
            <div class="login-container">
                <h2>Family Tree Login</h2>
                <?php
                wp_login_form(array(
                    'redirect' => home_url('/family-dashboard'),
                    'label_username' => 'Username or Email',
                    'label_remember' => 'Remember Me'
                ));
                ?>

                <?php if (get_option('users_can_register')): ?>
                    <div class="register-link">
                        <p>Don't have an account? <a href="<?php echo wp_registration_url(); ?>">Register here</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function family_admin($atts)
    {
        if (!current_user_can('manage_family')) {
            return '<div class="family-message error"><p>You do not have permission to access the admin panel.</p></div>';
        }

        ob_start();
        ?>
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
                        <?php
                        $users = get_users(array(
                            'meta_query' => array(
                                array(
                                    'key' => 'wp_capabilities',
                                    'value' => 'family',
                                    'compare' => 'LIKE'
                                )
                            )
                        ));

                        if ($users):
                            ?>
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user):
                                        $roles = $user->roles;
                                        $primary_role = !empty($roles) ? $roles[0] : 'No role';
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html($user->user_login); ?></td>
                                            <td><?php echo esc_html($user->display_name); ?></td>
                                            <td><?php echo esc_html($user->user_email); ?></td>
                                            <td><span class="role-badge"><?php echo esc_html($primary_role); ?></span></td>
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
                        <a href="/family-tree" class="btn btn-secondary">View Tree</a>
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

                    var formData = $(this).serializeArray();
                    var data = {};

                    formData.forEach(function (field) {
                        data[field.name] = field.value;
                    });

                    data.action = 'create_family_user';
                    data.nonce = family_tree.nonce;

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
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    public function add_family_member($atts)
    {
        if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
            return '<div class="family-message error"><p>You do not have permission to add family members.</p></div>';
        }

        ob_start();
        ?>
        <div class="add-member-page">
            <div class="page-header">
                <h1>Add Family Member</h1>
                <a href="/family-dashboard" class="btn btn-back">← Back to Dashboard</a>
            </div>

            <form id="add-member-form" class="member-form">
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Life Events</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="birth_date">Birth Date</label>
                            <input type="date" id="birth_date" name="birth_date">
                        </div>
                        <div class="form-group">
                            <label for="death_date">Death Date</label>
                            <input type="date" id="death_date" name="death_date">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Family Relationships</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="parent1_id">Parent 1</label>
                            <select id="parent1_id" name="parent1_id">
                                <option value="">Select Parent</option>
                                <?php
                                $members = FamilyTreeDatabase::get_members();
                                foreach ($members as $member) {
                                    echo '<option value="' . $member->id . '">' . $member->first_name . ' ' . $member->last_name . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="parent2_id">Parent 2</label>
                            <select id="parent2_id" name="parent2_id">
                                <option value="">Select Parent</option>
                                <?php
                                foreach ($members as $member) {
                                    echo '<option value="' . $member->id . '">' . $member->first_name . ' ' . $member->last_name . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Additional Information</h3>
                    <div class="form-group">
                        <label for="photo_url">Photo URL</label>
                        <input type="url" id="photo_url" name="photo_url" placeholder="https://example.com/photo.jpg">
                    </div>
                    <div class="form-group">
                        <label for="biography">Biography</label>
                        <textarea id="biography" name="biography" rows="4"
                            placeholder="Tell us about this family member..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Family Member</button>
                    <a href="/family-dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

            <div id="form-message"></div>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                $('#add-member-form').on('submit', function (e) {
                    e.preventDefault();

                    var formData = $(this).serializeArray();
                    var data = {};

                    formData.forEach(function (field) {
                        data[field.name] = field.value;
                    });

                    data.action = 'add_family_member';
                    data.nonce = family_tree.nonce;

                    $.post(family_tree.ajax_url, data, function (response) {
                        if (response.success) {
                            $('#form-message').html('<div class="message success">' + response.data + '</div>');
                            $('#add-member-form')[0].reset();
                            setTimeout(function () {
                                window.location.href = '/family-dashboard';
                            }, 2000);
                        } else {
                            $('#form-message').html('<div class="message error">' + response.data + '</div>');
                        }
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    public function family_tree_view($atts)
    {
        if (!is_user_logged_in()) {
            return $this->family_login($atts);
        }

        ob_start();
        ?>
        <div class="family-tree-view">
            <div class="tree-header">
                <h1>Family Tree</h1>
                <div class="tree-controls">
                    <a href="/family-dashboard" class="btn btn-secondary">← Back to Dashboard</a>
                    <button class="btn btn-outline" onclick="location.reload()">Refresh</button>
                </div>
            </div>

            <div class="tree-container">
                <div id="tree-visualization">
                    <div class="loading-message">
                        <p>Loading family tree...</p>
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>

            <div class="tree-info">
                <p><strong>Family Tree View</strong> - This shows all your family members in a simple layout.</p>
            </div>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                // Simple tree display
                function loadTreeData() {
                    $.ajax({
                        url: family_tree.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'get_tree_data',
                            nonce: family_tree.nonce
                        },
                        success: function (response) {
                            if (response.success) {
                                displaySimpleTree(response.data);
                            }
                        }
                    });
                }

                function displaySimpleTree(members) {
                    var html = '<div class="simple-tree-grid">';

                    if (members && members.length > 0) {
                        members.forEach(function (member) {
                            html += `
                            <div class="tree-member-card">
                                <div class="member-photo">
                                    ${member.photo ?
                            '<img src="' + member.photo + '" alt="' + member.firstName + '">' :
                            '<div class="photo-placeholder">' + member.firstName.charAt(0) + '</div>'
                        }
                                </div>
                                <div class="member-info">
                                    <h4>${member.firstName} ${member.lastName}</h4>
                                    <p>Born: ${member.birthDate || 'Unknown'}</p>
                                    ${member.deathDate ? '<p>Died: ' + member.deathDate + '</p>' : ''}
                                    <p class="gender">${member.gender}</p>
                                </div>
                            </div>
                        `;
                        });
                    } else {
                        html += '<div class="no-data"><p>No family members found.</p></div>';
                    }

                    html += '</div>';
                    $('#tree-visualization').html(html);
                }

                loadTreeData();
            });
        </script>
        <?php
        return ob_get_clean();
    }
}

new FamilyTreeShortcodes();
?>