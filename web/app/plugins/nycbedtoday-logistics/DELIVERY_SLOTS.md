# Delivery Slots Generator and Management

This document describes the delivery slots feature, which provides configurable two-hour delivery slots with daily cutoff times, per-slot capacity, and blackout date management.

## Overview

The delivery slots system allows administrators to:

1. **Configure global parameters** for slot generation (duration, capacity, cutoff times)
2. **Generate and manage delivery slots** via admin UI or CLI commands
3. **Adjust individual slot capacity** and mark slots as active/inactive
4. **Set blackout dates** when delivery is not available
5. **Display available slots** to customers based on current time and cutoff rules
6. **Track slot reservations** with automatic capacity management

## Features

### Database Storage

The plugin stores delivery slots in a dedicated database table (`wp_nycbt_delivery_slots`) containing:

- **id** - Unique slot identifier
- **date** - Delivery date (YYYY-MM-DD)
- **start_time** - Slot start time (HH:MM:SS format)
- **end_time** - Slot end time (HH:MM:SS format)
- **capacity** - Maximum deliveries allowed in this slot
- **reserved_count** - Current number of reservations
- **status** - Slot status (active/inactive)
- **created_at** - Slot creation timestamp
- **updated_at** - Last modification timestamp

The table includes indexes on:
- `date` for fast date-based queries
- `status` for filtering active slots
- `date, start_time, end_time` as unique constraint to prevent duplicates

### Global Settings

Configure slot generation parameters in **Settings → Same-day Logistics**:

- **Order Cut-off Time** - Orders after this time are scheduled for next day (default: 10:00 AM)
- **Delivery Hours** - Start and end times for delivery window (default: 2:00 PM - 8:00 PM)
- **Slot Duration** - Length of each slot in hours (default: 2 hours)
- **Slot Capacity** - Maximum deliveries per slot (default: 10)
- **Blackout Dates** - Dates when no delivery is available (one per line, YYYY-MM-DD format)

### Admin UI

The plugin provides a "Delivery Slots" tab in the admin settings page with:

#### Slot Generation Form

- **Start Date** - Generate slots starting from this date (default: today)
- **End Date** - Generate slots until this date (default: 30 days from start)
- **Force Regenerate** - Checkbox to overwrite existing slots in the date range
- **Generate Slots Button** - Creates slots based on selected parameters

#### Slots List

A table showing all upcoming delivery slots with:

- Date and time (start/end)
- Slot capacity (editable inline)
- Current reserved count
- Available spots
- Status (Active/Inactive)
- Actions:
  - **Update** - Change capacity for a slot
  - **Delete** - Remove a slot

Features include:
- Pagination (100 slots per page)
- Only shows future slots from today onwards
- Inline capacity editing with quick update button
- Delete confirmation to prevent accidental removal

### Slot Generation

#### Automatic Generation

On plugin activation, the system generates delivery slots for the next 30 days automatically.

The daily cron job (`nycbt_generate_delivery_slots`) runs daily to generate slots for the next day at the 30-day mark, ensuring continuous slot availability.

#### Manual Generation

Use the admin UI form or WP-CLI commands:

```bash
# Generate slots for 30 days starting today
wp nycbt logistics generate-slots

# Generate slots for specific date range
wp nycbt logistics generate-slots --start-date=2024-12-15 --end-date=2024-12-20

# Force regenerate (overwrite existing slots)
wp nycbt logistics generate-slots --force
```

#### Generation Logic

For each date (excluding blackout dates):

1. Start hour: configured start time (default 14:00)
2. End hour: configured end time (default 20:00)
3. Slot duration: configured duration (default 2 hours)

Example with defaults (2-hour slots, 2:00 PM - 8:00 PM):
- 2:00 PM - 4:00 PM (14:00 - 16:00)
- 4:00 PM - 6:00 PM (16:00 - 18:00)
- 6:00 PM - 8:00 PM (18:00 - 20:00)

#### Idempotent Generation

The generation process checks for existing slots before creating new ones:
- If a slot already exists for the same date/time, it's skipped
- Use `--force` flag to overwrite existing slots
- Re-running without `--force` is safe and won't create duplicates

