#!/bin/bash

# MesChain-Sync Enterprise Test Runner
# Runs all tests and generates reports

echo "==================================================="
echo "MesChain-Sync Enterprise - Test Suite Runner"
echo "==================================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if PHPUnit is installed
if ! command -v phpunit &> /dev/null; then
    echo -e "${RED}Error: PHPUnit is not installed${NC}"
    echo "Please install PHPUnit: composer require --dev phpunit/phpunit"
    exit 1
fi

# Create necessary directories
mkdir -p tests/coverage-report
mkdir -p tests/logs
mkdir -p .phpunit.cache

# Run PHP syntax check
echo -e "${YELLOW}1. Running PHP syntax check...${NC}"
find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" -exec php -l {} \; > tests/logs/syntax-check.log 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ PHP syntax check passed${NC}"
else
    echo -e "${RED}✗ PHP syntax errors found. Check tests/logs/syntax-check.log${NC}"
    exit 1
fi

# Run PHP Code Sniffer (if installed)
if command -v phpcs &> /dev/null; then
    echo -e "${YELLOW}2. Running PHP Code Sniffer...${NC}"
    phpcs --standard=PSR12 admin/ system/library/meschain/ > tests/logs/phpcs.log 2>&1
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Code standards check passed${NC}"
    else
        echo -e "${YELLOW}⚠ Code style warnings found. Check tests/logs/phpcs.log${NC}"
    fi
else
    echo -e "${YELLOW}2. Skipping PHP Code Sniffer (not installed)${NC}"
fi

# Run security check
echo -e "${YELLOW}3. Running security check...${NC}"
# Check for common security issues
grep -r "SSL_VERIFYPEER.*false" --include="*.php" . > tests/logs/security-check.log 2>&1
grep -r "eval(" --include="*.php" . >> tests/logs/security-check.log 2>&1
grep -r "exec(" --include="*.php" . >> tests/logs/security-check.log 2>&1
grep -r "system(" --include="*.php" . >> tests/logs/security-check.log 2>&1

if [ ! -s tests/logs/security-check.log ]; then
    echo -e "${GREEN}✓ Security check passed${NC}"
else
    echo -e "${YELLOW}⚠ Potential security issues found. Check tests/logs/security-check.log${NC}"
fi

# Run Unit Tests
echo -e "${YELLOW}4. Running unit tests...${NC}"
if [ -f "phpunit.xml" ]; then
    phpunit --configuration phpunit.xml > tests/logs/phpunit.log 2>&1
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Unit tests passed${NC}"
    else
        echo -e "${RED}✗ Unit tests failed. Check tests/logs/phpunit.log${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}⚠ PHPUnit configuration not found, skipping unit tests${NC}"
fi

# Run Integration Tests
echo -e "${YELLOW}5. Running integration tests...${NC}"
if [ -f "tests/Integration/MarketplaceIntegrationTest.php" ]; then
    php tests/Integration/MarketplaceIntegrationTest.php > tests/logs/integration-test.log 2>&1
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Integration tests passed${NC}"
    else
        echo -e "${RED}✗ Integration tests failed. Check tests/logs/integration-test.log${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}⚠ Integration tests not found${NC}"
fi

# Run Performance Tests
echo -e "${YELLOW}6. Running performance tests...${NC}"
# Simple performance check - measure response time of critical functions
php -r "
require_once 'tests/PHPUnit/bootstrap.php';

\$start = microtime(true);
// Simulate critical operations
for (\$i = 0; \$i < 1000; \$i++) {
    \$hash = password_hash('test_password_' . \$i, PASSWORD_DEFAULT);
}
\$end = microtime(true);
\$time = (\$end - \$start) * 1000;

