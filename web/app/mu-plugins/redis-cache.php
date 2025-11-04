<?php

/**
 * Plugin Name: Redis Object Cache
 * Description: Enable Redis object cache for WordPress with health checks and diagnostics
 * Version: 1.0.0
 * Author: Platform Team
 */

if (!defined('ABSPATH')) {
    exit;
}

// Configuration
define('REDIS_CACHE_HOST', getenv('REDIS_HOST') ?: 'redis');
define('REDIS_CACHE_PORT', getenv('REDIS_PORT') ?: 6379);
define('REDIS_CACHE_PASSWORD', getenv('REDIS_PASSWORD') ?: null);
define('REDIS_CACHE_DB', getenv('REDIS_CACHE_DB') ?: 0);

// Enable Redis object cache if Redis extension is available
if (extension_loaded('redis')) {
    global $redis_server;
    $redis_server = [
        'host' => REDIS_CACHE_HOST,
        'port' => REDIS_CACHE_PORT,
        'password' => REDIS_CACHE_PASSWORD,
    ];

    // Initialize Redis connection
    try {
        $redis = new Redis();
        $redis->connect(REDIS_CACHE_HOST, REDIS_CACHE_PORT, 1, null, 1);
        
        if (REDIS_CACHE_PASSWORD) {
            $redis->auth(REDIS_CACHE_PASSWORD);
        }
        
        $redis->select(REDIS_CACHE_DB);
        
        // Test connection
        $redis->ping();
        
        // Cache is available
        define('WP_REDIS_ENABLED', true);
        
        // Store Redis instance for later use
        $GLOBALS['redis_cache_connection'] = $redis;
    } catch (Exception $e) {
        // Redis connection failed
        define('WP_REDIS_ENABLED', false);
        error_log('Redis connection failed: ' . $e->getMessage());
    }
} else {
    define('WP_REDIS_ENABLED', false);
}

/**
 * Health check function for Redis cache
 * Can be called via WP-CLI or accessed programmatically
 */
function blocksy_redis_health_check() {
    if (!defined('WP_REDIS_ENABLED')) {
        return [
            'status' => 'error',
            'message' => 'Redis extension not loaded'
        ];
    }

    if (!WP_REDIS_ENABLED) {
        return [
            'status' => 'error',
            'message' => 'Redis connection failed'
        ];
    }

    try {
        $redis = new Redis();
        $redis->connect(REDIS_CACHE_HOST, REDIS_CACHE_PORT, 1);
        
        if (REDIS_CACHE_PASSWORD) {
            $redis->auth(REDIS_CACHE_PASSWORD);
        }
        
        $redis->select(REDIS_CACHE_DB);
        
        // Get Redis info
        $info = $redis->info();
        $ping = $redis->ping();
        
        if ($ping === '+PONG' || $ping === true) {
            return [
                'status' => 'healthy',
                'message' => 'Redis cache is operational',
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 'unknown',
                'commands' => [
                    'check' => 'wp redis health-check',
                    'flush' => 'wp cache flush'
                ]
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Redis health check failed: ' . $e->getMessage()
        ];
    }

    return [
        'status' => 'error',
        'message' => 'Unknown Redis status'
    ];
}

/**
 * Register WP-CLI command for Redis health check
 */
if (defined('WP_CLI') && WP_CLI) {
    class Redis_Health_Check_Command {
        /**
         * Check Redis cache health status
         */
        public function health_check($args, $assoc_args) {
            $health = blocksy_redis_health_check();
            
            if ($health['status'] === 'healthy') {
                WP_CLI::success($health['message']);
                WP_CLI::line('Redis Version: ' . $health['redis_version']);
                WP_CLI::line('Used Memory: ' . $health['used_memory']);
                WP_CLI::line('Connected Clients: ' . $health['connected_clients']);
            } else {
                WP_CLI::error($health['message']);
            }
        }

        /**
         * Get Redis cache statistics
         */
        public function stats($args, $assoc_args) {
            if (!WP_REDIS_ENABLED) {
                WP_CLI::error('Redis is not enabled');
                return;
            }

            try {
                $redis = new Redis();
                $redis->connect(REDIS_CACHE_HOST, REDIS_CACHE_PORT);
                
                if (REDIS_CACHE_PASSWORD) {
                    $redis->auth(REDIS_CACHE_PASSWORD);
                }
                
                $redis->select(REDIS_CACHE_DB);
                $info = $redis->info();
                
                WP_CLI::line('=== Redis Statistics ===');
                WP_CLI::line('Version: ' . ($info['redis_version'] ?? 'N/A'));
                WP_CLI::line('Used Memory: ' . ($info['used_memory_human'] ?? 'N/A'));
                WP_CLI::line('Peak Memory: ' . ($info['used_memory_peak_human'] ?? 'N/A'));
                WP_CLI::line('Connected Clients: ' . ($info['connected_clients'] ?? 'N/A'));
                WP_CLI::line('Total Commands: ' . ($info['total_commands_processed'] ?? 'N/A'));
                WP_CLI::line('Uptime: ' . ($info['uptime_in_seconds'] ?? 'N/A') . ' seconds');
            } catch (Exception $e) {
                WP_CLI::error('Failed to get Redis stats: ' . $e->getMessage());
            }
        }
    }

    WP_CLI::add_command('redis', 'Redis_Health_Check_Command');
}

/**
 * Add admin notice if Redis is not available
 */
function blocksy_redis_admin_notice() {
    if (is_admin() && current_user_can('manage_options') && !WP_REDIS_ENABLED) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong>Redis Cache Notice:</strong> Redis is not available or not connected. 
                Object caching will fall back to database. Please verify Redis configuration.
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'blocksy_redis_admin_notice');

/**
 * Log cache operations in debug mode
 */
function blocksy_log_cache_operations() {
    if (defined('WP_DEBUG') && WP_DEBUG && WP_REDIS_ENABLED) {
        // This would log cache operations for debugging
        // Implement as needed for detailed monitoring
    }
}
add_action('init', 'blocksy_log_cache_operations');