### Cutoff Rules

#### Same-Day Delivery Eligibility

A slot is available for same-day delivery if:

1. Current time is **before the configured cutoff time** (default 10:00 AM)
2. The date is **not a blackout date**
3. The slot has **available capacity** (reserved < capacity)

#### Next-Day Cutoff

If the current time is **after cutoff time**:
- Today's slots are not available for same-day delivery
- Tomorrow's slots (if not blackout date) become available instead
- Continues to find the next available date

#### Example

With cutoff at 10:00 AM:
- At 9:30 AM: Can book today's 2:00 PM, 4:00 PM, 6:00 PM slots
- At 10:30 AM: Today's slots unavailable; tomorrow's slots available instead
- Blackout dates automatically skipped

### Capacity Management

#### Setting Capacity

1. **Global default** - Set in Settings, applies to all newly generated slots
2. **Per-slot override** - Update individual slots in the "Delivery Slots" tab

#### Tracking Usage

Each slot tracks:
- **Capacity** - Maximum allowed reservations
- **Reserved Count** - Current reservations
- **Available Spots** - Calculated as (Capacity - Reserved Count)

#### Automatic Updates

When a reservation is made or cancelled:
- `reserved_count` is automatically incremented/decremented
- Available spots update in real-time
- Slots at 0 available spots don't appear in frontend

### Blackout Dates

#### Configuration

Set blackout dates in **Settings → Same-day Logistics → Time Slot Settings**:

```
2024-12-25
2024-12-31
2025-01-01
2025-07-04
```

One date per line in YYYY-MM-DD format.

#### Usage

When generating or displaying slots:
- Any date in the blackout list is skipped
- No slots are generated for blackout dates
- No slots are available for selection on blackout dates

#### Examples

Common use cases:
- Public holidays (Christmas, New Year, Independence Day)
- Business closures or special events
- Maintenance days or unexpected closures

### CLI Commands

#### Generate Slots

```bash
wp nycbt logistics generate-slots [--start-date=<date>] [--end-date=<date>] [--force]
```

Options:
- `--start-date` - Start date (YYYY-MM-DD), default: today
- `--end-date` - End date (YYYY-MM-DD), default: 30 days from start
- `--force` - Overwrite existing slots in date range

Example:
```bash
wp nycbt logistics generate-slots --start-date=2024-12-15 --end-date=2025-01-15 --force
```

#### List Slots

```bash
wp nycbt logistics list-slots [--date=<date>] [--status=<status>]
```

Options:
- `--date` - Filter by specific date (YYYY-MM-DD)
- `--status` - Filter by status: 'active' or 'inactive' (default: active)

Example:
```bash
wp nycbt logistics list-slots --date=2024-12-25 --status=active
```

Output:
```
+----+------------+-------+-------+----------+----------+-----------+--------+
| ID | Date       | Start | End   | Capacity | Reserved | Available | Status |
+----+------------+-------+-------+----------+----------+-----------+--------+
| 1  | 2024-12-25 | 14:00 | 16:00 | 10       | 5        | 5         | active |
| 2  | 2024-12-25 | 16:00 | 18:00 | 10       | 10       | 0         | active |
| 3  | 2024-12-25 | 18:00 | 20:00 | 10       | 2        | 8         | active |
+----+------------+-------+-------+----------+----------+-----------+--------+
```

#### Update Slot

```bash
wp nycbt logistics update-slot <slot-id> [--capacity=<capacity>] [--status=<status>]
```

Options:
- `<slot-id>` - The ID of the slot to update (required)
- `--capacity` - New capacity value
- `--status` - New status (active or inactive)

Example:
```bash
wp nycbt logistics update-slot 5 --capacity=15
wp nycbt logistics update-slot 8 --status=inactive
```

#### Delete Slots for Date

```bash
wp nycbt logistics delete-slots <date>
```

Arguments:
- `<date>` - Date to delete slots for (YYYY-MM-DD format, required)

Example:
```bash
wp nycbt logistics delete-slots 2024-12-25
```

## Frontend Display

### Shortcode

