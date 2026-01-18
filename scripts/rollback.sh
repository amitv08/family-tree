#!/bin/bash
# Rollback Script for Family Tree Plugin
# Use this script to quickly rollback to a previous version

set -e

echo "🔄 Family Tree Plugin - Rollback Script"
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ROLLBACK_TAG=${ROLLBACK_TAG:-"previous"}
BACKUP_SUFFIX=$(date +%Y%m%d_%H%M%S)
ROLLBACK_TYPE=${ROLLBACK_TYPE:-"docker"} # docker or git

# Function to print colored output
print_status() {
    local status=$1
    local message=$2
    case $status in
        "PASS")
            echo -e "${GREEN}✓ PASS${NC}: $message"
            ;;
        "FAIL")
            echo -e "${RED}✗ FAIL${NC}: $message"
            ;;
        "WARN")
            echo -e "${YELLOW}⚠ WARN${NC}: $message"
            ;;
        "INFO")
            echo -e "${BLUE}ℹ INFO${NC}: $message"
            ;;
    esac
}

# Function to backup current state
backup_current_state() {
    print_status "INFO" "Creating backup of current state..."

    # Database backup
    if command -v docker &> /dev/null && docker-compose ps | grep -q mysql; then
        print_status "INFO" "Backing up database..."
        docker-compose exec -T mysql mysqldump -u familytree -pchangeme123 familytree > "backup_db_$BACKUP_SUFFIX.sql"
        print_status "PASS" "Database backup created: backup_db_$BACKUP_SUFFIX.sql"
    fi

    # File backup
    if [ -d "wp-content/plugins/family-tree" ]; then
        tar -czf "backup_plugin_$BACKUP_SUFFIX.tar.gz" wp-content/plugins/family-tree/
        print_status "PASS" "Plugin files backup created: backup_plugin_$BACKUP_SUFFIX.tar.gz"
    fi
}

# Function to rollback Docker deployment
rollback_docker() {
    print_status "INFO" "Rolling back Docker deployment..."

    # Stop current containers
    docker-compose down

    # Pull previous version
    if [ "$ROLLBACK_TAG" != "previous" ]; then
        docker-compose pull
    fi

    # Start containers with previous version
    docker-compose up -d

    # Wait for services to be healthy
    print_status "INFO" "Waiting for services to start..."
    sleep 30

    # Health check
    if curl -f http://localhost/health 2>/dev/null; then
        print_status "PASS" "Services are healthy after rollback"
    else
        print_status "FAIL" "Services failed health check after rollback"
        exit 1
    fi
}

# Function to rollback Git deployment
rollback_git() {
    print_status "INFO" "Rolling back Git deployment..."

    # Check if we have uncommitted changes
    if ! git diff --quiet || ! git diff --staged --quiet; then
        print_status "WARN" "You have uncommitted changes. Stashing them..."
        git stash push -m "Pre-rollback stash"
    fi

    # Reset to previous commit/tag
    if [ "$ROLLBACK_TAG" = "previous" ]; then
        git reset --hard HEAD~1
    else
        git reset --hard "$ROLLBACK_TAG"
    fi

    # If using Docker, rebuild and restart
    if [ -f "docker-compose.yml" ]; then
        docker-compose down
        docker-compose build --no-cache
        docker-compose up -d
    fi

    print_status "PASS" "Git rollback completed"
}

# Function to verify rollback
verify_rollback() {
    print_status "INFO" "Verifying rollback..."

    # Check if application is responding
    if curl -f http://localhost/wp-admin 2>/dev/null; then
        print_status "PASS" "Application is responding"
    else
        print_status "FAIL" "Application is not responding"
        return 1
    fi

    # Check database connectivity
    if docker-compose exec -T mysql mysql -u familytree -pchangeme123 -e "SELECT 1;" familytree 2>/dev/null; then
        print_status "PASS" "Database is accessible"
    else
        print_status "FAIL" "Database is not accessible"
        return 1
    fi

    return 0
}

# Function to cleanup
cleanup() {
    print_status "INFO" "Cleaning up old backups (keeping last 5)..."

    # Keep only last 5 backups
    ls -t backup_*.sql backup_*.tar.gz 2>/dev/null | tail -n +6 | xargs -r rm -f

    print_status "PASS" "Cleanup completed"
}

# Main rollback logic
main() {
    print_status "INFO" "Starting rollback to: $ROLLBACK_TAG"

    # Confirm rollback
    echo
    echo -e "${YELLOW}⚠️  WARNING: This will rollback the application to $ROLLBACK_TAG${NC}"
    echo -e "${YELLOW}   Make sure you have backups and understand the consequences.${NC}"
    echo
    read -p "Are you sure you want to continue? (yes/no): " -r
    if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
        print_status "INFO" "Rollback cancelled by user"
        exit 0
    fi

    # Create backup
    backup_current_state

    # Perform rollback based on type
    case $ROLLBACK_TYPE in
        "docker")
            rollback_docker
            ;;
        "git")
            rollback_git
            ;;
        *)
            print_status "FAIL" "Unknown rollback type: $ROLLBACK_TYPE"
            exit 1
            ;;
    esac

    # Verify rollback
    if verify_rollback; then
        print_status "PASS" "Rollback completed successfully!"
        print_status "INFO" "Backups created: backup_db_$BACKUP_SUFFIX.sql, backup_plugin_$BACKUP_SUFFIX.tar.gz"
    else
        print_status "FAIL" "Rollback verification failed. Check logs and consider manual intervention."
        exit 1
    fi

    # Cleanup
    cleanup

    print_status "INFO" "Rollback process completed"
}

# Show usage
usage() {
    echo "Usage: $0 [OPTIONS]"
    echo
    echo "Options:"
    echo "  -t, --tag TAG     Rollback to specific tag/commit (default: previous)"
    echo "  -T, --type TYPE   Rollback type: docker or git (default: docker)"
    echo "  -h, --help        Show this help"
    echo
    echo "Examples:"
    echo "  $0                          # Rollback to previous version"
    echo "  $0 -t v1.2.3              # Rollback to specific tag"
    echo "  $0 -T git                  # Use git-based rollback"
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -t|--tag)
            ROLLBACK_TAG="$2"
            shift 2
            ;;
        -T|--type)
            ROLLBACK_TYPE="$2"
            shift 2
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            print_status "FAIL" "Unknown option: $1"
            usage
            exit 1
            ;;
    esac
done

# Run main function
main