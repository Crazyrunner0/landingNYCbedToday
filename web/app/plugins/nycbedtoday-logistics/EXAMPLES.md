# Usage Examples

## Delivery Slots - CLI Examples

### Generate Delivery Slots

Generate slots for the next 30 days from today:

```bash
wp nycbt logistics generate-slots
```

Generate slots for a specific date range:

```bash
wp nycbt logistics generate-slots --start-date=2024-12-15 --end-date=2025-01-15
```

Force regenerate slots (overwrite existing ones):

```bash
wp nycbt logistics generate-slots --force
```

### List Delivery Slots

List all active delivery slots:

```bash
wp nycbt logistics list-slots
```

List slots for a specific date:

```bash
wp nycbt logistics list-slots --date=2024-12-20
```

List inactive slots:

```bash
wp nycbt logistics list-slots --status=inactive
```

### Update Slot Capacity

Increase capacity for a specific slot:

```bash
wp nycbt logistics update-slot 5 --capacity=15
```

Mark a slot as inactive:

```bash
wp nycbt logistics update-slot 8 --status=inactive
```

### Delete Slots for a Date

Delete all slots for a specific date:

```bash
wp nycbt logistics delete-slots 2024-12-25
```

## Delivery Slots - Programmatic Examples

### Generate Slots in PHP

```php
// Generate 30 days of slots starting today
$count = NYCBEDTODAY_Logistics_Delivery_Slots::generate_slots(
    current_time('Y-m-d'),
    date('Y-m-d', strtotime('+30 days'))
);
echo "Generated $count slots";
```

### Get Slots for a Date

```php
// Get all active slots for a date
$slots = NYCBEDTODAY_Logistics_Delivery_Slots::get_slots('2024-12-20', 'active');

foreach ($slots as $slot) {
    echo $slot->date;
    echo ' ' . $slot->start_time . ' - ' . $slot->end_time;
    echo ' (Capacity: ' . $slot->capacity . ', Reserved: ' . $slot->reserved_count . ')';
}
```

### Check Slot Availability

```php
// Check how many spots are available in a slot
$available = NYCBEDTODAY_Logistics_Delivery_Slots::get_available_capacity(
    '2024-12-20',
    '14:00:00',
    '16:00:00'
);

if ($available > 0) {
    echo "$available spots available";
} else {
    echo "This slot is full";
}
```

### Update Slot Capacity

```php
// Increase capacity for a slot
NYCBEDTODAY_Logistics_Delivery_Slots::update_slot(5, [
    'capacity' => 20,
    'status' => 'active'
]);
```

### Delete a Slot

```php
// Delete a specific slot
NYCBEDTODAY_Logistics_Delivery_Slots::delete_slot(5);

// Delete all slots for a date
NYCBEDTODAY_Logistics_Delivery_Slots::delete_slots_for_date('2024-12-25');
```

### Make a Reservation

```php
// Reserve a slot (automatically increments reserved_count)
$reservation_id = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot(
    '2024-12-20',
    '14:00',
    '16:00',
    '10001',
    $order_id
);

if ($reservation_id) {
    echo "Slot reserved: $reservation_id";
}
```

### Cancel a Reservation

```php
// Cancel a reservation (automatically decrements reserved_count)
NYCBEDTODAY_Logistics_Slot_Reservation::cancel_reservation($reservation_id);
```

## Shortcode Examples

### Basic ZIP Code Checker

Add this to any page or post to display a ZIP code availability checker:

```
[nycbt_check_zip]
```

### Customized ZIP Code Checker

```
[nycbt_check_zip button_text="Check Delivery" placeholder="ZIP Code"]
```

### Available Slots Display

Display available delivery slots:

```
[nycbt_available_slots]
```

### Available Slots for Specific Date

```
[nycbt_available_slots date="2024-12-20"]
```

### Available Slots Without Date Picker

```
[nycbt_available_slots show_date_picker="no"]
```

## API Examples

### JavaScript - Check ZIP Code

```javascript
fetch('/wp-json/nycbedtoday-logistics/v1/check-zip', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce
    },
    body: JSON.stringify({
        zip: '10001'
    })
})
.then(response => response.json())
.then(data => {
    if (data.valid) {
        console.log('ZIP is valid, next available:', data.next_available_date);
    } else {
        console.log('ZIP is not valid:', data.message);
    }
});
```

### JavaScript - Get Available Slots

```javascript
fetch('/wp-json/nycbedtoday-logistics/v1/available-slots?date=2024-12-15', {
    method: 'GET',
    headers: {
        'X-WP-Nonce': nonce
    }
})
.then(response => response.json())
.then(data => {
    console.log('Available slots:', data.slots);
    data.slots.forEach(slot => {
        console.log(`${slot.label} - ${slot.available} spots`);
    });
});
```

### JavaScript - Reserve a Slot

```javascript
fetch('/wp-json/nycbedtoday-logistics/v1/reserve-slot', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce
    },
    body: JSON.stringify({
        date: '2024-12-15',
        start: '14:00',
        end: '16:00',
        zip: '10001'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Reservation ID:', data.reservation_id);
    }
});
```

### PHP - Check ZIP Code

```php
$is_valid = NYCBEDTODAY_Logistics_ZIP_Manager::is_zip_whitelisted('10001');
if ($is_valid) {
    echo 'We deliver to this ZIP code!';
}
```

