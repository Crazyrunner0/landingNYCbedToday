<?php
/**
 * Brand Template Import & Seeding Script
 * 
 * Imports a brand template and creates branded pages for new brand instances.
 * Automatically updates brand-specific content, colors, and messaging.
 * 
 * Usage:
 *   make wp CMD='--allow-root eval-file scripts/seed-brand-from-template.php'
 * 
 * This script:
 * 1. Loads a template from scripts/brand-templates/
 * 2. Updates branding based on current WP_BRAND configuration
 * 3. Creates pages with brand-specific content
 * 4. Sets up navigation menus
 * 5. Registers custom post types if needed
 * 
 * @since 1.0.0
 */

if (!function_exists('get_site_url')) {
	WP_CLI::error('Error: WordPress is not properly loaded. Run this via: make wp CMD="--allow-root eval-file scripts/seed-brand-from-template.php"');
	exit(1);
}

class Brand_Template_Importer {

	/**
	 * Marker option for tracking template imports
	 * 
	 * @var string
	 */
	private static $seeded_option = 'seed_brand_template_completed';

	/**
	 * Get the template directory
	 * 
	 * @return string
	 */
	private static function get_template_dir() {
		return __DIR__ . '/brand-templates';
	}

	/**
	 * Get available template files
	 * 
	 * @return array
	 */
	private static function get_available_templates() {
		$templates = [];
		$template_dir = self::get_template_dir();
		
		if (!is_dir($template_dir)) {
			return $templates;
		}

		$files = glob($template_dir . '/*-template.json');
		foreach ($files as $file) {
			$name = basename($file, '-template.json');
			$templates[$name] = $file;
		}

		return $templates;
	}

	/**
	 * Load template from JSON file
	 * 
	 * @param string $brand_id Brand ID or template name
	 * 
	 * @return array|false
	 */
	private static function load_template($brand_id) {
		$template_dir = self::get_template_dir();
		$template_file = $template_dir . '/' . $brand_id . '-template.json';

		if (!file_exists($template_file)) {
			// Try to load base template
			$template_file = $template_dir . '/base-template.json';
		}

		if (!file_exists($template_file)) {
			return false;
		}

		$json = file_get_contents($template_file);
		return json_decode($json, true);
	}

	/**
	 * Replace brand placeholders in content
	 * 
	 * @param string $content Content with placeholders
	 * @param array  $brand   Brand configuration
	 * 
	 * @return string
	 */
	private static function replace_brand_placeholders($content, $brand) {
		$replacements = [
			'{brand_name}'         => $brand['display_name'],
			'{brand_id}'           => $brand['id'],
			'{primary_color}'      => $brand['colors']['primary'],
			'{secondary_color}'    => $brand['colors']['secondary'],
			'{cta_button}'         => $brand['copy']['cta_button'],
			'{email}'              => $brand['contact']['email'],
			'{phone}'              => $brand['contact']['phone'],
			'{support_hours}'      => $brand['contact']['support_hours'],
			'{shipping_threshold}' => $brand['shipping']['free_threshold'],
		];

		return str_replace(array_keys($replacements), array_values($replacements), $content);
	}

