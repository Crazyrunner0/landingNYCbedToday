<?php

defined('ABSPATH') || exit;

class NYCBEDTODAY_Logistics_WooCommerce_Integration {
    
    public static function init() {
        add_action('woocommerce_after_shipping_rate', [self::class, 'add_delivery_slot_selector']);
        add_action('woocommerce_checkout_process', [self::class, 'validate_delivery_slot']);
        add_action('woocommerce_checkout_update_order_meta', [self::class, 'save_delivery_slot_to_order']);
        add_action('woocommerce_order_status_changed', [self::class, 'handle_order_status_change'], 10, 4);
        add_action('woocommerce_email_order_meta', [self::class, 'add_delivery_slot_to_email'], 10, 3);
        add_action('woocommerce_admin_order_data_after_shipping_address', [self::class, 'display_delivery_slot_in_admin']);
        add_action('woocommerce_thankyou', [self::class, 'display_delivery_slot_on_thank_you_page']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_checkout_scripts']);
        
        add_filter('woocommerce_checkout_fields', [self::class, 'add_zip_validation_field']);
    }
    
    public static function add_zip_validation_field($fields) {
        if (isset($fields['billing']['billing_postcode'])) {
            $fields['billing']['billing_postcode']['custom_attributes'] = [
                'data-nycbt-zip-validation' => 'true'
            ];
        }
        if (isset($fields['shipping']['shipping_postcode'])) {
            $fields['shipping']['shipping_postcode']['custom_attributes'] = [
                'data-nycbt-zip-validation' => 'true'
            ];
        }
        return $fields;
    }
    
    public static function enqueue_checkout_scripts() {
        if (is_checkout()) {
            wp_enqueue_style(
                'nycbedtoday-logistics-checkout',
                NYCBEDTODAY_LOGISTICS_URL . '/assets/checkout.css',
                [],
                NYCBEDTODAY_LOGISTICS_VERSION
            );
            
            wp_enqueue_script(
                'nycbedtoday-logistics-checkout',
                NYCBEDTODAY_LOGISTICS_URL . '/assets/checkout.js',
                ['jquery'],
                NYCBEDTODAY_LOGISTICS_VERSION,
                true
            );
            
            wp_localize_script('nycbedtoday-logistics-checkout', 'nycbtLogistics', [
                'apiUrl' => rest_url('nycbedtoday-logistics/v1'),
                'nonce' => wp_create_nonce('wp_rest'),
                'messages' => [
                    'invalidZip' => __('Sorry, we do not deliver to this ZIP code.', 'nycbedtoday-logistics'),
                    'selectSlot' => __('Please select a delivery time slot.', 'nycbedtoday-logistics'),
                    'loadingSlots' => __('Loading available time slots...', 'nycbedtoday-logistics'),
                    'noSlots' => __('No delivery slots available for this date.', 'nycbedtoday-logistics'),
                ]
            ]);
        }
    }
    
    public static function add_delivery_slot_selector() {
        ?>
        <div class="nycbt-delivery-slot-selector">
            <h3><?php _e('Select Delivery Time', 'nycbedtoday-logistics'); ?></h3>
            <div class="nycbt-slot-loading" style="display:none;">
                <p><?php _e('Loading available time slots...', 'nycbedtoday-logistics'); ?></p>
            </div>
            <div class="nycbt-slot-error" style="display:none; color: #e2401c;"></div>
            <div class="nycbt-slots-container"></div>
            <input type="hidden" name="nycbt_delivery_date" id="nycbt_delivery_date" value="">
            <input type="hidden" name="nycbt_delivery_slot_start" id="nycbt_delivery_slot_start" value="">
            <input type="hidden" name="nycbt_delivery_slot_end" id="nycbt_delivery_slot_end" value="">
            <input type="hidden" name="nycbt_reservation_id" id="nycbt_reservation_id" value="">
        </div>
        <?php
    }
    
    public static function validate_delivery_slot() {
        $delivery_date = isset($_POST['nycbt_delivery_date']) ? sanitize_text_field($_POST['nycbt_delivery_date']) : '';
        $slot_start = isset($_POST['nycbt_delivery_slot_start']) ? sanitize_text_field($_POST['nycbt_delivery_slot_start']) : '';
        $slot_end = isset($_POST['nycbt_delivery_slot_end']) ? sanitize_text_field($_POST['nycbt_delivery_slot_end']) : '';
        
        if (empty($delivery_date) || empty($slot_start) || empty($slot_end)) {
            wc_add_notice(__('Please select a delivery time slot.', 'nycbedtoday-logistics'), 'error');
            return;
        }
        
        $zip = isset($_POST['billing_postcode']) ? sanitize_text_field($_POST['billing_postcode']) : '';
        if (!empty($_POST['ship_to_different_address'])) {
            $zip = isset($_POST['shipping_postcode']) ? sanitize_text_field($_POST['shipping_postcode']) : $zip;
        }
        
        $zip = preg_replace('/[^0-9]/', '', $zip);
        $zip = str_pad($zip, 5, '0', STR_PAD_LEFT);
        
        if (!NYCBEDTODAY_Logistics_ZIP_Manager::is_zip_whitelisted($zip)) {
            wc_add_notice(__('We do not deliver to your ZIP code.', 'nycbedtoday-logistics'), 'error');
            return;
        }
        
        $available = NYCBEDTODAY_Logistics_Slot_Generator::get_slot_available_capacity($delivery_date, $slot_start, $slot_end);
        if ($available <= 0) {
            wc_add_notice(__('The selected delivery slot is no longer available. Please choose another.', 'nycbedtoday-logistics'), 'error');
        }
    }
    
    public static function save_delivery_slot_to_order($order_id) {
        $delivery_date = isset($_POST['nycbt_delivery_date']) ? sanitize_text_field($_POST['nycbt_delivery_date']) : '';
        $slot_start = isset($_POST['nycbt_delivery_slot_start']) ? sanitize_text_field($_POST['nycbt_delivery_slot_start']) : '';
        $slot_end = isset($_POST['nycbt_delivery_slot_end']) ? sanitize_text_field($_POST['nycbt_delivery_slot_end']) : '';
        $reservation_id = isset($_POST['nycbt_reservation_id']) ? intval($_POST['nycbt_reservation_id']) : 0;
        
        if (empty($delivery_date) || empty($slot_start) || empty($slot_end)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        $zip = $order->get_shipping_postcode();
        if (empty($zip)) {
            $zip = $order->get_billing_postcode();
        }
        
        $zip = preg_replace('/[^0-9]/', '', $zip);
        $zip = str_pad($zip, 5, '0', STR_PAD_LEFT);
        
        if ($reservation_id) {
            NYCBEDTODAY_Logistics_Slot_Reservation::update_reservation_order($reservation_id, $order_id);
        } else {
            $reservation_id = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot($delivery_date, $slot_start, $slot_end, $zip, $order_id);
        }
        
        update_post_meta($order_id, '_nycbt_delivery_date', $delivery_date);
        update_post_meta($order_id, '_nycbt_delivery_slot_start', $slot_start);
        update_post_meta($order_id, '_nycbt_delivery_slot_end', $slot_end);
        update_post_meta($order_id, '_nycbt_reservation_id', $reservation_id);
        
        $slot_label = self::format_slot_label($delivery_date, $slot_start, $slot_end);
        update_post_meta($order_id, '_nycbt_delivery_slot_label', $slot_label);
    }
    
    public static function handle_order_status_change($order_id, $old_status, $new_status, $order) {
        $reservation_id = get_post_meta($order_id, '_nycbt_reservation_id', true);
        
        if (!$reservation_id) {
            return;
        }
        
        if ($new_status === 'completed' || $new_status === 'processing') {
            NYCBEDTODAY_Logistics_Slot_Reservation::confirm_reservation($reservation_id);
        } elseif ($new_status === 'cancelled' || $new_status === 'refunded' || $new_status === 'failed') {
            NYCBEDTODAY_Logistics_Slot_Reservation::cancel_reservation($reservation_id);
        }
    }
    
    public static function add_delivery_slot_to_email($order, $sent_to_admin, $plain_text) {
        $delivery_slot = get_post_meta($order->get_id(), '_nycbt_delivery_slot_label', true);
        
        if (!empty($delivery_slot)) {
            if ($plain_text) {
                echo "\n" . __('Delivery Time:', 'nycbedtoday-logistics') . ' ' . $delivery_slot . "\n";
            } else {
                echo '<h2>' . __('Delivery Information', 'nycbedtoday-logistics') . '</h2>';
                echo '<p><strong>' . __('Delivery Time:', 'nycbedtoday-logistics') . '</strong> ' . esc_html($delivery_slot) . '</p>';
            }
        }
    }
    
    public static function display_delivery_slot_in_admin($order) {
        $delivery_slot = get_post_meta($order->get_id(), '_nycbt_delivery_slot_label', true);
        
        if (!empty($delivery_slot)) {
            ?>
            <div class="nycbt-admin-delivery-info">
                <h3><?php _e('Delivery Information', 'nycbedtoday-logistics'); ?></h3>
                <p><strong><?php _e('Delivery Time:', 'nycbedtoday-logistics'); ?></strong> <?php echo esc_html($delivery_slot); ?></p>
            </div>
            <?php
        }
    }
    
    public static function display_delivery_slot_on_thank_you_page($order_id) {
        $delivery_slot = get_post_meta($order_id, '_nycbt_delivery_slot_label', true);
        
        if (!empty($delivery_slot)) {
            ?>
            <section class="nycbt-delivery-info">
                <h2><?php _e('Delivery Information', 'nycbedtoday-logistics'); ?></h2>
                <p><?php _e('Your order will be delivered on:', 'nycbedtoday-logistics'); ?></p>
                <p class="nycbt-delivery-slot"><strong><?php echo esc_html($delivery_slot); ?></strong></p>
            </section>
            <?php
        }
    }
    
    private static function format_slot_label($date, $start, $end) {
        $date_formatted = date('l, F j, Y', strtotime($date));
        $start_time = date('g:i A', strtotime($start));
        $end_time = date('g:i A', strtotime($end));
        
        return sprintf('%s between %s - %s', $date_formatted, $start_time, $end_time);
    }
}
