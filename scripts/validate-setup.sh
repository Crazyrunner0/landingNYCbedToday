#!/bin/bash
# WooCommerce Setup Validation Script

echo "======================================"
echo "WooCommerce Setup Validation"
echo "======================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0

# Check function
check() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $2"
        PASSED=$((PASSED + 1))
    else
        echo -e "${RED}✗${NC} $2"
        FAILED=$((FAILED + 1))
    fi
}

# 1. Check if Composer files exist
echo "Checking files..."
[ -f "composer.json" ]
check $? "composer.json exists"

[ -f ".env.example" ]
check $? ".env.example exists"

[ -f "web/app/mu-plugins/woocommerce-custom-setup.php" ]
check $? "woocommerce-custom-setup.php exists"

[ -f "web/app/mu-plugins/woocommerce-activation-helper.php" ]
check $? "woocommerce-activation-helper.php exists"

# 2. Check composer.json for WooCommerce
echo ""
echo "Checking composer.json dependencies..."
grep -q "wpackagist-plugin/woocommerce" composer.json
check $? "WooCommerce in composer.json"

grep -q "wpackagist-plugin/woocommerce-gateway-stripe" composer.json
check $? "Stripe Gateway in composer.json"

# 3. Check .env.example for required variables
echo ""
echo "Checking .env.example configuration..."
grep -q "STRIPE_PUBLISHABLE_KEY" .env.example
check $? "STRIPE_PUBLISHABLE_KEY in .env.example"

grep -q "STRIPE_SECRET_KEY" .env.example
check $? "STRIPE_SECRET_KEY in .env.example"

grep -q "GA4_MEASUREMENT_ID" .env.example
check $? "GA4_MEASUREMENT_ID in .env.example"

grep -q "META_PIXEL_ID" .env.example
check $? "META_PIXEL_ID in .env.example"

# 4. Check MU-Plugin content
echo ""
echo "Checking MU-Plugin implementation..."
grep -q "class WooCommerce_Custom_Setup" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "WooCommerce_Custom_Setup class exists"

grep -q "reduce_checkout_fields" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Checkout field reduction implemented"

grep -q "add_sticky_cta" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Sticky CTA implemented"

grep -q "seed_products_once" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Product seeding implemented"

grep -q "configure_stripe" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Stripe configuration implemented"

grep -q "add_ga4_tracking" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "GA4 tracking implemented"

grep -q "add_meta_pixel_tracking" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Meta Pixel tracking implemented"

grep -q "track_view_item" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "view_item event tracking"

grep -q "track_add_to_cart" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "add_to_cart event tracking"

grep -q "track_begin_checkout" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "begin_checkout event tracking"

grep -q "track_purchase" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "purchase event tracking"

# 5. Check product seeding
echo ""
echo "Checking product definitions..."
grep -q "Twin.*Mattress" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Twin Mattress product"

grep -q "Full.*Mattress" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Full Mattress product"

grep -q "Queen.*Mattress" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Queen Mattress product"

grep -q "King.*Mattress" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "King Mattress product"

grep -q "Old Bed Removal" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Old Bed Removal add-on"

grep -q "Stair" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Stair Carry add-on"

grep -q "Mattress Protector" web/app/mu-plugins/woocommerce-custom-setup.php
check $? "Mattress Protector add-on"

# 6. Check documentation
echo ""
echo "Checking documentation..."
[ -f "README_WOOCOMMERCE.md" ]
check $? "README_WOOCOMMERCE.md exists"

[ -f "QUICKSTART_WOOCOMMERCE.md" ]
check $? "QUICKSTART_WOOCOMMERCE.md exists"

[ -f "WOOCOMMERCE_SETUP.md" ]
check $? "WOOCOMMERCE_SETUP.md exists"

[ -f "TESTING_CHECKLIST.md" ]
check $? "TESTING_CHECKLIST.md exists"

[ -f "IMPLEMENTATION_SUMMARY.md" ]
check $? "IMPLEMENTATION_SUMMARY.md exists"

# 7. Check PHP syntax
echo ""
echo "Checking PHP syntax..."
if command -v docker &> /dev/null && docker compose ps php &> /dev/null; then
    docker compose exec -T php php -l web/app/mu-plugins/woocommerce-custom-setup.php > /dev/null 2>&1
    check $? "woocommerce-custom-setup.php syntax valid"
    
    docker compose exec -T php php -l web/app/mu-plugins/woocommerce-activation-helper.php > /dev/null 2>&1
    check $? "woocommerce-activation-helper.php syntax valid"
else
    echo -e "${YELLOW}⚠${NC} Docker not running - skipping PHP syntax check"
fi

# Summary
echo ""
echo "======================================"
echo "Validation Summary"
echo "======================================"
echo -e "${GREEN}Passed: $PASSED${NC}"
if [ $FAILED -gt 0 ]; then
    echo -e "${RED}Failed: $FAILED${NC}"
else
    echo -e "${GREEN}Failed: $FAILED${NC}"
fi
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Run: make up"
    echo "2. Run: make composer CMD='install'"
    echo "3. Configure .env with your API keys"
    echo "4. Install WordPress at http://localhost:8080"
    echo "5. Activate WooCommerce plugins"
    echo ""
    echo "See QUICKSTART_WOOCOMMERCE.md for detailed instructions"
    exit 0
else
    echo -e "${RED}✗ Some checks failed${NC}"
    echo "Please review the errors above"
    exit 1
fi
