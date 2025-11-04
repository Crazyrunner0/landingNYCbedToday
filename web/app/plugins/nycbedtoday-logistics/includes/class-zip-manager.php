<?php

defined('ABSPATH') || exit;

class NYCBEDTODAY_Logistics_ZIP_Manager {
    
    private static $option_name = 'nycbedtoday_logistics_zip_whitelist';
    private static $data_file = null;
    
    public static function init() {
        add_action('wp_ajax_nycbt_get_zip_codes', [self::class, 'ajax_get_zip_codes']);
        add_action('wp_ajax_nycbt_add_zip_code', [self::class, 'ajax_add_zip_code']);
        add_action('wp_ajax_nycbt_remove_zip_code', [self::class, 'ajax_remove_zip_code']);
        add_action('wp_ajax_nycbt_bulk_import_zips', [self::class, 'ajax_bulk_import_zips']);
        add_action('wp_ajax_nycbt_export_zips', [self::class, 'ajax_export_zips']);
        add_action('wp_ajax_nycbt_reseed_zips', [self::class, 'ajax_reseed_zips']);
    }
    
    public static function seed_default_zips() {
        if (get_option(self::$option_name)) {
            return;
        }
        
        $default_zips = self::get_default_nyc_zips();
        update_option(self::$option_name, $default_zips);
    }
    
    public static function get_default_nyc_zips() {
        return [
            '10001', '10002', '10003', '10004', '10005', '10006', '10007', '10009',
            '10010', '10011', '10012', '10013', '10014', '10016', '10017', '10018',
            '10019', '10020', '10021', '10022', '10023', '10024', '10025', '10026',
            '10027', '10028', '10029', '10030', '10031', '10032', '10033', '10034',
            '10035', '10036', '10037', '10038', '10039', '10040', '10044', '10065',
            '10069', '10075', '10128', '10280', '10282',
            '10301', '10302', '10303', '10304', '10305', '10306', '10307', '10308',
            '10309', '10310', '10311', '10312', '10314',
            '10451', '10452', '10453', '10454', '10455', '10456', '10457', '10458',
            '10459', '10460', '10461', '10462', '10463', '10464', '10465', '10466',
            '10467', '10468', '10469', '10470', '10471', '10472', '10473', '10474',
            '10475',
            '11004', '11005', '11101', '11102', '11103', '11104', '11105', '11106',
            '11109', '11201', '11203', '11204', '11205', '11206', '11207', '11208',
            '11209', '11210', '11211', '11212', '11213', '11214', '11215', '11216',
            '11217', '11218', '11219', '11220', '11221', '11222', '11223', '11224',
            '11225', '11226', '11228', '11229', '11230', '11231', '11232', '11233',
            '11234', '11235', '11236', '11237', '11238', '11239',
        ];
    }
    
    public static function get_zip_whitelist() {
        return get_option(self::$option_name, []);
    }
    
    public static function is_zip_whitelisted($zip) {
        $whitelist = self::get_zip_whitelist();
        return in_array($zip, $whitelist);
    }
    
    public static function add_zip($zip) {
        $zip = self::sanitize_zip($zip);
        if (empty($zip)) {
            return false;
        }
        
        $whitelist = self::get_zip_whitelist();
        if (!in_array($zip, $whitelist)) {
            $whitelist[] = $zip;
            sort($whitelist);
            update_option(self::$option_name, $whitelist);
            return true;
        }
        return false;
    }
    
    public static function remove_zip($zip) {
        $zip = self::sanitize_zip($zip);
        $whitelist = self::get_zip_whitelist();
        $key = array_search($zip, $whitelist);
        
        if ($key !== false) {
            unset($whitelist[$key]);
            $whitelist = array_values($whitelist);
            update_option(self::$option_name, $whitelist);
            return true;
        }
        return false;
    }
    
    private static function sanitize_zip($zip) {
        $zip = preg_replace('/[^0-9]/', '', $zip);
        return str_pad($zip, 5, '0', STR_PAD_LEFT);
    }
    
