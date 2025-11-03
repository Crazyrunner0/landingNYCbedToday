<?php
/**
 * Plugin Name: NYC Bed Today Same-Day Logistics
 * Plugin URI: https://nycbedtoday.com
 * Description: Same-day delivery logistics management with ZIP whitelist, time slot booking, and WooCommerce integration.
 * Version: 1.0.0
 * Author: NYC Bed Today
 * Text Domain: nycbedtoday-logistics
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('NYCBEDTODAY_LOGISTICS_VERSION', '1.0.0');
define('NYCBEDTODAY_LOGISTICS_PATH', __DIR__);
define('NYCBEDTODAY_LOGISTICS_URL', plugins_url('', __FILE__));

require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/class-settings.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/class-zip-manager.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/class-delivery-slots.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/class-slot-generator.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/class-slot-reservation.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/class-public-api.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/class-woocommerce-integration.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/shortcodes.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/blocks.php';
require_once NYCBEDTODAY_LOGISTICS_PATH . '/includes/class-cli-commands.php';

function nycbedtoday_logistics_init() {
    NYCBEDTODAY_Logistics_Settings::init();
    NYCBEDTODAY_Logistics_ZIP_Manager::init();
    NYCBEDTODAY_Logistics_Delivery_Slots::init();
    NYCBEDTODAY_Logistics_Slot_Generator::init();
    NYCBEDTODAY_Logistics_Slot_Reservation::init();
    NYCBEDTODAY_Logistics_Public_API::init();
    
    if (class_exists('WooCommerce')) {
        NYCBEDTODAY_Logistics_WooCommerce_Integration::init();
    }
    
    nycbedtoday_logistics_register_shortcodes();
    nycbedtoday_logistics_register_blocks();
}

add_action('plugins_loaded', 'nycbedtoday_logistics_init');
add_action('nycbt_generate_delivery_slots', 'nycbedtoday_logistics_generate_slots_cron');

function nycbedtoday_logistics_generate_slots_cron() {
    NYCBEDTODAY_Logistics_Delivery_Slots::generate_slots(date('Y-m-d', strtotime('+30 days')), date('Y-m-d', strtotime('+31 days')));
}

register_activation_hook(__FILE__, 'nycbedtoday_logistics_activate');
function nycbedtoday_logistics_activate() {
    NYCBEDTODAY_Logistics_Settings::activate();
    NYCBEDTODAY_Logistics_ZIP_Manager::seed_default_zips();
    NYCBEDTODAY_Logistics_Delivery_Slots::create_table();
    NYCBEDTODAY_Logistics_Slot_Reservation::create_table();
    NYCBEDTODAY_Logistics_Delivery_Slots::generate_slots(current_time('Y-m-d'), date('Y-m-d', strtotime('+30 days')));
}

register_deactivation_hook(__FILE__, 'nycbedtoday_logistics_deactivate');
function nycbedtoday_logistics_deactivate() {
}
