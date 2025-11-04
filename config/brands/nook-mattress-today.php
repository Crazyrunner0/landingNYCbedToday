<?php
/**
 * NookMattressToday Brand Configuration
 * 
 * Mattress-focused brand configuration
 * 
 * @since 1.0.0
 */

return array_merge(
	include __DIR__ . '/base.php',
	[
		// Brand Identifier
		'id'              => 'nook-mattress-today',
		'name'            => 'NookMattressToday',
		'display_name'    => 'Nook Mattress Today',
		'description'     => 'Premium Mattresses & Sleep Solutions',
		
		// Logo & Branding (customize for mattress brand)
		'logo'            => [
			'url'           => '/app/themes/twentytwentyfour/assets/images/nook-mattress-logo.svg',
			'width'         => 200,
			'height'        => 60,
			'alt_text'      => 'Nook Mattress Today Logo',
		],
		'favicon'         => [
			'url'           => '/app/themes/twentytwentyfour/assets/images/nook-mattress-favicon.ico',
		],
		'og_image'        => [
			'url'           => '/app/themes/twentytwentyfour/assets/images/nook-mattress-og.png',
			'width'         => 1200,
			'height'        => 630,
		],
		
		// Copy & Messaging (tailored for mattresses)
		'copy'            => [
			'cta_button'    => 'Shop Mattresses',
			'view_products' => 'View All Mattresses',
			'learn_more'    => 'Learn More',
			'about_us'      => 'About Nook Mattress',
			'contact_us'    => 'Contact Us',
			'shipping_text' => 'Free shipping & 120-night trial on all mattresses',
		],
		
		// Contact Information (can be overridden per brand)
		'contact'         => [
			'email'         => 'support@nookmattresstoday.com',
			'phone'         => '1-800-MATTRESS',
			'support_hours' => 'Monday–Friday, 9am–7pm EST',
		],
		
		// Social Media
		'social'          => [
			'facebook'      => 'https://facebook.com/nookmattresstoday',
			'instagram'     => 'https://instagram.com/nookmattresstoday',
			'pinterest'     => 'https://pinterest.com/nookmattresstoday',
			'tiktok'        => 'https://tiktok.com/@nookmattresstoday',
		],
		
		// SEO & Analytics
		'seo'             => [
			'title_template'    => '%s | Nook Mattress Today',
			'meta_description'  => 'Premium mattresses and sleep solutions in New York & surrounding areas',
			'og_title'          => 'Nook Mattress Today',
			'og_description'    => 'Premium mattresses and sleep solutions delivery',
		],
		
		// Stripe Configuration (production keys set via CI/CD secrets)
		'stripe'          => [
			'public_key'    => env('STRIPE_TEST_PUBLIC_KEY', 'pk_test_mattress'),
			'secret_key'    => env('STRIPE_TEST_SECRET_KEY', 'sk_test_mattress'),
		],
		
		// WooCommerce Settings
		'woocommerce'     => [
			'currency'      => 'USD',
			'currency_pos'  => 'left',
			'thousand_sep'  => ',',
			'decimal_sep'   => '.',
			'decimals'      => 2,
		],
		
		// Analytics (brand-specific tracking IDs set via CI/CD secrets)
		'analytics'       => [
			'ga4_measurement_id'  => env('GA4_MEASUREMENT_ID_MATTRESS', env('GA4_MEASUREMENT_ID', '')),
			'facebook_pixel_id'   => env('META_PIXEL_ID_MATTRESS', env('META_PIXEL_ID', '')),
		],
		
		// Shipping & Delivery (mattress-specific)
		'shipping'        => [
			'methods'           => ['standard', 'express'],
			'default_method'    => 'standard',
			'free_threshold'    => 100,
			'service_areas'     => [
				'Manhattan',
				'Brooklyn',
				'Queens',
				'Bronx',
				'Staten Island',
				'New Jersey',
				'Connecticut',
			],
			'trial_days'        => 120,
			'warranty_years'    => 10,
		],
	]
);
