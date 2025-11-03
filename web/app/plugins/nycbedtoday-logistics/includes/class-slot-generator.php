<?php

defined('ABSPATH') || exit;

class NYCBEDTODAY_Logistics_Slot_Generator {
    
    public static function init() {
    }
    
    public static function get_available_slots($date = null) {
        if (empty($date)) {
            $date = current_time('Y-m-d');
        }
        
        $today = current_time('Y-m-d');
        
        if ($date < $today) {
            return [];
        }
        
        if ($date === $today && !self::is_before_cutoff()) {
            return [];
        }
        
        if (self::is_blackout_date($date)) {
            return [];
        }
        
        $db_slots = NYCBEDTODAY_Logistics_Delivery_Slots::get_slots($date, 'active');
        
        $slots = [];
        foreach ($db_slots as $slot) {
            $available = $slot->capacity - $slot->reserved_count;
            if ($available > 0) {
                $slots[] = [
                    'date' => $slot->date,
                    'start' => substr($slot->start_time, 0, 5),
                    'end' => substr($slot->end_time, 0, 5),
                    'label' => self::format_slot_label(substr($slot->start_time, 0, 5), substr($slot->end_time, 0, 5)),
                    'available' => $available,
                ];
            }
        }
        
        return $slots;
    }
    
    public static function is_before_cutoff() {
        $cutoff_hour = NYCBEDTODAY_Logistics_Settings::get_setting('cutoff_hour', '10');
        $cutoff_minute = NYCBEDTODAY_Logistics_Settings::get_setting('cutoff_minute', '00');
        
        $current_time = current_time('timestamp');
        $cutoff_time = strtotime(current_time('Y-m-d') . ' ' . $cutoff_hour . ':' . $cutoff_minute . ':00');
        
        return $current_time < $cutoff_time;
    }
    
    public static function is_blackout_date($date) {
        $blackout_dates = NYCBEDTODAY_Logistics_Settings::get_setting('blackout_dates', '');
        
        if (empty($blackout_dates)) {
            return false;
        }
        
        $dates = array_map('trim', explode("\n", $blackout_dates));
        return in_array($date, $dates);
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
            $slot_start = sprintf('%02d:00', $current_hour);
            $slot_end = sprintf('%02d:00', $current_hour + $duration);
            
            $slots[] = [
                'date' => $date,
                'start' => $slot_start,
                'end' => $slot_end,
                'label' => self::format_slot_label($slot_start, $slot_end),
            ];
            
            $current_hour += $duration;
        }
        
        return $slots;
    }
    
    private static function format_slot_label($start, $end) {
        $start_time = strtotime($start);
        $end_time = strtotime($end);
        
        return date('g:i A', $start_time) . ' - ' . date('g:i A', $end_time);
    }
    
    public static function get_slot_available_capacity($date, $start, $end) {
        $start_time = $start . ':00';
        $end_time = $end . ':00';
        return NYCBEDTODAY_Logistics_Delivery_Slots::get_available_capacity($date, $start_time, $end_time);
    }
    
    public static function get_next_available_date() {
        $today = current_time('Y-m-d');
        
        if (self::is_before_cutoff() && !self::is_blackout_date($today)) {
            $slots = self::get_available_slots($today);
            if (!empty($slots)) {
                return $today;
            }
        }
        
        for ($i = 1; $i <= 30; $i++) {
            $date = date('Y-m-d', strtotime($today . ' +' . $i . ' days'));
            
            if (!self::is_blackout_date($date)) {
                $slots = NYCBEDTODAY_Logistics_Delivery_Slots::get_slots($date, 'active');
                if (!empty($slots)) {
                    return $date;
                }
            }
        }
        
        return null;
    }
}
