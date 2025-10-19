<?php
if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
    wp_redirect('/family-login');
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Family Member</title>
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

        .add-member-page {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .page-header h1 {
            color: #333;
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

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #545b62;
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

        .member-form {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
        }

        .form-section h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.2em;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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

        input[type="file"] {
            padding: 8px;
            border: 2px dashed #e0e0e0;
            background: #fafafa;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        #form-message {
            margin-top: 20px;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
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

        .photo-preview {
            margin-top: 10px;
            text-align: center;
        }

        .photo-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }

        .required {
            color: #e53e3e;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <div class="add-member-page">
        <div class="page-header">
            <h1>Add Family Member</h1>
            <a href="/family-dashboard" class="btn btn-back">← Back to Dashboard</a>
        </div>

        <form id="add-member-form" class="member-form" enctype="multipart/form-data">
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
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
                <div class="form-group">
                    <label for="parent_id">Parent</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">Select Parent (Optional)</option>
                        <?php
                        $members = FamilyTreeDatabase::get_members();
                        foreach ($members as $member) {
                            echo '<option value="' . esc_attr($member->id) . '">' . esc_html($member->first_name . ' ' . $member->last_name) . '</option>';
                        }
                        ?>
                    </select>
                    <small style="color: #666; margin-top: 5px;">Select a parent to establish family relationships</small>
                </div>
            </div>

            <div class="form-section">
                <h3>Photo</h3>
                <div class="form-group full-width">
                    <label for="photo">Upload Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <small style="color: #666; margin-top: 5px;">Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                    <div class="photo-preview" id="photo-preview" style="display: none;">
                        <img id="preview-image" src="" alt="Photo preview">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Additional Information</h3>
                <div class="form-group full-width">
                    <label for="biography">Biography</label>
                    <textarea id="biography" name="biography" rows="4" placeholder="Tell us about this family member..."></textarea>
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
            // Photo preview functionality
            $('#photo').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview-image').attr('src', e.target.result);
                        $('#photo-preview').show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#photo-preview').hide();
                }
            });

            $('#add-member-form').on('submit', function (e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'add_family_member');
                formData.append('nonce', family_tree.nonce);

                // Show loading state
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.text();
                submitBtn.text('Adding...').prop('disabled', true);

                $.ajax({
                    url: family_tree.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            $('#form-message').html('<div class="message success">' + response.data + '</div>');
                            $('#add-member-form')[0].reset();
                            $('#photo-preview').hide();
                            setTimeout(function () {
                                window.location.href = '/family-dashboard';
                            }, 2000);
                        } else {
                            $('#form-message').html('<div class="message error">' + response.data + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#form-message').html('<div class="message error">Error: ' + error + '</div>');
                    },
                    complete: function() {
                        submitBtn.text(originalText).prop('disabled', false);
                    }
                });
            });
        });
    </script>

    <?php wp_footer(); ?>
</body>

</html>