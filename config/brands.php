<?php
/**
 * Brand Configuration Registry & Loader
 * 
 * Central hub for all brand-specific configurations.
 * Determines which brand is active via WP_BRAND environment variable.
 * Provides utilities for accessing brand settings throughout the application.
 * 
 * Usage:
 *   $brand = Brand_Config::get_brand();           // Get current brand config
 *   $logo = Brand_Config::get( 'logo.url' );      // Get specific setting (dot notation)
 *   $color = Brand_Config::get_color( 'primary' ); // Get color value
 *   $copy = Brand_Config::get_copy( 'cta_button' ); // Get copy text
 * 
 * @since 1.0.0
 */

if (!class_exists('Brand_Config')) {

	class Brand_Config {

		/**
		 * List of available brands
		 * 
		 * @var array
		 */
		private static $brands = [
			'nook-dresser-today'   => 'NookDresserToday',
			'nook-mattress-today'  => 'NookMattressToday',
		];

		/**
		 * Current brand configuration
		 * 
		 * @var array
		 */
		private static $config = [];

		/**
		 * Initialize brand configuration
		 * 
		 * Called automatically on first access.
		 * Loads the appropriate brand config based on WP_BRAND environment variable.
		 * 
		 * @return void
		 */
		public static function init() {
			if (!empty(self::$config)) {
				return; // Already initialized
			}

			$brand_id = self::get_brand_id();

			if (!file_exists(__DIR__ . "/brands/{$brand_id}.php")) {
				// Fallback to base if brand config not found
				$brand_id = 'base';
			}

			self::$config = include __DIR__ . "/brands/{$brand_id}.php";

			// Apply filters for extensibility
			self::$config = apply_filters('brand_config', self::$config, $brand_id);
		}

		/**
		 * Get the active brand ID
		 * 
		 * Checks (in order):
		 * 1. WP_BRAND constant
		 * 2. WP_BRAND environment variable
		 * 3. Default to 'nook-dresser-today'
		 * 
		 * @return string
		 */
		public static function get_brand_id() {
			if (defined('WP_BRAND')) {
				return WP_BRAND;
			}

			$brand = getenv('WP_BRAND');
			if ($brand) {
				return $brand;
			}

			// Default to first brand if not specified
			return 'nook-dresser-today';
		}

		/**
		 * Get complete brand configuration
		 * 
		 * @return array
		 */
		public static function get_brand() {
			self::init();
			return self::$config;
		}

		/**
		 * Get a configuration value using dot notation
		 * 
		 * Examples:
		 *   get( 'name' )                    // Top-level key
		 *   get( 'logo.url' )                // Nested key
		 *   get( 'colors.primary' )          // Deep nested
		 *   get( 'nonexistent', 'default' )  // With default
		 * 
		 * @param string $key     Config key (supports dot notation)
		 * @param mixed  $default Default value if key not found
		 * 
		 * @return mixed
		 */
		public static function get($key, $default = null) {
			self::init();

			if (strpos($key, '.') === false) {
				return self::$config[$key] ?? $default;
			}

			$keys = explode('.', $key);
			$value = self::$config;

			foreach ($keys as $k) {
				if (!is_array($value) || !isset($value[$k])) {
					return $default;
				}
				$value = $value[$k];
			}

			return $value;
		}

		/**
		 * Get a color from the brand palette
		 * 
		 * @param string $key     Color key (e.g., 'primary', 'secondary')
		 * @param string $default Default color if key not found
		 * 
		 * @return string Hex color code
		 */
		public static function get_color($key, $default = '#000000') {
			return self::get("colors.{$key}", $default);
		}

		/**
		 * Get copy/messaging text
		 * 
		 * @param string $key     Copy key (e.g., 'cta_button')
		 * @param string $default Default text if key not found
		 * 
		 * @return string
		 */
		public static function get_copy($key, $default = '') {
			return self::get("copy.{$key}", $default);
		}

		/**
		 * Get brand name for display
		 * 
		 * @return string
		 */
		public static function get_name() {
			return self::get('display_name', 'Brand');
		}

		/**
		 * Get brand ID
		 * 
		 * @return string
		 */
		public static function get_id() {
			return self::get('id', 'base');
		}

		/**
		 * Get logo configuration
		 * 
		 * @return array
		 */
		public static function get_logo() {
			return self::get('logo', []);
		}

		/**
		 * Get all available brands
		 * 
		 * @return array
		 */
		public static function get_available_brands() {
			return self::$brands;
		}

		/**
		 * Check if brand exists
		 * 
		 * @param string $brand_id
		 * 
		 * @return bool
		 */
		public static function brand_exists($brand_id) {
			return isset(self::$brands[$brand_id]) || file_exists(__DIR__ . "/brands/{$brand_id}.php");
		}
	}

}

// Initialize on load
Brand_Config::init();
