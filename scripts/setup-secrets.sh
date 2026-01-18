# This file contains example secret values
# DO NOT commit actual secrets to version control

# MySQL Root Password (for production)
echo "your-secure-mysql-root-password-here" > docker/secrets/mysql_root_password.txt

# MySQL User Password
echo "your-secure-mysql-user-password-here" > docker/secrets/mysql_password.txt

# WordPress Salts (generate unique ones for production)
echo "your-wordpress-auth-key-here" > docker/secrets/wp_auth_key.txt
echo "your-wordpress-secure-auth-key-here" > docker/secrets/wp_secure_auth_key.txt
echo "your-wordpress-logged-in-key-here" > docker/secrets/wp_logged_in_key.txt
echo "your-wordpress-nonce-key-here" > docker/secrets/wp_nonce_key.txt
echo "your-wordpress-auth-salt-here" > docker/secrets/wp_auth_salt.txt
echo "your-wordpress-secure-auth-salt-here" > docker/secrets/wp_secure_auth_salt.txt
echo "your-wordpress-logged-in-salt-here" > docker/secrets/wp_logged_in_salt.txt
echo "your-wordpress-nonce-salt-here" > docker/secrets/wp_nonce_salt.txt

# JWT Secret (if using JWT authentication)
echo "your-jwt-secret-key-here" > docker/secrets/jwt_secret.txt

# API Keys
echo "your-external-api-key-here" > docker/secrets/api_key.txt

echo "Secret files created. Remember to:"
echo "1. Replace example values with actual secrets"
echo "2. Set proper file permissions (600)"
echo "3. Add secrets/ to .gitignore"
echo "4. Use secret management tools in production"