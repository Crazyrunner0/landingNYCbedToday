<?php
/**
 * Critical CSS Handler
 * Inlines critical CSS in the head for faster initial render
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inline critical CSS in head
 */
function blocksy_child_inline_critical_css() {
    $critical_css_file = get_stylesheet_directory() . '/assets/css/critical.css';
    
    if (file_exists($critical_css_file)) {
        $critical_css = file_get_contents($critical_css_file);
        
        if ($critical_css) {
            echo '<style id="critical-css">' . wp_strip_all_tags($critical_css) . '</style>';
        }
    }
}
add_action('wp_head', 'blocksy_child_inline_critical_css', 1);

/**
 * Load non-critical CSS asynchronously
 */
function blocksy_child_async_css() {
    ?>
    <script>
    (function() {
        var css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = '<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/css/main.css'); ?>';
        css.media = 'print';
        css.onload = function() {
            this.media = 'all';
        };
        document.head.appendChild(css);
    })();
    </script>
    <noscript>
        <link rel="stylesheet" href="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/css/main.css'); ?>">
    </noscript>
    <?php
}
add_action('wp_head', 'blocksy_child_async_css', 20);

/**
 * Add preload for critical assets
 */
function blocksy_child_preload_critical_assets() {
    // Preload critical CSS file for browsers that support it
    echo '<link rel="preload" href="' . esc_url(get_stylesheet_directory_uri() . '/assets/css/critical.css') . '" as="style" importance="high">';
}
add_action('wp_head', 'blocksy_child_preload_critical_assets', 0);
