<?php

/**
 * Plugin Name: Redis Object Cache
 * Description: Enable Redis object cache for WordPress
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enable Redis object cache if Redis is available
if (extension_loaded('redis')) {
    global $redis_server;
    $redis_server = [
        'host' => getenv('REDIS_HOST') ?: 'redis',
        'port' => getenv('REDIS_PORT') ?: 6379,
    ];
}
