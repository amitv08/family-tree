<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}
if (!current_user_can('edit_family_members')) {
    wp_die('You do not have permission to add members.');
}
wp_head();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Clan</title>
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

        .add-clan-page {
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

        .clan-form {
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

        input,
        select,
        textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
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

    <nav class="top-menu">
        <a href="/family-dashboard">🏠 Dashboard</a>
        <a href="/browse-members" class="active">👨‍👩‍👧 Members</a>
        <a href="/browse-clans">🏰 Clans</a>
    </nav>

    <div class="dashboard-container">
        <h2>Add New Member</h2>

        <form id="addMemberForm" class="member-form">
            <div class="form-section">

                <!-- Clan selection -->
                <div class="form-group">
                    <label for="clan_id">Clan *</label>
                    <select name="clan_id" id="clan_id" data-selected="">
                        <option value="">-- Select Clan --</option>
                        <!-- Populated by JS -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="clan_location_id">Clan Location *</label>
                    <select name="clan_location_id" id="clan_location_id" data-selected="">
                        <option value="">-- Select Location --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="clan_surname_id">Clan Surname *</label>
                    <select name="clan_surname_id" id="clan_surname_id" data-selected="">
                        <option value="">-- Select Surname --</option>
                    </select>
                </div>

                <!-- Existing member fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Birth Date</label>
                        <input type="date" name="birth_date">
                    </div>
                    <div class="form-group">
                        <label>Death Date</label>
                        <input type="date" name="death_date">
                    </div>
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">-- Select --</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Biography</label>
                    <textarea name="biography"></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Member</button>
                <a href="/browse-members" class="btn btn-secondary">Cancel</a>
            </div>

            <div id="memberMessage" class="message"></div>
        </form>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            // client-side submit to existing add_member ajax handler
            $('#addMemberForm').on('submit', function (e) {
                e.preventDefault();
                const data = {
                    action: 'add_family_member', // existing action name in your plugin
                    nonce: family_tree.nonce,
                    first_name: $('[name="first_name"]').val(),
                    last_name: $('[name="last_name"]').val(),
                    birth_date: $('[name="birth_date"]').val(),
                    death_date: $('[name="death_date"]').val(),
                    gender: $('[name="gender"]').val(),
                    biography: $('[name="biography"]').val(),
                    parent1_id: $('[name="parent1_id"]').val ? $('[name="parent1_id"]').val() : null,
                    parent2_id: $('[name="parent2_id"]').val ? $('[name="parent2_id"]').val() : null,
                    clan_id: $('[name="clan_id"]').val(),
                    clan_location_id: $('[name="clan_location_id"]').val(),
                    clan_surname_id: $('[name="clan_surname_id"]').val()
                };

                // basic client validation
                if (!data.clan_id || !data.clan_location_id || !data.clan_surname_id) {
                    $('#memberMessage').removeClass().addClass('message error').text('Please select clan, location and surname').show();
                    return;
                }

                $.post(family_tree.ajax_url, data, function (res) {
                    if (res.success) {
                        $('#memberMessage').removeClass().addClass('message success').text('Member added successfully').show();
                        setTimeout(function () { window.location.href = '/browse-members'; }, 900);
                    } else {
                        $('#memberMessage').removeClass().addClass('message error').text(res.data || 'Error adding member').show();
                    }
                });
            });
        });
    </script>

    <?php wp_footer(); ?>
</body>

</html>