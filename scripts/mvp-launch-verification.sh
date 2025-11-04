#!/bin/bash

# NYC Bed Today MVP Launch Verification Script
# Comprehensive checks for all MVP components
# Usage: bash scripts/mvp-launch-verification.sh

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
PASS=0
FAIL=0
WARN=0

# Logging functions
log_success() {
    echo -e "${GREEN}✓${NC} $*"
    ((PASS++))
}

log_error() {
    echo -e "${RED}✗${NC} $*"
    ((FAIL++))
}

log_warn() {
    echo -e "${YELLOW}⚠${NC} $*"
    ((WARN++))
}

log_info() {
    echo -e "${BLUE}ℹ${NC} $*"
}

log_section() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}$*${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

# Check if running in WordPress environment
check_wordpress_environment() {
    log_section "WordPress Environment Checks"
    
    if [ ! -f "docker-compose.yml" ]; then
        log_error "Not in project root (docker-compose.yml not found)"
        exit 1
    fi
    
    log_success "Project root verified"
    
    # Check if containers are running
    if docker compose ps | grep -q "php.*Up"; then
        log_success "PHP container is running"
    else
        log_warn "PHP container not running - some checks will be skipped"
        return 1
    fi
}

# Check file structure
check_file_structure() {
    log_section "File Structure Verification"
    
    local files=(
        "web/app/plugins/nycbedtoday-logistics/nycbedtoday-logistics.php"
        "web/app/plugins/nycbedtoday-blocks/nycbedtoday-blocks.php"
        "web/app/mu-plugins/woocommerce-sameday-logistics.php"
        "web/app/mu-plugins/woocommerce-custom-setup.php"
        "web/app/mu-plugins/rankmath-setup.php"
        "web/app/mu-plugins/redis-cache.php"
        "web/app/mu-plugins/cache-headers.php"
        "web/app/mu-plugins/analytics-integration.php"
        ".github/workflows/deploy-staging.yml"
        "scripts/deploy-staging.sh"
        "config/environments/staging.php"
    )
    
    for file in "${files[@]}"; do
        if [ -f "$file" ]; then
            log_success "$file exists"
        else
            log_error "$file missing"
        fi
    done
}

# Check plugin definitions
check_plugin_definitions() {
    log_section "Plugin Definition Checks"
    
    # Check logistics plugin header
    if grep -q "Plugin Name: NYC Bed Today Same-Day Logistics" \
        "web/app/plugins/nycbedtoday-logistics/nycbedtoday-logistics.php"; then
        log_success "Logistics plugin header valid"
    else
        log_error "Logistics plugin header invalid"
    fi
    
    # Check blocks plugin header
    if grep -q "Plugin Name: NYC Bed Today Blocks" \
        "web/app/plugins/nycbedtoday-blocks/nycbedtoday-blocks.php"; then
        log_success "Blocks plugin header valid"
    else
        log_error "Blocks plugin header invalid"
    fi
    
    # Check for required class definitions
    if grep -q "class NYCBEDTODAY_Logistics_Delivery_Slots" \
        "web/app/plugins/nycbedtoday-logistics/includes/class-delivery-slots.php"; then
        log_success "Delivery Slots class defined"
    else
        log_error "Delivery Slots class not found"
    fi
}

# Check WooCommerce integration
check_woocommerce_integration() {
    log_section "WooCommerce Integration Checks"
    
    if grep -q "class WooCommerce_Sameday_Logistics" \
        "web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
        log_success "WooCommerce integration class found"
    else
        log_error "WooCommerce integration class missing"
    fi
    
    if grep -q "add_checkout_field" \
        "web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
        log_success "Checkout field registration found"
    else
        log_error "Checkout field registration missing"
    fi
    
    if grep -q "woocommerce_checkout_create_order\|woocommerce_email_order_meta" \
        "web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
        log_success "Order/email integration hooks found"
    else
        log_error "Order/email integration hooks missing"
    fi
}

# Check SEO setup
check_seo_setup() {
    log_section "SEO Configuration Checks"
    
    if grep -q "RankMath" "web/app/mu-plugins/rankmath-setup.php"; then
        log_success "RankMath setup file exists"
    else
        log_error "RankMath setup incomplete"
    fi
    
    if [ -f "scripts/rankmath-settings.json" ]; then
        log_success "RankMath settings template exists"
    else
        log_error "RankMath settings template missing"
    fi
}

# Check analytics setup
check_analytics_setup() {
    log_section "Analytics Configuration Checks"
    
    if grep -q "GA4_MEASUREMENT_ID\|ga4_measurement_id" "web/app/mu-plugins/analytics-integration.php"; then
        log_success "GA4 implementation found"
    else
        log_error "GA4 implementation missing"
    fi
    
    if grep -q "META_PIXEL_ID\|meta_pixel_id" "web/app/mu-plugins/analytics-integration.php"; then
        log_success "Meta Pixel implementation found"
    else
        log_error "Meta Pixel implementation missing"
    fi
    
    if grep -q "view_item\|add_to_cart\|begin_checkout\|purchase" \
        "web/app/mu-plugins/analytics-integration.php"; then
        log_success "GA4 events configured"
    else
        log_error "GA4 events not fully configured"
    fi
}

# Check deployment configuration
check_deployment_config() {
    log_section "Deployment Configuration Checks"
    
    if [ -f ".github/workflows/deploy-staging.yml" ]; then
        if grep -q "name: Deploy Staging" ".github/workflows/deploy-staging.yml"; then
            log_success "Deploy Staging workflow exists"
        else
            log_error "Deploy Staging workflow not properly configured"
        fi
    else
        log_error "Deploy Staging workflow file missing"
    fi
    
    if [ -f "scripts/deploy-staging.sh" ]; then
        if [ -x "scripts/deploy-staging.sh" ]; then
            log_success "Deploy script is executable"
        else
            log_error "Deploy script is not executable"
        fi
    else
        log_error "Deploy script missing"
    fi
    
    if grep -q "DISALLOW_INDEXING" "config/environments/staging.php"; then
        log_success "Staging environment disables indexing"
    else
        log_error "Staging environment indexing not disabled"
    fi
}

# Check environment configuration
check_environment_config() {
    log_section "Environment Configuration Checks"
    
    local required_vars=(
        "WP_ENV"
        "WP_HOME"
        "WP_SITEURL"
        "DB_NAME"
        "DB_USER"
        "DB_PASSWORD"
    )
    
    for var in "${required_vars[@]}"; do
        if grep -q "^${var}=" ".env.example"; then
            log_success "Environment variable $var documented"
        else
            log_warn "Environment variable $var not in .env.example"
        fi
    done
    
    local analytics_vars=(
        "GA4_MEASUREMENT_ID"
        "META_PIXEL_ID"
        "STRIPE_TEST_PUBLIC_KEY"
        "STRIPE_TEST_SECRET_KEY"
    )
    
    for var in "${analytics_vars[@]}"; do
        if grep -q "^${var}=" ".env.example" || grep -q "^# ${var}=" ".env.example"; then
            log_success "Analytics variable $var documented"
        else
            log_warn "Analytics variable $var not in .env.example"
        fi
    done
}

# Check cache headers
check_cache_headers() {
    log_section "Cache & Security Headers Checks"
    
    if [ -f "web/app/mu-plugins/cache-headers.php" ]; then
        log_success "Cache headers mu-plugin exists"
        
        if grep -q "Cache-Control" "web/app/mu-plugins/cache-headers.php"; then
            log_success "Cache-Control headers configured"
        else
            log_error "Cache-Control headers not configured"
        fi
        
        if grep -q "X-Frame-Options\|X-Content-Type-Options\|X-XSS-Protection" \
            "web/app/mu-plugins/cache-headers.php"; then
            log_success "Security headers configured"
        else
            log_error "Security headers not configured"
        fi
    else
        log_error "Cache headers mu-plugin missing"
    fi
}

# Check Redis cache
check_redis_cache() {
    log_section "Redis Cache Configuration Checks"
    
    if [ -f "web/app/mu-plugins/redis-cache.php" ]; then
        log_success "Redis cache mu-plugin exists"
        
        if grep -q "WP_REDIS_HOST\|class.*Redis" "web/app/mu-plugins/redis-cache.php"; then
            log_success "Redis configuration found"
        else
            log_error "Redis configuration incomplete"
        fi
    else
        log_warn "Redis cache mu-plugin not found (optional)"
    fi
}

# Check documentation
check_documentation() {
    log_section "Documentation Checks"
    
    local docs=(
        "MVP_LAUNCH_VERIFICATION.md"
        "LOGISTICS_PLUGIN_SUMMARY.md"
        "CHECKOUT_SLOT_INTEGRATION.md"
        "STAGING_DEPLOYMENT_GUIDE.md"
        "SEO_BASELINE_RANKMATH.md"
        "ANALYTICS_PIXEL_IMPLEMENTATION.md"
        "PERFORMANCE_IMPLEMENTATION_SUMMARY.md"
    )
    
    for doc in "${docs[@]}"; do
        if [ -f "$doc" ]; then
            log_success "Documentation file: $doc"
        else
            log_warn "Documentation missing: $doc"
        fi
    done
}

# Check git configuration
check_git_config() {
    log_section "Git Configuration Checks"
    
    if [ -f ".gitignore" ]; then
        log_success ".gitignore exists"
        
        if grep -q "web/app/plugins/nycbedtoday-" ".gitignore"; then
            log_warn ".gitignore excludes plugins (should allow nycbedtoday-*)"
        elif grep -q "!web/app/plugins/nycbedtoday-" ".gitignore"; then
            log_success ".gitignore correctly allows nycbedtoday plugins"
        fi
    else
        log_error ".gitignore missing"
    fi
    
    # Check current branch
    if git rev-parse --abbrev-ref HEAD | grep -q "feat-nycbedtoday-mvp"; then
        log_success "Working on correct branch: $(git rev-parse --abbrev-ref HEAD)"
    else
        log_warn "Not on expected branch: $(git rev-parse --abbrev-ref HEAD)"
    fi
}

# WordPress plugin checks (if running)
check_wordpress_plugins() {
    log_section "WordPress Plugin Status (if available)"
    
    if ! docker compose ps | grep -q "php.*Up"; then
        log_info "Skipping WordPress checks - PHP container not running"
        return 0
    fi
    
    log_info "Running WordPress plugin checks..."
    
    # Check if WooCommerce can be found
    if [ -d "web/app/plugins/woocommerce" ] || \
       docker compose exec -T php wp plugin list --status=active 2>/dev/null | grep -q "woocommerce"; then
        log_success "WooCommerce found/active"
    else
        log_warn "WooCommerce not yet activated (this is OK for first run)"
    fi
    
    # Check logistics plugin
    if docker compose exec -T php wp plugin list 2>/dev/null | grep -q "nycbedtoday-logistics"; then
        log_success "Logistics plugin detected"
    else
        log_warn "Logistics plugin not yet loaded"
    fi
}

# Summary report
print_summary() {
    log_section "Verification Summary"
    
    local total=$((PASS + FAIL + WARN))
    
    echo ""
    echo -e "${GREEN}Passed: ${PASS}${NC}"
    echo -e "${RED}Failed: ${FAIL}${NC}"
    echo -e "${YELLOW}Warnings: ${WARN}${NC}"
    echo -e "Total: ${total}"
    echo ""
    
    if [ $FAIL -eq 0 ]; then
        echo -e "${GREEN}✓ MVP Launch Bundle Ready!${NC}"
        echo ""
        echo "Next steps:"
        echo "1. Deploy to staging: git push origin main"
        echo "2. Verify staging deployment: check GitHub Actions"
        echo "3. Run E2E tests from MVP_LAUNCH_VERIFICATION.md"
        echo "4. Verify analytics events in GA4 DebugView"
        echo ""
        return 0
    else
        echo -e "${RED}✗ Issues found - review above${NC}"
        echo ""
        return 1
    fi
}

# Main execution
main() {
    echo -e "${BLUE}"
    echo "╔════════════════════════════════════════════╗"
    echo "║  NYC Bed Today MVP Launch Verification     ║"
    echo "║  All components integration check          ║"
    echo "╚════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo ""
    
    check_wordpress_environment || true
    check_file_structure
    check_plugin_definitions
    check_woocommerce_integration
    check_seo_setup
    check_analytics_setup
    check_deployment_config
    check_environment_config
    check_cache_headers
    check_redis_cache
    check_documentation
    check_git_config
    check_wordpress_plugins
    
    print_summary
}

main "$@"
