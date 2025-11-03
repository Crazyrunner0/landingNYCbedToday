<?php
/**
 * Plugin Name: WooCommerce Minimal Setup
 * Description: Minimal WooCommerce setup for US-based store with single-page checkout and seeded products
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

class WooCommerce_Minimal_Setup {

	private static $instance = null;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Ensure WooCommerce is activated
		add_action('wp_loaded', [$this, 'ensure_woocommerce_activated']);
		// Initialize on plugins_loaded
		add_action('plugins_loaded', [$this, 'initialize_setup']);
		add_action('admin_init', [$this, 'complete_first_setup']);
	}

	/**
	 * Ensure WooCommerce plugin is activated
	 */
	public function ensure_woocommerce_activated() {
		// Check if WooCommerce is already active
		if (is_plugin_active('woocommerce/woocommerce.php')) {
			return;
		}

		// Check if the plugin file exists
		$plugin_file = 'woocommerce/woocommerce.php';
		if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
			// Plugin hasn't been installed yet via Composer
			return;
		}

		// Activate the plugin
		activate_plugin($plugin_file);
	}

	/**
	 * Initialize WooCommerce setup once it's loaded
	 */
	public function initialize_setup() {
		if (!class_exists('WooCommerce')) {
			return;
		}

		// Configure store settings
		$this->configure_store_settings();

		// Configure checkout fields for single-page experience
		add_filter('woocommerce_checkout_fields', [$this, 'simplify_checkout_fields']);
	}

	/**
	 * Simplify checkout fields for cleaner single-page checkout
	 */
	public function simplify_checkout_fields($fields) {
		// Remove unnecessary billing fields
		if (isset($fields['billing'])) {
			unset($fields['billing']['billing_company']);
			unset($fields['billing']['billing_address_2']);

			// Make phone optional
			if (isset($fields['billing']['billing_phone'])) {
				$fields['billing']['billing_phone']['required'] = false;
			}
		}

		// Simplify shipping fields
		if (isset($fields['shipping'])) {
			unset($fields['shipping']['shipping_company']);
			unset($fields['shipping']['shipping_address_2']);
		}

		return $fields;
	}

	/**
	 * Complete first-time setup on admin pages
	 */
	public function complete_first_setup() {
		if (!class_exists('WooCommerce')) {
			return;
		}

		if (get_option('wc_minimal_setup_complete')) {
			return;
		}

		// Seed products
		$this->seed_products();

		// Set up WooCommerce pages
		$this->setup_woocommerce_pages();

		// Mark setup as complete
		update_option('wc_minimal_setup_complete', true);
	}

	/**
	 * Configure basic WooCommerce store settings
	 */
	private function configure_store_settings() {
		// Set currency to USD
		update_option('woocommerce_currency', 'USD');

		// Set default country to US
		update_option('woocommerce_default_country', 'US');

		// Set store address (minimal required info)
		update_option('woocommerce_store_address', '123 Main Street');
		update_option('woocommerce_store_city', 'New York');
		update_option('woocommerce_store_postcode', '10001');
		update_option('woocommerce_store_state', 'NY');

		// Enable guest checkout
		update_option('woocommerce_enable_guest_checkout', 'yes');

		// Disable order notes field for simpler checkout
		update_option('woocommerce_enable_order_notes_field', 'no');

		// Show terms checkbox on checkout
		update_option('woocommerce_checkout_show_terms', 'yes');

		// Disable account creation notice
		update_option('woocommerce_enable_checkout_login_reminder', 'no');

		// Allow guest and account creation from checkout
		update_option('woocommerce_enable_signup_and_login_from_checkout', 'yes');
		update_option('woocommerce_enable_myaccount_registration', 'yes');

		// Set measurement units (for mattresses)
		update_option('woocommerce_weight_unit', 'lbs');
		update_option('woocommerce_dimension_unit', 'in');

		// Force secure checkout if HTTPS
		if (is_ssl()) {
			update_option('woocommerce_force_ssl_checkout', 'yes');
		}
	}

	/**
	 * Seed four mattress products
	 */
	private function seed_products() {
		$bed_sizes = [
			'Twin'  => 599.00,
			'Full'  => 799.00,
			'Queen' => 999.00,
			'King'  => 1299.00,
		];

		$seeded_products = get_option('wc_minimal_seeded_product_ids', []);

		foreach ($bed_sizes as $size => $price) {
			// Check if product already exists
			$existing = $this->get_product_by_name($size . ' Mattress');
			if ($existing) {
				continue;
			}

			// Create product
			$product = new WC_Product_Simple();
			$product->set_name($size . ' Mattress');
			$product->set_status('publish');
			$product->set_catalog_visibility('visible');
			$product->set_description('Premium ' . $size . ' size mattress with advanced comfort technology.');
			$product->set_short_description('High-quality ' . $size . ' mattress.');
			$product->set_regular_price($price);
			$product->set_manage_stock(true);
			$product->set_stock_quantity(50);
			$product->set_stock_status('instock');

			$product_id = $product->save();

			if ($product_id) {
				$seeded_products[] = $product_id;
			}
		}

		if (!empty($seeded_products)) {
			update_option('wc_minimal_seeded_product_ids', array_unique($seeded_products));
		}
	}

	/**
	 * Helper function to find product by name
	 */
	private function get_product_by_name($product_name) {
		global $wpdb;

		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND post_status = %s",
				$product_name,
				'product',
				'publish'
			)
		);

		return $post_id ? wc_get_product($post_id) : null;
	}

	/**
	 * Set up WooCommerce pages (Shop, Cart, Checkout, My Account)
	 */
	private function setup_woocommerce_pages() {
		$pages = [
			'woocommerce_shop_page_id'      => ['Shop', ''],
			'woocommerce_cart_page_id'      => ['Cart', '[woocommerce_cart]'],
			'woocommerce_checkout_page_id'  => ['Checkout', '[woocommerce_checkout]'],
			'woocommerce_myaccount_page_id' => ['My Account', '[woocommerce_my_account]'],
		];

		foreach ($pages as $option => $page_data) {
			list($title, $content) = $page_data;

			$page_id = get_option($option);

			if (!$page_id || !get_post($page_id)) {
				// Create the page
				$page_id = wp_insert_post([
					'post_title'   => $title,
					'post_content' => $content,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_author'  => 1,
				]);

				if ($page_id && !is_wp_error($page_id)) {
					update_option($option, $page_id);
				}
			}
		}
	}
}

// Initialize
WooCommerce_Minimal_Setup::get_instance();
