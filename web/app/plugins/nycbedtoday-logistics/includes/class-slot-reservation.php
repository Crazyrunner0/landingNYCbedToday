<?php

defined('ABSPATH') || exit;

class NYCBEDTODAY_Logistics_Slot_Reservation {
    
    private static $table_name = 'nycbt_slot_reservations';
    
    public static function init() {
    }
    
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id bigint(20) UNSIGNED DEFAULT NULL,
            delivery_date date NOT NULL,
            slot_start time NOT NULL,
            slot_end time NOT NULL,
            zip_code varchar(10) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'reserved',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY delivery_date (delivery_date),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function reserve_slot($date, $start, $end, $zip_code, $order_id = null) {
        global $wpdb;
        
        $available = NYCBEDTODAY_Logistics_Slot_Generator::get_slot_available_capacity($date, $start, $end);
        
        if ($available <= 0) {
            return false;
        }
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $result = $wpdb->insert(
            $table_name,
            [
                'order_id' => $order_id,
                'delivery_date' => $date,
                'slot_start' => $start,
                'slot_end' => $end,
                'zip_code' => $zip_code,
                'status' => 'reserved',
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s']
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    public static function update_reservation_order($reservation_id, $order_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->update(
            $table_name,
            ['order_id' => $order_id],
            ['id' => $reservation_id],
            ['%d'],
            ['%d']
        );
    }
    
    public static function get_reservation_by_order($order_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d ORDER BY created_at DESC LIMIT 1",
            $order_id
        ));
    }
    
    public static function count_reservations($date, $start, $end) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE delivery_date = %s 
             AND slot_start = %s 
             AND slot_end = %s 
             AND status IN ('reserved', 'confirmed')",
            $date,
            $start,
            $end
        ));
    }
    
    public static function cancel_reservation($reservation_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->update(
            $table_name,
            ['status' => 'cancelled'],
            ['id' => $reservation_id],
            ['%s'],
            ['%d']
        );
    }
    
    public static function confirm_reservation($reservation_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->update(
            $table_name,
            ['status' => 'confirmed'],
            ['id' => $reservation_id],
            ['%s'],
            ['%d']
        );
    }
    
    public static function get_reservation($reservation_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $reservation_id
        ));
    }
}
