<?php

class NYCBEDTODAY_Logistics_Slot_Generator_Test extends WP_UnitTestCase {
    
    public function setUp() {
        parent::setUp();
        
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
    
    public function test_generate_slots_for_date() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $slots = NYCBEDTODAY_Logistics_Slot_Generator::get_available_slots($tomorrow);
        
        $this->assertNotEmpty($slots);
        $this->assertEquals(3, count($slots));
        
        $this->assertEquals('14:00', $slots[0]['start']);
        $this->assertEquals('16:00', $slots[0]['end']);
        
        $this->assertEquals('16:00', $slots[1]['start']);
        $this->assertEquals('18:00', $slots[1]['end']);
        
        $this->assertEquals('18:00', $slots[2]['start']);
        $this->assertEquals('20:00', $slots[2]['end']);
    }
    
    public function test_is_blackout_date() {
        update_option('nycbedtoday_logistics_settings', [
            'cutoff_hour' => '10',
            'cutoff_minute' => '00',
            'slot_duration_hours' => '2',
            'slot_capacity' => '10',
            'start_hour' => '14',
            'end_hour' => '20',
            'blackout_dates' => "2024-12-25\n2024-12-31",
        ]);
        
        $this->assertTrue(NYCBEDTODAY_Logistics_Slot_Generator::is_blackout_date('2024-12-25'));
        $this->assertTrue(NYCBEDTODAY_Logistics_Slot_Generator::is_blackout_date('2024-12-31'));
        $this->assertFalse(NYCBEDTODAY_Logistics_Slot_Generator::is_blackout_date('2024-12-26'));
    }
    
    public function test_blackout_date_returns_no_slots() {
        update_option('nycbedtoday_logistics_settings', [
            'cutoff_hour' => '10',
            'cutoff_minute' => '00',
            'slot_duration_hours' => '2',
            'slot_capacity' => '10',
            'start_hour' => '14',
            'end_hour' => '20',
            'blackout_dates' => "2024-12-25",
        ]);
        
        $slots = NYCBEDTODAY_Logistics_Slot_Generator::get_available_slots('2024-12-25');
        
        $this->assertEmpty($slots);
    }
    
    public function test_past_date_returns_no_slots() {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        $slots = NYCBEDTODAY_Logistics_Slot_Generator::get_available_slots($yesterday);
        
        $this->assertEmpty($slots);
    }
}
