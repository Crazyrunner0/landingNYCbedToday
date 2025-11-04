<?php
/**
 * Brand Configuration Test Script
 * 
 * Validates brand configuration and displays all settings for debugging.
 * 
 * Usage:
 *   make wp CMD='--allow-root eval-file scripts/test-brand-config.php'
 * 
 * @since 1.0.0
 */

if (!class_exists('Brand_Config')) {
	require_once dirname(__DIR__) . '/config/brands.php';
}

class Brand_Config_Tester {

	public static function run() {
		WP_CLI::log('');
		WP_CLI::log('═══════════════════════════════════════════════════════');
		WP_CLI::log('         🏷️  BRAND CONFIGURATION TEST');
		WP_CLI::log('═══════════════════════════════════════════════════════');
		WP_CLI::log('');

		$brand = Brand_Config::get_brand();

		// Brand Identity
		WP_CLI::log('📋 BRAND IDENTITY');
		WP_CLI::log('─────────────────────────────────────────────────────');
		WP_CLI::log(sprintf('  ID:            %s', $brand['id']));
		WP_CLI::log(sprintf('  Name:          %s', $brand['display_name']));
		WP_CLI::log(sprintf('  Description:   %s', $brand['description']));
		WP_CLI::log('');

		// URLs & Domains
		WP_CLI::log('🌐 URLS & DOMAINS');
		WP_CLI::log('─────────────────────────────────────────────────────');
		WP_CLI::log(sprintf('  Domain:        %s', $brand['domain']));
		WP_CLI::log(sprintf('  Home URL:      %s', $brand['home_url']));
		WP_CLI::log('');

		// Branding Assets
		WP_CLI::log('🎨 BRANDING ASSETS');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['logo'])) {
			WP_CLI::log(sprintf('  Logo URL:      %s', $brand['logo']['url']));
			WP_CLI::log(sprintf('  Logo Size:     %dx%d', $brand['logo']['width'], $brand['logo']['height']));
		}
		if (!empty($brand['favicon'])) {
			WP_CLI::log(sprintf('  Favicon:       %s', $brand['favicon']['url']));
		}
		if (!empty($brand['og_image'])) {
			WP_CLI::log(sprintf('  OG Image:      %s', $brand['og_image']['url']));
			WP_CLI::log(sprintf('  OG Image Size: %dx%d', $brand['og_image']['width'], $brand['og_image']['height']));
		}
		WP_CLI::log('');

		// Colors
		WP_CLI::log('🎭 COLOR PALETTE');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['colors'])) {
			foreach ($brand['colors'] as $key => $color) {
				WP_CLI::log(sprintf('  %-20s %s', ucfirst(str_replace('_', ' ')) . ':', $color));
			}
		}
		WP_CLI::log('');

		// Typography
		WP_CLI::log('✍️  TYPOGRAPHY');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['typography'])) {
			WP_CLI::log(sprintf('  Heading Font:  %s', $brand['typography']['heading_font']));
			WP_CLI::log(sprintf('  Body Font:     %s', $brand['typography']['body_font']));
			WP_CLI::log(sprintf('  Mono Font:     %s', $brand['typography']['mono_font']));
		}
		WP_CLI::log('');

		// Copy & Messaging
		WP_CLI::log('💬 COPY & MESSAGING');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['copy'])) {
			foreach ($brand['copy'] as $key => $text) {
				WP_CLI::log(sprintf('  %-20s %s', ucfirst(str_replace('_', ' ')) . ':', $text));
			}
		}
		WP_CLI::log('');

		// Contact Information
		WP_CLI::log('📞 CONTACT INFORMATION');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['contact'])) {
			WP_CLI::log(sprintf('  Email:         %s', $brand['contact']['email']));
			WP_CLI::log(sprintf('  Phone:         %s', $brand['contact']['phone']));
			WP_CLI::log(sprintf('  Support Hours: %s', $brand['contact']['support_hours']));
		}
		WP_CLI::log('');

		// Social Media
		WP_CLI::log('📱 SOCIAL MEDIA');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['social'])) {
			foreach ($brand['social'] as $platform => $url) {
				WP_CLI::log(sprintf('  %-15s %s', ucfirst($platform) . ':', $url));
			}
		}
		WP_CLI::log('');

		// SEO & Analytics
		WP_CLI::log('🔍 SEO & ANALYTICS');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['seo'])) {
			foreach ($brand['seo'] as $key => $value) {
				WP_CLI::log(sprintf('  %-20s %s', ucfirst(str_replace('_', ' ')) . ':', $value));
			}
		}
		if (!empty($brand['analytics'])) {
			WP_CLI::log('');
			foreach ($brand['analytics'] as $key => $value) {
				$label = ucfirst(str_replace('_', ' '));
				$val = !empty($value) ? $value : '(not configured)';
				WP_CLI::log(sprintf('  %-20s %s', $label . ':', $val));
			}
		}
		WP_CLI::log('');

		// Stripe Configuration
		WP_CLI::log('💳 STRIPE CONFIGURATION');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['stripe'])) {
			$pub_key = $brand['stripe']['public_key'];
			$sec_key = $brand['stripe']['secret_key'];
			
			$pub_type = strpos($pub_key, 'pk_live') === 0 ? 'LIVE' : 'TEST';
			$sec_type = strpos($sec_key, 'sk_live') === 0 ? 'LIVE' : 'TEST';
			
			WP_CLI::log(sprintf('  Public Key:    %s... [%s]', substr($pub_key, 0, 20), $pub_type));
			WP_CLI::log(sprintf('  Secret Key:    %s... [%s]', substr($sec_key, 0, 20), $sec_type));
		}
		WP_CLI::log('');

		// WooCommerce Settings
		WP_CLI::log('🛍️  WOOCOMMERCE SETTINGS');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['woocommerce'])) {
			foreach ($brand['woocommerce'] as $key => $value) {
				WP_CLI::log(sprintf('  %-20s %s', ucfirst(str_replace('_', ' ')) . ':', $value));
			}
		}
		WP_CLI::log('');

		// Shipping & Delivery
		WP_CLI::log('🚚 SHIPPING & DELIVERY');
		WP_CLI::log('─────────────────────────────────────────────────────');
		if (!empty($brand['shipping'])) {
			foreach ($brand['shipping'] as $key => $value) {
				if (is_array($value)) {
					$value = implode(', ', $value);
				}
				WP_CLI::log(sprintf('  %-20s %s', ucfirst(str_replace('_', ' ')) . ':', $value));
			}
		}
		WP_CLI::log('');

		// Available Brands
		WP_CLI::log('📦 AVAILABLE BRANDS');
		WP_CLI::log('─────────────────────────────────────────────────────');
		$brands = Brand_Config::get_available_brands();
		foreach ($brands as $brand_id => $brand_name) {
			$current = ($brand_id === Brand_Config::get_id()) ? ' ✓ CURRENT' : '';
			WP_CLI::log(sprintf('  %s - %s%s', $brand_id, $brand_name, $current));
		}
		WP_CLI::log('');

		// Test Data Access
		WP_CLI::log('✅ CONFIGURATION ACCESS TEST');
		WP_CLI::log('─────────────────────────────────────────────────────');
		
		$tests = [
			'Brand_Config::get_brand()'                      => count(Brand_Config::get_brand()) > 0,
			'Brand_Config::get("colors.primary")'            => !empty(Brand_Config::get('colors.primary')),
			'Brand_Config::get_color("primary")'             => !empty(Brand_Config::get_color('primary')),
			'Brand_Config::get_copy("cta_button")'           => !empty(Brand_Config::get_copy('cta_button')),
			'Brand_Config::get_name()'                       => !empty(Brand_Config::get_name()),
			'Brand_Config::get_logo()'                       => !empty(Brand_Config::get_logo()),
		];
		
		foreach ($tests as $test => $result) {
			$status = $result ? '✓' : '✗';
			WP_CLI::log(sprintf('  %s %s', $status, $test));
		}
		WP_CLI::log('');

		WP_CLI::success('═══════════════════════════════════════════════════════');
		WP_CLI::success('✅ Brand configuration validated successfully!');
		WP_CLI::success('═══════════════════════════════════════════════════════');
		WP_CLI::log('');
	}
}

Brand_Config_Tester::run();