The `[nycbt_available_slots]` shortcode now displays slots from the database:

```php
[nycbt_available_slots]
[nycbt_available_slots date="2024-12-20" show_date_picker="yes"]
```

Attributes:
- `date` - Optional specific date (YYYY-MM-DD), defaults to next available
- `show_date_picker` - Show date selector (yes/no), default: yes

### Block

The "Available Slots" Gutenberg block displays database-stored slots with:
- Date selector dropdown
- Formatted time ranges
- Availability status
- Responsive layout

### REST API

The `/wp-json/nycbedtoday-logistics/v1/available-slots` endpoint returns:

```json
{
  "date": "2024-12-20",
  "slots": [
    {
      "date": "2024-12-20",
      "start": "14:00",
      "end": "16:00",
      "label": "2:00 PM - 4:00 PM",
      "available": 8
    },
    {
      "date": "2024-12-20",
      "start": "16:00",
      "end": "18:00",
      "label": "4:00 PM - 6:00 PM",
      "available": 10
    }
  ]
}
```

## Database Schema

### Delivery Slots Table

```sql
CREATE TABLE wp_nycbt_delivery_slots (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    date date NOT NULL,
    start_time time NOT NULL,
    end_time time NOT NULL,
    capacity int(11) NOT NULL DEFAULT 10,
    reserved_count int(11) NOT NULL DEFAULT 0,
    status varchar(20) NOT NULL DEFAULT 'active',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY date_time (date, start_time, end_time),
    KEY status (status),
    KEY date (date)
);
```

### Reservations Table

The existing reservations table tracks customer reservations and updates:

```sql
CREATE TABLE wp_nycbt_slot_reservations (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id bigint(20) UNSIGNED DEFAULT NULL,
    delivery_date date NOT NULL,
    slot_start time NOT NULL,
    slot_end time NOT NULL,
    zip_code varchar(10) NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'reserved',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY order_id (order_id),
    KEY delivery_date (delivery_date),
    KEY status (status)
);
```

## Code Examples

### Generate Slots Programmatically

```php
// Generate slots for next 30 days
$count = NYCBEDTODAY_Logistics_Delivery_Slots::generate_slots(
    current_time('Y-m-d'),
    date('Y-m-d', strtotime('+30 days'))
);
echo "Generated $count slots";

// Force regenerate (overwrite existing)
$count = NYCBEDTODAY_Logistics_Delivery_Slots::generate_slots(
    '2024-12-15',
    '2024-12-20',
    true // force
);
```

### Get Available Slots

```php
// Get slots from generator (respects cutoff and blackout dates)
$slots = NYCBEDTODAY_Logistics_Slot_Generator::get_available_slots('2024-12-20');

// Get all slots for a date (database query)
$slots = NYCBEDTODAY_Logistics_Delivery_Slots::get_slots('2024-12-20', 'active');

// Get specific slot
$slot = NYCBEDTODAY_Logistics_Delivery_Slots::get_slot($slot_id);
```

### Update Slot Capacity

```php
NYCBEDTODAY_Logistics_Delivery_Slots::update_slot($slot_id, [
    'capacity' => 15,
    'status' => 'active'
]);
```

### Check Availability

```php
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

### Manage Reservations

When a customer reserves a slot, the system automatically:

```php
// Reserve a slot
$reservation_id = NYCBEDTODAY_Logistics_Slot_Reservation::reserve_slot(
    '2024-12-20',
    '14:00',
    '16:00',
    '10001',
    $order_id
);

// Automatically increments reserved_count in delivery_slots table

// Cancel reservation
NYCBEDTODAY_Logistics_Slot_Reservation::cancel_reservation($reservation_id);

