<?php
/**
 * Seed Pages Script
 * Run via: make wp CMD='--allow-root eval-file scripts/seed-pages.php'
 * 
 * Creates core pages (Home, Checkout, Privacy, Terms) with placeholder content
 * and registers primary/footer navigation menus.
 * 
 * This script is idempotent - running it multiple times will update existing pages
 * but not create duplicates.
 */

if (!function_exists('get_site_url')) {
    echo "Error: WordPress is not properly loaded. Run this via: make wp CMD='--allow-root eval-file scripts/seed-pages.php'\n";
    exit(1);
}

class SeedPagesScript {
    private static $pages_seeded_option = 'seed_pages_script_completed';
    private static $pages = [];

    public static function run() {
        WP_CLI::log('🌱 Starting pages seeding...');

        // Check if already seeded
        $seeded = get_option(self::$pages_seeded_option);
        if ($seeded) {
            WP_CLI::log('✓ Pages already seeded (option found).');
            WP_CLI::log('  To re-seed, use: make wp CMD="--allow-root option delete seed_pages_script_completed"');
            return;
        }

        // Step 1: Create pages
        WP_CLI::log('Step 1: Creating core pages...');
        if (!self::create_pages()) {
            WP_CLI::error('Failed to create pages');
            return;
        }
        WP_CLI::success('✓ Pages created');

        // Step 2: Set home page
        WP_CLI::log('Step 2: Setting home page...');
        if (!self::set_home_page()) {
            WP_CLI::error('Failed to set home page');
            return;
        }
        WP_CLI::success('✓ Home page configured');

        // Step 3: Create navigation menus
        WP_CLI::log('Step 3: Creating navigation menus...');
        if (!self::create_navigation_menus()) {
            WP_CLI::error('Failed to create navigation menus');
            return;
        }
        WP_CLI::success('✓ Navigation menus created');

        // Step 4: Assign menus to locations
        WP_CLI::log('Step 4: Assigning menus to theme locations...');
        if (!self::assign_menus_to_locations()) {
            WP_CLI::error('Failed to assign menus');
            return;
        }
        WP_CLI::success('✓ Menus assigned to locations');

        // Mark as completed
        update_option(self::$pages_seeded_option, time());

        WP_CLI::success('🎉 Pages seeding completed successfully!');
        WP_CLI::log('');
        WP_CLI::log('📍 Pages created:');
        WP_CLI::log('  • Home:             ' . get_site_url() . '/');
        WP_CLI::log('  • Checkout:         ' . get_site_url() . '/checkout/');
        WP_CLI::log('  • Privacy Policy:   ' . get_site_url() . '/privacy-policy/');
        WP_CLI::log('  • Terms:            ' . get_site_url() . '/terms/');
        WP_CLI::log('');
        WP_CLI::log('🎯 Navigation Menus:');
        WP_CLI::log('  • Primary Menu (Header)');
        WP_CLI::log('  • Footer Menu');
    }

