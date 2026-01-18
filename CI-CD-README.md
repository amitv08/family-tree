# CI/CD Pipeline Setup

This directory contains the complete CI/CD pipeline configuration for the Family Tree WordPress plugin.

## 🚀 Quick Start

### Prerequisites
- GitHub repository with Actions enabled
- Docker and Docker Compose
- Node.js 18+ and npm
- PHP 8.2+ with Composer
- GitHub Container Registry access (optional)

### Initial Setup

1. **Enable GitHub Actions** in your repository settings
2. **Configure secrets** in GitHub repository:
   ```
   SNYK_TOKEN=your-snyk-token
   DOCKERHUB_USERNAME=your-dockerhub-username
   DOCKERHUB_TOKEN=your-dockerhub-token
   ```
3. **Push to main branch** to trigger the first CI/CD run

## 📁 File Structure

```
.github/
├── workflows/
│   └── ci-cd.yml              # Main CI/CD pipeline
├── copilot-instructions.md    # GitHub Copilot guidance

docker/
├── wordpress/
│   ├── Dockerfile            # Multi-stage WordPress build
│   └── custom.ini           # PHP configuration
├── nginx/
│   └── Dockerfile           # Optimized Nginx build
├── monitoring/
│   └── prometheus.yml       # Monitoring configuration
├── secrets/                 # Secret management (example)
└── docker-compose.prod.yml  # Production overrides

scripts/
├── setup-secrets.sh         # Secret management script
└── rollback.sh             # Emergency rollback script

docs/
├── CI-CD-PIPELINE.md        # Detailed pipeline documentation
└── DEPLOYMENT-CHECKLIST.md  # Deployment checklist template

includes/Config/
└── FeatureFlags.php         # Feature flag management

composer.json                # PHP dependencies
package.json                 # Node.js build tools
.dockerignore               # Docker build optimization
```

## 🔧 Pipeline Stages

### 1. Quality Assurance (QA)
```yaml
- PHP syntax validation
- PHPUnit test execution
- Code coverage reporting
- Static analysis (PHPStan)
- Code linting (Super Linter)
```

### 2. Security Scanning
```yaml
- Trivy vulnerability scanning
- Snyk dependency analysis
- Container image security scan
- SARIF report generation
```

### 3. Docker Build & Push
```yaml
- Multi-stage build optimization
- Layer caching
- Multi-platform builds (AMD64/ARM64)
- Image signing and attestation
```

### 4. Integration Testing
```yaml
- WordPress environment setup
- Plugin activation testing
- API endpoint validation
- Database migration testing
```

### 5. Deployment
```yaml
- Staging: Automatic on main branch
- Production: Manual approval required
- Blue-green deployment strategy
- Health checks and monitoring
```

## 🛠️ Local Development

### Running Tests Locally
```bash
# Run all tests
./run-tests.sh

# Run specific test suite
composer test

# Run with coverage
composer test:coverage
```

### Building Docker Images Locally
```bash
# Build all images
docker-compose build

# Build specific service
docker-compose build wordpress

# Run with production config
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Managing Secrets
```bash
# Setup example secrets (DO NOT use in production)
./scripts/setup-secrets.sh

# In production, use:
# - HashiCorp Vault
# - AWS Secrets Manager
# - Azure Key Vault
```

## 🚨 Emergency Rollback

### Quick Rollback
```bash
# Rollback to previous version
./scripts/rollback.sh

# Rollback to specific tag
./scripts/rollback.sh -t v1.2.3

# Git-based rollback
./scripts/rollback.sh -T git
```

### Rollback Verification
- Application health checks
- Database integrity
- User functionality testing
- Performance monitoring

## 📊 Monitoring & Alerting

### Included Services
- **Prometheus**: Metrics collection
- **Grafana**: Visualization dashboard
- **Loki**: Log aggregation
- **cAdvisor**: Container metrics
- **Node Exporter**: System metrics

### Starting Monitoring Stack
```bash
# Start with monitoring enabled
docker-compose --profile monitoring up -d

# Access dashboards
# Grafana: http://localhost:3001 (admin/admin)
# Prometheus: http://localhost:9090
```

## 🔒 Security Features

### Container Security
- Non-root user execution
- Minimal attack surface
- Regular security updates
- Vulnerability scanning

### Secret Management
- No secrets in Docker images
- Environment-specific secrets
- Secret rotation procedures
- Audit logging

### Code Security
- Dependency vulnerability scanning
- Static application security testing (SAST)
- Container image scanning
- Automated security updates

## 🎯 Feature Flags

Enable gradual rollouts and A/B testing:

```php
use FamilyTree\Config\FeatureFlags;

// Check if feature is enabled
if (FeatureFlags::isEnabled('ai_relationship_detection')) {
    // Show AI features
}

// User-specific override
FeatureFlags::setUserOverride($userId, 'social_sharing', true);
```

## 📋 Deployment Checklist

Use the deployment checklist template in `docs/DEPLOYMENT-CHECKLIST.md` for each deployment.

## 🔄 Continuous Improvement

### Regular Maintenance
- [ ] Update base images monthly
- [ ] Review dependencies quarterly
- [ ] Rotate secrets annually
- [ ] Update security policies
- [ ] Performance optimization

### Metrics to Monitor
- Build success rate
- Deployment frequency
- Mean time to recovery (MTTR)
- Change failure rate
- Test coverage percentage

## 🆘 Troubleshooting

### Common Issues

**Build Failures**
```bash
# Check build logs
docker-compose build --progress=plain wordpress

# Debug build context
docker build --no-cache --progress=plain -f docker/wordpress/Dockerfile .
```

**Test Failures**
```bash
# Run tests with verbose output
phpunit --verbose --debug

# Check test environment
docker-compose logs mysql
```

**Deployment Issues**
```bash
# Check container health
docker-compose ps

# View application logs
docker-compose logs wordpress

# Test application endpoints
curl -f http://localhost/health
```

## 📚 Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
- [Prometheus Monitoring](https://prometheus.io/docs/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

## 🤝 Contributing

1. Create a feature branch
2. Make your changes
3. Ensure tests pass
4. Update documentation
5. Create a pull request

The CI/CD pipeline will automatically run all checks on your PR!