# CI/CD Pipeline for Family Tree WordPress Plugin

## Overview

This document describes the CI/CD pipeline for the Family Tree WordPress plugin, including automated testing, security scanning, Docker image building, and deployment procedures.

## Pipeline Stages

### 1. Quality Assurance (QA)
- **PHP Syntax Check**: Validates all PHP files for syntax errors
- **Static Analysis**: Runs PHPStan for code quality analysis
- **Unit Tests**: Executes PHPUnit test suite with coverage reporting
- **Integration Tests**: Tests plugin functionality in isolated WordPress environment
- **Code Linting**: Validates PHP, JavaScript, CSS, and Docker files

### 2. Security Scanning
- **Vulnerability Scanning**: Uses Trivy to scan for known vulnerabilities
- **Dependency Scanning**: Snyk scans PHP dependencies for security issues
- **Container Scanning**: Scans Docker images for vulnerabilities before deployment

### 3. Docker Image Building
- **Multi-stage Builds**: Optimized Dockerfiles with separate build stages
- **Layer Caching**: Utilizes GitHub Actions cache for faster builds
- **Multi-platform**: Builds for both AMD64 and ARM64 architectures
- **Security Scanning**: Images scanned before pushing to registry

### 4. Deployment
- **Staging**: Automatic deployment to staging on main branch pushes
- **Production**: Manual deployment with approval workflow
- **Rollback**: Automated rollback procedures for failed deployments

## Environment Management

### Secret Management
- Database credentials stored in AWS Secrets Manager or HashiCorp Vault
- API keys and tokens managed through GitHub Secrets
- Environment-specific configuration files

### Feature Flags
- Gradual rollout capabilities using feature flags
- A/B testing support for new features
- Emergency disable switches for problematic features

## Deployment Checklist

### Pre-Deployment
- [ ] All tests passing in CI/CD pipeline
- [ ] Security scans completed without critical vulnerabilities
- [ ] Code review completed and approved
- [ ] Database migrations tested in staging
- [ ] Performance benchmarks met
- [ ] Documentation updated

### Deployment Steps
1. **Staging Deployment** (Automatic)
   - Triggered by push to main branch
   - Deploys to staging environment
   - Runs smoke tests automatically

2. **Production Deployment** (Manual)
   - Requires manual approval
   - Blue-green deployment strategy
   - Health checks before traffic switch
   - Monitoring alerts configured

### Post-Deployment
- [ ] Verify application health
- [ ] Check logs for errors
- [ ] Validate database integrity
- [ ] Test critical user flows
- [ ] Monitor performance metrics
- [ ] Update deployment documentation

## Rollback Procedures

### Automatic Rollback
- Health checks fail → automatic rollback to previous version
- Error rate exceeds threshold → automatic rollback
- Performance degradation detected → automatic rollback

### Manual Rollback
1. Identify the issue and confirm rollback necessity
2. Execute rollback command/script
3. Verify rollback completion
4. Test critical functionality
5. Communicate with stakeholders
6. Investigate root cause

### Rollback Commands
```bash
# For Docker Compose deployments
docker-compose down
docker-compose pull  # Pull previous version
docker-compose up -d

# For Kubernetes deployments
kubectl rollout undo deployment/family-tree
kubectl rollout status deployment/family-tree
```

## Monitoring and Alerting

### Health Checks
- Application health endpoints
- Database connectivity
- External service dependencies
- Performance metrics (response time, throughput)

### Alerts
- Deployment failures
- High error rates
- Performance degradation
- Security incidents

### Logging
- Centralized logging with correlation IDs
- Structured logging for better searchability
- Log retention policies
- Audit logging for compliance

## Security Considerations

### Container Security
- Non-root user execution
- Minimal base images
- Regular security updates
- Vulnerability scanning in CI/CD

### Secret Management
- No secrets in code or Docker images
- Environment-specific secrets
- Secret rotation procedures
- Access logging and auditing

### Network Security
- Service mesh for east-west traffic
- Network policies and segmentation
- TLS everywhere
- API gateway for external access

## Performance Optimization

### Docker Optimizations
- Multi-stage builds to reduce image size
- Layer caching for faster builds
- Distroless images where possible
- Efficient COPY commands

### Application Performance
- Opcode caching (OPcache)
- Database query optimization
- CDN for static assets
- Horizontal scaling capabilities

## Troubleshooting

### Common Issues
1. **Build Failures**: Check logs for specific error messages
2. **Test Failures**: Review test output and fix failing tests
3. **Security Scan Failures**: Address vulnerabilities or suppress false positives
4. **Deployment Failures**: Check environment configuration and connectivity

### Debug Commands
```bash
# Check container logs
docker-compose logs -f

# Check application health
curl -f http://localhost/health

# Run tests locally
./run-tests.sh

# Check security scan results
trivy fs --format json .
```

## Maintenance

### Regular Tasks
- [ ] Update base images monthly
- [ ] Review and update dependencies quarterly
- [ ] Rotate secrets annually
- [ ] Review and update security policies
- [ ] Performance optimization reviews

### Emergency Procedures
1. Assess the situation and impact
2. Communicate with stakeholders
3. Execute appropriate response (rollback, hotfix, etc.)
4. Document the incident and resolution
5. Review and improve processes