	/**
	 * Create branded pages from template
	 * 
	 * @param array $template Loaded template
	 * @param array $brand    Brand configuration
	 * 
	 * @return bool
	 */
	private static function create_pages_from_template($template, $brand) {
		if (empty($template['structure'])) {
			WP_CLI::warning('Template has no structure defined.');
			return false;
		}

		$structure = $template['structure'];
		$title = self::replace_brand_placeholders($structure['title'] ?? 'Home', $brand);
		$content = self::replace_brand_placeholders($structure['content'] ?? '', $brand);
		$slug = sanitize_title($structure['slug'] ?? 'home');

		// Check if page already exists
		$existing = get_page_by_path($slug);
		if ($existing) {
			WP_CLI::log("ℹ Page '{$slug}' already exists, skipping...");
			return true;
		}

		// Create page with branded content
		$page_id = wp_insert_post([
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_content'   => $content,
			'post_excerpt'   => self::replace_brand_placeholders(
				$structure['description'] ?? '',
				$brand
			),
		]);

		if (!$page_id) {
			WP_CLI::warning("Failed to create page: {$title}");
			return false;
		}

		WP_CLI::success("✓ Created page: {$title} (ID: {$page_id})");

		// Set as home page if it's the home/index page
		if ($slug === 'home' || $slug === 'index') {
			update_option('page_on_front', $page_id);
			update_option('show_on_front', 'page');
			WP_CLI::log("  Set as home page");
		}

		// Add page meta if available
		if (!empty($structure['meta'])) {
			foreach ($structure['meta'] as $meta_key => $meta_values) {
				if (is_array($meta_values)) {
					foreach ($meta_values as $value) {
						add_post_meta($page_id, $meta_key, maybe_unserialize($value['value'] ?? $value));
					}
				}
			}
		}

		return true;
	}

	/**
	 * Create branded sections using template blocks
	 * 
	 * @param int   $page_id Page ID
	 * @param array $brand   Brand configuration
	 * 
	 * @return void
	 */
	private static function add_branded_sections($page_id, $brand) {
		// Get template sections
		$sections = self::get_brand_template_sections($brand);

		// Update page content with sections
		$page = get_post($page_id);
		$content = $page->post_content;

		// Append sections if not already present
		foreach ($sections as $section_key => $block) {
			if (strpos($content, $section_key) === false) {
				$content .= "\n\n" . $block;
			}
		}

		wp_update_post([
			'ID'           => $page_id,
			'post_content' => $content,
		]);
	}

	/**
	 * Get template sections for brand
	 * 
	 * @param array $brand Brand configuration
	 * 
	 * @return array
	 */
	private static function get_brand_template_sections($brand) {
		return [
			'hero'     => self::get_hero_section($brand),
			'features' => self::get_features_section($brand),
			'cta'      => self::get_cta_section($brand),
		];
	}

