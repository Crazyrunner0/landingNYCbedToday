<?php
/**
 * Plugin Name: Stripe Live Switch
 * Description: Automatically switches Stripe from test to live keys based on WP_ENV
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

class Stripe_Live_Switch {

	private static $instance = null;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('plugins_loaded', [$this, 'configure_stripe_keys']);
	}

	/**
	 * Configure Stripe keys based on environment
	 *
	 * In production (WP_ENV=production), uses live keys.
	 * In all other environments, uses test keys.
	 */
	public function configure_stripe_keys() {
		// Verify WooCommerce and Stripe are available
		if (!class_exists('WooCommerce') || !class_exists('WC_Stripe')) {
			return;
		}

		// Get current environment
		$env = defined('WP_ENV') ? WP_ENV : 'development';
		$is_production = 'production' === $env;

		// Determine which keys to use
		if ($is_production) {
			$publishable_key = env('STRIPE_LIVE_PUBLIC_KEY');
			$secret_key = env('STRIPE_LIVE_SECRET_KEY');
			$test_mode = false;
		} else {
			$publishable_key = env('STRIPE_TEST_PUBLIC_KEY');
			$secret_key = env('STRIPE_TEST_SECRET_KEY');
			$test_mode = true;
		}

		// If keys are not provided, skip configuration
		if (empty($publishable_key) || empty($secret_key)) {
			error_log(
				sprintf(
					'Stripe keys not configured for %s environment. Expected %s keys.',
					$env,
					$is_production ? 'LIVE' : 'TEST'
				)
			);
			return;
		}

		// Get current Stripe settings
		$stripe_settings = get_option('woocommerce_stripe_settings', []);

		// Update Stripe settings with appropriate keys
		$updated_settings = array_merge(
			$stripe_settings,
			[
				'enabled'                           => 'yes',
				'testmode'                          => $test_mode ? 'yes' : 'no',
				'test_publishable_key'              => $publishable_key,
				'test_secret_key'                   => $secret_key,
				'publishable_key'                   => $publishable_key,
				'secret_key'                        => $secret_key,
				'payment_request'                   => 'yes',
				'payment_request_button_type'       => 'buy',
				'payment_request_button_theme'      => 'dark',
				'payment_request_button_locations'  => ['product', 'checkout'],
				'statement_descriptor'              => 'NYC Bed Today',
			]
		);

		// Only update if keys actually changed (to avoid unnecessary option updates)
		if ($updated_settings !== $stripe_settings) {
			update_option('woocommerce_stripe_settings', $updated_settings);

			// Log the change
			error_log(
				sprintf(
					'Stripe keys updated for %s environment (test_mode: %s)',
					$env,
					$test_mode ? 'yes' : 'no'
				)
			);
		}

		// Ensure Payment Request Buttons (Apple Pay, Google Pay) are enabled
		$this->ensure_payment_request_buttons_enabled();
	}

	/**
	 * Verify Payment Request Buttons are enabled for Apple Pay / Google Pay
	 */
	private function ensure_payment_request_buttons_enabled() {
		$stripe_settings = get_option('woocommerce_stripe_settings', []);

		if (empty($stripe_settings['payment_request']) || 'yes' !== $stripe_settings['payment_request']) {
			$stripe_settings['payment_request'] = 'yes';
			update_option('woocommerce_stripe_settings', $stripe_settings);
		}
	}
}

// Initialize
Stripe_Live_Switch::get_instance();
