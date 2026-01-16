# Family Tree WordPress Plugin - Docker Setup

A comprehensive Docker-based development and production environment for the Family Tree WordPress plugin, featuring security hardening, performance optimization, and extensive testing capabilities.

## 🏗️ Architecture Overview

### Production Architecture
```
Internet → Nginx (SSL/TLS) → WordPress (FPM) → MySQL/MariaDB
                     ↓
                   Redis (Cache)
```

### Development Architecture
```
Developer → Nginx → WordPress (FPM) → MySQL/MariaDB
             ↓              ↓
         Adminer       Redis (Cache)
         PHPMyAdmin    Node.js (Frontend)
         MailHog
```

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Make (optional, for convenience commands)
- Git

### Development Setup
```bash
# Clone the repository
git clone <repository-url>
cd family-tree

# Start development environment
make dev
# or
docker-compose --profile dev up -d

# Access the application
# Family Tree: http://localhost:8080
# Adminer:     http://localhost:8081
# PHPMyAdmin:  http://localhost:8082
# MailHog:     http://localhost:8025
```

### Production Setup
```bash
# Create secrets directory
mkdir -p secrets ssl

# Generate SSL certificates (for production)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout ssl/key.pem -out ssl/cert.pem

# Set database passwords
echo "your_mysql_root_password" > secrets/mysql_root_password
echo "your_mysql_user_password" > secrets/mysql_password

# Start production environment
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## 🔧 Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `NGINX_PORT` | 8080 | Nginx HTTP port |
| `NGINX_SSL_PORT` | 443 | Nginx HTTPS port |
| `DB_NAME` | familytree | MySQL database name |
| `DB_USER` | familytree | MySQL username |
| `DB_PASSWORD` | changeme123 | MySQL password |
| `MYSQL_ROOT_PASSWORD` | rootpass123 | MySQL root password |
| `WP_DEBUG` | false | WordPress debug mode |
| `WP_TABLE_PREFIX` | wp_ | WordPress table prefix |

### Custom PHP Configuration

- `docker/wordpress/uploads.ini` - File upload settings
- `docker/wordpress/custom.ini` - PHP performance settings
- `docker/mysql/my.cnf` - MySQL optimization settings

## 🧪 Testing

### Run All Tests
```bash
make test
# or
./run-tests.sh
```

### Individual Test Suites

```bash
# Unit tests
make test-unit

# Integration tests
make test-integration

# Performance tests
make test-performance

# Security tests
make test-security

# End-to-end tests
make test-e2e

# Frontend tests
make test-frontend

# PHP syntax check
make test-syntax
```

### Test Structure

```
tests/
├── unit/                 # PHPUnit unit tests
├── integration/          # Database integration tests
├── performance/          # Performance benchmarking
├── security/            # Security vulnerability tests
├── e2e/                 # End-to-end user journey tests
├── bootstrap.php        # Test bootstrap
└── results/             # Test output directory
```

## 🔒 Security Features

### Container Security
- Non-root user execution
- No new privileges
- Read-only file systems where possible
- Minimal attack surface

### Application Security
- Security headers (CSP, HSTS, X-Frame-Options)
- Rate limiting on API endpoints
- SQL injection prevention
- XSS protection
- CSRF protection via nonces

### Network Security
- SSL/TLS encryption
- HTTP to HTTPS redirection
- Restricted admin access
- CORS configuration

## ⚡ Performance Optimizations

### WordPress Optimizations
- Redis object caching
- OPcache enabled
- Gzip compression
- Browser caching headers
- Database query optimization

### Container Optimizations
- Multi-stage Docker builds
- Alpine Linux base images
- Resource limits and reservations
- Health checks for auto-healing

### Nginx Optimizations
- HTTP/2 support
- FastCGI caching
- Rate limiting
- Static file serving
- Keep-alive connections

## 📊 Monitoring & Logging

### Health Checks
- Container health monitoring
- Application endpoint checks
- Database connectivity tests

### Logging
- Centralized log collection
- Structured logging format
- Log rotation
- Error tracking

### Metrics
```bash
# View container status
make status

# View logs
make logs
make logs-nginx
make logs-wordpress

# Health check
make health
```

## 🔄 Development Workflow

### Code Changes
1. Make changes to plugin files
2. Run tests: `make test`
3. Check logs: `make logs-wordpress`
4. Debug with: `docker-compose exec wordpress bash`

### Database Operations
```bash
# Backup database
make backup

# Restore database
make restore FILE=backup.sql

# Clean test data
make clean-test-data
```

### Frontend Development
```bash
# Start Node.js development server
docker-compose --profile dev up node

# Access frontend dev server
# http://localhost:3000
```

## 🚢 Deployment

### Production Deployment
```bash
# Build production images
docker-compose -f docker-compose.yml -f docker-compose.prod.yml build

# Deploy with zero downtime
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Scale services if needed
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --scale wordpress=3
```

### SSL Certificate Management
```bash
# Using Let's Encrypt
certbot certonly --webroot -w /var/www/html -d yourdomain.com

# Copy certificates
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem ssl/cert.pem
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem ssl/key.pem

# Reload nginx
docker-compose restart nginx
```

## 🐛 Troubleshooting

### Common Issues

**Plugin not loading:**
```bash
# Check plugin activation
docker-compose exec wordpress wp plugin list

# Check error logs
make logs-wordpress
```

**Database connection issues:**
```bash
# Test database connection
docker-compose exec mysql mysql -u familytree -p familytree

# Check MySQL logs
make logs-mysql
```

**Performance issues:**
```bash
# Check resource usage
docker stats

# Run performance tests
make test-performance
```

**SSL issues:**
```bash
# Check certificate validity
openssl x509 -in ssl/cert.pem -text -noout

# Test SSL connection
openssl s_client -connect localhost:443
```

## 📚 API Documentation

### REST API Endpoints

- `GET /wp-json/family-tree/v1/members` - List family members
- `POST /wp-json/family-tree/v1/members` - Create member
- `GET /wp-json/family-tree/v1/members/{id}` - Get member details
- `PUT /wp-json/family-tree/v1/members/{id}` - Update member
- `DELETE /wp-json/family-tree/v1/members/{id}` - Delete member

### AJAX Endpoints

- `wp_ajax_create_member` - Create family member
- `wp_ajax_get_members` - Retrieve members list
- `wp_ajax_update_member` - Update member information
- `wp_ajax_delete_member` - Remove member

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes with tests
4. Run full test suite: `make test`
5. Submit pull request

### Code Standards

- PSR-12 PHP coding standards
- WordPress coding standards
- ESLint for JavaScript
- Comprehensive test coverage (>80%)

## 📄 License

This project is licensed under the GPL v2 or later.

## 🆘 Support

- Documentation: [docs/](docs/)
- Issues: GitHub Issues
- Discussions: GitHub Discussions

---

**Built with ❤️ for family history preservation**</content>
<parameter name="filePath">/home/amit/projects/family-tree/DOCKER_README.md