    public static function ajax_get_zip_codes() {
        check_ajax_referer('nycbt_logistics_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        
        $zip_codes = self::get_zip_whitelist();
        wp_send_json_success(['zip_codes' => $zip_codes]);
    }
    
    public static function ajax_add_zip_code() {
        check_ajax_referer('nycbt_logistics_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        
        $zip = isset($_POST['zip_code']) ? $_POST['zip_code'] : '';
        
        if (self::add_zip($zip)) {
            wp_send_json_success([
                'message' => 'ZIP code added successfully',
                'zip_codes' => self::get_zip_whitelist()
            ]);
        } else {
            wp_send_json_error(['message' => 'ZIP code already exists or is invalid']);
        }
    }
    
    public static function ajax_remove_zip_code() {
        check_ajax_referer('nycbt_logistics_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        
        $zip = isset($_POST['zip_code']) ? $_POST['zip_code'] : '';
        
        if (self::remove_zip($zip)) {
            wp_send_json_success([
                'message' => 'ZIP code removed successfully',
                'zip_codes' => self::get_zip_whitelist()
            ]);
        } else {
            wp_send_json_error(['message' => 'ZIP code not found']);
        }
    }
    
    public static function ajax_bulk_import_zips() {
        check_ajax_referer('nycbt_logistics_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        
        $zips_text = isset($_POST['zips_text']) ? sanitize_textarea_field($_POST['zips_text']) : '';
        $clear_existing = isset($_POST['clear_existing']) && $_POST['clear_existing'] === 'true';
        
        if (empty($zips_text)) {
            wp_send_json_error(['message' => 'No ZIP codes provided']);
        }
        
        $imported = self::import_zips($zips_text, $clear_existing);
        
        if ($imported > 0) {
            wp_send_json_success([
                'message' => sprintf('Successfully imported %d ZIP codes', $imported),
                'zip_codes' => self::get_zip_whitelist(),
                'count' => count(self::get_zip_whitelist())
            ]);
        } else {
            wp_send_json_error(['message' => 'No valid ZIP codes found in the provided text']);
        }
    }
    
    public static function ajax_export_zips() {
        check_ajax_referer('nycbt_logistics_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        
        $zips = self::get_zip_whitelist();
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'json';
        
        if ($format === 'csv') {
            $content = implode("\n", $zips);
            $filename = 'zip-codes-' . date('Y-m-d-His') . '.csv';
            wp_send_json_success([
                'content' => $content,
                'filename' => $filename,
                'format' => 'csv'
            ]);
        } else {
            $data = [
                'version' => '1.0.0',
                'description' => 'NYC Bed Today ZIP Codes Export',
                'exported_at' => current_time('c'),
                'total_zips' => count($zips),
                'zips' => $zips
            ];
            $content = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $filename = 'zip-codes-' . date('Y-m-d-His') . '.json';
            wp_send_json_success([
                'content' => $content,
                'filename' => $filename,
                'format' => 'json'
            ]);
        }
    }
    
    public static function ajax_reseed_zips() {
        check_ajax_referer('nycbt_logistics_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        
        $result = self::reseed_default_zips();
        
        if ($result) {
            wp_send_json_success([
                'message' => sprintf('Reseeded %d NYC ZIP codes', count($result)),
                'zip_codes' => self::get_zip_whitelist(),
                'count' => count(self::get_zip_whitelist())
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to reseed ZIP codes']);
        }
    }
    
    public static function import_zips($zips_text, $clear_existing = false) {
        $zips = array_filter(array_map('trim', explode("\n", $zips_text)));
        $imported = 0;
        
        if ($clear_existing) {
            update_option(self::$option_name, []);
        }
        
        $existing = self::get_zip_whitelist();
        
        foreach ($zips as $zip) {
            $zip = self::sanitize_zip($zip);
            if (!empty($zip) && !in_array($zip, $existing)) {
                $existing[] = $zip;
                $imported++;
            }
        }
        
        sort($existing);
        update_option(self::$option_name, $existing);
        
        return $imported;
    }
    
    public static function reseed_default_zips() {
        $zips = self::get_default_nyc_zips();
        $zips = array_unique($zips);
        sort($zips);
        update_option(self::$option_name, $zips);
        return $zips;
    }
    
    public static function load_zips_from_file($file_path) {
        if (!file_exists($file_path)) {
            return [];
        }
        
        $content = file_get_contents($file_path);
        
        if (strpos($file_path, '.json') !== false) {
            $data = json_decode($content, true);
            return isset($data['zips']) ? (array) $data['zips'] : [];
        } elseif (strpos($file_path, '.csv') !== false) {
            $lines = explode("\n", $content);
            return array_filter(array_map('trim', $lines));
        }
        
        return [];
    }
}
