<?php
/**
 * WP-CLI Script: Import RankMath Settings
 *
 * Usage: wp eval-file scripts/rankmath-import-settings.php
 *
 * Imports RankMath settings from JSON configuration file
 *
 * @package NYC_Bed_Today
 */

if (!defined('ABSPATH')) {
	exit;
}

// Load settings from JSON file
$settings_file = dirname(__FILE__) . '/rankmath-settings.json';

if (!file_exists($settings_file)) {
	WP_CLI::error("Settings file not found: $settings_file");
	exit(1);
}

$settings_json = file_get_contents($settings_file);
$settings = json_decode($settings_json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
	WP_CLI::error("Invalid JSON in settings file: " . json_last_error_msg());
	exit(1);
}

// Replace template variables
$site_url = home_url();
foreach ($settings as $option_name => $option_value) {
	// Replace template variables in the settings
	$option_value = json_decode(
		str_replace('{{ SITE_URL }}', $site_url, json_encode($option_value)),
		true
	);

	// Skip if RankMath is not active
	if (strpos($option_name, 'rank-math') !== 0) {
		continue;
	}

	// Get current settings
	$current = get_option($option_name, []);

	// Merge new settings with existing ones (don't overwrite completely)
	if (is_array($option_value) && is_array($current)) {
		$merged = array_merge($current, $option_value);
	} else {
		$merged = $option_value;
	}

	// Update the option
	update_option($option_name, $merged);

	WP_CLI::success("Updated: $option_name");
}

WP_CLI::success("RankMath settings imported successfully!");
