#!/bin/bash
set -e

echo "=================================="
echo "WooCommerce Setup Script"
echo "=================================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo "✓ .env file created"
    echo ""
    echo "⚠️  IMPORTANT: Update the following in your .env file:"
    echo "   - STRIPE_PUBLISHABLE_KEY"
    echo "   - STRIPE_SECRET_KEY"
    echo "   - GA4_MEASUREMENT_ID"
    echo "   - META_PIXEL_ID"
    echo ""
else
    echo "✓ .env file already exists"
fi

# Check if WordPress is installed
echo "Checking WordPress installation..."
if docker compose exec -T php wp core is-installed --allow-root 2>/dev/null; then
    echo "✓ WordPress is already installed"
    echo ""
    
    # Activate plugins
    echo "Activating WooCommerce plugins..."
    docker compose exec -T php wp plugin activate woocommerce --allow-root 2>/dev/null || echo "  → WooCommerce not found, will be available after composer install"
    docker compose exec -T php wp plugin activate woocommerce-gateway-stripe --allow-root 2>/dev/null || echo "  → Stripe Gateway not found, will be available after composer install"
    echo ""
    
    echo "Checking WooCommerce pages..."
    docker compose exec -T php wp wc tool run install_pages --user=admin --allow-root 2>/dev/null || echo "  → WooCommerce pages will be created automatically"
    echo ""
    
else
    echo "⚠️  WordPress is not installed yet"
    echo "   Please visit http://localhost:8080 to complete WordPress installation"
    echo ""
fi

echo "=================================="
echo "Next Steps:"
echo "=================================="
echo ""
echo "1. If not done already:"
echo "   - Run: make up (or docker compose up -d)"
echo "   - Run: make composer CMD='install'"
echo "   - Visit: http://localhost:8080"
echo "   - Complete WordPress installation"
echo ""
echo "2. Configure your .env file with:"
echo "   - Stripe test API keys"
echo "   - GA4 Measurement ID"
echo "   - Meta Pixel ID"
echo ""
echo "3. After WordPress installation:"
echo "   - Go to Plugins → Activate WooCommerce"
echo "   - Go to Plugins → Activate WooCommerce Stripe Gateway"
echo "   - Products will be automatically seeded"
echo ""
echo "4. Test the checkout:"
echo "   - Browse to shop page"
echo "   - Add products to cart"
echo "   - Go to checkout"
echo "   - Use test card: 4242 4242 4242 4242"
echo ""
echo "5. Verify analytics:"
echo "   - GA4 DebugView for Google Analytics events"
echo "   - Meta Test Events for Facebook Pixel events"
echo ""
echo "See WOOCOMMERCE_SETUP.md for detailed instructions"
echo ""
