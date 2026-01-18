<?php
/**
 * Custom registration page with plugin styling
 */

if (is_user_logged_in()) {
    wp_redirect('/family-dashboard');
    exit;
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp-submit'])) {
    $errors = [];
    $user_login = sanitize_user($_POST['user_login'] ?? '');
    $user_email = sanitize_email($_POST['user_email'] ?? '');
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');

    // Validate input
    if (empty($user_login)) {
        $errors[] = 'Username is required.';
    }
    if (empty($user_email)) {
        $errors[] = 'Email is required.';
    } elseif (!is_email($user_email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (username_exists($user_login)) {
        $errors[] = 'Username already exists.';
    }
    if (email_exists($user_email)) {
        $errors[] = 'Email address already exists.';
    }

    if (empty($errors)) {
        // Generate a random password
        $password = wp_generate_password(12, false);

        // Create the user
        $user_id = wp_create_user($user_login, $password, $user_email);

        if (!is_wp_error($user_id)) {
            // Set user role to family_viewer
            wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => 'family_viewer'
            ]);

            // Send welcome email with credentials
            $subject = 'Welcome to Family Tree - Your Account Details';
            $message = sprintf(
                "Welcome to Family Tree!\n\nYour account has been created successfully.\n\nUsername: %s\nPassword: %s\n\nPlease log in at: %s\n\nYou can change your password after logging in.\n\nBest regards,\nFamily Tree Team",
                $user_login,
                $password,
                home_url('/family-login')
            );

            wp_mail($user_email, $subject, $message);

            // Redirect to login with success message
            wp_redirect('/family-login?registered=success&username=' . urlencode($user_login));
            exit;
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$page_title = 'Create Account';
$page_content = '
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1>Create Your Family Tree Account</h1>
            <p>Join your family\'s digital tree</p>
        </div>

        <form name="registerform" id="registerform" action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" class="login-form">
            ' . (isset($errors) && !empty($errors) ? '<div class="error-message" style="background: var(--color-error-light); color: var(--color-error); padding: var(--spacing-lg); border-radius: var(--radius-base); margin-bottom: var(--spacing-lg);"><ul style="margin: 0; padding-left: var(--spacing-xl);"><li>' . implode('</li><li>', array_map('esc_html', $errors)) . '</li></ul></div>' : '') . '

            <div class="form-group">
                <label for="user_login">Username <span class="required">*</span></label>
                <input type="text" name="user_login" id="user_login" value="' . esc_attr($_POST['user_login'] ?? '') . '" required>
            </div>

            <div class="form-group">
                <label for="user_email">Email <span class="required">*</span></label>
                <input type="email" name="user_email" id="user_email" value="' . esc_attr($_POST['user_email'] ?? '') . '" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="' . esc_attr($_POST['first_name'] ?? '') . '">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="' . esc_attr($_POST['last_name'] ?? '') . '">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="wp-submit" id="wp-submit" class="btn btn-primary btn-full">
                    Create Account
                </button>
            </div>
        </form>

        <div class="login-footer">
            <p>Already have an account? <a href="/family-login">Log in here</a></p>
        </div>
    </div>
</div>
';

include FAMILY_TREE_PATH . 'templates/components/page-layout.php';
?>