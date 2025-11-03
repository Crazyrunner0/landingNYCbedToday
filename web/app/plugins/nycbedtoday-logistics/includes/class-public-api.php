<?php

defined('ABSPATH') || exit;

class NYCBEDTODAY_Logistics_Public_API {
    
    public static function init() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }
    
    public static function register_routes() {
        register_rest_route('nycbedtoday-logistics/v1', '/check-zip', [
            'methods' => 'POST',
            'callback' => [self::class, 'check_zip'],
            'permission_callback' => '__return_true',
            'args' => [
                'zip' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
        
        register_rest_route('nycbedtoday-logistics/v1', '/available-slots', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_available_slots'],
            'permission_callback' => '__return_true',
            'args' => [
                'date' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
        
        register_rest_route('nycbedtoday-logistics/v1', '/reserve-slot', [
            'methods' => 'POST',
            'callback' => [self::class, 'reserve_slot'],
            'permission_callback' => '__return_true',
            'args' => [
                'date' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'start' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'end' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'zip' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }
    
    public static function check_zip($request) {
        $zip = $request->get_param('zip');
        
        $zip = preg_replace('/[^0-9]/', '', $zip);
        $zip = str_pad($zip, 5, '0', STR_PAD_LEFT);
        
        $is_valid = NYCBEDTODAY_Logistics_ZIP_Manager::is_zip_whitelisted($zip);
        
        $response = [
            'valid' => $is_valid,
            'zip' => $zip,
        ];
        
        if ($is_valid) {
            $next_date = NYCBEDTODAY_Logistics_Slot_Generator::get_next_available_date();
            $response['next_available_date'] = $next_date;
        } else {
            $response['message'] = __('Sorry, we do not deliver to this ZIP code.', 'nycbedtoday-logistics');
        }
        
        return rest_ensure_response($response);
    }
    
    public static function get_available_slots($request) {
        $date = $request->get_param('date');
        
        if (empty($date)) {
            $date = NYCBEDTODAY_Logistics_Slot_Generator::get_next_available_date();
        }
        
        if (empty($date)) {
            return rest_ensure_response([
                'slots' => [],
                'message' => __('No delivery slots available at this time.', 'nycbedtoday-logistics'),
            ]);
        }
        
        $slots = NYCBEDTODAY_Logistics_Slot_Generator::get_available_slots($date);
        
        return rest_ensure_response([
            'date' => $date,
            'slots' => $slots,
        ]);
    }
    
    public static function reserve_slot($request) {
        $date = $request->get_param('date');
        $start = $request->get_param('start');
        $end = $request->get_param('end');
        $zip = $request->get_param('zip');
        
        $zip = preg_replace('/[^0-9]/', '', $zip);
        $zip = str_pad($zip, 5, '0', STR_PAD_LEFT);
        
        if (!NYCBEDTODAY_Logistics_ZIP_Manager::is_zip_whitelisted($zip)) {
            return new WP_Error(
                'invalid_zip',
                __('This ZIP code is not eligible for delivery.', 'nycbedtoday-logistics'),
                ['status' => 400]
            );
        }
        
        $reservation_id = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot($date, $start, $end, $zip);
        
        if (!$reservation_id) {
            return new WP_Error(
                'reservation_failed',
                __('Unable to reserve slot. It may no longer be available.', 'nycbedtoday-logistics'),
                ['status' => 400]
            );
        }
        
        if (WC()->session) {
            WC()->session->set('nycbt_temp_reservation_id', $reservation_id);
        }
        
        return rest_ensure_response([
            'success' => true,
            'reservation_id' => $reservation_id,
            'message' => __('Slot reserved successfully.', 'nycbedtoday-logistics'),
        ]);
    }
}
