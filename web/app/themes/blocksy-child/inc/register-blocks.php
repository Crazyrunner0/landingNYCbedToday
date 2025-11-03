<?php
/**
 * Register Gutenberg Blocks
 *
 * Registers custom blocks for the landing page
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all custom blocks
 */
function blocksy_child_register_blocks() {
    $block_dirs = [
        'hero-offer',
        'social-proof-strip',
        'value-stack',
        'how-it-works',
        'final-cta',
        'local-neighborhoods',
    ];

    foreach ($block_dirs as $block) {
        $block_path = BLOCKSY_CHILD_DIR . '/blocks/' . $block;
        if (file_exists($block_path . '/block.json')) {
            register_block_type_from_metadata($block_path);
        }
    }
}
add_action('init', 'blocksy_child_register_blocks');

/**
 * Enqueue block editor styles and scripts
 */
function blocksy_child_enqueue_block_editor_assets() {
    $block_dirs = [
        'hero-offer',
        'social-proof-strip',
        'value-stack',
        'how-it-works',
        'final-cta',
        'local-neighborhoods',
    ];

    foreach ($block_dirs as $block) {
        $build_dir = BLOCKSY_CHILD_URI . '/build';
        $build_path = BLOCKSY_CHILD_DIR . '/build';
        $asset_file = $build_path . '/' . $block . '.asset.php';

        // Load asset file for dependencies and version
        $asset_data = file_exists($asset_file) ? require $asset_file : [
            'dependencies' => [],
            'version'      => BLOCKSY_CHILD_VERSION,
        ];

        // Enqueue editor style (main CSS)
        if (file_exists($build_path . '/' . $block . '.css')) {
            wp_enqueue_style(
                'blocksy-child-' . $block . '-editor-css',
                $build_dir . '/' . $block . '.css',
                [],
                $asset_data['version']
            );
        }

        // Enqueue editor script
        if (file_exists($build_path . '/' . $block . '.js')) {
            wp_enqueue_script(
                'blocksy-child-' . $block . '-editor-js',
                $build_dir . '/' . $block . '.js',
                $asset_data['dependencies'],
                $asset_data['version'],
                true
            );
        }
    }
}
add_action('enqueue_block_editor_assets', 'blocksy_child_enqueue_block_editor_assets');

/**
 * Enqueue block frontend styles
 */
function blocksy_child_enqueue_block_assets() {
    $block_dirs = [
        'hero-offer',
        'social-proof-strip',
        'value-stack',
        'how-it-works',
        'final-cta',
        'local-neighborhoods',
    ];

    foreach ($block_dirs as $block) {
        $build_path = BLOCKSY_CHILD_DIR . '/build';
        $build_dir = BLOCKSY_CHILD_URI . '/build';
        $asset_file = $build_path . '/' . $block . '.asset.php';
        $asset_data = file_exists($asset_file) ? require $asset_file : [
            'dependencies' => [],
            'version'      => BLOCKSY_CHILD_VERSION,
        ];

        if (file_exists($build_path . '/' . $block . '.css')) {
            wp_enqueue_style(
                'blocksy-child-' . $block . '-css',
                $build_dir . '/' . $block . '.css',
                [],
                $asset_data['version']
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_block_assets');

/**
 * Register custom block category
 */
function blocksy_child_register_block_categories($categories, $post) {
    return array_merge(
        $categories,
        [
            [
                'slug'  => 'blocksy-landing',
                'title' => __('Blocksy Landing Blocks', 'blocksy-child'),
                'icon'  => 'layout',
            ],
        ]
    );
}
add_filter('block_categories_all', 'blocksy_child_register_block_categories', 10, 2);
