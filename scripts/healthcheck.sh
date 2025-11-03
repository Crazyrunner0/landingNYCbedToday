#!/bin/bash

# WordPress Stack Health Check Script
# Verifies that WordPress is properly installed and accessible
# Usage: ./scripts/healthcheck.sh [--verbose]

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

# Verbose mode
VERBOSE=false
if [ "$1" == "--verbose" ]; then
    VERBOSE=true
fi

# Helper function to print status
print_status() {
    local status=$1
    local message=$2
    
    if [ "$status" -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $message"
        CHECKS_PASSED=$((CHECKS_PASSED + 1))
    else
        echo -e "${RED}✗${NC} $message"
        CHECKS_FAILED=$((CHECKS_FAILED + 1))
    fi
}

# Helper function for verbose output
verbose() {
    if [ "$VERBOSE" = true ]; then
        echo -e "${BLUE}→${NC} $1"
    fi
}

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}WordPress Stack Health Check${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Check 1: Docker is running
echo "Checking infrastructure..."
verbose "Checking if Docker is available"
if command -v docker &> /dev/null; then
    print_status 0 "Docker is installed"
else
    print_status 1 "Docker is not installed"
    exit 1
fi

# Check 2: Docker Compose services are running
verbose "Checking Docker Compose services"
if docker compose ps &> /dev/null; then
    print_status 0 "Docker Compose is available"
else
    print_status 1 "Docker Compose is not available"
    exit 1
fi

# Check 3: PHP container is running
verbose "Checking if PHP container is running"
if docker compose ps php | grep -q "Up"; then
    print_status 0 "PHP container is running"
else
    print_status 1 "PHP container is not running"
    exit 1
fi

# Check 4: Database container is running
verbose "Checking if Database container is running"
if docker compose ps db | grep -q "Up"; then
    print_status 0 "Database container is running"
else
    print_status 1 "Database container is not running"
    exit 1
fi

# Check 5: Nginx container is running
verbose "Checking if Nginx container is running"
if docker compose ps nginx | grep -q "Up"; then
    print_status 0 "Nginx container is running"
else
    print_status 1 "Nginx container is not running"
    exit 1
fi

# Check 6: WordPress core files exist
echo ""
echo "Checking WordPress files..."
verbose "Checking if WordPress core files exist"
if [ -d "web/wp" ]; then
    print_status 0 "WordPress core files exist"
else
    print_status 1 "WordPress core files not found"
    exit 1
fi

# Check 7: WordPress configuration files exist
verbose "Checking if WordPress config exists"
if [ -f "web/wp-config.php" ]; then
    print_status 0 "WordPress configuration exists"
else
    print_status 1 "WordPress configuration not found"
    exit 1
fi

# Check 8: .env file exists
verbose "Checking if .env file exists"
if [ -f ".env" ]; then
    print_status 0 ".env file exists"
else
    print_status 1 ".env file not found"
    exit 1
fi

# Check 9: WordPress is installed (via WP-CLI)
echo ""
echo "Checking WordPress installation..."
verbose "Checking if WordPress is installed"
if docker compose exec -T php wp core is-installed --allow-root 2> /dev/null; then
    print_status 0 "WordPress is installed"
else
    print_status 1 "WordPress is not installed"
fi

# Check 10: Database connectivity
echo ""
echo "Checking database connectivity..."
verbose "Checking database connection"
if docker compose exec -T php wp db check --allow-root 2> /dev/null; then
    print_status 0 "Database is accessible"
else
    print_status 1 "Cannot connect to database"
fi

# Check 11: WordPress can be reached via HTTP
echo ""
echo "Checking HTTP connectivity..."
verbose "Checking if WordPress is accessible via HTTP"
if curl -s http://localhost:8080 > /dev/null 2>&1; then
    print_status 0 "WordPress is accessible at http://localhost:8080"
else
    print_status 1 "Cannot reach WordPress at http://localhost:8080"
fi

# Check 12: Redis is running (optional but good to check)
verbose "Checking if Redis is available"
if docker compose ps redis | grep -q "Up"; then
    print_status 0 "Redis cache is running"
else
    print_status 1 "Redis cache is not running"
fi

# Summary
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "Health Check Summary"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}Passed: $CHECKS_PASSED${NC}"
if [ $CHECKS_FAILED -gt 0 ]; then
    echo -e "${RED}Failed: $CHECKS_FAILED${NC}"
else
    echo -e "${GREEN}Failed: $CHECKS_FAILED${NC}"
fi
echo ""

# Exit with appropriate code
if [ $CHECKS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All critical checks passed!${NC}"
    echo ""
    echo "Your WordPress stack is healthy and ready to use."
    echo ""
    echo "Quick links:"
    echo "  • WordPress: http://localhost:8080"
    echo "  • Database: localhost:3306"
    echo "  • Redis: localhost:6379"
    echo ""
    exit 0
else
    echo -e "${RED}✗ Some checks failed${NC}"
    echo ""
    echo "Please review the errors above and run:"
    echo "  make logs"
    echo ""
    echo "For more help, see README.md"
    exit 1
fi
