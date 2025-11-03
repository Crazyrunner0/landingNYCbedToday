<?php

defined('ABSPATH') || exit;

function nycbedtoday_logistics_register_blocks() {
    add_action('init', 'nycbedtoday_logistics_register_block_types');
    add_filter('block_categories_all', 'nycbedtoday_logistics_register_block_category', 10, 2);
}

function nycbedtoday_logistics_register_block_category($categories) {
    return array_merge(
        $categories,
        [
            [
                'slug' => 'nycbedtoday-logistics',
                'title' => __('NYC Bed Today - Logistics', 'nycbedtoday-logistics'),
            ],
        ]
    );
}

function nycbedtoday_logistics_register_block_types() {
    if (!function_exists('register_block_type')) {
        return;
    }
    
    register_block_type('nycbedtoday-logistics/zip-checker', [
        'render_callback' => 'nycbedtoday_logistics_render_zip_checker_block',
        'attributes' => [
            'buttonText' => [
                'type' => 'string',
                'default' => __('Check Availability', 'nycbedtoday-logistics'),
            ],
            'placeholder' => [
                'type' => 'string',
                'default' => __('Enter ZIP Code', 'nycbedtoday-logistics'),
            ],
        ],
    ]);
    
    register_block_type('nycbedtoday-logistics/available-slots', [
        'render_callback' => 'nycbedtoday_logistics_render_available_slots_block',
        'attributes' => [
            'date' => [
                'type' => 'string',
                'default' => '',
            ],
            'showDatePicker' => [
                'type' => 'boolean',
                'default' => true,
            ],
        ],
    ]);
}

function nycbedtoday_logistics_render_zip_checker_block($attributes) {
    $atts = [
        'button_text' => $attributes['buttonText'],
        'placeholder' => $attributes['placeholder'],
    ];
    
    return nycbedtoday_logistics_check_zip_shortcode($atts);
}

function nycbedtoday_logistics_render_available_slots_block($attributes) {
    $atts = [
        'date' => $attributes['date'],
        'show_date_picker' => $attributes['showDatePicker'] ? 'yes' : 'no',
    ];
    
    return nycbedtoday_logistics_available_slots_shortcode($atts);
}
