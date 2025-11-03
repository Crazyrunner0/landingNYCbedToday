#!/bin/bash
set -e

echo "=================================="
echo "WooCommerce Product Seeding Script"
echo "=================================="
echo ""

# Check if WordPress is installed
if ! docker compose exec -T php wp core is-installed --allow-root 2>/dev/null; then
    echo "❌ WordPress is not installed"
    echo "Please run 'make bootstrap' or complete WordPress installation first"
    exit 1
fi

# Check if WooCommerce is activated
if ! docker compose exec -T php wp plugin is-active woocommerce --allow-root 2>/dev/null; then
    echo "❌ WooCommerce is not activated"
    echo "Activating WooCommerce..."
    docker compose exec -T php wp plugin activate woocommerce --allow-root
fi

echo "Running product seeding script..."
echo ""

docker compose exec -T php wp --allow-root eval-file scripts/seed-woocommerce-products.php

echo ""
echo "=================================="
echo "Seeding Complete!"
echo "=================================="
echo ""
echo "View products:"
echo "  make wp CMD='post list --post_type=product'"
echo ""
