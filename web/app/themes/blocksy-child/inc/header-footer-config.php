<?php
/**
 * Header and Footer Configuration
 * Customizes Blocksy header and footer for optimal performance
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customize header settings for performance
 */
function blocksy_child_header_config($config) {
    // Add custom header configuration
    $config['performance_mode'] = true;
    
    return $config;
}
add_filter('blocksy:header:config', 'blocksy_child_header_config');

/**
 * Add custom header elements
 */
function blocksy_child_custom_header_elements() {
    ?>
    <!-- Custom header elements can be added here -->
    <?php
}
add_action('blocksy:header:before', 'blocksy_child_custom_header_elements');

/**
 * Customize footer settings
 */
function blocksy_child_footer_config($config) {
    // Add custom footer configuration
    $config['minimal_mode'] = true;
    
    return $config;
}
add_filter('blocksy:footer:config', 'blocksy_child_footer_config');

/**
 * Add custom footer elements
 */
function blocksy_child_custom_footer_elements() {
    ?>
    <div class="footer-custom-content">
        <!-- Custom footer content -->
    </div>
    <?php
}
add_action('blocksy:footer:after', 'blocksy_child_custom_footer_elements');

/**
 * Optimize header scripts
 */
function blocksy_child_optimize_header_scripts() {
    // Remove unnecessary header scripts on specific pages
    if (is_page_template('templates/landing-page.php')) {
        // Add page-specific optimizations
    }
}
add_action('wp_enqueue_scripts', 'blocksy_child_optimize_header_scripts', 99);

/**
 * Add menu descriptions for better accessibility
 */
function blocksy_child_menu_description($item_output, $item, $depth, $args) {
    if ($item->description && $depth === 0) {
        $item_output = str_replace(
            '</a>',
            '<span class="menu-description visually-hidden">' . esc_html($item->description) . '</span></a>',
            $item_output
        );
    }
    
    return $item_output;
}
add_filter('walker_nav_menu_start_el', 'blocksy_child_menu_description', 10, 4);

/**
 * Add structured data for navigation
 */
function blocksy_child_navigation_schema() {
    if (has_nav_menu('primary-menu')) {
        $menu_items = wp_get_nav_menu_items(get_nav_menu_locations()['primary-menu']);
        
        if ($menu_items) {
            $schema_items = [];
            
            foreach ($menu_items as $item) {
                $schema_items[] = [
                    '@type' => 'SiteNavigationElement',
                    'name' => $item->title,
                    'url' => $item->url
                ];
            }
            
            $schema = [
                '@context' => 'https://schema.org',
                '@graph' => $schema_items
            ];
            
            echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
        }
    }
}
add_action('wp_footer', 'blocksy_child_navigation_schema', 98);

/**
 * Mobile menu optimization
 */
function blocksy_child_mobile_menu_config($config) {
    // Configure mobile menu for better performance
    $config['lazy_load'] = true;
    $config['animation'] = 'minimal';
    
    return $config;
}
add_filter('blocksy:mobile-menu:config', 'blocksy_child_mobile_menu_config');
