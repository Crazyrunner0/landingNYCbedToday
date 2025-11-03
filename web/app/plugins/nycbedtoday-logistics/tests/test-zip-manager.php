<?php

class NYCBEDTODAY_Logistics_ZIP_Manager_Test extends WP_UnitTestCase {
    
    public function setUp() {
        parent::setUp();
        delete_option('nycbedtoday_logistics_zip_whitelist');
    }
    
    public function test_seed_default_zips() {
        NYCBEDTODAY_Logistics_ZIP_Manager::seed_default_zips();
        
        $whitelist = NYCBEDTODAY_Logistics_ZIP_Manager::get_zip_whitelist();
        
        $this->assertNotEmpty($whitelist);
        $this->assertContains('10001', $whitelist);
        $this->assertContains('11201', $whitelist);
    }
    
    public function test_is_zip_whitelisted() {
        NYCBEDTODAY_Logistics_ZIP_Manager::seed_default_zips();
        
        $this->assertTrue(NYCBEDTODAY_Logistics_ZIP_Manager::is_zip_whitelisted('10001'));
        $this->assertFalse(NYCBEDTODAY_Logistics_ZIP_Manager::is_zip_whitelisted('90210'));
    }
    
    public function test_add_zip() {
        NYCBEDTODAY_Logistics_ZIP_Manager::seed_default_zips();
        
        $result = NYCBEDTODAY_Logistics_ZIP_Manager::add_zip('12345');
        
        $this->assertTrue($result);
        $this->assertTrue(NYCBEDTODAY_Logistics_ZIP_Manager::is_zip_whitelisted('12345'));
        
        $duplicate = NYCBEDTODAY_Logistics_ZIP_Manager::add_zip('12345');
        $this->assertFalse($duplicate);
    }
    
    public function test_remove_zip() {
        NYCBEDTODAY_Logistics_ZIP_Manager::seed_default_zips();
        
        $result = NYCBEDTODAY_Logistics_ZIP_Manager::remove_zip('10001');
        
        $this->assertTrue($result);
        $this->assertFalse(NYCBEDTODAY_Logistics_ZIP_Manager::is_zip_whitelisted('10001'));
        
        $nonexistent = NYCBEDTODAY_Logistics_ZIP_Manager::remove_zip('99999');
        $this->assertFalse($nonexistent);
    }
}
