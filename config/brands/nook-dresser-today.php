<?php
/**
 * NookDresserToday Brand Configuration
 * 
 * Dresser/furniture-focused brand configuration
 * 
 * @since 1.0.0
 */

return array_merge(
	include __DIR__ . '/base.php',
	[
		// Brand Identifier
		'id'              => 'nook-dresser-today',
		'name'            => 'NookDresserToday',
		'display_name'    => 'Nook Dresser Today',
		'description'     => 'Premium Dressers & Bedroom Furniture',
		
		// Logo & Branding (customize for dresser brand)
		'logo'            => [
			'url'           => '/app/themes/twentytwentyfour/assets/images/nook-dresser-logo.svg',
			'width'         => 200,
			'height'        => 60,
			'alt_text'      => 'Nook Dresser Today Logo',
		],
		'favicon'         => [
			'url'           => '/app/themes/twentytwentyfour/assets/images/nook-dresser-favicon.ico',
		],
		'og_image'        => [
			'url'           => '/app/themes/twentytwentyfour/assets/images/nook-dresser-og.png',
			'width'         => 1200,
			'height'        => 630,
		],
		
		// Copy & Messaging (tailored for dressers)
		'copy'            => [
			'cta_button'    => 'Shop Dressers',
			'view_products' => 'View All Dressers',
			'learn_more'    => 'Learn More',
			'about_us'      => 'About Nook Dresser',
			'contact_us'    => 'Contact Us',
			'shipping_text' => 'Free shipping on dressers over $500',
		],
		
		// Contact Information (can be overridden per brand)
		'contact'         => [
			'email'         => 'support@nookdressertoday.com',
			'phone'         => '1-800-DRESSER-1',
			'support_hours' => 'Monday–Friday, 9am–6pm EST',
		],
		
		// Social Media
		'social'          => [
			'facebook'      => 'https://facebook.com/nookdressertoday',
			'instagram'     => 'https://instagram.com/nookdressertoday',
			'pinterest'     => 'https://pinterest.com/nookdressertoday',
			'tiktok'        => 'https://tiktok.com/@nookdressertoday',
		],
		
		// SEO & Analytics
		'seo'             => [
			'title_template'    => '%s | Nook Dresser Today',
			'meta_description'  => 'Premium dressers and bedroom furniture in New York & surrounding areas',
			'og_title'          => 'Nook Dresser Today',
			'og_description'    => 'Premium dressers and bedroom furniture delivery',
		],
		
		// Stripe Configuration (production keys set via CI/CD secrets)
		'stripe'          => [
			'public_key'    => env('STRIPE_TEST_PUBLIC_KEY', 'pk_test_dresser'),
			'secret_key'    => env('STRIPE_TEST_SECRET_KEY', 'sk_test_dresser'),
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
			'ga4_measurement_id'  => env('GA4_MEASUREMENT_ID_DRESSER', env('GA4_MEASUREMENT_ID', '')),
			'facebook_pixel_id'   => env('META_PIXEL_ID_DRESSER', env('META_PIXEL_ID', '')),
		],
		
		// Shipping & Delivery (furniture-specific)
		'shipping'        => [
			'methods'           => ['standard', 'express', 'white-glove'],
			'default_method'    => 'standard',
			'free_threshold'    => 500,
			'service_areas'     => [
				'Manhattan',
				'Brooklyn',
				'Queens',
				'Bronx',
				'Staten Island',
				'New Jersey',
				'Connecticut',
			],
			'assembly_available' => true,
			'assembly_cost'      => 150,
		],
	]
);
