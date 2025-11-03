<?php
/**
 * Plugin Name: Stripe Test Setup
 * Description: Configures WooCommerce Stripe gateway in test mode with Payment Request Buttons
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

class Stripe_Test_Setup {

	private static $instance = null;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Ensure Stripe gateway is activated
		add_action('wp_loaded', [$this, 'ensure_stripe_activated']);
		// Configure Stripe on plugins_loaded
		add_action('plugins_loaded', [$this, 'configure_stripe']);
	}

	/**
	 * Ensure WooCommerce Stripe gateway plugin is activated
	 */
	public function ensure_stripe_activated() {
		// Check if Stripe is already active
		if (is_plugin_active('woocommerce-gateway-stripe/woocommerce-gateway-stripe.php')) {
			return;
		}

		// Check if the plugin file exists
		$plugin_file = 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php';
		if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
			// Plugin hasn't been installed yet via Composer
			return;
		}

		// Activate the plugin
		activate_plugin($plugin_file);
	}

	/**
	 * Configure Stripe gateway for test mode with Payment Request Buttons
	 */
	public function configure_stripe() {
		// Verify WooCommerce and Stripe are available
		if (!class_exists('WooCommerce') || !class_exists('WC_Stripe')) {
			return;
		}

		// Check if Stripe gateway is already configured
		$stripe_settings = get_option('woocommerce_stripe_settings', []);

		// Only configure if not already set up or if keys are empty
		if (!empty($stripe_settings) && isset($stripe_settings['enabled']) && $stripe_settings['enabled'] === 'yes') {
			// Stripe is already configured, skip
			return;
		}

		// Get keys from environment variables
		$test_public_key = env('STRIPE_TEST_PUBLIC_KEY');
		$test_secret_key = env('STRIPE_TEST_SECRET_KEY');

		// If keys are not provided, skip configuration
		if (empty($test_public_key) || empty($test_secret_key)) {
			return;
		}

		// Configure Stripe settings
		$stripe_settings = array_merge($stripe_settings, [
			'enabled'                           => 'yes',
			'title'                             => 'Credit Card',
			'description'                       => 'Pay securely with your credit card or digital wallet.',
			'testmode'                          => 'yes',
			'test_publishable_key'              => $test_public_key,
			'test_secret_key'                   => $test_secret_key,
			'publishable_key'                   => $test_public_key,
			'secret_key'                        => $test_secret_key,
			'payment_request'                   => 'yes',
			'payment_request_button_type'       => 'buy',
			'payment_request_button_theme'      => 'dark',
			'payment_request_button_locations'  => ['product', 'checkout'],
			'statement_descriptor'              => 'NYC Bed Today',
		]);

		// Update the settings
		update_option('woocommerce_stripe_settings', $stripe_settings);
	}
}

// Initialize
Stripe_Test_Setup::get_instance();
