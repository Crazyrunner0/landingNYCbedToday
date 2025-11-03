#!/bin/bash
set -e

echo "=================================="
echo "WooCommerce Minimal Setup Script"
echo "=================================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo "✓ .env file created"
    echo ""
fi

# Check if WordPress is installed
echo "Checking WordPress installation..."
if ! docker compose exec -T php wp core is-installed --allow-root 2>/dev/null; then
    echo "❌ WordPress is not installed yet"
    echo "   Please run 'make bootstrap' or visit http://localhost:8080 to complete WordPress setup"
    exit 1
fi

echo "✓ WordPress is installed"
echo ""

# Activate WooCommerce plugin
echo "Activating WooCommerce plugin..."
if docker compose exec -T php wp plugin is-active woocommerce --allow-root 2>/dev/null; then
    echo "✓ WooCommerce is already active"
else
    if docker compose exec -T php wp plugin activate woocommerce --allow-root 2>/dev/null; then
        echo "✓ WooCommerce activated"
    else
        echo "⚠️  Could not activate WooCommerce (will be activated automatically by mu-plugin)"
    fi
fi

echo ""

# Wait a moment for plugins to fully load
sleep 1

# Create WooCommerce pages
echo "Setting up WooCommerce pages..."
if docker compose exec -T php wp wc tool run install_pages --user=admin --allow-root 2>/dev/null; then
    echo "✓ WooCommerce pages configured"
else
    echo "⚠️  Skipping auto-page creation (will be created by mu-plugin if needed)"
fi

echo ""

# Seed products
echo "Seeding mattress products..."
docker compose exec -T php wp --allow-root eval-file scripts/seed-woocommerce-products.php

echo ""
echo "=================================="
echo "WooCommerce Minimal Setup Complete!"
echo "=================================="
echo ""
echo "Next steps:"
echo "1. Visit: http://localhost:8080"
echo "2. Navigate to: /shop/ to view products"
echo "3. Navigate to: /checkout/ to test checkout"
echo ""
echo "Useful commands:"
echo "  - View products:   make wp CMD='post list --post_type=product'"
echo "  - View settings:   make wp CMD='option list | grep woocommerce'"
echo "  - Test checkout:   Open http://localhost:8080/checkout/"
echo ""
