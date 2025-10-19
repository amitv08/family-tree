<?php
if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
    wp_redirect('/family-login');
    exit;
}

$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$member = FamilyTreeDatabase::get_member($member_id);

if (!$member) {
    echo '<div class="error-message">Member not found.</div>';
    exit;
}

// Helper function for selected options
function is_selected($value, $compare)
{
    return $value == $compare ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Family Member</title>
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
            line-height: 1.6;
        }

        .edit-member-page {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-back {
            background: transparent;
            color: #007cba;
            text-decoration: none;
        }

        .member-form {
            margin-top: 20px;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .form-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .message {
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        small {
            color: #666;
            font-size: 12px;
        }
    </style>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <div class="edit-member-page">
        <div class="page-header">
            <h1>Edit Family Member: <?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></h1>
            <a href="/family-dashboard" class="btn btn-back">← Back to Dashboard</a>
        </div>

        <form id="edit-member-form" class="member-form">
            <input type="hidden" name="member_id" value="<?php echo $member->id; ?>">

            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name"
                            value="<?php echo esc_attr($member->first_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name"
                            value="<?php echo esc_attr($member->last_name); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo is_selected($member->gender, 'male'); ?>>Male</option>
                        <option value="female" <?php echo is_selected($member->gender, 'female'); ?>>Female</option>
                        <option value="other" <?php echo is_selected($member->gender, 'other'); ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3>Life Events</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="birth_date">Birth Date</label>
                        <input type="date" id="birth_date" name="birth_date"
                            value="<?php echo esc_attr($member->birth_date); ?>">
                    </div>
                    <div class="form-group">
                        <label for="death_date">Death Date</label>
                        <input type="date" id="death_date" name="death_date"
                            value="<?php echo esc_attr($member->death_date); ?>">
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
                            $all_members = FamilyTreeDatabase::get_members();
                            foreach ($all_members as $m) {
                                if ($m->id != $member->id) { // Can't be own parent
                                    echo '<option value="' . $m->id . '" ' . is_selected($member->parent1_id, $m->id) . '>' .
                                        esc_html($m->first_name . ' ' . $m->last_name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="parent2_id">Parent 2</label>
                        <select id="parent2_id" name="parent2_id">
                            <option value="">Select Parent</option>
                            <?php
                            foreach ($all_members as $m) {
                                if ($m->id != $member->id) { // Can't be own parent
                                    echo '<option value="' . $m->id . '" ' . is_selected($member->parent2_id, $m->id) . '>' .
                                        esc_html($m->first_name . ' ' . $m->last_name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <small>Note: A person cannot be their own parent.</small>
            </div>

            <div class="form-section">
                <h3>Additional Information</h3>
                <div class="form-group">
                    <label for="photo_url">Photo URL</label>
                    <input type="url" id="photo_url" name="photo_url"
                        value="<?php echo esc_attr($member->photo_url); ?>" placeholder="https://example.com/photo.jpg">
                </div>
                <div class="form-group">
                    <label for="biography">Biography</label>
                    <textarea id="biography" name="biography" rows="4"
                        placeholder="Tell us about this family member..."><?php echo esc_textarea($member->biography); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Member</button>
                <a href="/family-dashboard" class="btn btn-secondary">Cancel</a>
                <button type="button" class="btn btn-danger" onclick="deleteMember(<?php echo $member->id; ?>)">Delete
                    Member</button>
            </div>
        </form>

        <div id="form-message"></div>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            $('#edit-member-form').on('submit', function (e) {
                e.preventDefault();

                console.log('Form submitted'); // Debug log

                // Show loading state
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.text();
                submitBtn.text('Updating...').prop('disabled', true);

                var formData = $(this).serializeArray();
                var data = {};

                formData.forEach(function (field) {
                    data[field.name] = field.value;
                });

                data.action = 'update_family_member';
                data.nonce = family_tree.nonce;

                console.log('Sending data:', data); // Debug log

                $.post(family_tree.ajax_url, data, function (response) {
                    console.log('Response received:', response); // Debug log

                    // Restore button state
                    submitBtn.text(originalText).prop('disabled', false);

                    if (response.success) {
                        $('#form-message').html('<div class="message success">' + response.data + '</div>');
                        setTimeout(function () {
                            window.location.href = '/family-dashboard';
                        }, 2000);
                    } else {
                        $('#form-message').html('<div class="message error">' + response.data + '</div>');
                    }
                }).fail(function (xhr, status, error) {
                    console.error('AJAX error:', error); // Debug log
                    submitBtn.text(originalText).prop('disabled', false);
                    $('#form-message').html('<div class="message error">Network error: ' + error + '</div>');
                });
            });
        });

        function deleteMember(memberId) {
            if (confirm('Are you sure you want to delete this family member? This action cannot be undone.')) {
                var data = {
                    action: 'delete_family_member',
                    member_id: memberId,
                    nonce: family_tree.nonce
                };

                jQuery.post(family_tree.ajax_url, data, function (response) {
                    if (response.success) {
                        alert(response.data);
                        window.location.href = '/family-dashboard';
                    } else {
                        alert('Error: ' + response.data);
                    }
                });
            }
        }
    </script>

    <?php wp_footer(); ?>
</body>

</html>