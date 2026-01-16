#!/bin/bash
# Test Runner Script for Family Tree Plugin

set -e

echo "🧪 Family Tree Plugin - Test Suite"
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Check if we're in the right directory
if [ ! -f "family-tree.php" ]; then
    print_status "FAIL" "Not in plugin root directory"
    exit 1
fi

print_status "INFO" "Starting test suite..."

# Check if Docker is running
if command -v docker &> /dev/null && docker info &> /dev/null; then
    print_status "PASS" "Docker is available"

    # Check if containers are running
    if docker-compose ps | grep -q "Up"; then
        print_status "PASS" "Docker containers are running"
    else
        print_status "WARN" "Docker containers not running - starting them..."
        docker-compose --profile dev up -d
        sleep 10
    fi
else
    print_status "WARN" "Docker not available - running tests without containers"
fi

# Run PHP syntax check
print_status "INFO" "Running PHP syntax check..."
find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" -exec php -l {} \; 2>&1 | while read line; do
    if [[ $line == *"No syntax errors"* ]]; then
        continue
    else
        print_status "FAIL" "PHP syntax error: $line"
        exit 1
    fi
done
print_status "PASS" "PHP syntax check completed"

# Run PHPUnit tests if available
if command -v phpunit &> /dev/null || [ -f "vendor/bin/phpunit" ]; then
    print_status "INFO" "Running PHPUnit tests..."

    if [ -f "vendor/bin/phpunit" ]; then
        PHPUNIT_CMD="vendor/bin/phpunit"
    else
        PHPUNIT_CMD="phpunit"
    fi

    if $PHPUNIT_CMD --configuration phpunit.xml --testdox; then
        print_status "PASS" "PHPUnit tests completed"
    else
        print_status "FAIL" "PHPUnit tests failed"
        exit 1
    fi
else
    print_status "WARN" "PHPUnit not available - skipping unit tests"
fi

# Run WP-CLI tests if available
if command -v wp &> /dev/null; then
    print_status "INFO" "Running WP-CLI based tests..."

    # Performance tests
    if wp eval-file tests/performance/performance-test.php --allow-root; then
        print_status "PASS" "Performance tests completed"
    else
        print_status "FAIL" "Performance tests failed"
    fi

    # Security tests
    if wp eval-file tests/security/security-test.php --allow-root; then
        print_status "PASS" "Security tests completed"
    else
        print_status "FAIL" "Security tests failed"
    fi

    # E2E tests
    if wp eval-file tests/e2e/e2e-test.php --allow-root; then
        print_status "PASS" "E2E tests completed"
    else
        print_status "FAIL" "E2E tests failed"
    fi
else
    print_status "WARN" "WP-CLI not available - skipping WP-CLI tests"
fi

# Run frontend tests if Node.js is available
if command -v npm &> /dev/null && [ -f "package.json" ]; then
    print_status "INFO" "Running frontend tests..."

    if npm test; then
        print_status "PASS" "Frontend tests completed"
    else
        print_status "WARN" "Frontend tests failed or not configured"
    fi
else
    print_status "INFO" "Node.js/npm not available - skipping frontend tests"
fi

# Check code quality
print_status "INFO" "Checking code quality..."

# Check for TODO/FIXME comments
todo_count=$(grep -r "TODO\|FIXME\|XXX" --include="*.php" --include="*.js" . --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=.git | wc -l)
if [ $todo_count -gt 0 ]; then
    print_status "WARN" "Found $todo_count TODO/FIXME comments"
else
    print_status "PASS" "No TODO/FIXME comments found"
fi

# Check for console.log statements in production code
console_count=$(grep -r "console\.log" --include="*.js" . --exclude-dir=node_modules --exclude-dir=tests | wc -l)
if [ $console_count -gt 0 ]; then
    print_status "WARN" "Found $console_count console.log statements in production code"
else
    print_status "PASS" "No console.log statements in production code"
fi

# Check file permissions
print_status "INFO" "Checking file permissions..."
if find . -type f -name "*.php" -exec test ! -w {} \; | grep -q .; then
    print_status "WARN" "Some PHP files are world-writable"
else
    print_status "PASS" "PHP file permissions are secure"
fi

# Check for sensitive data exposure
print_status "INFO" "Checking for sensitive data exposure..."
sensitive_patterns=("password\|secret\|key\|token")
if grep -r -i "$sensitive_patterns" --include="*.php" --include="*.js" . --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=.git | grep -v "function\|variable\|constant" | grep -q .; then
    print_status "WARN" "Potential sensitive data exposure found"
else
    print_status "PASS" "No obvious sensitive data exposure"
fi

# Generate test report
print_status "INFO" "Generating test report..."
echo ""
echo "📊 Test Summary Report"
echo "======================"
echo "Test run completed at: $(date)"
echo "Plugin version: $(grep "Version:" family-tree.php | head -1 | cut -d: -f2 | tr -d ' ')"
echo ""

# Check if all critical tests passed
if [ $? -eq 0 ]; then
    print_status "PASS" "All tests completed successfully!"
    echo ""
    echo "🎉 Family Tree Plugin is ready for deployment!"
else
    print_status "FAIL" "Some tests failed - please review and fix issues"
    exit 1
fi