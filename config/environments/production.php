<?php
/**
 * Production environment configuration
 *
 * Includes security hardening, caching, and performance optimizations
 */

use Roots\WPConfig\Config;
use function Env\env;

/**
 * Security Settings
 */
Config::define('WP_DEBUG', false);
Config::define('WP_DEBUG_LOG', env('WP_DEBUG_LOG') ?: true);
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('SCRIPT_DEBUG', false);

/**
 * Object Cache (Redis)
 * Make sure Redis is running and WP Redis plugin is active
 */
// Redis is configured via wp-redis plugin activated in wp-config
// Connection details are set in WP-CLI config if needed

/**
 * WordPress Cron
 * Disable WP-Cron and use system cron instead
 * Add to crontab: */5 * * * * curl -s https://nycbedtoday.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
 */
Config::define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: true);

/**
 * File and Plugin/Theme Updates
 * Disable automatic updates and file modifications
 */
Config::define('AUTOMATIC_UPDATER_DISABLED', true);
Config::define('DISALLOW_FILE_MODS', true);
Config::define('DISALLOW_FILE_EDIT', true);

/**
 * Disable search engine indexing if needed (e.g., for staging)
 * Can be overridden via environment variable
 */
if (env('DISALLOW_INDEXING')) {
    Config::define('DISALLOW_INDEXING', true);
}

/**
 * SSL/HTTPS Configuration
 * Cloudflare handles SSL termination
 * X-Forwarded-Proto is set by Cloudflare
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

/**
 * Cache Headers for CloudFlare and Browser
 * Set appropriate cache control headers for assets
 */
if (defined('DOING_AJAX')) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
} elseif (defined('WP_DEBUG') && !WP_DEBUG) {
    // Allow aggressive caching in production
    header('Cache-Control: public, max-age=31536000'); // 1 year for versioned assets
}

/**
 * Memory Limits
 * Increase for production to handle larger operations
 */
Config::define('WP_MEMORY_LIMIT', '256M');
Config::define('WP_MAX_MEMORY_LIMIT', '512M');

/**
 * Database Optimization
 * Use InnoDB engine where possible
 */
Config::define('DB_COLLATE', 'utf8mb4_unicode_ci');

/**
 * WooCommerce Settings
 * Configured in WooCommerce admin, but defaults ensure consistency
 */
Config::define('WC_DISABLE_ALL_RULES_UI', false);

/**
 * Backup and Recovery
 * Document daily backup strategy in ops-runbook
 */
// Backups should be configured via:
// 1. Automated backup service (e.g., UpdraftPlus with S3)
// 2. System cron: mysqldump + remote upload
// 3. Database replication

/**
 * Error Logging
 * Errors logged to file instead of displayed
 */
ini_set('log_errors', '1');
ini_set('error_log', WP_CONTENT_DIR . '/debug.log');

/**
 * Performance Monitoring
 * APM and performance tracking configured via plugins
 */
// New Relic APM or similar can be configured here
// add_filter('wp_new_user_approve_filter_caps', ...);
