<?php
/**
 * Font Preload Handler
 * Preloads critical fonts for faster text rendering
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Preload critical fonts
 */
function blocksy_child_preload_fonts() {
    $fonts_to_preload = apply_filters('blocksy_child_preload_fonts', [
        // Add your font files here when you have them
        // Example:
        // [
        //     'href' => get_stylesheet_directory_uri() . '/assets/fonts/inter-v12-latin-regular.woff2',
        //     'type' => 'font/woff2',
        //     'crossorigin' => 'anonymous'
        // ]
    ]);

    foreach ($fonts_to_preload as $font) {
        printf(
            '<link rel="preload" href="%s" as="font" type="%s" crossorigin="%s">',
            esc_url($font['href']),
            esc_attr($font['type']),
            esc_attr($font['crossorigin'] ?? 'anonymous')
        );
    }
}
add_action('wp_head', 'blocksy_child_preload_fonts', 1);

/**
 * Add font-display: swap to Google Fonts if used
 */
function blocksy_child_optimize_google_fonts($html, $handle) {
    if (strpos($handle, 'google-fonts') !== false || strpos($html, 'fonts.googleapis.com') !== false) {
        $html = str_replace("rel='stylesheet'", "rel='stylesheet' media='print' onload=\"this.media='all'\"", $html);
    }
    return $html;
}
add_filter('style_loader_tag', 'blocksy_child_optimize_google_fonts', 10, 2);

/**
 * Preconnect to Google Fonts domains
 */
function blocksy_child_font_preconnect() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php
}
add_action('wp_head', 'blocksy_child_font_preconnect', 0);

/**
 * Add resource hints for DNS prefetch
 */
function blocksy_child_resource_hints($urls, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        $urls[] = '//fonts.googleapis.com';
        $urls[] = '//fonts.gstatic.com';
    }
    
    if ('preconnect' === $relation_type) {
        $urls[] = [
            'href' => 'https://fonts.googleapis.com',
            'crossorigin' => 'anonymous'
        ];
        $urls[] = [
            'href' => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous'
        ];
    }
    
    return $urls;
}
add_filter('wp_resource_hints', 'blocksy_child_resource_hints', 10, 2);
