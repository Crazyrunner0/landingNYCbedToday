<?php
/**
 * Plugin Name: WooCommerce Same-day Logistics
 * Description: Provides NYC ZIP whitelist, time slot management with capacity, cut-off controls, blackout dates, and checkout reservations.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Sameday_Logistics {
    private const OPTION_KEY = 'sameday_logistics_settings';
    private const OPTION_HOLDS_KEY = 'sameday_slot_holds';
    private const SESSION_SLOT_KEY = 'sameday_selected_slot';
    private const SESSION_TOKEN_KEY = 'sameday_slot_token';
    private const SESSION_ZIP_KEY = 'sameday_selected_zip';
    private const SLOT_INTERVAL_MINUTES = 120;
    private const HOLD_DURATION_MINUTES = 20; // minutes

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('woocommerce_checkout_update_order_review', [$this, 'handle_order_review_update']);
        add_action('woocommerce_thankyou', [$this, 'clear_session_data'], 20);
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_change'], 10, 4);
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            return;
        }

        add_filter('woocommerce_checkout_fields', [$this, 'add_checkout_field']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_checkout_assets']);
        add_action('woocommerce_checkout_process', [$this, 'validate_checkout']);
        add_action('woocommerce_checkout_create_order', [$this, 'add_order_meta'], 10, 2);
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'render_admin_order_meta']);
        add_action('woocommerce_email_order_meta', [$this, 'render_email_order_meta'], 20, 3);
        add_action('woocommerce_order_details_after_order_table', [$this, 'render_frontend_order_meta']);
        add_action('wp_ajax_sameday_get_slots', [$this, 'handle_slots_request']);
        add_action('wp_ajax_nopriv_sameday_get_slots', [$this, 'handle_slots_request']);
    }

    public function register_settings() {
        register_setting('sameday_logistics_settings_group', self::OPTION_KEY, [$this, 'sanitize_settings']);
    }

    public function register_admin_menu() {
        if (!class_exists('WooCommerce')) {
            return;
        }

        add_submenu_page(
            'woocommerce',
            __('Same-day Delivery', 'sameday-logistics'),
            __('Same-day Delivery', 'sameday-logistics'),
            'manage_woocommerce',
            'sameday-logistics',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        $settings = $this->get_settings();
        $timezone = wp_timezone_string();
        $slot_templates = $this->generate_slots_for_template();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Same-day Delivery Settings', 'sameday-logistics'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('sameday_logistics_settings_group');
                ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('NYC ZIP Whitelist', 'sameday-logistics'); ?></th>
                            <td>
                                <textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[zip_whitelist]" rows="5" cols="40" class="large-text code"><?php echo esc_textarea(implode("\n", $settings['zip_whitelist'])); ?></textarea>
                                <p class="description"><?php esc_html_e('Enter one ZIP code per line. Customers must live in these ZIP codes to see same-day delivery slots.', 'sameday-logistics'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Default Slot Capacity', 'sameday-logistics'); ?></th>
                            <td>
                                <input type="number" min="1" name="<?php echo esc_attr(self::OPTION_KEY); ?>[default_capacity]" value="<?php echo esc_attr($settings['default_capacity']); ?>" class="small-text" />
                                <p class="description"><?php esc_html_e('Number of deliveries allowed per slot by default.', 'sameday-logistics'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Slot Window', 'sameday-logistics'); ?></th>
                            <td>
                                <label>
                                    <?php esc_html_e('Start', 'sameday-logistics'); ?>
                                    <input type="time" name="<?php echo esc_attr(self::OPTION_KEY); ?>[slot_start]" value="<?php echo esc_attr($settings['slot_start']); ?>" />
                                </label>
                                &nbsp;&nbsp;
                                <label>
                                    <?php esc_html_e('End', 'sameday-logistics'); ?>
                                    <input type="time" name="<?php echo esc_attr(self::OPTION_KEY); ?>[slot_end]" value="<?php echo esc_attr($settings['slot_end']); ?>" />
                                </label>
                                <p class="description"><?php esc_html_e('Two-hour slots are generated between these times each day.', 'sameday-logistics'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Daily Cut-off Time', 'sameday-logistics'); ?></th>
                            <td>
                                <input type="time" name="<?php echo esc_attr(self::OPTION_KEY); ?>[cutoff_time]" value="<?php echo esc_attr($settings['cutoff_time']); ?>" />
                                <p class="description"><?php esc_html_e('After this time, customers will see the next available day. Timezone:', 'sameday-logistics'); ?> <strong><?php echo esc_html($timezone); ?></strong></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Blackout Dates', 'sameday-logistics'); ?></th>
                            <td>
                                <textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[blackout_dates]" rows="4" cols="40" class="large-text code"><?php echo esc_textarea(implode("\n", $settings['blackout_dates'])); ?></textarea>
                                <p class="description"><?php esc_html_e('Dates with no deliveries (format: YYYY-MM-DD). One per line.', 'sameday-logistics'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Per-slot Capacity Overrides', 'sameday-logistics'); ?></th>
                            <td>
                                <?php if (empty($slot_templates)) : ?>
                                    <p><?php esc_html_e('Adjust start/end times to generate slots. Overrides will appear here.', 'sameday-logistics'); ?></p>
                                <?php else : ?>
                                    <table class="widefat striped" style="max-width:400px;">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Slot', 'sameday-logistics'); ?></th>
                                                <th><?php esc_html_e('Capacity', 'sameday-logistics'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($slot_templates as $slot) :
                                                $key = $slot['key'];
                                                $value = isset($settings['slot_capacities'][$key]) ? $settings['slot_capacities'][$key] : '';
                                                ?>
                                                <tr>
                                                    <td><?php echo esc_html($slot['label']); ?></td>
                                                    <td>
                                                        <input type="number" min="0" name="<?php echo esc_attr(self::OPTION_KEY); ?>[slot_capacities][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" class="small-text" />
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <p class="description"><?php esc_html_e('Leave blank to use the default capacity for a slot.', 'sameday-logistics'); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function add_checkout_field($fields) {
        $fields['order']['delivery_timeslot'] = [
            'type' => 'select',
            'required' => true,
            'label' => __('Delivery Time Slot', 'sameday-logistics'),
            'options' => [
                '' => __('Select a delivery slot', 'sameday-logistics'),
            ],
            'class' => ['form-row-wide'],
            'priority' => 30,
        ];

        return $fields;
    }

    public function enqueue_checkout_assets() {
        if (!is_checkout()) {
            return;
        }

        wp_register_script('sameday-logistics', '', ['jquery'], '1.0.0', true);
        wp_enqueue_script('sameday-logistics');

        $selected_slot = '';
        $selected_zip = '';

        if (WC()->session) {
            $selected_slot = (string) WC()->session->get(self::SESSION_SLOT_KEY, '');
            $selected_zip = (string) WC()->session->get(self::SESSION_ZIP_KEY, '');
        }

        $customer = WC()->customer;
        $initial_zip = '';

        if ($customer) {
            $initial_zip = $customer->get_shipping_postcode();
            if (empty($initial_zip)) {
                $initial_zip = $customer->get_billing_postcode();
            }
        }

        $data = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sameday_logistics_nonce'),
            'selected_slot' => $selected_slot,
            'initial_zip' => $selected_zip ? $selected_zip : $initial_zip,
            'messages' => [
                'loading' => __('Checking delivery availability…', 'sameday-logistics'),
                'no_slots' => __('No delivery slots are available. Please try a different day.', 'sameday-logistics'),
                'zip_not_allowed' => __('Same-day delivery is not available in your area.', 'sameday-logistics'),
                'error' => __('Unable to load delivery slots. Please try again.', 'sameday-logistics'),
                'date_prefix' => __('Delivery slots for %s', 'sameday-logistics'),
            ],
        ];

        wp_add_inline_script('sameday-logistics', 'window.SameDayLogistics = ' . wp_json_encode($data) . ';');
        wp_add_inline_script('sameday-logistics', $this->get_inline_script());
    }

    public function handle_slots_request() {
        check_ajax_referer('sameday_logistics_nonce', 'nonce');

        $zip = isset($_POST['zip']) ? sanitize_text_field(wp_unslash($_POST['zip'])) : '';

        if (empty($zip)) {
            wp_send_json_error([
                'message' => __('Enter a ZIP code to view available delivery slots.', 'sameday-logistics'),
            ]);
        }

        if (!$this->is_zip_whitelisted($zip)) {
            wp_send_json_error([
                'message' => __('Same-day delivery is not available for this ZIP code.', 'sameday-logistics'),
            ]);
        }

        $data = $this->get_available_slots_response($zip);

        if (empty($data['slots'])) {
            wp_send_json_error([
                'message' => __('No delivery slots remain for the upcoming delivery day.', 'sameday-logistics'),
            ]);
        }

        if (WC()->session) {
            WC()->session->set(self::SESSION_ZIP_KEY, $zip);
        }

        wp_send_json_success($data);
    }

    public function handle_order_review_update($posted_data) {
        if (!WC()->session) {
            return;
        }

        parse_str($posted_data, $data);

        $zip = '';
        if (!empty($data['shipping_postcode'])) {
            $zip = sanitize_text_field(wp_unslash($data['shipping_postcode']));
        } elseif (!empty($data['billing_postcode'])) {
            $zip = sanitize_text_field(wp_unslash($data['billing_postcode']));
        }

        if ($zip) {
            WC()->session->set(self::SESSION_ZIP_KEY, $zip);
        }

        if (empty($data['delivery_timeslot'])) {
            $this->clear_session_slot();
            return;
        }

        $slot_value = sanitize_text_field(wp_unslash($data['delivery_timeslot']));

        if (!$this->is_valid_slot_value($slot_value)) {
            return;
        }

        if (!$zip || !$this->is_zip_whitelisted($zip)) {
            $this->clear_session_slot();
            return;
        }

        $availability = $this->is_slot_available($slot_value, $this->get_session_token(false));

        if (!$availability['available']) {
            $this->clear_session_slot();
            return;
        }

        WC()->session->set(self::SESSION_SLOT_KEY, $slot_value);
        $this->create_hold($slot_value);
    }

    public function validate_checkout() {
        $zip = '';

        if (!empty($_POST['shipping_postcode'])) {
            $zip = sanitize_text_field(wp_unslash($_POST['shipping_postcode']));
        } elseif (!empty($_POST['billing_postcode'])) {
            $zip = sanitize_text_field(wp_unslash($_POST['billing_postcode']));
        }

        if (!$zip || !$this->is_zip_whitelisted($zip)) {
            $this->clear_session_slot();
            wc_add_notice(__('Same-day delivery is only available within select NYC ZIP codes.', 'sameday-logistics'), 'error');
            return;
        }

        $slot_value = isset($_POST['delivery_timeslot']) ? sanitize_text_field(wp_unslash($_POST['delivery_timeslot'])) : '';

        if (empty($slot_value)) {
            $this->clear_session_slot();
            wc_add_notice(__('Please choose a delivery time slot.', 'sameday-logistics'), 'error');
            return;
        }

        if (!$this->is_valid_slot_value($slot_value)) {
            $this->clear_session_slot();
            wc_add_notice(__('The selected delivery slot is invalid. Please choose another slot.', 'sameday-logistics'), 'error');
            return;
        }

        $availability = $this->is_slot_available($slot_value, $this->get_session_token(false));

        if (!$availability['available']) {
            $this->clear_session_slot();
            wc_add_notice(__('The selected delivery slot is no longer available. Please pick another slot.', 'sameday-logistics'), 'error');
            return;
        }

        if (WC()->session) {
            WC()->session->set(self::SESSION_SLOT_KEY, $slot_value);
            WC()->session->set(self::SESSION_ZIP_KEY, $zip);
        }

        $this->create_hold($slot_value);
    }

    public function add_order_meta($order, $data) {
        $slot_value = '';

        if (!empty($_POST['delivery_timeslot'])) {
            $slot_value = sanitize_text_field(wp_unslash($_POST['delivery_timeslot']));
        } elseif (WC()->session) {
            $slot_value = (string) WC()->session->get(self::SESSION_SLOT_KEY, '');
        }

        if (!$this->is_valid_slot_value($slot_value)) {
            return;
        }

        [$date, $slot_key] = $this->parse_slot_value($slot_value);

        $order->update_meta_data('_sameday_delivery_slot_key', $slot_value);
        $order->update_meta_data('_sameday_delivery_date', $date);
        $order->update_meta_data('_sameday_delivery_slot', $slot_key);
        $order->update_meta_data('_sameday_delivery_display', $this->format_full_slot_label($date, $slot_key));

        if (!empty($data['shipping_postcode'])) {
            $zip = sanitize_text_field($data['shipping_postcode']);
        } elseif (!empty($data['billing_postcode'])) {
            $zip = sanitize_text_field($data['billing_postcode']);
        } else {
            $zip = '';
        }

        if ($zip) {
            $order->update_meta_data('_sameday_delivery_zip', $zip);
        }

        $this->release_hold($slot_value);
    }

    public function render_admin_order_meta($order) {
        $slot_display = $order->get_meta('_sameday_delivery_display');

        if (!$slot_display) {
            return;
        }
        printf('<p><strong>%s:</strong> %s</p>', esc_html__('Same-day Delivery', 'sameday-logistics'), esc_html($slot_display));
    }

    public function render_email_order_meta($order, $sent_to_admin, $plain_text) {
        $slot_display = $order->get_meta('_sameday_delivery_display');

        if (!$slot_display) {
            return;
        }

        $label = __('Delivery Window', 'sameday-logistics');

        if ($plain_text) {
            echo "\n" . $label . ': ' . $slot_display . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return;
        }

        printf('<p><strong>%s:</strong> %s</p>', esc_html($label), esc_html($slot_display));
    }

    public function render_frontend_order_meta($order) {
        $slot_display = $order->get_meta('_sameday_delivery_display');

        if (!$slot_display) {
            return;
        }
        ?>
        <section class="woocommerce-order-section">
            <h2><?php esc_html_e('Delivery Window', 'sameday-logistics'); ?></h2>
            <p><?php echo esc_html($slot_display); ?></p>
        </section>
        <?php
    }

    public function clear_session_data() {
        if (!WC()->session) {
            return;
        }

        $this->clear_session_slot();
        WC()->session->__unset(self::SESSION_TOKEN_KEY);
        WC()->session->__unset(self::SESSION_ZIP_KEY);
    }

    public function handle_order_status_change($order_id, $old_status, $new_status, $order) {
        if (!$order || !$order_id) {
            return;
        }

        $slot_value = $order->get_meta('_sameday_delivery_slot_key');

        if (!$slot_value) {
            return;
        }

        // Release hold if order is cancelled, refunded, or failed
        if (in_array($new_status, ['cancelled', 'refunded', 'failed'], true)) {
            $this->release_hold_for_order($slot_value, $order_id);
        }
    }

    private function release_hold_for_order($slot_value, $order_id) {
        if (!$slot_value) {
            return;
        }

        $holds = $this->get_all_holds();

        if (!isset($holds[$slot_value]) || !is_array($holds[$slot_value])) {
            return;
        }

        // Remove any holds for this slot and order
        foreach ($holds[$slot_value] as $token => $expiry) {
            unset($holds[$slot_value][$token]);
        }

        if (empty($holds[$slot_value])) {
            unset($holds[$slot_value]);
        }

        update_option(self::OPTION_HOLDS_KEY, $holds);
    }

    private function get_settings() {
        $defaults = $this->get_default_settings();
        $settings = get_option(self::OPTION_KEY, []);

        if (!is_array($settings)) {
            return $defaults;
        }

        return wp_parse_args($settings, $defaults);
    }

    private function get_default_settings() {
        return [
            'zip_whitelist' => ['10001', '10002', '10003', '10004', '10005', '10006', '10007', '10009', '10010', '10011', '10012', '10013'],
            'default_capacity' => 4,
            'slot_start' => '10:00',
            'slot_end' => '20:00',
            'cutoff_time' => '14:00',
            'blackout_dates' => [],
            'slot_capacities' => [],
        ];
    }

    public function sanitize_settings($input) {
        $defaults = $this->get_default_settings();
        $output = $defaults;

        if (isset($input['zip_whitelist'])) {
            $raw = preg_split('/[\r\n,]+/', (string) $input['zip_whitelist']);
            $output['zip_whitelist'] = [];

            foreach ($raw as $zip) {
                $zip = trim($zip);
                if ($zip !== '') {
                    $output['zip_whitelist'][] = substr(preg_replace('/[^0-9]/', '', $zip), 0, 10);
                }
            }

            $output['zip_whitelist'] = array_values(array_unique(array_filter($output['zip_whitelist'])));
        }

        if (isset($input['default_capacity'])) {
            $capacity = intval($input['default_capacity']);
            $output['default_capacity'] = max(1, $capacity);
        }

        $output['slot_start'] = $this->sanitize_time_field($input['slot_start'] ?? $defaults['slot_start'], $defaults['slot_start']);
        $output['slot_end'] = $this->sanitize_time_field($input['slot_end'] ?? $defaults['slot_end'], $defaults['slot_end']);
        $output['cutoff_time'] = $this->sanitize_time_field($input['cutoff_time'] ?? $defaults['cutoff_time'], $defaults['cutoff_time']);

        $output['blackout_dates'] = [];
        if (!empty($input['blackout_dates'])) {
            $dates = preg_split('/[\r\n,]+/', (string) $input['blackout_dates']);
            foreach ($dates as $date) {
                $date = trim($date);
                if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $output['blackout_dates'][] = $date;
                }
            }
        }

        $output['slot_capacities'] = [];
        if (!empty($input['slot_capacities']) && is_array($input['slot_capacities'])) {
            foreach ($input['slot_capacities'] as $key => $value) {
                $key = sanitize_text_field($key);
                if (!$this->is_slot_key($key)) {
                    continue;
                }

                $value = trim((string) $value);
                if ($value === '') {
                    continue;
                }

                $capacity = max(0, intval($value));
                $output['slot_capacities'][$key] = $capacity;
            }
        }

        return $output;
    }

    private function sanitize_time_field($time, $default) {
        $time = trim((string) $time);

        if (!$time) {
            return $default;
        }

        $timezone = $this->get_timezone();
        $formatted = DateTimeImmutable::createFromFormat('H:i', $time, $timezone);

        if (!$formatted) {
            return $default;
        }

        return $formatted->format('H:i');
    }

    private function is_zip_whitelisted($zip) {
        $zip = substr(preg_replace('/[^0-9]/', '', $zip), 0, 10);
        $settings = $this->get_settings();

        return in_array($zip, $settings['zip_whitelist'], true);
    }

    private function get_available_slots_response($zip) {
        $settings = $this->get_settings();
        $timezone = $this->get_timezone();
        $now = new DateTimeImmutable('now', $timezone);

        $target_date = $this->get_first_available_date($now, $settings);
        $selected_token = $this->get_session_token(false);
        $slots = $this->generate_slots_for_date($target_date, $selected_token, true);

        if (empty($slots)) {
            $fallback_date = $this->get_next_available_date($target_date, $settings);
            if ($fallback_date) {
                $slots = $this->generate_slots_for_date($fallback_date, $selected_token, true);
                $target_date = $fallback_date;
            }
        }

        $date_string = $target_date->format('Y-m-d');
        $date_label = $this->format_date_label($target_date, $now);

        $data_slots = [];
        $session_slot = WC()->session ? (string) WC()->session->get(self::SESSION_SLOT_KEY, '') : '';

        foreach ($slots as $slot) {
            $value = $date_string . '|' . $slot['key'];
            $label = sprintf('%s (%d %s)', $slot['label'], $slot['available'], _n('spot left', 'spots left', $slot['available'], 'sameday-logistics'));
            $data_slots[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return [
            'date' => $date_string,
            'date_label' => $date_label,
            'slots' => $data_slots,
            'selected' => $session_slot,
        ];
    }

    private function generate_slots_for_date(DateTimeImmutable $date, $exclude_token = null, $only_available = false) {
        $settings = $this->get_settings();
        $slots = [];

        $start = $this->create_datetime_from_time($date, $settings['slot_start']);
        $end = $this->create_datetime_from_time($date, $settings['slot_end']);

        if (!$start || !$end || $end <= $start) {
            return $slots;
        }

        $interval = new DateInterval('PT' . self::SLOT_INTERVAL_MINUTES . 'M');

        for ($cursor = $start; $cursor < $end; $cursor = $cursor->add($interval)) {
            $slot_end = $cursor->add($interval);

            if ($slot_end > $end) {
                break;
            }

            $slot_key = $cursor->format('H:i') . '-' . $slot_end->format('H:i');
            $capacity = $this->get_slot_capacity($slot_key, $settings);
            $usage = $this->get_slot_usage($date->format('Y-m-d'), $slot_key, $exclude_token);
            $available = max(0, $capacity - $usage['total']);

            if ($only_available && $available <= 0) {
                continue;
            }

            $slots[] = [
                'key' => $slot_key,
                'label' => $this->format_slot_label($cursor, $slot_end),
                'capacity' => $capacity,
                'reserved' => $usage['orders'],
                'holds' => $usage['holds'],
                'available' => $available,
            ];
        }

        return $slots;
    }

    private function generate_slots_for_template() {
        $settings = $this->get_settings();
        $timezone = $this->get_timezone();
        $date = new DateTimeImmutable('today', $timezone);

        return $this->generate_slots_for_date($date, null, false);
    }

    private function get_slot_capacity($slot_key, $settings = null) {
        $settings = $settings ?: $this->get_settings();

        if (isset($settings['slot_capacities'][$slot_key]) && $settings['slot_capacities'][$slot_key] !== '') {
            return max(0, intval($settings['slot_capacities'][$slot_key]));
        }

        return max(0, intval($settings['default_capacity']));
    }

    private function get_slot_usage($date, $slot_key, $exclude_token = null) {
        $slot_value = $date . '|' . $slot_key;

        $order_count = 0;

        if (function_exists('wc_get_orders')) {
            $orders = wc_get_orders([
                'limit' => -1,
                'status' => ['pending', 'processing', 'on-hold', 'completed'],
                'type' => 'shop_order',
                'return' => 'ids',
                'meta_query' => [
                    [
                        'key' => '_sameday_delivery_slot_key',
                        'value' => $slot_value,
                    ],
                ],
            ]);

            $order_count = is_array($orders) ? count($orders) : 0;
        }

        $holds = $this->count_slot_holds($slot_value, $exclude_token);

        return [
            'orders' => $order_count,
            'holds' => $holds,
            'total' => $order_count + $holds,
        ];
    }

    private function count_slot_holds($slot_value, $exclude_token = null) {
        $holds = $this->get_all_holds();

        if (!isset($holds[$slot_value]) || !is_array($holds[$slot_value])) {
            return 0;
        }

        $count = 0;
        $now = time();

        foreach ($holds[$slot_value] as $token => $expiry) {
            if ($exclude_token && $token === $exclude_token) {
                continue;
            }

            if ($expiry > $now) {
                $count++;
            }
        }

        return $count;
    }

    private function get_all_holds() {
        $holds = get_option(self::OPTION_HOLDS_KEY, []);

        if (!is_array($holds)) {
            $holds = [];
        }

        return $this->cleanup_holds($holds);
    }

    private function cleanup_holds(array $holds) {
        $now = time();
        $updated = false;

        foreach ($holds as $slot_value => $tokens) {
            if (!is_array($tokens)) {
                unset($holds[$slot_value]);
                $updated = true;
                continue;
            }

            foreach ($tokens as $token => $expiry) {
                if ($expiry <= $now) {
                    unset($holds[$slot_value][$token]);
                    $updated = true;
                }
            }

            if (empty($holds[$slot_value])) {
                unset($holds[$slot_value]);
                $updated = true;
            }
        }

        if ($updated) {
            update_option(self::OPTION_HOLDS_KEY, $holds);
        }

        return $holds;
    }

    private function create_hold($slot_value) {
        $token = $this->get_session_token();

        if (!$token) {
            return;
        }

        $holds = $this->get_all_holds();

        foreach ($holds as $value => $tokens) {
            if (isset($tokens[$token]) && $value !== $slot_value) {
                unset($holds[$value][$token]);
            }
        }

        if (!isset($holds[$slot_value])) {
            $holds[$slot_value] = [];
        }

        $holds[$slot_value][$token] = time() + (self::HOLD_DURATION_MINUTES * MINUTE_IN_SECONDS);

        update_option(self::OPTION_HOLDS_KEY, $holds);
    }

    private function release_hold($slot_value) {
        $token = $this->get_session_token(false);

        if (!$token) {
            return;
        }

        $holds = $this->get_all_holds();

        if (isset($holds[$slot_value][$token])) {
            unset($holds[$slot_value][$token]);
            if (empty($holds[$slot_value])) {
                unset($holds[$slot_value]);
            }
            update_option(self::OPTION_HOLDS_KEY, $holds);
        }
    }

    private function clear_session_slot() {
        if (!WC()->session) {
            return;
        }

        $slot_value = (string) WC()->session->get(self::SESSION_SLOT_KEY, '');

        if ($slot_value) {
            $this->release_hold($slot_value);
        }

        WC()->session->__unset(self::SESSION_SLOT_KEY);
    }

    private function get_session_token($create = true) {
        if (!WC()->session) {
            return null;
        }

        $token = WC()->session->get(self::SESSION_TOKEN_KEY);

        if (!$token && $create) {
            $token = wp_generate_uuid4();
            WC()->session->set(self::SESSION_TOKEN_KEY, $token);
        }

        return $token ?: null;
    }

    private function is_slot_available($slot_value, $exclude_token = null) {
        if (!$this->is_valid_slot_value($slot_value)) {
            return [
                'available' => false,
            ];
        }

        [$date, $slot_key] = $this->parse_slot_value($slot_value);
        $settings = $this->get_settings();

        if (in_array($date, $settings['blackout_dates'], true)) {
            return [
                'available' => false,
            ];
        }

        $capacity = $this->get_slot_capacity($slot_key, $settings);
        $usage = $this->get_slot_usage($date, $slot_key, $exclude_token);

        return [
            'available' => ($capacity - $usage['total']) > 0,
            'capacity' => $capacity,
            'reserved' => $usage['total'],
        ];
    }

    private function get_first_available_date(DateTimeImmutable $now, array $settings) {
        $date = new DateTimeImmutable('today', $this->get_timezone());

        if ($this->is_after_cutoff($now, $settings)) {
            $date = $date->modify('+1 day');
        }

        while (in_array($date->format('Y-m-d'), $settings['blackout_dates'], true)) {
            $date = $date->modify('+1 day');
        }

        return $date;
    }

    private function get_next_available_date(DateTimeImmutable $date, array $settings) {
        $attempts = 0;
        do {
            $date = $date->modify('+1 day');
            $attempts++;
            if ($attempts > 14) {
                return null;
            }
        } while (in_array($date->format('Y-m-d'), $settings['blackout_dates'], true));

        return $date;
    }

    private function is_after_cutoff(DateTimeImmutable $now, array $settings) {
        $cutoff_time = $settings['cutoff_time'];
        $date_string = $now->format('Y-m-d') . ' ' . $cutoff_time;
        $cutoff = DateTimeImmutable::createFromFormat('Y-m-d H:i', $date_string, $this->get_timezone());

        if (!$cutoff) {
            return false;
        }

        return $now > $cutoff;
    }

    private function format_date_label(DateTimeImmutable $date, DateTimeImmutable $now) {
        $date_string = $date->format('Y-m-d');
        $today = $now->format('Y-m-d');
        $tomorrow = $now->modify('+1 day')->format('Y-m-d');

        if ($date_string === $today) {
            /* translators: %s: formatted date */
            return sprintf(__('Today (%s)', 'sameday-logistics'), wp_date('F j, Y', $date->getTimestamp()));
        }

        if ($date_string === $tomorrow) {
            /* translators: %s: formatted date */
            return sprintf(__('Tomorrow (%s)', 'sameday-logistics'), wp_date('F j, Y', $date->getTimestamp()));
        }

        return wp_date('l, F j, Y', $date->getTimestamp());
    }

    private function create_datetime_from_time(DateTimeImmutable $date, $time) {
        return DateTimeImmutable::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $time, $this->get_timezone());
    }

    private function format_slot_label(DateTimeImmutable $start, DateTimeImmutable $end) {
        return sprintf(
            '%s - %s',
            wp_date('g:i A', $start->getTimestamp()),
            wp_date('g:i A', $end->getTimestamp())
        );
    }

    private function format_full_slot_label($date, $slot_key) {
        [$start_time, $end_time] = explode('-', $slot_key);
        $timezone = $this->get_timezone();
        $start = DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $start_time, $timezone);
        $end = DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $end_time, $timezone);

        if (!$start || !$end) {
            return $date . ' ' . $slot_key;
        }

        $date_label = wp_date('l, F j, Y', $start->getTimestamp());

        return sprintf('%s — %s - %s', $date_label, wp_date('g:i A', $start->getTimestamp()), wp_date('g:i A', $end->getTimestamp()));
    }

    private function is_valid_slot_value($value) {
        if (!$value) {
            return false;
        }

        [$date, $slot_key] = array_pad(explode('|', $value), 2, '');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        return $this->is_slot_key($slot_key);
    }

    private function parse_slot_value($value) {
        [$date, $slot_key] = array_pad(explode('|', $value), 2, '');

        return [$date, $slot_key];
    }

    private function is_slot_key($slot_key) {
        return (bool) preg_match('/^\d{2}:\d{2}-\d{2}:\d{2}$/', $slot_key);
    }

    private function get_inline_script() {
        return <<<'JS'
(function($){
    const settings = window.SameDayLogistics || {};
    const fieldSelector = '#delivery_timeslot';
    const messageId = 'sameday-slot-message';

    function ensureMessageContainer() {
        let $container = $('#' + messageId);
        if (!$container.length) {
            const $field = $(fieldSelector);
            if (!$field.length) {
                return null;
            }
            $container = $('<div/>', {
                id: messageId,
                class: 'woocommerce-info sameday-slot-message'
            });
            $field.closest('.form-row').append($container);
        }
        return $container;
    }

    function setMessage(type, text) {
        const $container = ensureMessageContainer();
        if (!$container) {
            return;
        }
        $container.removeClass('woocommerce-error woocommerce-info');
        if (type === 'error') {
            $container.addClass('woocommerce-error');
        } else {
            $container.addClass('woocommerce-info');
        }
        if (text) {
            $container.text(text).show();
        } else {
            $container.hide();
        }
    }

    function clearOptions() {
        const $field = $(fieldSelector);
        if (!$field.length) {
            return;
        }
        $field.find('option').not(':first').remove();
        $field.prop('disabled', true);
    }

    function populateSlots(data) {
        const $field = $(fieldSelector);
        if (!$field.length) {
            return;
        }
        clearOptions();

        if (!data || !Array.isArray(data.slots) || !data.slots.length) {
            setMessage('error', settings.messages ? settings.messages.no_slots : '');
            return;
        }

        const selected = data.selected || settings.selected_slot || '';

        data.slots.forEach(function(slot){
            const $option = $('<option/>', {
                value: slot.value,
                text: slot.label
            });
            if (selected && slot.value === selected) {
                $option.prop('selected', true);
            }
            $field.append($option);
        });

        $field.prop('disabled', false);

        let infoMessage = '';
        if (data.date_label) {
            if (settings.messages && settings.messages.date_prefix) {
                infoMessage = settings.messages.date_prefix.replace('%s', data.date_label);
            } else {
                infoMessage = data.date_label;
            }
        }

        setMessage('info', infoMessage);
        $(document.body).trigger('update_checkout');
    }

    function fetchSlots(zip) {
        const trimmed = (zip || '').replace(/\s+/g, '');
        if (!trimmed) {
            clearOptions();
            setMessage('info', '');
            return;
        }

        setMessage('info', settings.messages ? settings.messages.loading : '');

        $.post(settings.ajax_url, {
            action: 'sameday_get_slots',
            nonce: settings.nonce,
            zip: trimmed
        }).done(function(response){
            if (response && response.success) {
                populateSlots(response.data);
            } else {
                clearOptions();
                if (response && response.data && response.data.message) {
                    setMessage('error', response.data.message);
                } else {
                    setMessage('error', settings.messages ? settings.messages.error : '');
                }
            }
        }).fail(function(){
            clearOptions();
            setMessage('error', settings.messages ? settings.messages.error : '');
        });
    }

    function bindZipListeners() {
        $(document.body).on('change', '#shipping_postcode, #billing_postcode', function(){
            fetchSlots($(this).val());
        });
    }

    $(function(){
        bindZipListeners();
        const initialZip = settings.initial_zip || '';
        if (initialZip) {
            fetchSlots(initialZip);
        }
    });
})(jQuery);
JS;
    }

    private function get_timezone() {
        return wp_timezone();
    }
}

WooCommerce_Sameday_Logistics::get_instance();
