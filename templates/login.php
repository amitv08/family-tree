<?php
if (is_user_logged_in()) {
    wp_redirect('/family-dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .login-header p {
            color: #666;
            font-size: 16px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: #007cba;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 10px 0;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #007cba;
        }

        .remember-me label {
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }

        .login-button {
            padding: 15px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .login-button:hover {
            background: #005a87;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .register-link p {
            color: #666;
            margin-bottom: 10px;
        }

        .register-button {
            display: inline-block;
            padding: 12px 24px;
            background: #38A169;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-button:hover {
            background: #2F855A;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.3);
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #007cba;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #005a87;
            text-decoration: underline;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .demo-notice {
            background: #e7f3ff;
            color: #0066cc;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #b3d9ff;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .family-logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .family-logo span {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 25px;
            }
            
            .login-header h2 {
                font-size: 24px;
            }
            
            .form-group input[type="text"],
            .form-group input[type="password"] {
                padding: 12px;
                font-size: 14px;
            }
            
            .login-button {
                padding: 12px;
                font-size: 14px;
            }
        }

        /* Custom styling for WordPress login form elements */
        #loginform {
            background: none;
            border: none;
            box-shadow: none;
            padding: 0;
            margin: 0;
        }

        #loginform p {
            margin-bottom: 0;
        }

        .login .message {
            background: #e7f3ff;
            color: #0066cc;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #b3d9ff;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
    <?php wp_head(); ?>
</head>

<body class="login">
    <div class="login-container">
        <div class="family-logo">
            <span>👨‍👩‍👧‍👦</span>
        </div>
        
        <div class="login-header">
            <h2>Family Tree Login</h2>
            <p>Access your family history</p>
        </div>

        <?php
        // Show any error messages
        if (isset($_GET['login']) && $_GET['login'] == 'failed') {
            echo '<div class="error-message">Invalid username or password. Please try again.</div>';
        }
        
        // Show logout message
        if (isset($_GET['loggedout']) && $_GET['loggedout'] == 'true') {
            echo '<div class="demo-notice">You have been successfully logged out.</div>';
        }
        ?>

        <form name="loginform" id="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post" class="login-form">
            <div class="form-group">
                <label for="user_login">Username or Email</label>
                <input type="text" name="log" id="user_login" class="input" value="" size="20" placeholder="Enter your username or email">
            </div>

            <div class="form-group">
                <label for="user_pass">Password</label>
                <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" placeholder="Enter your password">
            </div>

            <div class="remember-me">
                <input name="rememberme" type="checkbox" id="rememberme" value="forever">
                <label for="rememberme">Remember Me</label>
            </div>

            <input type="submit" name="wp-submit" id="wp-submit" class="login-button" value="Log In">
            <input type="hidden" name="redirect_to" value="/family-dashboard">

            <div class="forgot-password">
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgot your password?</a>
            </div>
        </form>

        <?php if (get_option('users_can_register')): ?>
            <div class="register-link">
                <p>Don't have an account?</p>
                <a href="<?php echo esc_url(wp_registration_url()); ?>" class="register-button">Create Account</a>
            </div>
        <?php else: ?>
            <div class="demo-notice">
                <p>Contact the administrator to request access to the family tree.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            
            inputs.forEach(input => {
                // Add focus effect
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });

            // Add loading state to login button
            const loginForm = document.getElementById('loginform');
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    const submitButton = this.querySelector('#wp-submit');
                    submitButton.value = 'Logging in...';
                    submitButton.disabled = true;
                });
            }
        });
    </script>

    <?php wp_footer(); ?>
</body>

</html>