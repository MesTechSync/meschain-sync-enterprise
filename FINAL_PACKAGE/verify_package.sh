#!/bin/bash

# MesChain Trendyol Integration - Package Verification Script
# This script verifies the integrity and completeness of the production package

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
CHECKS_PASSED=0
CHECKS_FAILED=0
TOTAL_CHECKS=0

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
    ((CHECKS_PASSED++))
    ((TOTAL_CHECKS++))
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
    ((CHECKS_FAILED++))
    ((TOTAL_CHECKS++))
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Header
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║              MesChain Trendyol Integration v1.0.0           ║"
echo "║                   Package Verification                      ║"
echo "║                                                              ║"
echo "║  Verifying production package integrity and completeness     ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo

print_status "Starting package verification..."

# Check core files
print_status "Checking core files..."

CORE_FILES=(
    "README.md"
    "composer.json"
    "phpunit.xml"
    "package.json"
    "setup.sh"
    "build.sh"
    ".gitignore"
    "DEPLOYMENT_GUIDE.md"
    "PROJECT_SUMMARY.md"
)

for file in "${CORE_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "Core file exists: $file"
    else
        print_error "Missing core file: $file"
    fi
done

# Check directory structure
print_status "Checking directory structure..."

REQUIRED_DIRS=(
    "build"
    "deployment"
    "docs"
    "monitoring"
    "tests"
    "tests/unit"
    "tests/integration"
    "tests/e2e"
    "tests/performance"
    "tests/security"
)

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        print_success "Directory exists: $dir"
    else
        print_error "Missing directory: $dir"
    fi
done

# Check test files
print_status "Checking test files..."

TEST_FILES=(
    "tests/TestCase.php"
    "tests/run_tests.php"
    "tests/unit/TrendyolClientTest.php"
    "tests/integration/TrendyolIntegrationTest.php"
    "tests/e2e/TrendyolE2ETest.php"
    "tests/performance/PerformanceTest.php"
    "tests/security/SecurityAuditTest.php"
)

for file in "${TEST_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "Test file exists: $file"
    else
        print_error "Missing test file: $file"
    fi
done

# Check deployment files
print_status "Checking deployment files..."

DEPLOYMENT_FILES=(
    "deployment/deploy.sh"
    "deployment/health_check.sh"
    "deployment/rollback.sh"
)

for file in "${DEPLOYMENT_FILES[@]}"; do
    if [ -f "$file" ]; then
        if [ -x "$file" ]; then
            print_success "Deployment script exists and is executable: $file"
        else
            print_error "Deployment script exists but is not executable: $file"
        fi
    else
        print_error "Missing deployment file: $file"
    fi
done

# Check monitoring files
print_status "Checking monitoring files..."

MONITORING_FILES=(
    "monitoring/setup_monitoring.sh"
    "monitoring/dashboards/dashboard.js"
)

for file in "${MONITORING_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "Monitoring file exists: $file"
    else
        print_error "Missing monitoring file: $file"
    fi
done

# Check build files
print_status "Checking build files..."

BUILD_FILES=(
    "build/create_ocmod_package.php"
)

for file in "${BUILD_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "Build file exists: $file"
    else
        print_error "Missing build file: $file"
    fi
done

# Check documentation
print_status "Checking documentation..."

DOC_FILES=(
    "docs/USER_GUIDE.md"
)

for file in "${DOC_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "Documentation file exists: $file"
    else
        print_error "Missing documentation file: $file"
    fi
done

# Check file permissions
print_status "Checking file permissions..."

EXECUTABLE_FILES=(
    "setup.sh"
    "build.sh"
    "deployment/deploy.sh"
    "deployment/health_check.sh"
    "deployment/rollback.sh"
    "monitoring/setup_monitoring.sh"
)

for file in "${EXECUTABLE_FILES[@]}"; do
    if [ -f "$file" ]; then
        if [ -x "$file" ]; then
            print_success "File is executable: $file"
        else
            print_error "File should be executable: $file"
        fi
    fi
done

# Check JSON syntax
print_status "Checking JSON syntax..."

JSON_FILES=(
    "composer.json"
    "package.json"
)

for file in "${JSON_FILES[@]}"; do
    if [ -f "$file" ]; then
        if python3 -m json.tool "$file" > /dev/null 2>&1; then
            print_success "Valid JSON syntax: $file"
        elif php -r "json_decode(file_get_contents('$file')); if(json_last_error() !== JSON_ERROR_NONE) exit(1);" 2>/dev/null; then
            print_success "Valid JSON syntax: $file"
        else
            print_error "Invalid JSON syntax: $file"
        fi
    fi
done

# Check XML syntax
print_status "Checking XML syntax..."

if [ -f "phpunit.xml" ]; then
    if xmllint --noout phpunit.xml 2>/dev/null; then
        print_success "Valid XML syntax: phpunit.xml"
    else
        print_warning "Cannot validate XML syntax (xmllint not available): phpunit.xml"
    fi
fi

# Check PHP syntax
print_status "Checking PHP syntax..."

find . -name "*.php" -type f | while read -r file; do
    if php -l "$file" > /dev/null 2>&1; then
        print_success "Valid PHP syntax: $file"
    else
        print_error "Invalid PHP syntax: $file"
    fi
done

# Check for required PHP extensions
print_status "Checking PHP extensions..."

REQUIRED_EXTENSIONS=("curl" "json" "mbstring" "pdo" "openssl")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "^$ext$"; then
        print_success "PHP extension available: $ext"
    else
        print_error "Missing PHP extension: $ext"
    fi
done

# Summary
echo
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                    VERIFICATION SUMMARY                      ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo
echo "Total Checks: $TOTAL_CHECKS"
echo -e "Passed: ${GREEN}$CHECKS_PASSED${NC}"
echo -e "Failed: ${RED}$CHECKS_FAILED${NC}"

if [ $CHECKS_FAILED -eq 0 ]; then
    echo
    print_success "Package verification completed successfully!"
    echo
    echo "✅ All checks passed - Package is ready for production deployment"
    echo
    echo "Next steps:"
    echo "1. Run './setup.sh --production' to prepare environment"
    echo "2. Configure .env file with your settings"
    echo "3. Run './deployment/deploy.sh' to deploy to production"
    echo
    exit 0
else
    echo
    print_error "Package verification failed!"
    echo
    echo "❌ $CHECKS_FAILED checks failed - Please fix issues before deployment"
    echo
    exit 1
fi