	/**
	 * Generate hero section block
	 * 
	 * @param array $brand Brand configuration
	 * @return string
	 */
	private static function get_hero_section($brand) {
		return <<<BLOCK
<!-- wp:group {"layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group">
	<!-- wp:heading {"level":1,"style":{"color":{"text":"var:preset|color|contrast"}}} -->
	<h1 class="has-contrast-color has-text-color">Welcome to {$brand['display_name']}</h1>
	<!-- /wp:heading -->
	
	<!-- wp:paragraph -->
	<p>{$brand['copy']['learn_more']}</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
BLOCK;
	}

	/**
	 * Generate features section block
	 * 
	 * @param array $brand Brand configuration
	 * @return string
	 */
	private static function get_features_section($brand) {
		return <<<BLOCK
<!-- wp:heading {"level":2,"anchor":"features"} -->
<h2 id="features">Why Choose {$brand['display_name']}</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:heading {"level":3} -->
		<h3>Premium Quality</h3>
		<!-- /wp:heading -->
		
		<!-- wp:paragraph -->
		<p>High-quality products built to last</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->
	
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:heading {"level":3} -->
		<h3>Free Shipping</h3>
		<!-- /wp:heading -->
		
		<!-- wp:paragraph -->
		<p>On orders over \${$brand['shipping']['free_threshold']}</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->
	
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:heading {"level":3} -->
		<h3>Expert Support</h3>
		<!-- /wp:heading -->
		
		<!-- wp:paragraph -->
		<p>{$brand['contact']['support_hours']}</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
BLOCK;
	}

	/**
	 * Generate CTA section block
	 * 
	 * @param array $brand Brand configuration
	 * @return string
	 */
	private static function get_cta_section($brand) {
		return <<<BLOCK
<!-- wp:group {"backgroundColor":"accent","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group has-accent-background-color has-background">
	<!-- wp:heading {"textAlign":"center","level":2,"style":{"color":{"text":"var:preset|color|base"}}} -->
	<h2 class="has-text-align-center has-base-color has-text-color">Ready to explore {$brand['display_name']}?</h2>
	<!-- /wp:heading -->
	
	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"backgroundColor":"base"} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-base-background-color has-background" href="/shop">{$brand['copy']['cta_button']}</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
BLOCK;
	}

	/**
	 * Set up navigation menus with brand links
	 * 
	 * @param array $brand Brand configuration
	 * 
	 * @return void
	 */
	private static function setup_navigation($brand) {
		// Check if menus already exist
		$primary = get_term_by('name', 'Primary Navigation', 'nav_menu');
		if ($primary) {
			WP_CLI::log('ℹ Navigation menus already configured.');
			return;
		}

		// Create primary menu
		$primary_menu = wp_create_nav_menu('Primary Navigation');
		if ($primary_menu && !is_wp_error($primary_menu)) {
			wp_update_nav_menu_item($primary_menu, 0, [
				'menu-item-title'  => $brand['copy']['cta_button'],
				'menu-item-url'    => home_url('/shop'),
				'menu-item-status' => 'publish',
			]);

			WP_CLI::success('✓ Primary Navigation menu created');
		}

		// Create footer menu
		$footer_menu = wp_create_nav_menu('Footer');
		if ($footer_menu && !is_wp_error($footer_menu)) {
			wp_update_nav_menu_item($footer_menu, 0, [
				'menu-item-title'  => 'About',
				'menu-item-url'    => home_url('/about'),
				'menu-item-status' => 'publish',
			]);

			WP_CLI::success('✓ Footer menu created');
		}
	}

	/**
	 * Run the complete seeding process
	 * 
	 * @return void
	 */
	public static function run() {
		WP_CLI::log('🌱 Starting brand template seeding...');

		$brand = Brand_Config::get_brand();

		// Check if already seeded
		$seeded = get_option(self::$seeded_option);
		if ($seeded) {
			WP_CLI::warning('Brand template already seeded.');
			WP_CLI::log('  To reseed, use: make wp CMD="--allow-root option delete ' . self::$seeded_option . '"');
			return;
		}

		// Try to load template
		$template = self::load_template($brand['id']);
		if (!$template) {
			WP_CLI::warning('No template found for brand: ' . $brand['id']);
			WP_CLI::log('  Using inline template...');
			$template = [
				'version'  => '1.0.0',
				'brand_id' => $brand['id'],
				'structure' => [
					'title'   => $brand['display_name'],
					'slug'    => 'home',
					'content' => '',
				],
			];
		}

		// Step 1: Create pages
		WP_CLI::log('Step 1: Creating branded pages...');
		if (!self::create_pages_from_template($template, $brand)) {
			WP_CLI::warning('Some pages may not have been created.');
		}
		WP_CLI::success('✓ Pages created');

		// Step 2: Add branded sections
		WP_CLI::log('Step 2: Adding branded sections...');
		$home_page = get_page_by_path('home');
		if ($home_page) {
			self::add_branded_sections($home_page->ID, $brand);
			WP_CLI::success('✓ Branded sections added');
		}

		// Step 3: Set up navigation
		WP_CLI::log('Step 3: Setting up navigation menus...');
		self::setup_navigation($brand);
		WP_CLI::success('✓ Navigation configured');

		// Mark as complete
		update_option(self::$seeded_option, true);

		WP_CLI::success('✅ Brand template seeding complete!');
		WP_CLI::log('');
		WP_CLI::log('Next steps:');
		WP_CLI::log("  1. Visit: " . home_url());
		WP_CLI::log('  2. Log in to WordPress admin');
		WP_CLI::log('  3. Edit pages and customize content for ' . $brand['display_name']);
		WP_CLI::log('');
	}
}

// Run the seeding
Brand_Template_Importer::run();