if (\$time < 1000) {
    echo \"✓ Performance test passed ({\$time}ms for 1000 operations)\n\";
    exit(0);
} else {
    echo \"✗ Performance test failed ({\$time}ms for 1000 operations - too slow)\n\";
    exit(1);
}
" > tests/logs/performance-test.log 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Performance tests passed${NC}"
else
    echo -e "${RED}✗ Performance tests failed. Check tests/logs/performance-test.log${NC}"
fi

# Generate Test Summary
echo ""
echo "==================================================="
echo "TEST SUMMARY"
echo "==================================================="

# Count results
TOTAL_TESTS=6
PASSED_TESTS=$(grep -c "✓" tests/logs/*.log 2>/dev/null || echo 0)
FAILED_TESTS=$(grep -c "✗" tests/logs/*.log 2>/dev/null || echo 0)
WARNING_TESTS=$(grep -c "⚠" tests/logs/*.log 2>/dev/null || echo 0)

echo "Total Tests Run: $TOTAL_TESTS"
echo -e "${GREEN}Passed: $PASSED_TESTS${NC}"
echo -e "${RED}Failed: $FAILED_TESTS${NC}"
echo -e "${YELLOW}Warnings: $WARNING_TESTS${NC}"

# Generate HTML report
cat > tests/test-report.html <<EOF
<!DOCTYPE html>
<html>
<head>
    <title>MesChain-Sync Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #333; color: white; padding: 20px; }
        .summary { margin: 20px 0; }
        .passed { color: green; }
        .failed { color: red; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MesChain-Sync Enterprise Test Report</h1>
        <p>Generated: $(date)</p>
    </div>

    <div class="summary">
        <h2>Test Summary</h2>
        <p>Total Tests: $TOTAL_TESTS</p>
        <p class="passed">Passed: $PASSED_TESTS</p>
        <p class="failed">Failed: $FAILED_TESTS</p>
        <p class="warning">Warnings: $WARNING_TESTS</p>
    </div>

    <h2>Test Results</h2>
    <table>
        <tr>
            <th>Test</th>
            <th>Status</th>
            <th>Details</th>
        </tr>
        <tr>
            <td>PHP Syntax Check</td>
            <td>$(grep -q "✓" tests/logs/syntax-check.log && echo '<span class="passed">PASSED</span>' || echo '<span class="failed">FAILED</span>')</td>
            <td>Validates PHP syntax in all files</td>
        </tr>
        <tr>
            <td>Code Standards</td>
            <td>$(grep -q "✓" tests/logs/phpcs.log && echo '<span class="passed">PASSED</span>' || echo '<span class="warning">WARNING</span>')</td>
            <td>PSR-12 compliance check</td>
        </tr>
        <tr>
            <td>Security Check</td>
            <td>$([ ! -s tests/logs/security-check.log ] && echo '<span class="passed">PASSED</span>' || echo '<span class="warning">WARNING</span>')</td>
            <td>Checks for common security issues</td>
        </tr>
        <tr>
            <td>Unit Tests</td>
            <td>$(grep -q "✓" tests/logs/phpunit.log && echo '<span class="passed">PASSED</span>' || echo '<span class="failed">FAILED</span>')</td>
            <td>PHPUnit test suite</td>
        </tr>
        <tr>
            <td>Integration Tests</td>
            <td>$(grep -q "✓" tests/logs/integration-test.log && echo '<span class="passed">PASSED</span>' || echo '<span class="failed">FAILED</span>')</td>
            <td>Marketplace integration tests</td>
        </tr>
        <tr>
            <td>Performance Tests</td>
            <td>$(grep -q "✓" tests/logs/performance-test.log && echo '<span class="passed">PASSED</span>' || echo '<span class="failed">FAILED</span>')</td>
            <td>Performance benchmarks</td>
        </tr>
    </table>
</body>
</html>
EOF

echo ""
echo "Test report generated: tests/test-report.html"
echo "Log files available in: tests/logs/"

# Exit with appropriate code
if [ $FAILED_TESTS -gt 0 ]; then
    exit 1
else
    exit 0
fi
