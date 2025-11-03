<?php
/**
 * Blocksy Child Theme Functions
 * High-performance child theme with optimized asset loading
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
 * Enqueue performance JavaScript
 */
function blocksy_child_enqueue_scripts() {
    wp_enqueue_script(
        'blocksy-child-performance',
        BLOCKSY_CHILD_URI . '/assets/js/performance.js',
        [],
        BLOCKSY_CHILD_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_scripts', 10);

/**
 * Load optimization modules
 */
require_once BLOCKSY_CHILD_DIR . '/inc/critical-css.php';
require_once BLOCKSY_CHILD_DIR . '/inc/font-preload.php';
require_once BLOCKSY_CHILD_DIR . '/inc/asset-optimization.php';
require_once BLOCKSY_CHILD_DIR . '/inc/header-footer-config.php';

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
 * Customize Blocksy options
 */
function blocksy_child_customize_options($options) {
    // Add custom options here
    return $options;
}
add_filter('blocksy:options:general', 'blocksy_child_customize_options');

/**
 * Add body classes for performance optimization
 */
function blocksy_child_body_classes($classes) {
    $classes[] = 'loading';
    
    if (is_page_template('templates/landing-page.php')) {
        $classes[] = 'landing-page';
    }
    
    return $classes;
}
add_filter('body_class', 'blocksy_child_body_classes');

/**
 * Optimize header output
 */
function blocksy_child_header_output() {
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="theme-color" content="#2563eb">
    <link rel="dns-prefetch" href="<?php echo esc_url(home_url('/')); ?>">
    <?php
}
add_action('wp_head', 'blocksy_child_header_output', 0);

/**
 * Add skip link for accessibility
 */
function blocksy_child_skip_link() {
    echo '<a class="skip-link visually-hidden" href="#main-content">' . esc_html__('Skip to content', 'blocksy-child') . '</a>';
}
add_action('wp_body_open', 'blocksy_child_skip_link', 1);

/**
 * Customize menu output with performance attributes
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
 * Remove unnecessary header meta
 */
function blocksy_child_clean_head() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
}
add_action('init', 'blocksy_child_clean_head');

/**
 * Add custom Blocksy header/footer templates
 */
function blocksy_child_custom_header_footer() {
    // This hook allows customization of Blocksy header/footer
    // You can add custom header/footer logic here
}
add_action('blocksy:header:after', 'blocksy_child_custom_header_footer');

/**
 * Optimize archive queries for better performance
 */
function blocksy_child_optimize_queries($query) {
    if (!is_admin() && $query->is_main_query()) {
        // Limit posts per page for better performance
        if (is_archive() || is_home()) {
            $query->set('posts_per_page', 12);
        }
    }
}
add_action('pre_get_posts', 'blocksy_child_optimize_queries');

/**
 * Add performance hints to REST API responses
 */
function blocksy_child_rest_performance_headers() {
    header('Cache-Control: max-age=3600');
}
add_action('rest_api_init', function() {
    add_action('rest_pre_serve_request', 'blocksy_child_rest_performance_headers');
});

/**
 * Disable admin bar on frontend for better performance
 */
add_filter('show_admin_bar', function($show) {
    return current_user_can('administrator') ? $show : false;
});

/**
 * Custom excerpt length
 */
function blocksy_child_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'blocksy_child_excerpt_length');

/**
 * Add schema.org markup for better SEO
 */
function blocksy_child_schema_markup() {
    if (is_front_page() || is_home()) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => home_url('/')
        ];
        
        echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
    }
}
add_action('wp_footer', 'blocksy_child_schema_markup', 99);
