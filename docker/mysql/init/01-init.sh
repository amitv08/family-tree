#!/bin/bash
# MySQL initialization script for Family Tree database

echo "Setting up Family Tree database..."

# Wait for MySQL to be ready
while ! mysqladmin ping -h"localhost" -P"3306" --silent; do
    echo "Waiting for MySQL to be ready..."
    sleep 2
done

echo "MySQL is ready. Creating database and user..."

# Create database and user if they don't exist
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" << EOF
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user if not exists
CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';

-- Grant privileges
GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Show created user
SELECT User, Host FROM mysql.user WHERE User = '${MYSQL_USER}';
EOF

echo "Database setup complete!"

# Optional: Run any additional setup scripts
if [ -d "/docker-entrypoint-initdb.d" ]; then
    for f in /docker-entrypoint-initdb.d/*.sql; do
        if [ -f "$f" ]; then
            echo "Running $f"
            mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}" < "$f"
        fi
    done
fi</content>
<parameter name="filePath">/home/amit/projects/family-tree/docker/mysql/init/01-init.sh