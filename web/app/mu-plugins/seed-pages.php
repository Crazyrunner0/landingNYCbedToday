<?php
/**
 * Plugin Name: Seed Pages
 * Description: Creates core pages (Home, Checkout, Privacy, Terms) with placeholder Gutenberg content
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Seed_Pages {
    /**
     * Initialize the pages seeding functionality
     */
    public static function init() {
        add_action('init', [self::class, 'register_seed_pages_endpoint']);
    }

    /**
     * Register WP-CLI command for seeding pages
     */
    public static function register_seed_pages_endpoint() {
        if (class_exists('WP_CLI')) {
            \WP_CLI::add_command('seed pages', [self::class, 'seed_pages_command']);
        }
    }

    /**
     * WP-CLI command to seed pages
     */
    public static function seed_pages_command($args, $assoc_args) {
        // Check if pages are already seeded
        $pages_seeded = get_option('seed_pages_created');
        
        if ($pages_seeded && !isset($assoc_args['force'])) {
            \WP_CLI::log('✓ Pages already seeded. Use --force to re-seed.');
            return;
        }

        // Create pages
        $pages_created = self::create_pages();
        
        if (!$pages_created) {
            \WP_CLI::error('Failed to create pages');
            return;
        }

        // Create menus
        $menus_created = self::create_menus();
        
        if (!$menus_created) {
            \WP_CLI::error('Failed to create menus');
            return;
        }

        // Set home page
        self::set_home_page();

        // Mark as seeded
        update_option('seed_pages_created', time());

        \WP_CLI::success('Pages and menus seeded successfully!');
    }

    /**
     * Create core pages with Gutenberg content
     */
    public static function create_pages() {
        $pages = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'content' => self::get_home_content(),
                'set_front_page' => true,
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

        $created_count = 0;

        foreach ($pages as $page_data) {
            // Check if page exists
            $existing = get_page_by_path($page_data['slug']);
            
            if ($existing) {
                // Update existing page content
                wp_update_post([
                    'ID' => $existing->ID,
                    'post_content' => $page_data['content'],
                ]);
                $created_count++;
            } else {
                // Create new page
                $page_id = wp_insert_post([
                    'post_type' => 'page',
                    'post_title' => $page_data['title'],
                    'post_name' => $page_data['slug'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                ]);

                if (is_wp_error($page_id)) {
                    return false;
                }

                $created_count++;
            }
        }

        return $created_count > 0;
    }

    /**
     * Create navigation menus
     */
    public static function create_menus() {
        $menu_names = [
            'primary-menu' => 'Primary Menu',
            'footer-menu' => 'Footer Menu',
        ];

        $home_page = get_page_by_path('home');
        $checkout_page = get_page_by_path('checkout');
        $privacy_page = get_page_by_path('privacy-policy');
        $terms_page = get_page_by_path('terms');

        foreach ($menu_names as $menu_location => $menu_name) {
            // Check if menu exists
            $existing_menu = get_term_by('name', $menu_name, 'nav_menu');
            
            if (!$existing_menu) {
                $menu_id = wp_create_nav_menu($menu_name);
            } else {
                $menu_id = $existing_menu->term_id;
                // Clear existing menu items
                $menu_items = wp_get_nav_menu_items($menu_id);
                if ($menu_items) {
                    foreach ($menu_items as $item) {
                        wp_delete_post($item->ID, true);
                    }
                }
            }

            if (is_wp_error($menu_id)) {
                return false;
            }

            // Add menu items based on menu type
            if ($menu_location === 'primary-menu') {
                self::add_menu_items($menu_id, [
                    ['title' => 'Home', 'object' => 'page', 'object_id' => $home_page->ID],
                    ['title' => 'Checkout', 'object' => 'page', 'object_id' => $checkout_page->ID],
                    ['title' => 'Terms', 'object' => 'page', 'object_id' => $terms_page->ID],
                    ['title' => 'Privacy', 'object' => 'page', 'object_id' => $privacy_page->ID],
                ]);
            } elseif ($menu_location === 'footer-menu') {
                self::add_menu_items($menu_id, [
                    ['title' => 'Home', 'object' => 'page', 'object_id' => $home_page->ID],
                    ['title' => 'Terms', 'object' => 'page', 'object_id' => $terms_page->ID],
                    ['title' => 'Privacy', 'object' => 'page', 'object_id' => $privacy_page->ID],
                ]);
            }

            // Assign menu to location
            $locations = get_theme_mod('nav_menu_locations', []);
            $locations[$menu_location] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }

        return true;
    }

    /**
     * Add items to a menu
     */
    public static function add_menu_items($menu_id, $items) {
        foreach ($items as $index => $item) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => sanitize_text_field($item['title']),
                'menu-item-object' => $item['object'],
                'menu-item-object-id' => $item['object_id'],
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
            ]);
        }
    }

    /**
     * Set the home page as front page
     */
    public static function set_home_page() {
        $home_page = get_page_by_path('home');
        
        if ($home_page) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $home_page->ID);
        }
    }

    /**
     * Get Home page Gutenberg content
     */
    public static function get_home_content() {
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

    /**
     * Get Checkout page Gutenberg content
     */
    public static function get_checkout_content() {
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

    /**
     * Get Privacy Policy page Gutenberg content
     */
    public static function get_privacy_content() {
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

    /**
     * Get Terms & Conditions page Gutenberg content
     */
    public static function get_terms_content() {
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

// Initialize on WordPress load
Seed_Pages::init();
