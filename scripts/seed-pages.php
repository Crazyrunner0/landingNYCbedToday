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
 * 
 * HOME PAGE CONTENT GUIDE FOR EDITORS:
 * ====================================
 * All placeholder content on the home page is easily editable in the WordPress admin:
 * 
 * 1. HERO OFFER (Hero Section with CTA)
 *    - Edit: Home page > WordPress Editor > Hero Offer block
 *    - Change: Headline, subheadline, description, button text/link, colors
 * 
 * 2. PRICING PACKAGES (Pricing Table)
 *    - Edit: Home page > WordPress Editor > Premium Mattress Packages table
 *    - Add/update pricing rows with your products and prices
 *    - Click table to edit content directly
 * 
 * 3. SOCIAL PROOF (Trust Signals)
 *    - Edit: Home page > WordPress Editor > Social Proof Strip block
 *    - Modify: Customer counts, ratings, benefits
 *    - Add/remove items using the block controls
 * 
 * 4. REVIEWS & TESTIMONIALS (Customer Reviews Section)
 *    - Edit: Home page > WordPress Editor > Customer Reviews quote blocks
 *    - Replace placeholder quotes with real customer testimonials
 *    - Update customer names and locations
 * 
 * 5. VALUE STACK (Why Choose Us)
 *    - Edit: Home page > WordPress Editor > Value Stack block
 *    - Modify: Titles and descriptions for each value proposition
 *    - Add/remove items using the block controls
 * 
 * 6. HOW IT WORKS (Process Steps)
 *    - Edit: Home page > WordPress Editor > How It Works block
 *    - Update: Step titles and descriptions
 *    - Add/remove steps as needed
 * 
 * 7. DELIVERY & RETURN POLICIES
 *    - Edit: Home page > WordPress Editor > Our Policies section lists
 *    - Update: Policy details for delivery, returns, shipping
 *    - Modify: Lists under Delivery, Returns & Warranties, and Shipping
 * 
 * 8. SERVICE AREAS (Local Neighborhoods)
 *    - Edit: Home page > WordPress Editor > Local Neighborhoods block
 *    - Update: Borough names and service descriptions
 *    - Add/remove neighborhoods using the block controls
 * 
 * NAVIGATION ANCHORS:
 * All major sections have anchor links in the header and footer menus:
 * - #why-choose-us  - "Why Choose Us" section
 * - #pricing        - Pricing packages section
 * - #reviews        - Customer reviews section
 * - #policies       - Delivery policies section
 * 
 * These anchors enable smooth scrolling navigation between sections.
 * To add more anchor links, edit the menu items in WordPress admin.
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
                'items' => [
                    ['type' => 'page', 'slug' => 'home', 'title' => null],
                    ['type' => 'custom', 'title' => 'Why Choose Us', 'url' => '#why-choose-us'],
                    ['type' => 'custom', 'title' => 'Pricing', 'url' => '#pricing'],
                    ['type' => 'custom', 'title' => 'Reviews', 'url' => '#reviews'],
                    ['type' => 'page', 'slug' => 'checkout', 'title' => null],
                ],
            ],
            'footer-menu' => [
                'display_name' => 'Footer Menu',
                'items' => [
                    ['type' => 'page', 'slug' => 'home', 'title' => null],
                    ['type' => 'custom', 'title' => 'Policies', 'url' => '#policies'],
                    ['type' => 'page', 'slug' => 'terms', 'title' => null],
                    ['type' => 'page', 'slug' => 'privacy-policy', 'title' => null],
                ],
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
            foreach ($data['items'] as $item_data) {
                if ($item_data['type'] === 'page') {
                    if (!isset(self::$pages[$item_data['slug']])) {
                        WP_CLI::warning("Page '{$item_data['slug']}' not found for menu");
                        continue;
                    }

                    $page_title = get_the_title(self::$pages[$item_data['slug']]);

                    wp_update_nav_menu_item($menu_id, 0, [
                        'menu-item-title' => sanitize_text_field($page_title),
                        'menu-item-object' => 'page',
                        'menu-item-object-id' => self::$pages[$item_data['slug']],
                        'menu-item-type' => 'post_type',
                        'menu-item-status' => 'publish',
                    ]);
                } else if ($item_data['type'] === 'custom') {
                    wp_update_nav_menu_item($menu_id, 0, [
                        'menu-item-title' => sanitize_text_field($item_data['title']),
                        'menu-item-url' => esc_url($item_data['url']),
                        'menu-item-type' => 'custom',
                        'menu-item-status' => 'publish',
                    ]);
                }
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
        return '<!-- wp:blocksy-child/hero-offer {"headline":"Get Your Perfect Mattress Today","subheadline":"Premium comfort starts here","description":"Find the mattress that\'s right for you with our expert selection and fast delivery.","buttonText":"Shop Now","buttonUrl":"#shop","backgroundColor":"#2563eb","textColor":"#ffffff"} -->
<div class="wp-block-blocksy-child-hero-offer" style="background-color: rgb(37, 99, 235); color: rgb(255, 255, 255); padding: 80px 40px; text-align: center;">
    <div class="hero-offer-content">
        <h1 class="hero-offer-headline">Get Your Perfect Mattress Today</h1>
        <p class="hero-offer-subheadline">Premium comfort starts here</p>
        <p class="hero-offer-description">Find the mattress that\'s right for you with our expert selection and fast delivery.</p>
        <a href="#shop" class="hero-offer-button">Shop Now</a>
    </div>
</div>
<!-- /wp:blocksy-child/hero-offer -->

<!-- wp:blocksy-child/social-proof-strip {"items":[{"id":1,"text":"Trusted by 50,000+ customers","icon":"star"},{"id":2,"text":"4.9/5 stars average rating","icon":"star"},{"id":3,"text":"Free same-day delivery on select orders","icon":"truck"}]} -->
<div class="wp-block-blocksy-child-social-proof-strip">
    <div class="social-proof-strip">
        <div class="social-proof-items">
            <div class="social-proof-item">
                <span class="social-proof-text">Trusted by 50,000+ customers</span>
            </div>
            <div class="social-proof-item">
                <span class="social-proof-text">4.9/5 stars average rating</span>
            </div>
            <div class="social-proof-item">
                <span class="social-proof-text">Free same-day delivery on select orders</span>
            </div>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/social-proof-strip -->

<!-- wp:heading {"anchor":"pricing","level":2} -->
<h2 id="pricing">Premium Mattress Packages</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Choose from our curated selection of mattresses designed for every comfort preference and budget.</p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table"><table><tbody><tr><td>Package</td><td>Size</td><td>Price</td><td>Features</td></tr><tr><td><strong>Comfort Plus</strong></td><td>Queen</td><td>$799</td><td>Memory foam, 10-year warranty, free delivery</td></tr><tr><td><strong>Luxury Pro</strong></td><td>Queen</td><td>$1,299</td><td>Hybrid construction, advanced cooling, 15-year warranty, white glove delivery</td></tr><tr><td><strong>Elite Choice</strong></td><td>Queen</td><td>$1,799</td><td>Premium materials, adjustable support, 20-year warranty, white glove + setup</td></tr><tr><td><strong>Sleep Bliss</strong></td><td>Queen</td><td>$499</td><td>Gel-infused foam, 5-year warranty, free delivery, 30-night trial</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph -->
<p><em>All mattresses come with a 30-night sleep trial and free returns if not completely satisfied.</em></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"anchor":"why-choose-us","level":2} -->
<h2 id="why-choose-us">Why Choose Us</h2>
<!-- /wp:heading -->

<!-- wp:blocksy-child/value-stack {"title":"Why Choose Us","items":[{"id":1,"title":"Premium Quality","description":"Expert-selected mattresses from trusted brands with years of customer satisfaction"},{"id":2,"title":"Fast Delivery","description":"Same-day and next-day delivery options available in select areas"},{"id":3,"title":"100% Satisfaction","description":"30-night sleep trial on all mattresses with hassle-free returns"}]} -->
<div class="wp-block-blocksy-child-value-stack">
    <div class="value-stack">
        <h2 class="value-stack-title" style="display: none;">Why Choose Us</h2>
        <div class="value-stack-items">
            <div class="value-stack-item">
                <h3 class="value-item-title">Premium Quality</h3>
                <p class="value-item-description">Expert-selected mattresses from trusted brands with years of customer satisfaction</p>
            </div>
            <div class="value-stack-item">
                <h3 class="value-item-title">Fast Delivery</h3>
                <p class="value-item-description">Same-day and next-day delivery options available in select areas</p>
            </div>
            <div class="value-stack-item">
                <h3 class="value-item-title">100% Satisfaction</h3>
                <p class="value-item-description">30-night sleep trial on all mattresses with hassle-free returns</p>
            </div>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/value-stack -->

<!-- wp:heading {"anchor":"reviews","level":2} -->
<h2 id="reviews">Customer Reviews</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>See what our satisfied customers have to say about their mattress purchases.</p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column">
<!-- wp:quote {"className":"is-style-default"} -->
<blockquote class="wp-block-quote"><p>"Best purchase I\'ve made in years! The mattress arrived quickly and is incredibly comfortable. I\'ve been sleeping better than ever."</p><cite>Sarah M. - Manhattan</cite></blockquote>
<!-- /wp:quote -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:quote {"className":"is-style-default"} -->
<blockquote class="wp-block-quote"><p>"The delivery team was professional and friendly. They set up the mattress perfectly. Highly recommend!"</p><cite>James T. - Brooklyn</cite></blockquote>
<!-- /wp:quote -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:quote {"className":"is-style-default"} -->
<blockquote class="wp-block-quote"><p>"Tried the 30-night trial and loved it from day one. Great quality at a reasonable price."</p><cite>Maria G. - Queens</cite></blockquote>
<!-- /wp:quote -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:blocksy-child/how-it-works {"title":"How It Works","steps":[{"id":1,"number":"1","title":"Browse Selection","description":"Explore our wide variety of premium mattresses online"},{"id":2,"number":"2","title":"Select & Compare","description":"Compare features, prices, and customer reviews"},{"id":3,"number":"3","title":"Quick Checkout","description":"Secure payment and schedule your delivery"},{"id":4,"number":"4","title":"Fast Delivery","description":"Same-day or next-day delivery to your home"}]} -->
<div class="wp-block-blocksy-child-how-it-works">
    <div class="how-it-works">
        <h2 class="how-it-works-title">How It Works</h2>
        <div class="how-it-works-steps">
            <div class="how-it-works-step">
                <div class="step-number">1</div>
                <h3 class="step-title">Browse Selection</h3>
                <p class="step-description">Explore our wide variety of premium mattresses online</p>
            </div>
            <div class="how-it-works-step">
                <div class="step-number">2</div>
                <h3 class="step-title">Select & Compare</h3>
                <p class="step-description">Compare features, prices, and customer reviews</p>
            </div>
            <div class="how-it-works-step">
                <div class="step-number">3</div>
                <h3 class="step-title">Quick Checkout</h3>
                <p class="step-description">Secure payment and schedule your delivery</p>
            </div>
            <div class="how-it-works-step">
                <div class="step-number">4</div>
                <h3 class="step-title">Fast Delivery</h3>
                <p class="step-description">Same-day or next-day delivery to your home</p>
            </div>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/how-it-works -->

<!-- wp:heading {"anchor":"policies","level":2} -->
<h2 id="policies">Our Policies</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3>Delivery</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><li>Same-day delivery available for orders placed before 2 PM</li><li>Next-day delivery for all service areas</li><li>Free delivery on orders over $600</li><li>White glove service available for premium packages</li><li>Real-time tracking for all orders</li></ul>
<!-- /wp:list -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3>Returns &amp; Warranties</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><li>30-night sleep trial on all mattresses</li><li>Free returns within 30 days</li><li>5-20 year warranties depending on mattress model</li><li>Lifetime customer support</li><li>Hassle-free returns process</li></ul>
<!-- /wp:list -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3>Shipping Information</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><li>Ships within 1-2 business days of order</li><li>Insured for full replacement value</li><li>Delivery times: 1-2 business days in NYC area</li><li>International shipping available on select items</li><li>Special handling for mattress delivery</li></ul>
<!-- /wp:list -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:blocksy-child/local-neighborhoods {"title":"Serving Your Neighborhood","description":"We proudly serve customers across multiple neighborhoods and boroughs with fast, reliable delivery.","neighborhoods":[{"id":1,"name":"Manhattan","description":"All neighborhoods with same-day delivery available"},{"id":2,"name":"Brooklyn","description":"All neighborhoods with fast next-day delivery"},{"id":3,"name":"Queens","description":"Select areas with next-day delivery service"},{"id":4,"name":"Bronx","description":"Select areas with flexible delivery options"}]} -->
<div class="wp-block-blocksy-child-local-neighborhoods" style="background-color: rgb(248, 250, 252);">
    <div class="local-neighborhoods">
        <h2 class="local-neighborhoods-title">Serving Your Neighborhood</h2>
        <p class="local-neighborhoods-description">We proudly serve customers across multiple neighborhoods and boroughs with fast, reliable delivery.</p>
        <div class="neighborhoods-grid">
            <div class="neighborhood-item">
                <h3 class="neighborhood-name">Manhattan</h3>
                <p class="neighborhood-description">All neighborhoods with same-day delivery available</p>
            </div>
            <div class="neighborhood-item">
                <h3 class="neighborhood-name">Brooklyn</h3>
                <p class="neighborhood-description">All neighborhoods with fast next-day delivery</p>
            </div>
            <div class="neighborhood-item">
                <h3 class="neighborhood-name">Queens</h3>
                <p class="neighborhood-description">Select areas with next-day delivery service</p>
            </div>
            <div class="neighborhood-item">
                <h3 class="neighborhood-name">Bronx</h3>
                <p class="neighborhood-description">Select areas with flexible delivery options</p>
            </div>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/local-neighborhoods -->

<!-- wp:blocksy-child/final-cta {"headline":"Ready to Find Your Perfect Mattress?","description":"Join thousands of satisfied customers who have transformed their sleep. Start your journey today with our 30-night risk-free trial.","primaryButtonText":"Shop Now","primaryButtonUrl":"#shop","secondaryButtonText":"Learn More","secondaryButtonUrl":"/privacy-policy/","backgroundColor":"#0f172a","textColor":"#ffffff"} -->
<div class="wp-block-blocksy-child-final-cta" style="background-color: rgb(15, 23, 42); color: rgb(255, 255, 255); padding: 60px 40px; text-align: center;">
    <div class="final-cta-content">
        <h2 class="final-cta-headline">Ready to Find Your Perfect Mattress?</h2>
        <p class="final-cta-description">Join thousands of satisfied customers who have transformed their sleep. Start your journey today with our 30-night risk-free trial.</p>
        <div class="final-cta-buttons">
            <a href="#shop" class="final-cta-button primary">Shop Now</a>
            <a href="/privacy-policy/" class="final-cta-button secondary">Learn More</a>
        </div>
    </div>
</div>
<!-- /wp:blocksy-child/final-cta -->';
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
