<?php
/**
 * Base Brand Configuration Template
 * 
 * All sibling brands extend this configuration.
 * Override properties in brand-specific config files.
 * 
 * @since 1.0.0
 */

return [
	// Brand Identifier
	'id'              => 'base',
	'name'            => 'Base Brand',
	'display_name'    => 'Base Brand',
	'description'     => 'Base brand configuration template',
	
	// URLs & Domains
	'domain'          => env('WP_HOME', 'http://localhost:8080'),
	'home_url'        => env('WP_HOME', 'http://localhost:8080'),
	
	// Logo & Branding Assets
	'logo'            => [
		'url'           => '/app/themes/twentytwentyfour/assets/images/logo.svg',
		'width'         => 200,
		'height'        => 60,
		'alt_text'      => 'Brand Logo',
	],
	'favicon'         => [
		'url'           => '/app/themes/twentytwentyfour/assets/images/favicon.ico',
	],
	'og_image'        => [
		'url'           => '/app/themes/twentytwentyfour/assets/images/og-image.png',
		'width'         => 1200,
		'height'        => 630,
	],
	
	// Color Palette
	'colors'          => [
		'primary'       => '#cfcabe',     // Accent
		'primary_dark'  => '#c2a990',     // Accent / Two
		'secondary'     => '#d8613c',     // Accent / Three
		'tertiary'      => '#b1c5a4',     // Accent / Four
		'quaternary'    => '#b5bdbc',     // Accent / Five
		'neutral_light' => '#f9f9f9',     // Base
		'neutral_white' => '#ffffff',     // Base / Two
		'neutral_dark'  => '#111111',     // Contrast
		'neutral_gray'  => '#636363',     // Contrast / Two
		'neutral_light_gray' => '#A4A4A4', // Contrast / Three
	],
	
	// Typography
	'typography'      => [
		'heading_font'  => 'Cardo',
		'body_font'     => 'Inter',
		'mono_font'     => 'monospace',
	],
	
	// Copy & Messaging
	'copy'            => [
		'cta_button'    => 'Shop Now',
		'view_products' => 'View Products',
		'learn_more'    => 'Learn More',
		'about_us'      => 'About Us',
		'contact_us'    => 'Contact Us',
		'shipping_text' => 'Free shipping on orders over $100',
	],
	
	// Contact Information
	'contact'         => [
		'email'         => 'hello@example.com',
		'phone'         => '1-800-123-4567',
		'support_hours' => 'Monday–Friday, 9am–5pm EST',
	],
	
	// Social Media
	'social'          => [
		'facebook'      => 'https://facebook.com/example',
		'instagram'     => 'https://instagram.com/example',
		'pinterest'     => 'https://pinterest.com/example',
		'tiktok'        => 'https://tiktok.com/@example',
	],
	
	// SEO & Analytics
	'seo'             => [
		'title_template'    => '%s | Base Brand',
		'meta_description'  => 'Base Brand Description',
		'og_title'          => 'Base Brand',
		'og_description'    => 'Base Brand Description',
	],
	
	// Stripe Configuration
	'stripe'          => [
		'public_key'    => env('STRIPE_TEST_PUBLIC_KEY', 'pk_test_'),
		'secret_key'    => env('STRIPE_TEST_SECRET_KEY', 'sk_test_'),
	],
	
	// WooCommerce Settings
	'woocommerce'     => [
		'currency'      => 'USD',
		'currency_pos'  => 'left',
		'thousand_sep'  => ',',
		'decimal_sep'   => '.',
		'decimals'      => 2,
	],
	
	// Analytics
	'analytics'       => [
		'ga4_measurement_id'  => env('GA4_MEASUREMENT_ID', ''),
		'facebook_pixel_id'   => env('META_PIXEL_ID', ''),
	],
	
	// Shipping & Delivery
	'shipping'        => [
		'methods'           => ['standard', 'express'],
		'default_method'    => 'standard',
		'free_threshold'    => 100,
		'service_areas'     => [
			'New York',
			'New Jersey',
			'Connecticut',
		],
	],
];
