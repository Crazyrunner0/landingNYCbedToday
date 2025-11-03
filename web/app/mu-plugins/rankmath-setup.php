<?php
/**
 * Plugin Name: RankMath SEO Setup
 * Description: Automatically activates and configures RankMath with site metadata, sitemaps, and JSON-LD schemas
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * RankMath Setup Class
 */
class NYCBEDTODAY_RankMath_Setup {

    /**
     * Initialize RankMath setup
     */
    public static function init() {
        // Activate RankMath plugin if it's installed but not active
        add_action('plugins_loaded', [__CLASS__, 'activate_rankmath'], 5);

        // Configure RankMath after it loads
        add_action('plugins_loaded', [__CLASS__, 'configure_rankmath'], 10);

        // Add JSON-LD schema output
        add_action('wp_head', [__CLASS__, 'output_jsonld_schemas'], 1);
    }

    /**
     * Activate RankMath plugin
     */
    public static function activate_rankmath() {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Check if RankMath is installed
        $rankmath_file = WP_PLUGIN_DIR . '/rank-math/rank-math.php';

        if (file_exists($rankmath_file) && !is_plugin_active('rank-math/rank-math.php')) {
            // Activate the plugin
            activate_plugin('rank-math/rank-math.php');
        }
    }

    /**
     * Configure RankMath settings
     */
    public static function configure_rankmath() {
        // Check if RankMath is active
        if (!function_exists('rank_math')) {
            return;
        }

        // Get current settings
        $settings = get_option('rank-math-options-general', []);

        // Set default settings if not already configured
        if (empty($settings)) {
            $site_title = get_bloginfo('name');
            $site_description = get_bloginfo('description');
            $site_url = home_url();

            $default_settings = [
                // General settings
                'site_title'      => $site_title,
                'site_description' => $site_description,

                // Enable features
                'enable_seo'      => true,
                'enable_sitemap'  => true,
                'enable_robots'   => true,
                'enable_redirects' => false,

                // Schema settings
                'enable_schema'   => true,
                'knowledge_graph' => 'business',
                'business_type'   => 'LocalBusiness',

                // Sitemap settings
                'sitemap_index'   => true,
                'sitemap_size'    => 100,
            ];

            update_option('rank-math-options-general', $default_settings);
        }

        // Configure modules (ensure sitemap and JSON-LD are enabled)
        $modules = get_option('rank-math-modules', []);
        $modules['seo']     = true;
        $modules['sitemap'] = true;
        $modules['schema']  = true;
        $modules['redirects'] = false;

        update_option('rank-math-modules', $modules);

        // Set up site metadata
        self::setup_site_metadata();

        // Set up robots.txt if not already configured
        self::setup_robots_txt();
    }

    /**
     * Set up site metadata for LocalBusiness schema
     */
    public static function setup_site_metadata() {
        // Get or create site metadata
        $site_meta = get_option('rank-math-site-meta', []);

        if (empty($site_meta)) {
            $site_meta = [
                'name'           => get_bloginfo('name'),
                'url'            => home_url(),
                'description'    => get_bloginfo('description'),
                'address'        => '123 Main Street, New York, NY 10001, USA',
                'phone'          => '+1 (212) 555-0123',
                'businessType'   => 'LocalBusiness',
                'sameAsUrls'     => [],
                'locationType'   => 'Local',
                'priceRange'     => '$$$',
                'serviceLocations' => [],
                'latitude'       => '40.7128',
                'longitude'      => '-74.0060',
            ];

            update_option('rank-math-site-meta', $site_meta);
        }
    }

    /**
     * Set up robots.txt configuration
     */
    public static function setup_robots_txt() {
        $robots_options = get_option('rank-math-options-titles', []);

        if (empty($robots_options['robots_default'])) {
            $robots_options['robots_default'] = 'index, follow';
            update_option('rank-math-options-titles', $robots_options);
        }
    }

    /**
     * Output JSON-LD schemas
     */
    public static function output_jsonld_schemas() {
        // Only output on frontend and for publicly viewable pages
        if (is_admin() || is_feed() || is_robots()) {
            return;
        }

        // Output LocalBusiness schema on homepage
        if (is_front_page()) {
            self::output_localbusiness_schema();
            self::output_breadcrumb_schema();
            self::output_faq_schema();
        }

        // Output BreadcrumbList on archive/single pages
        if (!is_front_page() && (is_single() || is_archive() || is_page())) {
            self::output_breadcrumb_schema();
        }
    }

