<?php
/**
 * Blocksy Child Theme Functions
 * Minimal skeleton setup - advanced optimizations deferred to Task 13
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BLOCKSY_CHILD_VERSION', '1.0.0');
define('BLOCKSY_CHILD_DIR', get_stylesheet_directory());
define('BLOCKSY_CHILD_URI', get_stylesheet_directory_uri());

/**
 * Enqueue parent and child theme styles
 */
function blocksy_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style(
        'blocksy-parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme()->parent()->get('Version')
    );

    // Enqueue child theme styles
    wp_enqueue_style(
        'blocksy-child-style',
        get_stylesheet_uri(),
        ['blocksy-parent-style'],
        BLOCKSY_CHILD_VERSION
    );
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_styles', 10);

/**
 * Register navigation menus
 */
function blocksy_child_register_menus() {
    register_nav_menus([
        'primary-menu' => __('Primary Menu', 'blocksy-child'),
        'footer-menu' => __('Footer Menu', 'blocksy-child'),
        'header-cta-menu' => __('Header CTA Menu', 'blocksy-child')
    ]);
}
add_action('after_setup_theme', 'blocksy_child_register_menus');

/**
 * Theme setup
 */
function blocksy_child_setup() {
    // Add support for editor styles
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');

    // Add support for custom line height
    add_theme_support('custom-line-height');

    // Add support for experimental link color
    add_theme_support('experimental-link-color');

    // Add support for custom spacing
    add_theme_support('custom-spacing');

    // Add support for custom units
    add_theme_support('custom-units');

    // Remove default WordPress block patterns
    remove_theme_support('core-block-patterns');
}
add_action('after_setup_theme', 'blocksy_child_setup', 11);

/**
 * Add body classes
 */
function blocksy_child_body_classes($classes) {
    if (is_page_template('templates/landing-page.php')) {
        $classes[] = 'landing-page';
    }
    
    return $classes;
}
add_filter('body_class', 'blocksy_child_body_classes');

/**
 * Add skip link for accessibility
 */
function blocksy_child_skip_link() {
    echo '<a class="skip-link visually-hidden" href="#main-content">' . esc_html__('Skip to content', 'blocksy-child') . '</a>';
}
add_action('wp_body_open', 'blocksy_child_skip_link', 1);

/**
 * Customize menu output with security attributes
 */
function blocksy_child_nav_menu_link_attributes($atts, $item, $args) {
    // Add rel="noopener" for external links
    if (!empty($item->url) && strpos($item->url, home_url()) === false) {
        $atts['rel'] = 'noopener noreferrer';
    }
    
    return $atts;
}
add_filter('nav_menu_link_attributes', 'blocksy_child_nav_menu_link_attributes', 10, 3);

/**
 * Custom excerpt length
 */
function blocksy_child_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'blocksy_child_excerpt_length');

/**
 * Register Gutenberg blocks (Task 16: Gutenberg Block Skeleton)
 */
require_once BLOCKSY_CHILD_DIR . '/inc/register-blocks.php';

/**
 * ============================================================================
 * PERFORMANCE OPTIMIZATION MODULES - ENABLED
 * ============================================================================
 * Performance optimization modules for Core Web Vitals targets:
 * - Critical CSS inlining for above-the-fold content
 * - Font preloading with preconnect hints
 * - Asset optimization (script deferring, lazy loading, emoji removal)
 * - Header/footer configuration optimization
 * - Web Vitals monitoring with PerformanceObserver
 * - WebP/AVIF media optimization pipeline
 * ============================================================================
 */

// Performance Optimization Modules:
require_once BLOCKSY_CHILD_DIR . '/inc/critical-css.php';
require_once BLOCKSY_CHILD_DIR . '/inc/font-preload.php';
require_once BLOCKSY_CHILD_DIR . '/inc/asset-optimization.php';
require_once BLOCKSY_CHILD_DIR . '/inc/header-footer-config.php';
require_once BLOCKSY_CHILD_DIR . '/inc/media-optimization.php';

/**
 * Customize Blocksy theme options
 */
function blocksy_child_customize_options($options) {
    return $options;
}
add_filter('blocksy:options:general', 'blocksy_child_customize_options');

/**
 * Add custom header output for viewport and theme-color
 */
function blocksy_child_header_output() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">';
    echo '<meta name="theme-color" content="#ffffff">';
}
add_action('wp_head', 'blocksy_child_header_output', 0);

/**
 * Clean unnecessary header elements
 */
function blocksy_child_clean_head() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
}
add_action('init', 'blocksy_child_clean_head');

/**
 * Custom header/footer logic
 */
function blocksy_child_custom_header_footer() {
    // Hook for custom header/footer manipulations
}
add_action('blocksy:header:after', 'blocksy_child_custom_header_footer');

/**
 * Optimize archive queries
 */
function blocksy_child_optimize_queries($query) {
    if (!is_admin() && $query->is_main_query()) {
        if ($query->is_archive() || $query->is_home()) {
            $query->set('posts_per_page', 12);
        }
    }
}
add_action('pre_get_posts', 'blocksy_child_optimize_queries');

/**
 * Add REST API performance headers
 */
function blocksy_child_rest_performance_headers() {
    header('Cache-Control: max-age=3600, public');
}
add_action('rest_api_init', 'blocksy_child_rest_performance_headers');

/**
 * Add schema markup for SEO
 */
function blocksy_child_schema_markup() {
    if (is_front_page() || is_home()) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => esc_url(home_url())
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
    }
}
add_action('wp_footer', 'blocksy_child_schema_markup', 99);

// Performance JavaScript for Web Vitals monitoring:
function blocksy_child_enqueue_performance_script() {
    wp_enqueue_script(
        'blocksy-child-performance',
        BLOCKSY_CHILD_URI . '/assets/js/performance.js',
        [],
        BLOCKSY_CHILD_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_performance_script', 11);
