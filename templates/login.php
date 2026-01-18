<?php
/**
 * Family Tree Plugin - Login Page
 * Updated with professional design system
 */

if (is_user_logged_in()) {
    wp_redirect('/family-dashboard');
    exit;
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Family Tree - Manage your genealogy online">
    <title>Login - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    
    <style>
        /* Login Page Specific Styles */
        body.login-page {
            background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: var(--color-bg-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-2xl);
            animation: slideUp 0.5s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }

        .login-logo {
            font-size: 3rem;
            margin-bottom: var(--spacing-md);
            display: block;
        }

        .login-header h1 {
            font-size: var(--font-size-xl);
            color: var(--color-text-primary);
            margin-bottom: var(--spacing-sm);
        }

        .login-header p {
            color: var(--color-text-secondary);
            margin: 0;
            font-size: var(--font-size-sm);
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }

        .form-group-login {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .form-group-login label {
            font-weight: var(--font-weight-medium);
            color: var(--color-text-primary);
            font-size: var(--font-size-sm);
        }

        .form-group-login input[type="text"],
        .form-group-login input[type="password"] {
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--color-border);
            border-radius: var(--radius-base);
            font-size: var(--font-size-base);
            font-family: var(--font-family-base);
            transition: all var(--transition-fast);
        }

        .form-group-login input[type="text"]:focus,
        .form-group-login input[type="password"]:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-size: var(--font-size-sm);
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--color-primary);
            cursor: pointer;
        }

        .remember-me label {
            cursor: pointer;
            margin: 0;
        }

        .login-button {
            padding: var(--spacing-lg) var(--spacing-xl);
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: var(--radius-base);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            cursor: pointer;
            transition: all var(--transition-fast);
            margin-top: var(--spacing-md);
        }

        .login-button:hover {
            background: var(--color-primary-dark);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:disabled {
            background: var(--color-secondary);
            cursor: not-allowed;
            opacity: 0.6;
            transform: none;
        }

        .forgot-password {
            text-align: center;
            margin-top: var(--spacing-lg);
            font-size: var(--font-size-sm);
        }

        .forgot-password a {
            color: var(--color-primary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .forgot-password a:hover {
            color: var(--color-primary-dark);
            text-decoration: underline;
        }

        .login-footer {
            text-align: center;
            margin-top: var(--spacing-2xl);
            padding-top: var(--spacing-2xl);
            border-top: 1px solid var(--color-border);
        }

        .login-footer p {
            color: var(--color-text-secondary);
            margin: 0 0 var(--spacing-lg) 0;
            font-size: var(--font-size-sm);
        }

        .register-button {
            display: inline-block;
            padding: var(--spacing-md) var(--spacing-xl);
            background: var(--color-success);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-base);
            font-weight: var(--font-weight-semibold);
            transition: all var(--transition-fast);
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }

        .register-button:hover {
            background: #1e7e34;
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .login-message {
            padding: var(--spacing-lg);
            border-radius: var(--radius-base);
            margin-bottom: var(--spacing-lg);
            display: none;
        }

        .login-message.error {
            background: var(--color-danger-light);
            color: var(--color-danger);
            border: 1px solid var(--color-danger);
            display: block;
        }

        .login-message.success {
            background: var(--color-success-light);
            color: var(--color-success);
            border: 1px solid var(--color-success);
            display: block;
        }

        .login-message.info {
            background: var(--color-info-light);
            color: var(--color-info);
            border: 1px solid var(--color-info);
            display: block;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: var(--spacing-xl);
            }

            .login-header h1 {
                font-size: var(--font-size-lg);
            }

            .login-logo {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <!-- Logo & Heading -->
            <div class="login-header">
                <span class="login-logo">🌳</span>
                <h1>Family Tree</h1>
                <p>Manage your genealogy online</p>
            </div>

            <!-- Error/Success Messages -->
            <?php if (isset($_GET['login']) && $_GET['login'] == 'failed'): ?>
                <div class="login-message error">
                    ❌ Invalid username or password. Please try again.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['loggedout']) && $_GET['loggedout'] == 'true'): ?>
                <div class="login-message success">
                    ✅ You have been logged out successfully. Goodbye!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['checkemail']) && $_GET['checkemail'] == 'confirm'): ?>
                <div class="login-message info">
                    ℹ️ Check your email for the confirmation link.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
                <div class="login-message success">
                    ✅ Account created successfully! <?php if (isset($_GET['username'])): ?>Your username is: <strong><?php echo esc_html($_GET['username']); ?></strong><?php endif; ?><br>
                    Please check your email for your password and login details.
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form name="loginform" id="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post" class="login-form">
                <!-- Username/Email -->
                <div class="form-group-login">
                    <label for="user_login">
                        👤 Username or Email Address
                    </label>
                    <input 
                        type="text" 
                        name="log" 
                        id="user_login" 
                        class="input" 
                        value="" 
                        size="20" 
                        placeholder="Enter your username or email"
                        autocomplete="username"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="form-group-login">
                    <label for="user_pass">
                        🔐 Password
                    </label>
                    <input 
                        type="password" 
                        name="pwd" 
                        id="user_pass" 
                        class="input" 
                        value="" 
                        size="20" 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <!-- Remember Me -->
                <div class="remember-me">
                    <input 
                        name="rememberme" 
                        type="checkbox" 
                        id="rememberme" 
                        value="forever"
                    >
                    <label for="rememberme">Remember me for 14 days</label>
                </div>

                <!-- Submit Button -->
                <input 
                    type="submit" 
                    name="wp-submit" 
                    id="wp-submit" 
                    class="login-button" 
                    value="Sign In"
                >

                <!-- Hidden Fields -->
                <input type="hidden" name="redirect_to" value="/family-dashboard">
            </form>

            <!-- Forgot Password Link -->
            <div class="forgot-password">
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                    Forgot your password?
                </a>
            </div>

            <!-- Registration/Admin Message -->
            <div class="login-footer">
                <?php if (get_option('users_can_register')): ?>
                    <p>Don't have an account yet?</p>
                    <a href="/family-register" class="register-button">
                        Create Account
                    </a>
                <?php else: ?>
                    <div style="background: var(--color-info-light); padding: var(--spacing-lg); border-radius: var(--radius-base); color: var(--color-info); font-size: var(--font-size-sm);">
                        <strong>📝 Need an account?</strong><br>
                        Contact the administrator to request access to the family tree.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Text -->
        <div style="text-align: center; margin-top: var(--spacing-2xl); color: rgba(255,255,255,0.8); font-size: var(--font-size-sm);">
            <p style="margin: 0;">
                © <?php echo date('Y'); ?> Family Tree Plugin v<?php echo \FamilyTree\Config::VERSION; ?>
            </p>
        </div>
    </div>

    <?php wp_footer(); ?>

    <script>
        // Disable submit button on form submit
        document.getElementById('loginform')?.addEventListener('submit', function() {
            document.getElementById('wp-submit').disabled = true;
            document.getElementById('wp-submit').value = 'Signing in...';
        });

        // Auto-focus on first empty field
        document.addEventListener('DOMContentLoaded', function() {
            const userInput = document.getElementById('user_login');
            const passInput = document.getElementById('user_pass');
            
            if (userInput && !userInput.value) {
                userInput.focus();
            } else if (passInput) {
                passInput.focus();
            }
        });
    </script>
</body>
</html>