// Automatically decrements reserved_count in delivery_slots table
```

## Migration and Persistence

### On Fresh Installation

1. Plugin activation creates the `wp_nycbt_delivery_slots` table
2. Default settings are saved (2-hour slots, 10:00 AM cutoff, 10 capacity)
3. Initial 30-day slot generation runs automatically

### Data Persistence

- Delivery slots persist in the database across deploys
- Reservations persist independently
- No slots are lost on plugin deactivation (table remains)
- Plugin reactivation uses existing slots

### Database Migrations

The plugin uses WordPress `dbDelta()` for schema management:
- Schema updates run on activation
- No manual migration steps required
- Safe for Bedrock deployments

## Troubleshooting

### No Slots Showing

**Check:**
1. Is current time before cutoff time?
   ```bash
   wp shell
   > current_time('H:i'); // Compare with cutoff_hour setting
   ```

2. Is the date blackout?
   ```php
   $is_blackout = NYCBEDTODAY_Logistics_Slot_Generator::is_blackout_date('2024-12-25');
   ```

3. Do slots exist in database?
   ```bash
   wp db query "SELECT COUNT(*) FROM wp_nycbt_delivery_slots WHERE date >= CURDATE();"
   ```

4. Do available slots have capacity?
   ```bash
   wp nycbt logistics list-slots --date=2024-12-20
   ```

### Slots Not Generated

**Check generation:**
```bash
wp nycbt logistics generate-slots --start-date=2024-12-15 --end-date=2024-12-20
wp nycbt logistics list-slots --date=2024-12-15
```

**Force regenerate if needed:**
```bash
wp nycbt logistics generate-slots --force
```

### Duplicate Slots

Should not occur due to unique constraint on (date, start_time, end_time).

If duplicates somehow exist:
1. Delete problematic slots
   ```bash
   wp nycbt logistics delete-slots 2024-12-20
   ```
2. Regenerate
   ```bash
   wp nycbt logistics generate-slots --start-date=2024-12-20 --end-date=2024-12-20
   ```

### Capacity Issues

Check individual slot:
```bash
wp nycbt logistics list-slots --date=2024-12-20
```

If reserved count is higher than capacity (data corruption):
```bash
wp db query "UPDATE wp_nycbt_delivery_slots SET reserved_count = 0 WHERE id = 5;"
```

## Performance Considerations

### Indexes

The table has indexes on:
- `date` - Fast date-based queries for calendar views
- `status` - Fast filtering of active slots
- `date + start_time + end_time` - Unique constraint prevents duplicates

### Query Patterns

Common queries are optimized:
- Get slots for a date: uses `date` index
- Filter active slots: uses `status` index
- Check duplicate before insert: uses unique constraint

### Recommended Practices

1. Generate slots in batches (30 days at a time)
2. Archive old reservations periodically (30+ days old)
3. Monitor table size if storing many historical slots
4. Run cleanup on inactive dates

## Customization

### Change Default Settings Programmatically

```php
$settings = NYCBEDTODAY_Logistics_Settings::get_default_settings();
$settings['slot_duration_hours'] = '3';
$settings['slot_capacity'] = '20';
update_option('nycbedtoday_logistics_settings', $settings);

// Regenerate slots with new settings
NYCBEDTODAY_Logistics_Delivery_Slots::generate_slots(
    current_time('Y-m-d'),
    date('Y-m-d', strtotime('+30 days')),
    true // force regenerate
);
```

### Add Custom Slot Status

Extend the update method to support more status values:

```php
NYCBEDTODAY_Logistics_Delivery_Slots::update_slot($slot_id, [
    'status' => 'maintenance' // Custom status
]);
```

### Hooks

Available actions:
- `nycbedtoday_logistics_slot_reserved` - When slot reserved
- `nycbedtoday_logistics_slot_confirmed` - When slot confirmed
- `nycbedtoday_logistics_slot_cancelled` - When slot cancelled

## Version History

### 2.0.0 - Delivery Slots Generator

Added in this update:
- `NYCBEDTODAY_Logistics_Delivery_Slots` class for slot management
- Database storage for delivery slots
- Admin UI for slot management and generation
- CLI commands for slot operations
- Automatic slot generation on activation and daily via cron
- Idempotent slot generation (no duplicates)
- Inline capacity editing
- Per-slot status control
- Integration with existing slot generator and reservations

## See Also

- [README.md](README.md) - Main plugin documentation
- [EXAMPLES.md](EXAMPLES.md) - Code examples and usage patterns
- [INSTALL.md](INSTALL.md) - Installation instructions
