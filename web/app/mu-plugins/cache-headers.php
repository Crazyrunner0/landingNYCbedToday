<?php
/**
 * Cache Headers Configuration
 *
 * Sets appropriate cache headers based on environment and content type.
 * Helps with performance optimization and proper caching behavior.
 */

defined('ABSPATH') || exit;

/**
 * Set cache headers for static assets and content
 */
function nycbedtoday_set_cache_headers() {
    if (is_admin() || is_user_logged_in()) {
        return;
    }

    $environment = defined('WP_ENV') ? WP_ENV : 'production';
    
    // Staging: Short cache to allow rapid testing
    if ('staging' === $environment) {
        header('Cache-Control: public, max-age=300, s-maxage=600');
        return;
    }

    // Production: Longer cache times
    if ('production' === $environment) {
        // Cache busting for static assets happens through versioned file names
        // Use long cache times for optimal performance
        if (is_singular(['page', 'post']) || is_front_page()) {
            header('Cache-Control: public, max-age=3600, s-maxage=86400');
        } elseif (is_archive() || is_search()) {
            header('Cache-Control: public, max-age=1800, s-maxage=3600');
        } else {
            header('Cache-Control: public, max-age=300, s-maxage=600');
        }
        return;
    }

    // Development: No cache
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Wed, 21 Oct 2015 07:28:00 GMT');
}

add_action('template_redirect', 'nycbedtoday_set_cache_headers', 1);

/**
 * Set X-Frame-Options and security headers
 */
function nycbedtoday_set_security_headers() {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

add_action('template_redirect', 'nycbedtoday_set_security_headers', 1);
