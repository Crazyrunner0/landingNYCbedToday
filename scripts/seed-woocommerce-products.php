<?php
/**
 * WP-CLI Script: Seed WooCommerce Mattress Products
 * 
 * Usage:
 *   wp --allow-root eval-file scripts/seed-woocommerce-products.php
 *
 * This script is safe to re-run - it checks for existing products before creating them.
 */

if (!class_exists('WooCommerce')) {
    WP_CLI::error('WooCommerce is not installed or activated.');
}

// Define bed sizes with pricing
$bed_sizes = [
    'Twin'  => 599.00,
    'Full'  => 799.00,
    'Queen' => 999.00,
    'King'  => 1299.00,
];

$created_count = 0;
$skipped_count = 0;

WP_CLI::log('Starting WooCommerce product seeding...');
WP_CLI::log('');

foreach ($bed_sizes as $size => $price) {
    $product_name = $size . ' Mattress';

    // Check if product already exists
    global $wpdb;
    $existing = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND post_status = 'publish'",
            $product_name,
            'product'
        )
    );

    if ($existing) {
        WP_CLI::log("⏭️  Skipping '{$product_name}' - already exists (ID: {$existing})");
        $skipped_count++;
        continue;
    }

    // Create product
    $product = new WC_Product_Simple();
    $product->set_name($product_name);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_description('Premium ' . $size . ' size mattress with advanced comfort technology.');
    $product->set_short_description('High-quality ' . $size . ' mattress.');
    $product->set_regular_price($price);
    $product->set_manage_stock(true);
    $product->set_stock_quantity(50);
    $product->set_stock_status('instock');

    try {
        $product_id = $product->save();
        if ($product_id) {
            WP_CLI::log("✓ Created '{$product_name}' (ID: {$product_id}) - Price: \${$price}");
            $created_count++;
        } else {
            WP_CLI::warning("Failed to create '{$product_name}'");
        }
    } catch (Exception $e) {
        WP_CLI::warning("Error creating '{$product_name}': " . $e->getMessage());
    }
}

WP_CLI::log('');
WP_CLI::log('Summary:');
WP_CLI::log("✓ Created: {$created_count} products");
WP_CLI::log("⏭️  Skipped: {$skipped_count} products");
WP_CLI::success('WooCommerce product seeding complete!');