### PHP - Get Available Slots

```php
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$slots = NYCBEDTODAY_Logistics_Slot_Generator::get_available_slots($tomorrow);

foreach ($slots as $slot) {
    echo sprintf(
        '%s - %d spots available<br>',
        $slot['label'],
        $slot['available']
    );
}
```

### PHP - Reserve a Slot

```php
$reservation_id = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot(
    '2024-12-15',  // date
    '14:00',       // start time
    '16:00',       // end time
    '10001',       // ZIP code
    123            // order ID (optional)
);

if ($reservation_id) {
    echo 'Slot reserved! ID: ' . $reservation_id;
}
```

### PHP - Add Custom ZIP Code

```php
$added = NYCBEDTODAY_Logistics_ZIP_Manager::add_zip('12345');
if ($added) {
    echo 'ZIP code added successfully';
}
```

### PHP - Check Slot Availability

```php
$available = NYCBEDTODAY_Logistics_Slot_Generator::get_slot_available_capacity(
    '2024-12-15',  // date
    '14:00',       // start time
    '16:00'        // end time
);

echo "Available spots: $available";
```

## Custom Hooks Examples

### Action Hook - After Slot Reserved

```php
add_action('woocommerce_checkout_update_order_meta', function($order_id) {
    $reservation_id = get_post_meta($order_id, '_nycbt_reservation_id', true);
    
    if ($reservation_id) {
        // Send custom notification
        // Update external system
        // Log analytics event
        error_log('Slot reserved for order ' . $order_id);
    }
}, 20);
```

### Filter Hook - Customize Slot Label

```php
add_filter('woocommerce_email_order_meta', function($order, $sent_to_admin, $plain_text) {
    $slot_label = get_post_meta($order->get_id(), '_nycbt_delivery_slot_label', true);
    
    if ($slot_label) {
        // Custom formatting
        $custom_label = strtoupper($slot_label);
        update_post_meta($order->get_id(), '_nycbt_delivery_slot_label', $custom_label);
    }
}, 5, 3);
```

## WooCommerce Integration Examples

### Get Delivery Info from Order

```php
$order = wc_get_order($order_id);

$delivery_date = get_post_meta($order_id, '_nycbt_delivery_date', true);
$slot_start = get_post_meta($order_id, '_nycbt_delivery_slot_start', true);
$slot_end = get_post_meta($order_id, '_nycbt_delivery_slot_end', true);
$slot_label = get_post_meta($order_id, '_nycbt_delivery_slot_label', true);

echo "Delivery: $slot_label";
```

### Custom Validation on Checkout

```php
add_action('woocommerce_checkout_process', function() {
    $delivery_date = $_POST['nycbt_delivery_date'] ?? '';
    
    if (empty($delivery_date)) {
        wc_add_notice('Please select a delivery time slot.', 'error');
        return;
    }
    
    // Custom validation logic
    $day_of_week = date('w', strtotime($delivery_date));
    if ($day_of_week == 0) { // Sunday
        wc_add_notice('Sunday delivery requires special handling.', 'notice');
    }
}, 5);
```

## Template Integration

### Add to Theme Template

```php
// In your theme's template file (e.g., page-delivery.php)

if (function_exists('nycbedtoday_logistics_check_zip_shortcode')) {
    echo nycbedtoday_logistics_check_zip_shortcode([
        'button_text' => 'Check Now',
        'placeholder' => 'Your ZIP'
    ]);
}
```

### Custom Widget

```php
class NYCBT_Delivery_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'nycbt_delivery_widget',
            'Delivery Checker'
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo do_shortcode('[nycbt_check_zip]');
        echo $args['after_widget'];
    }
}

add_action('widgets_init', function() {
    register_widget('NYCBT_Delivery_Widget');
});
```

## Admin Examples

### Query Reservations

```php
global $wpdb;
$table = $wpdb->prefix . 'nycbt_slot_reservations';

// Get today's deliveries
$today = current_time('Y-m-d');
$deliveries = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table 
     WHERE delivery_date = %s 
     AND status = 'confirmed'
     ORDER BY slot_start ASC",
    $today
));

foreach ($deliveries as $delivery) {
    echo sprintf(
        "Order #%d: %s - %s (ZIP: %s)<br>",
        $delivery->order_id,
        $delivery->slot_start,
        $delivery->slot_end,
        $delivery->zip_code
    );
}
```

### Export Reservations to CSV

```php
function nycbt_export_reservations() {
    global $wpdb;
    $table = $wpdb->prefix . 'nycbt_slot_reservations';
    
    $reservations = $wpdb->get_results(
        "SELECT * FROM $table ORDER BY delivery_date DESC"
    );
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reservations.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Date', 'Start', 'End', 'ZIP', 'Status']);
    
    foreach ($reservations as $r) {
        fputcsv($output, [
            $r->order_id,
            $r->delivery_date,
            $r->slot_start,
            $r->slot_end,
            $r->zip_code,
            $r->status
        ]);
    }
    
    fclose($output);
    exit;
}
```

## Testing Examples

See the `tests/` directory for complete unit test examples covering:

- ZIP code management
- Slot generation
- Capacity limits
- Blackout dates
- Reservations
