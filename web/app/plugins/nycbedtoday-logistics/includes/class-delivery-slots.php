<?php

defined('ABSPATH') || exit;

class NYCBEDTODAY_Logistics_Delivery_Slots {
    
    private static $table_name = 'nycbt_delivery_slots';
    
    public static function init() {
        add_action('wp_loaded', [self::class, 'maybe_schedule_generation']);
    }
    
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            capacity int(11) NOT NULL DEFAULT 10,
            reserved_count int(11) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY date_time (date, start_time, end_time),
            KEY status (status),
            KEY date (date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function generate_slots($start_date = null, $end_date = null, $force = false) {
        if (empty($start_date)) {
            $start_date = current_time('Y-m-d');
        }
        
        if (empty($end_date)) {
            $end_date = date('Y-m-d', strtotime($start_date . ' +30 days'));
        }
        
        $generated_count = 0;
        $current_date = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        
        while ($current_date <= $end_timestamp) {
            $date = date('Y-m-d', $current_date);
            
            if (!self::is_blackout_date($date)) {
                $slots_for_date = self::generate_slots_for_date($date);
                foreach ($slots_for_date as $slot) {
                    $inserted = self::insert_slot($slot['date'], $slot['start'], $slot['end'], $force);
                    if ($inserted) {
                        $generated_count++;
                    }
                }
            }
            
            $current_date = strtotime('+1 day', $current_date);
        }
        
        return $generated_count;
    }
    
    private static function generate_slots_for_date($date) {
        $start_hour = NYCBEDTODAY_Logistics_Settings::get_setting('start_hour', '14');
        $end_hour = NYCBEDTODAY_Logistics_Settings::get_setting('end_hour', '20');
        $slot_duration = NYCBEDTODAY_Logistics_Settings::get_setting('slot_duration_hours', '2');
        
        $slots = [];
        $current_hour = intval($start_hour);
        $final_hour = intval($end_hour);
        $duration = intval($slot_duration);
        
        while ($current_hour + $duration <= $final_hour) {
            $slot_start = sprintf('%02d:00:00', $current_hour);
            $slot_end = sprintf('%02d:00:00', $current_hour + $duration);
            
            $slots[] = [
                'date' => $date,
                'start' => $slot_start,
                'end' => $slot_end,
            ];
            
            $current_hour += $duration;
        }
        
        return $slots;
    }
    
    private static function insert_slot($date, $start, $end, $force = false) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        $capacity = NYCBEDTODAY_Logistics_Settings::get_setting('slot_capacity', 10);
        
        if (!$force) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE date = %s AND start_time = %s AND end_time = %s",
                $date,
                $start,
                $end
            ));
            
            if ($existing) {
                return false;
            }
        }
        
        $result = $wpdb->insert(
            $table_name,
            [
                'date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'capacity' => $capacity,
                'reserved_count' => 0,
                'status' => 'active',
            ],
            ['%s', '%s', '%s', '%d', '%d', '%s']
        );
        
        return $result !== false;
    }
    
    private static function is_blackout_date($date) {
        $blackout_dates = NYCBEDTODAY_Logistics_Settings::get_setting('blackout_dates', '');
        
        if (empty($blackout_dates)) {
            return false;
        }
        
        $dates = array_map('trim', explode("\n", $blackout_dates));
        return in_array($date, $dates);
    }
    
    public static function get_slots($date = null, $status = 'active') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        if (!empty($date)) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE date = %s AND status = %s ORDER BY start_time ASC",
                $date,
                $status
            ));
        } else {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE status = %s ORDER BY date ASC, start_time ASC",
                $status
            ));
        }
        
        return $results ?: [];
    }
    
    public static function get_slot($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }
    
    public static function update_slot($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $allowed_keys = ['capacity', 'reserved_count', 'status'];
        $update_data = [];
        $format = [];
        
        foreach ($allowed_keys as $key) {
            if (isset($data[$key])) {
                $update_data[$key] = $data[$key];
                $format[] = in_array($key, ['capacity', 'reserved_count']) ? '%d' : '%s';
            }
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $table_name,
            $update_data,
            ['id' => $id],
            $format,
            ['%d']
        );
    }
    
    public static function delete_slot($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->delete(
            $table_name,
            ['id' => $id],
            ['%d']
        );
    }
    
    public static function delete_slots_for_date($date) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->delete(
            $table_name,
            ['date' => $date],
            ['%s']
        );
    }
    
    public static function get_available_capacity($date, $start, $end) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $slot = $wpdb->get_row($wpdb->prepare(
            "SELECT capacity, reserved_count FROM $table_name WHERE date = %s AND start_time = %s AND end_time = %s AND status = 'active'",
            $date,
            $start,
            $end
        ));
        
        if (!$slot) {
            return 0;
        }
        
        return max(0, $slot->capacity - $slot->reserved_count);
    }
    
    public static function increment_reserved_count($date, $start, $end, $amount = 1) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET reserved_count = reserved_count + %d WHERE date = %s AND start_time = %s AND end_time = %s",
            $amount,
            $date,
            $start,
            $end
        ));
    }
    
    public static function decrement_reserved_count($date, $start, $end, $amount = 1) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET reserved_count = GREATEST(0, reserved_count - %d) WHERE date = %s AND start_time = %s AND end_time = %s",
            $amount,
            $date,
            $start,
            $end
        ));
    }
    
    public static function maybe_schedule_generation() {
        if (!wp_next_scheduled('nycbt_generate_delivery_slots')) {
            wp_schedule_event(time(), 'daily', 'nycbt_generate_delivery_slots');
        }
    }
}