    /**
     * Output LocalBusiness JSON-LD schema
     */
    public static function output_localbusiness_schema() {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => get_bloginfo('name'),
            'url'      => home_url(),
            'description' => get_bloginfo('description'),
            'address'  => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => '123 Main Street',
                'addressLocality' => 'New York',
                'addressRegion'   => 'NY',
                'postalCode'      => '10001',
                'addressCountry'  => 'US',
            ],
            'telephone' => '+1 (212) 555-0123',
            'image'    => get_site_icon_url(300),
            'sameAs'   => [
                'https://www.facebook.com/nycbedtoday',
                'https://www.instagram.com/nycbedtoday',
                'https://www.twitter.com/nycbedtoday',
            ],
        ];

        // Add geolocation if available
        $site_meta = get_option('rank-math-site-meta', []);
        if (!empty($site_meta['latitude']) && !empty($site_meta['longitude'])) {
            $schema['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $site_meta['latitude'],
                'longitude' => $site_meta['longitude'],
            ];
        }

        // Allow filtering of schema
        $schema = apply_filters('nycbedtoday_localbusiness_schema', $schema);

        echo wp_kses_post(self::render_jsonld($schema));
    }

    /**
     * Output BreadcrumbList JSON-LD schema
     */
    public static function output_breadcrumb_schema() {
        $breadcrumbs = [];
        $position    = 1;

        // Add home
        $breadcrumbs[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_bloginfo('name'),
            'item'     => home_url(),
        ];

        // Add current page/post
        if (is_singular() && !is_front_page()) {
            $breadcrumbs[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => get_the_title(),
                'item'     => get_the_permalink(),
            ];
        } elseif (is_archive()) {
            if (is_post_type_archive()) {
                $post_type = get_queried_object();
                $breadcrumbs[] = [
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $post_type->labels->name ?? get_post_type(),
                    'item'     => get_post_type_archive_link(get_post_type()),
                ];
            } elseif (is_category() || is_tag() || is_tax()) {
                $term = get_queried_object();
                $breadcrumbs[] = [
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $term->name,
                    'item'     => get_term_link($term),
                ];
            }
        }

        if (count($breadcrumbs) > 1) {
            $schema = [
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $breadcrumbs,
            ];

            $schema = apply_filters('nycbedtoday_breadcrumb_schema', $schema);

            echo wp_kses_post(self::render_jsonld($schema));
        }
    }

    /**
     * Output FAQPage JSON-LD schema with placeholders
     */
    public static function output_faq_schema() {
        // Placeholder FAQ schema - can be enhanced with actual FAQ blocks
        $faq_items = apply_filters('nycbedtoday_faq_items', [
            [
                'question' => 'What mattress sizes do you offer?',
                'answer'   => 'We offer a variety of mattress sizes including Twin, Full, Queen, and King sizes to fit any bedroom.',
            ],
            [
                'question' => 'Do you offer delivery?',
                'answer'   => 'Yes, we offer fast and reliable delivery to the NYC area. Delivery options will be shown during checkout.',
            ],
            [
                'question' => 'What is your return policy?',
                'answer'   => 'We offer a 30-day return policy on all mattresses. If you\'re not satisfied, we\'ll make it right.',
            ],
            [
                'question' => 'Are your mattresses eco-friendly?',
                'answer'   => 'Many of our mattresses are made with sustainable materials. Check product descriptions for specific eco-friendly details.',
            ],
        ]);

        if (empty($faq_items)) {
            return;
        }

        $faq_main_entity = [];
        foreach ($faq_items as $faq) {
            if (!empty($faq['question']) && !empty($faq['answer'])) {
                $faq_main_entity[] = [
                    '@type'          => 'Question',
                    'name'           => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $faq['answer'],
                    ],
                ];
            }
        }

        if (!empty($faq_main_entity)) {
            $schema = [
                '@context'      => 'https://schema.org',
                '@type'         => 'FAQPage',
                'mainEntity'    => $faq_main_entity,
            ];

            $schema = apply_filters('nycbedtoday_faq_schema', $schema);

            echo wp_kses_post(self::render_jsonld($schema));
        }
    }

    /**
     * Render JSON-LD schema as script tag
     *
     * @param array $schema The schema data.
     * @return string The rendered script tag.
     */
    private static function render_jsonld($schema) {
        return '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
}

// Initialize setup
NYCBEDTODAY_RankMath_Setup::init();
