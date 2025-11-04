<?php
/**
 * Brand Template Seeding Script
 * 
 * Creates a brand-aware content template by importing the landing page structure
 * from the current brand and exporting it as a reusable template.
 * 
 * Usage:
 *   make wp CMD='--allow-root eval-file scripts/seed-brand-template.php'
 * 
 * This script:
 * 1. Exports the current landing page structure
 * 2. Saves it as a JSON template in scripts/brand-templates/
 * 3. Provides WP-CLI commands for importing templates to new brands
 * 
 * @since 1.0.0
 */

if (!function_exists('get_site_url')) {
	WP_CLI::error('Error: WordPress is not properly loaded. Run this via: make wp CMD="--allow-root eval-file scripts/seed-brand-template.php"');
	exit(1);
}

class Brand_Template_Seeder {

	/**
	 * Get the template directory
	 * 
	 * @return string
	 */
	private static function get_template_dir() {
		$template_dir = __DIR__ . '/brand-templates';
		if (!is_dir($template_dir)) {
			mkdir($template_dir, 0755, true);
		}
		return $template_dir;
	}

	/**
	 * Export home page structure as template
	 * 
	 * @return array
	 */
	private static function export_home_page_structure() {
		$home_id = get_option('page_on_front');
		
		if (!$home_id) {
			WP_CLI::warning('Home page not found. Creating basic template structure...');
			return self::get_basic_template_structure();
		}

		$page = get_post($home_id);
		
		return [
			'title'       => $page->post_title,
			'slug'        => $page->post_name,
			'description' => $page->post_excerpt,
			'content'     => $page->post_content,
			'meta'        => get_post_meta($home_id),
			'blocks'      => parse_blocks($page->post_content),
		];
	}

	/**
	 * Get basic template structure for new brands
	 * 
	 * @return array
	 */
	private static function get_basic_template_structure() {
		return [
			'title'       => 'Home',
			'slug'        => 'home',
			'description' => 'Main landing page',
			'content'     => self::get_placeholder_content(),
			'meta'        => [],
			'blocks'      => [],
		];
	}

	/**
	 * Get placeholder content for new brands
	 * 
	 * @return string
	 */
	private static function get_placeholder_content() {
		$brand = Brand_Config::get_brand();
		
		return <<<HTML
<!-- wp:heading -->
<h1>Welcome to {$brand['display_name']}</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>{$brand['copy']['cta_button']} to explore our premium collection.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link" href="/shop">{$brand['copy']['cta_button']}</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
HTML;
	}

	/**
	 * Export current brand template
	 * 
	 * @return void
	 */
	public static function export_template() {
		WP_CLI::log('📤 Exporting landing page structure...');
		
		$structure = self::export_home_page_structure();
		$brand_id = Brand_Config::get_id();
		$filename = self::get_template_dir() . '/' . $brand_id . '-template.json';
		
		$exported = [
			'version'   => '1.0.0',
			'brand_id'  => $brand_id,
			'exported'  => current_time('mysql'),
			'structure' => $structure,
		];
		
		if (file_put_contents($filename, wp_json_encode($exported, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
			WP_CLI::success("✓ Template exported to: scripts/brand-templates/{$brand_id}-template.json");
			WP_CLI::log("  This template can be used to seed new brand instances.");
		} else {
			WP_CLI::error("Failed to export template to {$filename}");
		}
	}

	/**
	 * Get branded sections for seeding
	 * 
	 * @return array
	 */
	public static function get_branded_sections() {
		$brand = Brand_Config::get_brand();
		
		return [
			'hero'      => self::get_hero_section($brand),
			'features'  => self::get_features_section($brand),
			'cta'       => self::get_cta_section($brand),
			'footer'    => self::get_footer_section($brand),
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
	<h1 class="has-contrast-color has-text-color">{$brand['display_name']}</h1>
	<!-- /wp:heading -->
	
	<!-- wp:paragraph -->
	<p>{$brand['copy']['cta_button']}</p>
	<!-- /wp:paragraph -->
	
	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link">{$brand['copy']['cta_button']}</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
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
		$features = [
			['title' => 'Premium Quality', 'desc' => 'High-quality {type} built to last'],
			['title' => 'Free Shipping', 'desc' => 'On orders over $' . $brand['shipping']['free_threshold']],
			['title' => 'Expert Support', 'desc' => $brand['contact']['support_hours']],
		];
		
		$blocks = '';
		foreach ($features as $feature) {
			$blocks .= <<<BLOCK
<!-- wp:column -->
<div class="wp-block-column">
	<!-- wp:heading {"level":3} -->
	<h3>{$feature['title']}</h3>
	<!-- /wp:heading -->
	
	<!-- wp:paragraph -->
	<p>{$feature['desc']}</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
BLOCK;
		}
		
		return <<<BLOCK
<!-- wp:columns -->
<div class="wp-block-columns">
$blocks
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
	<!-- wp:heading {"textAlign":"center","style":{"color":{"text":"var:preset|color|base"}}} -->
	<h2 class="has-text-align-center has-base-color has-text-color">Ready to get started?</h2>
	<!-- /wp:heading -->
	
	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"backgroundColor":"base"} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-base-background-color has-background">{$brand['copy']['cta_button']}</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
BLOCK;
	}

	/**
	 * Generate footer section with brand info
	 * 
	 * @param array $brand Brand configuration
	 * @return string
	 */
	private static function get_footer_section($brand) {
		return <<<BLOCK
<!-- wp:group {"backgroundColor":"neutral_dark","textColor":"base","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}}} -->
<div class="wp-block-group has-neutral-dark-background-color has-base-color has-background has-text-color">
	<!-- wp:heading {"level":2,"style":{"color":{"text":"var:preset|color|base"}}} -->
	<h2 class="has-base-color has-text-color">{$brand['display_name']}</h2>
	<!-- /wp:heading -->
	
	<!-- wp:paragraph -->
	<p><strong>Contact:</strong><br />
	Email: <a href="mailto:{$brand['contact']['email']}">{$brand['contact']['email']}</a><br />
	Phone: <a href="tel:{$brand['contact']['phone']}">{$brand['contact']['phone']}</a></p>
	<!-- /wp:paragraph -->
	
	<!-- wp:paragraph -->
	<p><strong>Hours:</strong><br />{$brand['contact']['support_hours']}</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
BLOCK;
	}

	/**
	 * Generate template info for documentation
	 * 
	 * @return void
	 */
	public static function show_template_info() {
		$brand = Brand_Config::get_brand();
		$template_dir = self::get_template_dir();
		
		WP_CLI::log('');
		WP_CLI::log('📋 Brand Template Information');
		WP_CLI::log('─────────────────────────────');
		WP_CLI::log("Brand ID:    {$brand['id']}");
		WP_CLI::log("Brand Name:  {$brand['display_name']}");
		WP_CLI::log("Template Dir: {$template_dir}/");
		WP_CLI::log('');
		WP_CLI::log('To import a template for new brand setup:');
		WP_CLI::log('  1. Copy a template to new brand repo');
		WP_CLI::log('  2. Run: make wp CMD="--allow-root eval-file scripts/seed-brand-from-template.php --brand=NEW_BRAND"');
		WP_CLI::log('');
	}
}

// Run the export
Brand_Template_Seeder::export_template();
Brand_Template_Seeder::show_template_info();
