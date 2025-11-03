<?php

defined('ABSPATH') || exit;

function nycbedtoday_logistics_register_shortcodes() {
    add_shortcode('nycbt_check_zip', 'nycbedtoday_logistics_check_zip_shortcode');
    add_shortcode('nycbt_available_slots', 'nycbedtoday_logistics_available_slots_shortcode');
}

function nycbedtoday_logistics_check_zip_shortcode($atts) {
    $atts = shortcode_atts([
        'button_text' => __('Check Availability', 'nycbedtoday-logistics'),
        'placeholder' => __('Enter ZIP Code', 'nycbedtoday-logistics'),
    ], $atts);
    
    wp_enqueue_style(
        'nycbedtoday-logistics-public',
        NYCBEDTODAY_LOGISTICS_URL . '/assets/public.css',
        [],
        NYCBEDTODAY_LOGISTICS_VERSION
    );
    
    wp_enqueue_script(
        'nycbedtoday-logistics-public',
        NYCBEDTODAY_LOGISTICS_URL . '/assets/public.js',
        ['jquery'],
        NYCBEDTODAY_LOGISTICS_VERSION,
        true
    );
    
    wp_localize_script('nycbedtoday-logistics-public', 'nycbtLogistics', [
        'apiUrl' => rest_url('nycbedtoday-logistics/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
    
    ob_start();
    ?>
    <div class="nycbt-zip-checker">
        <div class="nycbt-zip-checker-form">
            <input type="text" 
                   class="nycbt-zip-input" 
                   placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                   maxlength="5" 
                   pattern="[0-9]{5}">
            <button type="button" class="nycbt-zip-check-btn">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
        </div>
        <div class="nycbt-zip-result"></div>
    </div>
    <?php
    return ob_get_clean();
}

function nycbedtoday_logistics_available_slots_shortcode($atts) {
    $atts = shortcode_atts([
        'date' => '',
        'show_date_picker' => 'yes',
    ], $atts);
    
    wp_enqueue_style(
        'nycbedtoday-logistics-public',
        NYCBEDTODAY_LOGISTICS_URL . '/assets/public.css',
        [],
        NYCBEDTODAY_LOGISTICS_VERSION
    );
    
    wp_enqueue_script(
        'nycbedtoday-logistics-public',
        NYCBEDTODAY_LOGISTICS_URL . '/assets/public.js',
        ['jquery'],
        NYCBEDTODAY_LOGISTICS_VERSION,
        true
    );
    
    wp_localize_script('nycbedtoday-logistics-public', 'nycbtLogistics', [
        'apiUrl' => rest_url('nycbedtoday-logistics/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
    
    $date = !empty($atts['date']) ? $atts['date'] : NYCBEDTODAY_Logistics_Slot_Generator::get_next_available_date();
    $slots = NYCBEDTODAY_Logistics_Slot_Generator::get_available_slots($date);
    
    ob_start();
    ?>
    <div class="nycbt-slots-display" data-date="<?php echo esc_attr($date); ?>">
        <?php if ($atts['show_date_picker'] === 'yes'): ?>
            <div class="nycbt-date-selector">
                <label for="nycbt-slot-date"><?php _e('Select Date:', 'nycbedtoday-logistics'); ?></label>
                <input type="date" id="nycbt-slot-date" value="<?php echo esc_attr($date); ?>" min="<?php echo current_time('Y-m-d'); ?>">
            </div>
        <?php endif; ?>
        
        <div class="nycbt-slots-list">
            <?php if (!empty($slots)): ?>
                <h3><?php printf(__('Available Slots for %s', 'nycbedtoday-logistics'), date('F j, Y', strtotime($date))); ?></h3>
                <ul>
                    <?php foreach ($slots as $slot): ?>
                        <li class="nycbt-slot-item">
                            <span class="nycbt-slot-time"><?php echo esc_html($slot['label']); ?></span>
                            <span class="nycbt-slot-capacity"><?php printf(__('%d spots available', 'nycbedtoday-logistics'), $slot['available']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php _e('No delivery slots available for this date.', 'nycbedtoday-logistics'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
