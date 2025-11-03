<?php

defined('ABSPATH') || exit;

class NYCBEDTODAY_Logistics_Settings {
    
    private static $option_name = 'nycbedtoday_logistics_settings';
    
    public static function init() {
        add_action('admin_menu', [self::class, 'add_settings_page']);
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);
    }
    
    public static function activate() {
        if (!get_option(self::$option_name)) {
            $defaults = self::get_default_settings();
            update_option(self::$option_name, $defaults);
        }
    }
    
    public static function get_default_settings() {
        return [
            'cutoff_hour' => '10',
            'cutoff_minute' => '00',
            'slot_duration_hours' => '2',
            'slot_capacity' => '10',
            'start_hour' => '14',
            'end_hour' => '20',
            'blackout_dates' => '',
        ];
    }
    
    public static function get_setting($key, $default = null) {
        $settings = get_option(self::$option_name, self::get_default_settings());
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    public static function add_settings_page() {
        add_options_page(
            __('Same-day Logistics', 'nycbedtoday-logistics'),
            __('Same-day Logistics', 'nycbedtoday-logistics'),
            'manage_options',
            'nycbedtoday-logistics',
            [self::class, 'render_settings_page']
        );
    }
    
    public static function register_settings() {
        register_setting('nycbedtoday_logistics_group', self::$option_name, [
            'sanitize_callback' => [self::class, 'sanitize_settings']
        ]);
        
        add_settings_section(
            'slot_settings_section',
            __('Time Slot Settings', 'nycbedtoday-logistics'),
            [self::class, 'render_slot_settings_section'],
            'nycbedtoday-logistics'
        );
        
        add_settings_field(
            'cutoff_time',
            __('Order Cut-off Time', 'nycbedtoday-logistics'),
            [self::class, 'render_cutoff_time_field'],
            'nycbedtoday-logistics',
            'slot_settings_section'
        );
        
        add_settings_field(
            'delivery_hours',
            __('Delivery Hours', 'nycbedtoday-logistics'),
            [self::class, 'render_delivery_hours_field'],
            'nycbedtoday-logistics',
            'slot_settings_section'
        );
        
        add_settings_field(
            'slot_capacity',
            __('Slot Capacity', 'nycbedtoday-logistics'),
            [self::class, 'render_slot_capacity_field'],
            'nycbedtoday-logistics',
            'slot_settings_section'
        );
        
        add_settings_field(
            'blackout_dates',
            __('Blackout Dates', 'nycbedtoday-logistics'),
            [self::class, 'render_blackout_dates_field'],
            'nycbedtoday-logistics',
            'slot_settings_section'
        );
    }
    
    public static function sanitize_settings($input) {
        $sanitized = [];
        
        $sanitized['cutoff_hour'] = sanitize_text_field($input['cutoff_hour']);
        $sanitized['cutoff_minute'] = sanitize_text_field($input['cutoff_minute']);
        $sanitized['slot_duration_hours'] = sanitize_text_field($input['slot_duration_hours']);
        $sanitized['slot_capacity'] = absint($input['slot_capacity']);
        $sanitized['start_hour'] = sanitize_text_field($input['start_hour']);
        $sanitized['end_hour'] = sanitize_text_field($input['end_hour']);
        $sanitized['blackout_dates'] = sanitize_textarea_field($input['blackout_dates']);
        
        return $sanitized;
    }
    
    public static function render_slot_settings_section() {
        echo '<p>' . __('Configure time slot generation and availability settings.', 'nycbedtoday-logistics') . '</p>';
    }
    
    public static function render_cutoff_time_field() {
        $settings = get_option(self::$option_name, self::get_default_settings());
        $hour = $settings['cutoff_hour'];
        $minute = $settings['cutoff_minute'];
        ?>
        <input type="number" name="<?php echo esc_attr(self::$option_name); ?>[cutoff_hour]" 
               value="<?php echo esc_attr($hour); ?>" min="0" max="23" step="1" style="width: 60px;">
        :
        <input type="number" name="<?php echo esc_attr(self::$option_name); ?>[cutoff_minute]" 
               value="<?php echo esc_attr($minute); ?>" min="0" max="59" step="1" style="width: 60px;">
        <p class="description"><?php _e('Orders placed after this time will be scheduled for the next day (24-hour format).', 'nycbedtoday-logistics'); ?></p>
        <?php
    }
    
    public static function render_delivery_hours_field() {
        $settings = get_option(self::$option_name, self::get_default_settings());
        $start = $settings['start_hour'];
        $end = $settings['end_hour'];
        $duration = $settings['slot_duration_hours'];
        ?>
        <label>
            <?php _e('Start:', 'nycbedtoday-logistics'); ?>
            <input type="number" name="<?php echo esc_attr(self::$option_name); ?>[start_hour]" 
                   value="<?php echo esc_attr($start); ?>" min="0" max="23" step="1" style="width: 60px;">:00
        </label>
        &nbsp;&nbsp;
        <label>
            <?php _e('End:', 'nycbedtoday-logistics'); ?>
            <input type="number" name="<?php echo esc_attr(self::$option_name); ?>[end_hour]" 
                   value="<?php echo esc_attr($end); ?>" min="0" max="23" step="1" style="width: 60px;">:00
        </label>
        &nbsp;&nbsp;
        <label>
            <?php _e('Slot Duration:', 'nycbedtoday-logistics'); ?>
            <input type="number" name="<?php echo esc_attr(self::$option_name); ?>[slot_duration_hours]" 
                   value="<?php echo esc_attr($duration); ?>" min="1" max="12" step="1" style="width: 60px;"> hours
        </label>
        <p class="description"><?php _e('Define the delivery time window (24-hour format).', 'nycbedtoday-logistics'); ?></p>
        <?php
    }
    
    public static function render_slot_capacity_field() {
        $settings = get_option(self::$option_name, self::get_default_settings());
        $capacity = $settings['slot_capacity'];
        ?>
        <input type="number" name="<?php echo esc_attr(self::$option_name); ?>[slot_capacity]" 
               value="<?php echo esc_attr($capacity); ?>" min="1" max="100" step="1" style="width: 100px;">
        <p class="description"><?php _e('Maximum number of deliveries per time slot.', 'nycbedtoday-logistics'); ?></p>
        <?php
    }
    
    public static function render_blackout_dates_field() {
        $settings = get_option(self::$option_name, self::get_default_settings());
        $blackout_dates = $settings['blackout_dates'];
        ?>
        <textarea name="<?php echo esc_attr(self::$option_name); ?>[blackout_dates]" 
                  rows="5" cols="50"><?php echo esc_textarea($blackout_dates); ?></textarea>
        <p class="description"><?php _e('Enter dates when delivery is not available (one per line, YYYY-MM-DD format). Example: 2024-12-25', 'nycbedtoday-logistics'); ?></p>
        <?php
    }
    
    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=nycbedtoday-logistics&tab=settings" 
                   class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Settings', 'nycbedtoday-logistics'); ?>
                </a>
                <a href="?page=nycbedtoday-logistics&tab=zip-codes" 
                   class="nav-tab <?php echo $active_tab === 'zip-codes' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('ZIP Codes', 'nycbedtoday-logistics'); ?>
                </a>
                <a href="?page=nycbedtoday-logistics&tab=reservations" 
                   class="nav-tab <?php echo $active_tab === 'reservations' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Reservations', 'nycbedtoday-logistics'); ?>
                </a>
            </h2>
            
            <?php if ($active_tab === 'settings'): ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('nycbedtoday_logistics_group');
                    do_settings_sections('nycbedtoday-logistics');
                    submit_button();
                    ?>
                </form>
            <?php elseif ($active_tab === 'zip-codes'): ?>
                <?php self::render_zip_codes_tab(); ?>
            <?php elseif ($active_tab === 'reservations'): ?>
                <?php self::render_reservations_tab(); ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public static function render_zip_codes_tab() {
        ?>
        <div class="nycbt-zip-manager">
            <h2><?php _e('NYC ZIP Code Whitelist', 'nycbedtoday-logistics'); ?></h2>
            <p><?php _e('Manage the list of ZIP codes eligible for same-day delivery.', 'nycbedtoday-logistics'); ?></p>
            <div id="nycbt-zip-list"></div>
        </div>
        <?php
    }
    
    public static function render_reservations_tab() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nycbt_slot_reservations';
        
        $reservations = $wpdb->get_results(
            "SELECT * FROM {$table_name} 
             ORDER BY delivery_date DESC, slot_start ASC 
             LIMIT 100"
        );
        ?>
        <div class="nycbt-reservations">
            <h2><?php _e('Recent Slot Reservations', 'nycbedtoday-logistics'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Order ID', 'nycbedtoday-logistics'); ?></th>
                        <th><?php _e('Delivery Date', 'nycbedtoday-logistics'); ?></th>
                        <th><?php _e('Time Slot', 'nycbedtoday-logistics'); ?></th>
                        <th><?php _e('ZIP Code', 'nycbedtoday-logistics'); ?></th>
                        <th><?php _e('Status', 'nycbedtoday-logistics'); ?></th>
                        <th><?php _e('Reserved At', 'nycbedtoday-logistics'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reservations)): ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td>
                                    <?php if ($reservation->order_id): ?>
                                        <a href="<?php echo admin_url('post.php?post=' . $reservation->order_id . '&action=edit'); ?>">
                                            #<?php echo esc_html($reservation->order_id); ?>
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(date('M j, Y', strtotime($reservation->delivery_date))); ?></td>
                                <td><?php echo esc_html($reservation->slot_start . ' - ' . $reservation->slot_end); ?></td>
                                <td><?php echo esc_html($reservation->zip_code); ?></td>
                                <td><?php echo esc_html($reservation->status); ?></td>
                                <td><?php echo esc_html(date('M j, Y g:i A', strtotime($reservation->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6"><?php _e('No reservations found.', 'nycbedtoday-logistics'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    public static function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_nycbedtoday-logistics') {
            return;
        }
        
        wp_enqueue_style(
            'nycbedtoday-logistics-admin',
            NYCBEDTODAY_LOGISTICS_URL . '/assets/admin.css',
            [],
            NYCBEDTODAY_LOGISTICS_VERSION
        );
        
        wp_enqueue_script(
            'nycbedtoday-logistics-admin',
            NYCBEDTODAY_LOGISTICS_URL . '/assets/admin.js',
            ['jquery'],
            NYCBEDTODAY_LOGISTICS_VERSION,
            true
        );
        
        wp_localize_script('nycbedtoday-logistics-admin', 'nycbtLogistics', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nycbt_logistics_nonce')
        ]);
    }
}