    private static function create_pages() {
        $pages = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'content' => self::get_home_content(),
            ],
            [
                'title' => 'Checkout',
                'slug' => 'checkout',
                'content' => self::get_checkout_content(),
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => self::get_privacy_content(),
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms',
                'content' => self::get_terms_content(),
            ],
        ];

        foreach ($pages as $page_data) {
            // Check if page already exists
            $existing_page = get_page_by_path($page_data['slug']);
            
            if ($existing_page) {
                // Update existing page
                $result = wp_update_post([
                    'ID' => $existing_page->ID,
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                ]);

                if (is_wp_error($result)) {
                    WP_CLI::error("Failed to update page '{$page_data['slug']}'");
                    return false;
                }

                self::$pages[$page_data['slug']] = $existing_page->ID;
            } else {
                // Create new page
                $page_id = wp_insert_post([
                    'post_type' => 'page',
                    'post_title' => $page_data['title'],
                    'post_name' => $page_data['slug'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                ], true);

                if (is_wp_error($page_id)) {
                    WP_CLI::error("Failed to create page '{$page_data['slug']}'");
                    return false;
                }

                self::$pages[$page_data['slug']] = $page_id;
            }

            WP_CLI::log("  ✓ {$page_data['title']} (ID: " . self::$pages[$page_data['slug']] . ")");
        }

        return true;
    }

    private static function set_home_page() {
        if (!isset(self::$pages['home'])) {
            WP_CLI::warning('Home page not found');
            return false;
        }

        update_option('show_on_front', 'page');
        update_option('page_on_front', self::$pages['home']);

        WP_CLI::log('  ✓ Front page set to Home');

        return true;
    }

    private static function create_navigation_menus() {
        $menu_data = [
            'primary-menu' => [
                'display_name' => 'Primary Menu',
                'items' => ['home', 'checkout', 'terms', 'privacy-policy'],
            ],
            'footer-menu' => [
                'display_name' => 'Footer Menu',
                'items' => ['home', 'terms', 'privacy-policy'],
            ],
        ];

        foreach ($menu_data as $menu_location => $data) {
            // Get or create menu
            $existing_menu = get_term_by('name', $data['display_name'], 'nav_menu');
            
            if ($existing_menu) {
                $menu_id = $existing_menu->term_id;

                // Clear existing items
                $menu_items = wp_get_nav_menu_items($menu_id);
                if ($menu_items) {
                    foreach ($menu_items as $item) {
                        wp_delete_post($item->ID, true);
                    }
                }
            } else {
                $menu_id = wp_create_nav_menu($data['display_name']);

                if (is_wp_error($menu_id)) {
                    WP_CLI::error("Failed to create menu '{$data['display_name']}'");
                    return false;
                }
            }

            // Add menu items
            foreach ($data['items'] as $page_slug) {
                if (!isset(self::$pages[$page_slug])) {
                    WP_CLI::warning("Page '{$page_slug}' not found for menu");
                    continue;
                }

                $page_title = get_the_title(self::$pages[$page_slug]);

                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title' => sanitize_text_field($page_title),
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => self::$pages[$page_slug],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                ]);
            }

            WP_CLI::log("  ✓ {$data['display_name']} (ID: {$menu_id})");
        }

        return true;
    }

    private static function assign_menus_to_locations() {
        $locations = get_theme_mod('nav_menu_locations', []);

        // Get or create primary menu
        $primary_menu = get_term_by('name', 'Primary Menu', 'nav_menu');
        if ($primary_menu) {
            $locations['primary-menu'] = $primary_menu->term_id;
            WP_CLI::log('  ✓ Primary Menu assigned to primary-menu location');
        }

        // Get or create footer menu
        $footer_menu = get_term_by('name', 'Footer Menu', 'nav_menu');
        if ($footer_menu) {
            $locations['footer-menu'] = $footer_menu->term_id;
            WP_CLI::log('  ✓ Footer Menu assigned to footer-menu location');
        }

        set_theme_mod('nav_menu_locations', $locations);

        return true;
    }

    private static function get_home_content() {
        return '<!-- wp:heading -->
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
<!-- /wp:paragraph -->';
    }

    private static function get_checkout_content() {
        return '<!-- wp:heading -->
<h1>Checkout</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Secure checkout page. The WooCommerce checkout block will be displayed here.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[woocommerce_checkout]
<!-- /wp:shortcode -->';
    }

    private static function get_privacy_content() {
        return '<!-- wp:heading -->
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
<!-- /wp:paragraph -->';
    }

    private static function get_terms_content() {
        return '<!-- wp:heading -->
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
<p>The materials on this website are provided on an \'as is\' basis. We make no warranties, expressed or implied, and hereby disclaim and negate all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
<!-- /wp:paragraph -->';
    }
}

// Run the script
SeedPagesScript::run();
