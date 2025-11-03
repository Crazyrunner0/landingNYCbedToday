#!/bin/bash
# Verification script for checkout slot integration

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo "================================================"
echo "Checkout Slot Integration Verification"
echo "================================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} Found: $1"
        return 0
    else
        echo -e "${RED}✗${NC} Missing: $1"
        return 1
    fi
}

check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} Found: $1"
        return 0
    else
        echo -e "${RED}✗${NC} Missing: $1"
        return 1
    fi
}

echo "1. Checking Core Files..."
echo "========================="

# Core mu-plugin
check_file "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"

# Tests
check_file "$PROJECT_ROOT/web/app/mu-plugins/tests/test-checkout-slot-integration.php"
check_file "$PROJECT_ROOT/web/app/mu-plugins/tests/bootstrap.php"
check_file "$PROJECT_ROOT/web/app/mu-plugins/tests/phpunit.xml"

# Documentation
check_file "$PROJECT_ROOT/CHECKOUT_SLOT_INTEGRATION.md"
check_file "$PROJECT_ROOT/SLOT_INTEGRATION_GUIDE.md"

echo ""
echo "2. Checking Documentation..."
echo "============================="

if grep -q "checkout slot integration\|Checkout slot integration\|WooCommerce Checkout" "$PROJECT_ROOT/CHECKOUT_SLOT_INTEGRATION.md"; then
    echo -e "${GREEN}✓${NC} CHECKOUT_SLOT_INTEGRATION.md has content"
else
    echo -e "${RED}✗${NC} CHECKOUT_SLOT_INTEGRATION.md is empty"
fi

if grep -q "WooCommerce_Sameday_Logistics" "$PROJECT_ROOT/SLOT_INTEGRATION_GUIDE.md"; then
    echo -e "${GREEN}✓${NC} SLOT_INTEGRATION_GUIDE.md has content"
else
    echo -e "${RED}✗${NC} SLOT_INTEGRATION_GUIDE.md is empty"
fi

echo ""
echo "3. Checking Code Structure..."
echo "=============================="

# Check for key class in mu-plugin
if grep -q "class WooCommerce_Sameday_Logistics" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
    echo -e "${GREEN}✓${NC} WooCommerce_Sameday_Logistics class found"
else
    echo -e "${RED}✗${NC} WooCommerce_Sameday_Logistics class NOT found"
fi

# Check for key methods
REQUIRED_PUBLIC_METHODS=(
    "add_checkout_field"
    "validate_checkout"
    "add_order_meta"
    "render_email_order_meta"
    "render_admin_order_meta"
    "handle_slots_request"
    "handle_order_status_change"
)

for method in "${REQUIRED_PUBLIC_METHODS[@]}"; do
    if grep -q "public function $method" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
        echo -e "${GREEN}✓${NC} Method found: $method()"
    else
        echo -e "${RED}✗${NC} Method NOT found: $method()"
    fi
done

# Check for private methods
REQUIRED_PRIVATE_METHODS=(
    "create_hold"
    "release_hold"
    "release_hold_for_order"
)

for method in "${REQUIRED_PRIVATE_METHODS[@]}"; do
    if grep -q "private function $method" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
        echo -e "${GREEN}✓${NC} Private method found: $method()"
    else
        echo -e "${YELLOW}!${NC} Private method NOT found: $method()"
    fi
done

echo ""
echo "4. Checking Test Coverage..."
echo "============================="

# Check for test methods
REQUIRED_TESTS=(
    "test_checkout_field_is_required"
    "test_slot_capacity_prevents_oversell"
    "test_order_metadata_stored_on_creation"
    "test_slot_info_appears_in_emails"
    "test_cancelled_order_releases_capacity"
    "test_concurrent_slot_selection_last_slot"
)

for test in "${REQUIRED_TESTS[@]}"; do
    if grep -q "public function $test" "$PROJECT_ROOT/web/app/mu-plugins/tests/test-checkout-slot-integration.php"; then
        echo -e "${GREEN}✓${NC} Test found: $test()"
    else
        echo -e "${YELLOW}!${NC} Test NOT found: $test()"
    fi
done

echo ""
echo "5. Feature Verification..."
echo "=========================="

# Check for capacity prevention
if grep -q "max(0, \$capacity - \$usage\['total'\])" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
    echo -e "${GREEN}✓${NC} Capacity calculation found"
else
    echo -e "${YELLOW}!${NC} Capacity calculation pattern not found"
fi

# Check for hold system
if grep -q "HOLD_DURATION_MINUTES" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
    echo -e "${GREEN}✓${NC} Hold system implemented"
else
    echo -e "${RED}✗${NC} Hold system NOT found"
fi

# Check for order metadata storage
if grep -q "_sameday_delivery_display" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
    echo -e "${GREEN}✓${NC} Order metadata storage implemented"
else
    echo -e "${RED}✗${NC} Order metadata storage NOT found"
fi

# Check for email integration
if grep -q "woocommerce_email_order_meta" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
    echo -e "${GREEN}✓${NC} Email integration implemented"
else
    echo -e "${RED}✗${NC} Email integration NOT found"
fi

# Check for cancellation handling
if grep -q "cancelled.*refunded.*failed" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
    echo -e "${GREEN}✓${NC} Cancellation/refund/failure handling implemented"
else
    echo -e "${YELLOW}!${NC} Cancellation/refund/failure handling pattern not found"
fi

# Check for order status change hook
if grep -q "woocommerce_order_status_changed" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
    echo -e "${GREEN}✓${NC} Order status change handling implemented"
else
    echo -e "${RED}✗${NC} Order status change handling NOT found"
fi

echo ""
echo "6. WooCommerce Hooks..."
echo "======================"

REQUIRED_HOOKS=(
    "woocommerce_checkout_fields"
    "woocommerce_checkout_process"
    "woocommerce_checkout_create_order"
    "woocommerce_email_order_meta"
    "woocommerce_admin_order_data_after_billing_address"
    "woocommerce_order_status_changed"
)

for hook in "${REQUIRED_HOOKS[@]}"; do
    if grep -q "$hook" "$PROJECT_ROOT/web/app/mu-plugins/woocommerce-sameday-logistics.php"; then
        echo -e "${GREEN}✓${NC} Hook registered: $hook"
    else
        echo -e "${RED}✗${NC} Hook NOT found: $hook"
    fi
done

echo ""
echo "================================================"
echo "Verification Complete!"
echo "================================================"
echo ""
echo "Next Steps:"
echo "1. Run tests: wp eval-file web/app/mu-plugins/tests/test-checkout-slot-integration.php"
echo "2. Configure slots: WooCommerce → Settings → Same-day Delivery"
echo "3. Manual test: Visit /checkout/ and verify slot selection works"
echo ""
