<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$clan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clan = FamilyTreeClanDatabase::get_clan($clan_id);

if (!$clan) {
    wp_die('Clan not found.');
}

wp_head();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Clan - <?php echo esc_html($clan->clan_name); ?></title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6f7;
            color: #333;
            line-height: 1.5;
        }

        .dashboard-container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 25px;
            color: #007cba;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
        }

        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .tag {
            background: #007cba;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .tag button {
            border: none;
            background: transparent;
            color: white;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background-color 0.2s ease;
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

        .message {
            margin-top: 15px;
            padding: 12px;
            border-radius: 5px;
            display: none;
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
    </style>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <!-- 🔹 Global Top Menu -->
    <nav class="top-menu">
        <a href="/family-dashboard">🏠 Dashboard</a>
        <a href="/browse-members">👨‍👩‍👧 Members</a>
        <a href="/browse-clans" class="active">🏰 Clans</a>
    </nav>
    
    <div class="dashboard-container">
        <h2>Edit Clan: <?php echo esc_html($clan->clan_name); ?></h2>

        <form id="editClanForm" class="family-form">
            <input type="hidden" name="clan_id" value="<?php echo $clan_id; ?>">

            <div class="form-group">
                <label>Clan Name *</label>
                <input type="text" name="clan_name" value="<?php echo esc_attr($clan->clan_name); ?>" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?php echo esc_textarea($clan->description); ?></textarea>
            </div>

            <div class="form-group">
                <label>Origin Year</label>
                <input type="number" name="origin_year" value="<?php echo esc_attr($clan->origin_year); ?>">
            </div>

            <div class="form-group">
                <label>Locations *</label>
                <input type="text" id="clanLocations" placeholder="Add and press Enter">
                <div id="locationTags" class="tag-container"></div>
            </div>

            <div class="form-group">
                <label>Surnames *</label>
                <input type="text" id="clanSurnames" placeholder="Add and press Enter">
                <div id="surnameTags" class="tag-container"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Clan</button>
                <a href="/browse-clans" class="btn btn-secondary">Cancel</a>
            </div>

            <div id="formMessage" class="message"></div>
        </form>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            console.log("Edit Clan ready");

            // Convert PHP arrays to JS safely
            let locations = <?php echo json_encode($clan->locations ?: []); ?>;
            let surnames = <?php echo json_encode($clan->surnames ?: []); ?>;

            // --- Helper functions for tag rendering ---
            function renderTags(arr, containerId) {
                const container = $('#' + containerId);
                container.empty();
                arr.forEach((tag, index) => {
                    container.append(
                        `<span class="tag">${tag}<button type="button" data-index="${index}" class="remove-tag">×</button></span>`
                    );
                });
            }

            function addTagInput(inputId, arr, containerId) {
                const input = $('#' + inputId);
                input.on('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const val = $(this).val().trim();
                        if (val && !arr.includes(val)) {
                            arr.push(val);
                            renderTags(arr, containerId);
                            $(this).val('');
                        }
                    }
                });
            }

            $(document).on('click', '.remove-tag', function () {
                const idx = $(this).data('index');
                const containerId = $(this).closest('.tag-container').attr('id');
                if (containerId.includes('location')) locations.splice(idx, 1);
                if (containerId.includes('surname')) surnames.splice(idx, 1);
                renderTags(containerId.includes('location') ? locations : surnames, containerId);
            });

            // Initialize
            renderTags(locations, 'locationTags');
            renderTags(surnames, 'surnameTags');
            addTagInput('clanLocations', locations, 'locationTags');
            addTagInput('clanSurnames', surnames, 'surnameTags');

            // --- Form submission ---
            $('#editClanForm').on('submit', function (e) {
                e.preventDefault();
                const btn = $(this).find('.btn-primary');
                const msgBox = $('#formMessage');
                msgBox.hide();

                btn.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: family_tree.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'update_clan',
                        nonce: family_tree.nonce,
                        clan_id: $('[name="clan_id"]').val(),
                        clan_name: $('[name="clan_name"]').val(),
                        description: $('[name="description"]').val(),
                        origin_year: $('[name="origin_year"]').val(),
                        locations: locations,
                        surnames: surnames
                    },
                    success: function (response) {
                        if (response.success) {
                            msgBox.removeClass('error').addClass('success')
                                .text('Clan updated successfully!')
                                .fadeIn();
                            setTimeout(() => {
                                window.location.href = '/browse-clans';
                            }, 1200);
                        } else {
                            msgBox.removeClass('success').addClass('error')
                                .text('Error: ' + response.data)
                                .fadeIn();
                        }
                    },
                    error: function (xhr, status, error) {
                        msgBox.removeClass('success').addClass('error')
                            .text('AJAX Error: ' + error)
                            .fadeIn();
                    },
                    complete: function () {
                        btn.prop('disabled', false).text('Update Clan');
                    }
                });
            });
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>

<?php wp_footer(); ?>
