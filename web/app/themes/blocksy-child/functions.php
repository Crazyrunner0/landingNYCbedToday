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

/*
 * ============================================================================
 * PERFORMANCE OPTIMIZATION MODULES - DISABLED FOR SKELETON SETUP
 * ============================================================================
 * The following modules are intentionally disabled and will be re-enabled in
 * Task 13: Performance Optimization Pass. They include:
 * - Critical CSS inlining
 * - Font preloading
 * - Asset optimization (script deferring, lazy loading, emoji removal)
 * - Header/footer configuration optimization
 * - Web Vitals monitoring
 *
 * To re-enable these modules in Task 13:
 * 1. Uncomment the require statements below
 * 2. Re-add commented-out functions back to functions.php
 * 3. Re-enable performance.js script enqueue
 * See PERFORMANCE_OPTIMIZATION.md for detailed re-enablement instructions.
 * ============================================================================
 */

// Performance Optimization Modules (disabled for skeleton setup):
// require_once BLOCKSY_CHILD_DIR . '/inc/critical-css.php';
// require_once BLOCKSY_CHILD_DIR . '/inc/font-preload.php';
// require_once BLOCKSY_CHILD_DIR . '/inc/asset-optimization.php';
// require_once BLOCKSY_CHILD_DIR . '/inc/header-footer-config.php';

// Performance JavaScript (disabled for skeleton setup):
// wp_enqueue_script(
//     'blocksy-child-performance',
//     BLOCKSY_CHILD_URI . '/assets/js/performance.js',
//     [],
//     BLOCKSY_CHILD_VERSION,
//     true
// );

/*
 * Disabled Performance Functions (commented for re-enablement in Task 13):
 *
 * - blocksy_child_customize_options(): Blocksy option customization
 * - blocksy_child_header_output(): Viewport and theme-color meta tags
 * - blocksy_child_clean_head(): Remove unnecessary header meta
 * - blocksy_child_custom_header_footer(): Custom header/footer logic
 * - blocksy_child_optimize_queries(): Archive query optimization
 * - blocksy_child_rest_performance_headers(): REST API cache headers
 * - blocksy_child_schema_markup(): Schema.org markup for SEO
 */
