<?php
/**
 * Plugin Name: NYC Bed Today Blocks
 * Plugin URI: https://nycbedtoday.com
 * Description: Custom Gutenberg blocks for NYC Bed Today marketing pages.
 * Version: 1.0.0
 * Author: NYC Bed Today
 * Text Domain: nycbedtoday-blocks
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

define('NYCBEDTODAY_BLOCKS_VERSION', '1.0.0');
define('NYCBEDTODAY_BLOCKS_PATH', __DIR__);
define('NYCBEDTODAY_BLOCKS_URL', plugins_url('', __FILE__));

require_once NYCBEDTODAY_BLOCKS_PATH . '/includes/blocks.php';
require_once NYCBEDTODAY_BLOCKS_PATH . '/includes/render.php';

add_action('init', 'nycbedtoday_blocks_register');

add_filter('block_categories_all', 'nycbedtoday_blocks_register_category', 10, 2);
