<?php

class NYCBEDTODAY_Logistics_Slot_Reservation_Test extends WP_UnitTestCase {
    
    public function setUp() {
        parent::setUp();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'nycbt_slot_reservations';
        $wpdb->query("TRUNCATE TABLE {$table_name}");
        
        update_option('nycbedtoday_logistics_settings', [
            'cutoff_hour' => '10',
            'cutoff_minute' => '00',
            'slot_duration_hours' => '2',
            'slot_capacity' => '10',
            'start_hour' => '14',
            'end_hour' => '20',
            'blackout_dates' => '',
        ]);
    }
    
    public function test_reserve_slot() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $reservation_id = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot(
            $tomorrow,
            '14:00',
            '16:00',
            '10001'
        );
        
        $this->assertNotFalse($reservation_id);
        $this->assertGreaterThan(0, $reservation_id);
    }
    
    public function test_count_reservations() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot($tomorrow, '14:00', '16:00', '10001');
        NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot($tomorrow, '14:00', '16:00', '10002');
        
        $count = NYCBEDTODAY_Logistics_Slot_Reservation::count_reservations($tomorrow, '14:00', '16:00');
        
        $this->assertEquals(2, $count);
    }
    
    public function test_slot_capacity_limit() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        for ($i = 0; $i < 10; $i++) {
            $result = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot(
                $tomorrow,
                '14:00',
                '16:00',
                '10001'
            );
            $this->assertNotFalse($result);
        }
        
        $result = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot(
            $tomorrow,
            '14:00',
            '16:00',
            '10001'
        );
        
        $this->assertFalse($result);
    }
    
    public function test_update_reservation_order() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $reservation_id = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot(
            $tomorrow,
            '14:00',
            '16:00',
            '10001'
        );
        
        $result = NYCBEDTODAY_Logistics_Slot_Reservation::update_reservation_order($reservation_id, 123);
        
        $this->assertNotFalse($result);
        
        $reservation = NYCBEDTODAY_Logistics_Slot_Reservation::get_reservation($reservation_id);
        $this->assertEquals(123, $reservation->order_id);
    }
    
    public function test_cancel_reservation() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $reservation_id = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot(
            $tomorrow,
            '14:00',
            '16:00',
            '10001'
        );
        
        NYCBEDTODAY_Logistics_Slot_Reservation::cancel_reservation($reservation_id);
        
        $reservation = NYCBEDTODAY_Logistics_Slot_Reservation::get_reservation($reservation_id);
        $this->assertEquals('cancelled', $reservation->status);
    }
}
