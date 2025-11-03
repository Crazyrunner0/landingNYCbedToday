#!/bin/bash
#
# Seed Pages Script
# Creates core pages (Home, Checkout, Privacy, Terms) with placeholder content
# and registers primary/footer navigation menus.
#
# Usage: ./scripts/seed-pages.sh
# Or via docker: docker compose exec php wp --allow-root eval-file scripts/seed-pages.php

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

log_error() {
    echo -e "${RED}✗ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Check if running in Docker
if ! command -v wp &> /dev/null; then
    log_error "WP-CLI not found. Please run this script from within the PHP container:"
    echo "  make shell"
    echo "  cd /app"
    echo "  ./scripts/seed-pages.sh"
    exit 1
fi

# Check if WordPress is installed
if ! wp core is-installed --allow-root 2>/dev/null; then
    log_error "WordPress is not installed. Please run WordPress installation first."
    exit 1
fi

log_info "Starting pages seeding..."

# Check if pages are already seeded
if wp option get seed_pages_script_completed --allow-root &>/dev/null; then
    log_success "Pages already seeded"
    log_info "To re-seed, run: wp option delete seed_pages_script_completed --allow-root"
    exit 0
fi

log_info ""
log_info "Step 1: Creating core pages..."

# Home Page
HOME_PAGE_ID=$(wp post create \
    --post_title='Home' \
    --post_name='home' \
    --post_type='page' \
    --post_status='publish' \
    --post_content='<!-- wp:heading -->
<h1>Welcome to Our Store</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This is a placeholder home page. Customize it with your brand content, products, and calls to action.</p>
<!-- /wp:paragraph -->

<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":2} -->
<h2>Featured Products</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Your featured products will appear here. Visit the WordPress admin to manage your product catalog.</p>
<!-- /wp:paragraph -->' \
    --allow-root 2>/dev/null | awk '{print $NF}')

if [ -z "$HOME_PAGE_ID" ] || [ "$HOME_PAGE_ID" == "Error:" ]; then
    # Try to get existing page
    HOME_PAGE_ID=$(wp post list --post_type=page --name=home --field=ID --allow-root 2>/dev/null | head -n1)
    if [ -z "$HOME_PAGE_ID" ]; then
        log_error "Failed to create or find Home page"
        exit 1
    fi
fi
log_success "Home page created (ID: $HOME_PAGE_ID)"

# Checkout Page
CHECKOUT_PAGE_ID=$(wp post create \
    --post_title='Checkout' \
    --post_name='checkout' \
    --post_type='page' \
    --post_status='publish' \
    --post_content='<!-- wp:heading -->
<h1>Checkout</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Secure checkout page. The WooCommerce checkout block will be displayed here.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[woocommerce_checkout]
<!-- /wp:shortcode -->' \
    --allow-root 2>/dev/null | awk '{print $NF}')

if [ -z "$CHECKOUT_PAGE_ID" ] || [ "$CHECKOUT_PAGE_ID" == "Error:" ]; then
    CHECKOUT_PAGE_ID=$(wp post list --post_type=page --name=checkout --field=ID --allow-root 2>/dev/null | head -n1)
    if [ -z "$CHECKOUT_PAGE_ID" ]; then
        log_error "Failed to create or find Checkout page"
        exit 1
    fi
fi
log_success "Checkout page created (ID: $CHECKOUT_PAGE_ID)"

# Privacy Policy Page
PRIVACY_PAGE_ID=$(wp post create \
    --post_title='Privacy Policy' \
    --post_name='privacy-policy' \
    --post_type='page' \
    --post_status='publish' \
    --post_content='<!-- wp:heading -->
<h1>Privacy Policy</h1>
<!-- /wp:heading -->

<!-- wp:heading {"level":2} -->
<h2>Introduction</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This is a placeholder privacy policy. Replace this with your actual privacy policy content to comply with relevant regulations.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Information We Collect</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Describe what information you collect from users and how you use it.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Contact Us</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>If you have questions about this privacy policy, please contact us.</p>
<!-- /wp:paragraph -->' \
    --allow-root 2>/dev/null | awk '{print $NF}')

if [ -z "$PRIVACY_PAGE_ID" ] || [ "$PRIVACY_PAGE_ID" == "Error:" ]; then
    PRIVACY_PAGE_ID=$(wp post list --post_type=page --name=privacy-policy --field=ID --allow-root 2>/dev/null | head -n1)
    if [ -z "$PRIVACY_PAGE_ID" ]; then
        log_error "Failed to create or find Privacy Policy page"
        exit 1
    fi
fi
log_success "Privacy Policy page created (ID: $PRIVACY_PAGE_ID)"

# Terms Page
TERMS_PAGE_ID=$(wp post create \
    --post_title='Terms & Conditions' \
    --post_name='terms' \
    --post_type='page' \
    --post_status='publish' \
    --post_content='<!-- wp:heading -->
<h1>Terms &amp; Conditions</h1>
<!-- /wp:heading -->

<!-- wp:heading {"level":2} -->
<h2>Acceptance of Terms</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Use License</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Permission is granted to temporarily download one copy of the materials (information or software) on this website for personal, non-commercial transitory viewing only.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Disclaimer</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The materials on this website are provided on an '"'"'as is'"'"' basis. We make no warranties, expressed or implied, and hereby disclaim and negate all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
<!-- /wp:paragraph -->' \
    --allow-root 2>/dev/null | awk '{print $NF}')

if [ -z "$TERMS_PAGE_ID" ] || [ "$TERMS_PAGE_ID" == "Error:" ]; then
    TERMS_PAGE_ID=$(wp post list --post_type=page --name=terms --field=ID --allow-root 2>/dev/null | head -n1)
    if [ -z "$TERMS_PAGE_ID" ]; then
        log_error "Failed to create or find Terms & Conditions page"
        exit 1
    fi
fi
log_success "Terms & Conditions page created (ID: $TERMS_PAGE_ID)"

log_info ""
log_info "Step 2: Setting home page..."

# Set front page
wp option update show_on_front page --allow-root > /dev/null
wp option update page_on_front "$HOME_PAGE_ID" --allow-root > /dev/null
log_success "Front page set to Home"

log_info ""
log_info "Step 3: Creating navigation menus..."

# Create Primary Menu
PRIMARY_MENU_ID=$(wp menu create "Primary Menu" --allow-root 2>/dev/null | awk '{print $NF}')
if [ -z "$PRIMARY_MENU_ID" ] || [ "$PRIMARY_MENU_ID" == "Error:" ]; then
    # Try to get existing menu
    PRIMARY_MENU_ID=$(wp menu list --field=term_id --format=csv --allow-root 2>/dev/null | grep -v "term_id" | head -n1)
    if [ -z "$PRIMARY_MENU_ID" ]; then
        log_warning "Could not get Primary Menu ID"
    fi
fi
log_success "Primary Menu created (ID: $PRIMARY_MENU_ID)"

# Create Footer Menu
FOOTER_MENU_ID=$(wp menu create "Footer Menu" --allow-root 2>/dev/null | awk '{print $NF}')
if [ -z "$FOOTER_MENU_ID" ] || [ "$FOOTER_MENU_ID" == "Error:" ]; then
    # Try to get existing menu
    FOOTER_MENU_ID=$(wp menu list --field=term_id --format=csv --allow-root 2>/dev/null | grep -v "term_id" | tail -n1)
    if [ -z "$FOOTER_MENU_ID" ]; then
        log_warning "Could not get Footer Menu ID"
    fi
fi
log_success "Footer Menu created (ID: $FOOTER_MENU_ID)"

log_info ""
log_info "Step 4: Adding menu items..."

# Add items to Primary Menu
wp menu item add-post "$PRIMARY_MENU_ID" "$HOME_PAGE_ID" --allow-root > /dev/null
wp menu item add-post "$PRIMARY_MENU_ID" "$CHECKOUT_PAGE_ID" --allow-root > /dev/null
wp menu item add-post "$PRIMARY_MENU_ID" "$TERMS_PAGE_ID" --allow-root > /dev/null
wp menu item add-post "$PRIMARY_MENU_ID" "$PRIVACY_PAGE_ID" --allow-root > /dev/null
log_success "Menu items added to Primary Menu"

# Add items to Footer Menu
wp menu item add-post "$FOOTER_MENU_ID" "$HOME_PAGE_ID" --allow-root > /dev/null
wp menu item add-post "$FOOTER_MENU_ID" "$TERMS_PAGE_ID" --allow-root > /dev/null
wp menu item add-post "$FOOTER_MENU_ID" "$PRIVACY_PAGE_ID" --allow-root > /dev/null
log_success "Menu items added to Footer Menu"

log_info ""
log_info "Step 5: Assigning menus to theme locations..."

# Assign menus to locations
wp menu location assign "$PRIMARY_MENU_ID" primary-menu --allow-root > /dev/null
wp menu location assign "$FOOTER_MENU_ID" footer-menu --allow-root > /dev/null
log_success "Menus assigned to theme locations"

log_info ""

# Mark as completed
wp option add seed_pages_script_completed "$(date +%s)" --allow-root > /dev/null

# Get site URL
SITE_URL=$(wp option get siteurl --allow-root)

log_success "🎉 Pages seeding completed successfully!"
echo ""
log_info "📍 Pages created:"
echo "  • Home:             $SITE_URL/"
echo "  • Checkout:         $SITE_URL/checkout/"
echo "  • Privacy Policy:   $SITE_URL/privacy-policy/"
echo "  • Terms:            $SITE_URL/terms/"
echo ""
log_info "🎯 Navigation Menus:"
echo "  • Primary Menu (Header)"
echo "  • Footer Menu"
