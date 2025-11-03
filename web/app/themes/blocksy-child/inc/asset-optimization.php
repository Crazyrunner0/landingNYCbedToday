<?php
/**
 * Asset Optimization
 * Optimizes loading of scripts and styles for better performance
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Defer non-critical JavaScript
 */
function blocksy_child_defer_scripts($tag, $handle, $src) {
    // Skip admin and login pages
    if (is_admin() || is_login()) {
        return $tag;
    }

    // Scripts that should not be deferred
    $skip_defer = apply_filters('blocksy_child_skip_defer_scripts', [
        'jquery',
        'jquery-core',
        'jquery-migrate'
    ]);

    if (in_array($handle, $skip_defer, true)) {
        return $tag;
    }

    // Add defer attribute
    if (strpos($tag, 'defer') === false && strpos($tag, 'async') === false) {
        $tag = str_replace(' src=', ' defer src=', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'blocksy_child_defer_scripts', 10, 3);

/**
 * Remove unused assets
 */
function blocksy_child_dequeue_unused_assets() {
    // Remove block library CSS if not using blocks on frontend
    if (!is_admin() && !is_singular()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-block-style');
    }

    // Remove global styles if not needed
    if (!current_theme_supports('wp-block-styles')) {
        wp_dequeue_style('global-styles');
    }

    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('wp_enqueue_scripts', 'blocksy_child_dequeue_unused_assets', 100);

/**
 * Disable WordPress emoji
 */
function blocksy_child_disable_emojis() {
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    
    add_filter('tiny_mce_plugins', function($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    });
    
    add_filter('wp_resource_hints', function($urls, $relation_type) {
        if ('dns-prefetch' === $relation_type) {
            $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/');
            $urls = array_diff($urls, [$emoji_svg_url]);
        }
        return $urls;
    }, 10, 2);
}
add_action('init', 'blocksy_child_disable_emojis');

/**
 * Remove query strings from static resources
 */
function blocksy_child_remove_query_strings($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('script_loader_src', 'blocksy_child_remove_query_strings', 15);
add_filter('style_loader_src', 'blocksy_child_remove_query_strings', 15);

/**
 * Add performance headers
 */
function blocksy_child_performance_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
    }
}
add_action('send_headers', 'blocksy_child_performance_headers');

/**
 * Optimize jQuery delivery
 */
function blocksy_child_optimize_jquery() {
    if (!is_admin() && !is_customize_preview()) {
        // Move jQuery to footer
        wp_scripts()->add_data('jquery', 'group', 1);
        wp_scripts()->add_data('jquery-core', 'group', 1);
        wp_scripts()->add_data('jquery-migrate', 'group', 1);
    }
}
add_action('wp_enqueue_scripts', 'blocksy_child_optimize_jquery', 1);

/**
 * Lazy load images
 */
function blocksy_child_add_lazy_loading($content) {
    if (is_admin() || is_feed() || wp_doing_ajax()) {
        return $content;
    }

    $content = preg_replace_callback('/<img([^>]+?)>/', function($matches) {
        $img_tag = $matches[0];
        
        // Skip if already has loading attribute
        if (strpos($img_tag, 'loading=') !== false) {
            return $img_tag;
        }
        
        // Add loading="lazy" attribute
        $img_tag = str_replace('<img', '<img loading="lazy"', $img_tag);
        
        return $img_tag;
    }, $content);
    
    return $content;
}
add_filter('the_content', 'blocksy_child_add_lazy_loading', 20);
add_filter('post_thumbnail_html', 'blocksy_child_add_lazy_loading', 20);

/**
 * Conditionally load assets based on page template
 */
function blocksy_child_conditional_assets() {
    // Remove unnecessary assets on landing pages
    if (is_page_template('templates/landing-page.php')) {
        // Remove comment reply script if not needed
        wp_deregister_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'blocksy_child_conditional_assets', 99);
