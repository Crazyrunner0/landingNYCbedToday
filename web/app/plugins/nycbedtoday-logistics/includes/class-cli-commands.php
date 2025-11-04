<?php

defined('ABSPATH') || exit;

if (class_exists('WP_CLI_Command')) {
    class NYCBEDTODAY_Logistics_CLI_Commands extends WP_CLI_Command {
        
        public function reseed_zips($args, $assoc_args) {
            WP_CLI::line('Reseeding default NYC ZIP codes...');
            
            $zips = NYCBEDTODAY_Logistics_ZIP_Manager::reseed_default_zips();
            
            if (!empty($zips)) {
                WP_CLI::success(sprintf('Reseeded %d NYC ZIP codes successfully.', count($zips)));
            } else {
                WP_CLI::error('Failed to reseed ZIP codes.');
            }
        }
        
        public function list_zips($args, $assoc_args) {
            $zips = NYCBEDTODAY_Logistics_ZIP_Manager::get_zip_whitelist();
            
            if (empty($zips)) {
                WP_CLI::line('No ZIP codes in whitelist.');
                return;
            }
            
            $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'list';
            
            if ($format === 'json') {
                WP_CLI::line(wp_json_encode($zips, JSON_PRETTY_PRINT));
            } elseif ($format === 'csv') {
                WP_CLI::line(implode(',', $zips));
            } else {
                foreach ($zips as $zip) {
                    WP_CLI::line($zip);
                }
            }
        }
        
        public function import_zips($args, $assoc_args) {
            if (empty($args[0])) {
                WP_CLI::error('File path is required.');
                return;
            }
            
            $file_path = $args[0];
            $clear_existing = isset($assoc_args['clear']);
            
            if (!file_exists($file_path)) {
                WP_CLI::error('File not found: ' . $file_path);
                return;
            }
            
            $zips = NYCBEDTODAY_Logistics_ZIP_Manager::load_zips_from_file($file_path);
            
            if (empty($zips)) {
                WP_CLI::error('No ZIP codes found in file.');
                return;
            }
            
            $zips_text = implode("\n", $zips);
            $imported = NYCBEDTODAY_Logistics_ZIP_Manager::import_zips($zips_text, $clear_existing);
            
            if ($imported > 0 || !$clear_existing) {
                WP_CLI::success(sprintf('Imported %d ZIP codes from %s', count($zips), basename($file_path)));
            } else {
                WP_CLI::error('Failed to import ZIP codes.');
            }
        }
        
        public function generate_slots($args, $assoc_args) {
            $start_date = isset($assoc_args['start-date']) ? $assoc_args['start-date'] : null;
            $end_date = isset($assoc_args['end-date']) ? $assoc_args['end-date'] : null;
            $force = isset($assoc_args['force']);
            
            if ($start_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
                WP_CLI::error('Invalid start-date format. Use YYYY-MM-DD');
                return;
            }
            
            if ($end_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
                WP_CLI::error('Invalid end-date format. Use YYYY-MM-DD');
                return;
            }
            
            WP_CLI::line('Generating delivery slots...');
            
            $count = NYCBEDTODAY_Logistics_Delivery_Slots::generate_slots($start_date, $end_date, $force);
            
            WP_CLI::success("Generated $count new delivery slots.");
        }
        
        public function list_slots($args, $assoc_args) {
            $date = isset($assoc_args['date']) ? $assoc_args['date'] : null;
            $status = isset($assoc_args['status']) ? $assoc_args['status'] : 'active';
            
            $slots = NYCBEDTODAY_Logistics_Delivery_Slots::get_slots($date, $status);
            
            if (empty($slots)) {
                WP_CLI::line('No slots found.');
                return;
            }
            
            $table_data = [];
            foreach ($slots as $slot) {
                $table_data[] = [
                    'ID' => $slot->id,
                    'Date' => $slot->date,
                    'Start' => $slot->start_time,
                    'End' => $slot->end_time,
                    'Capacity' => $slot->capacity,
                    'Reserved' => $slot->reserved_count,
                    'Available' => $slot->capacity - $slot->reserved_count,
                    'Status' => $slot->status,
                ];
            }
            
            WP_CLI\Utils\format_items('table', $table_data, ['ID', 'Date', 'Start', 'End', 'Capacity', 'Reserved', 'Available', 'Status']);
        }
        
        public function update_slot($args, $assoc_args) {
            if (empty($args[0])) {
                WP_CLI::error('Slot ID is required.');
                return;
            }
            
            $slot_id = intval($args[0]);
            $data = [];
            
            if (isset($assoc_args['capacity'])) {
                $data['capacity'] = intval($assoc_args['capacity']);
            }
            
            if (isset($assoc_args['status'])) {
                $data['status'] = sanitize_text_field($assoc_args['status']);
            }
            
            if (empty($data)) {
                WP_CLI::error('No data to update. Use --capacity or --status.');
                return;
            }
            
            $result = NYCBEDTODAY_Logistics_Delivery_Slots::update_slot($slot_id, $data);
            
            if ($result === false) {
                WP_CLI::error('Failed to update slot.');
                return;
            }
            
            WP_CLI::success("Slot $slot_id updated successfully.");
        }
        
        public function delete_slots_for_date($args, $assoc_args) {
            if (empty($args[0])) {
                WP_CLI::error('Date is required (YYYY-MM-DD format).');
                return;
            }
            
            $date = $args[0];
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                WP_CLI::error('Invalid date format. Use YYYY-MM-DD');
                return;
            }
            
            $result = NYCBEDTODAY_Logistics_Delivery_Slots::delete_slots_for_date($date);
            
            if ($result === false) {
                WP_CLI::error('Failed to delete slots.');
                return;
            }
            
            WP_CLI::success("Deleted $result slots for $date.");
        }
    }
    
    WP_CLI::add_command('nycbt zip reseed', array('NYCBEDTODAY_Logistics_CLI_Commands', 'reseed_zips'));
    WP_CLI::add_command('nycbt zip list', array('NYCBEDTODAY_Logistics_CLI_Commands', 'list_zips'));
    WP_CLI::add_command('nycbt zip import', array('NYCBEDTODAY_Logistics_CLI_Commands', 'import_zips'));
    WP_CLI::add_command('nycbt logistics generate-slots', array('NYCBEDTODAY_Logistics_CLI_Commands', 'generate_slots'));
    WP_CLI::add_command('nycbt logistics list-slots', array('NYCBEDTODAY_Logistics_CLI_Commands', 'list_slots'));
    WP_CLI::add_command('nycbt logistics update-slot', array('NYCBEDTODAY_Logistics_CLI_Commands', 'update_slot'));
    WP_CLI::add_command('nycbt logistics delete-slots', array('NYCBEDTODAY_Logistics_CLI_Commands', 'delete_slots_for_date'));
}
