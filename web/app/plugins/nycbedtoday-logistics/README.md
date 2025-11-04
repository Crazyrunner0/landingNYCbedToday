# NYC Bed Today Same-Day Logistics Plugin

A comprehensive WordPress plugin for managing same-day delivery logistics with ZIP code validation, time slot booking, and seamless WooCommerce integration.

## Features

- **ZIP Code Whitelist Management**: Admin interface to manage eligible NYC delivery ZIP codes
- **2-Hour Time Slot Generator**: Configurable delivery time slots with capacity management
- **Delivery Slots Database Storage**: Persistent slot storage with date, time, capacity, and reservation tracking
- **Slot Generation & Management**: Admin UI and CLI commands to generate, view, edit, and delete delivery slots
- **Configurable Slot Parameters**: Set slot duration, global capacity, delivery hours, and cutoff times
- **Blackout Dates**: Configure dates when delivery is not available
- **Order Cut-off Time**: Configurable cut-off time for same-day delivery (slots unavailable after cutoff)
- **Per-Slot Capacity Management**: Adjust individual slot capacity and track reservations in real-time
- **Public REST API**: Endpoints for ZIP validation and slot availability
- **Shortcodes & Blocks**: Easy content integration for ZIP checking and slot display
- **WooCommerce Integration**: Seamless checkout integration with slot selection and reservation
- **Order Meta Storage**: Delivery slot information stored in order meta and displayed in emails
- **Admin Dashboard**: View and manage all slot reservations
- **CLI Commands**: Generate, list, update, and manage slots from command line
- **Automatic Scheduling**: Daily cron job maintains rolling 30-day slot availability
- **Unit Tests**: Comprehensive test coverage for core functionality

## Installation

1. Upload the `nycbedtoday-logistics` folder to `/wp-content/plugins/` (or `web/app/plugins/` for Bedrock)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically:
   - Create necessary database tables
   - Seed default NYC ZIP codes
   - Set default configuration values

## Configuration

### Settings Page

Navigate to **Settings → Same-day Logistics** in the WordPress admin panel.

#### Time Slot Settings

- **Order Cut-off Time**: Orders placed after this time will be scheduled for the next available day (default: 10:00 AM)
- **Delivery Hours**: Configure the start and end time for delivery windows (default: 2:00 PM - 8:00 PM)
- **Slot Duration**: Length of each delivery slot in hours (default: 2 hours)
- **Slot Capacity**: Maximum number of deliveries per time slot (default: 10)
- **Blackout Dates**: Dates when delivery is not available (one per line, YYYY-MM-DD format)

Example blackout dates:
```
2024-12-25
2024-12-31
2024-01-01
```

### ZIP Code Management

Navigate to the **ZIP Codes** tab to:

- **View all whitelisted ZIP codes**: See the complete list in a sortable grid
- **Add new ZIP codes**: Enter individual ZIP codes to add them to the whitelist
- **Remove ZIP codes**: Click the remove button on any ZIP to delete it
- **Bulk Import**: Paste multiple ZIP codes (one per line or comma-separated) and optionally clear existing codes first
- **Export**: Download your current ZIP whitelist as JSON or CSV format
- **Reseed**: Restore the default NYC ZIP codes (100+ codes covering Manhattan, Brooklyn, Queens, Bronx, and Staten Island)

The plugin includes 100+ default NYC ZIP codes that can be restored at any time with a single click.

### Delivery Slots Management

Navigate to the **Delivery Slots** tab to:

#### Generate Slots

- **Start Date** - Choose when to begin slot generation
- **End Date** - Choose when to end slot generation (default: 30 days ahead)
- **Force Regenerate** - Overwrite existing slots in the date range
- Slots are generated automatically for each day, respecting blackout dates and configured hours

#### View and Manage Slots

- View all upcoming delivery slots
- See slot date, time, capacity, and reservation count
- **Edit Capacity** - Adjust the maximum deliveries for individual slots
- **Delete Slots** - Remove slots for specific dates
- Real-time availability calculation (capacity - reserved count)

### Reservations Dashboard

Navigate to the **Reservations** tab to:

- View all slot reservations
- See delivery dates, time slots, and order status
- Track capacity usage

## Usage

### Shortcodes

#### ZIP Code Checker

Display a ZIP code availability checker:

```
[nycbt_check_zip]
```

With custom text:

```
[nycbt_check_zip button_text="Check My ZIP" placeholder="Enter your ZIP"]
```

#### Available Slots Display

Show available time slots:

```
[nycbt_available_slots]
```

With options:

```
[nycbt_available_slots date="2024-12-20" show_date_picker="yes"]
```

### Gutenberg Blocks

The plugin provides two Gutenberg blocks under the "NYC Bed Today - Logistics" category:

1. **ZIP Code Checker**: Displays a ZIP validation form
2. **Available Slots**: Shows available delivery time slots

### WooCommerce Integration

The plugin automatically integrates with WooCommerce checkout:

1. After entering a shipping ZIP code, available time slots are displayed
2. Customer selects a delivery time slot
3. Slot is reserved and stored with the order
4. Delivery information appears in:
   - Order confirmation page
   - Order emails (customer and admin)
   - Admin order details page

## WP-CLI Commands

### ZIP Code Management

#### Reseed Default ZIP Codes

Reset the ZIP whitelist to default NYC ZIP codes:

```bash
wp nycbt zip reseed
```

This command will replace your current ZIP codes with the default list of 100+ NYC ZIP codes.

#### List ZIP Codes

Display all whitelisted ZIP codes:

```bash
wp nycbt zip list
wp nycbt zip list --format=json
wp nycbt zip list --format=csv
```

Output formats:
- `list` (default): One ZIP per line
- `json`: JSON formatted array
- `csv`: Comma-separated values

#### Import ZIP Codes

Import ZIP codes from a file:

```bash
wp nycbt zip import /path/to/zips.json
wp nycbt zip import /path/to/zips.csv --clear
```

Options:
- `--clear`: Clear existing ZIP codes before importing

Supported formats:
- JSON: `{"zips": ["10001", "10002", ...]}`
- CSV: One ZIP per line or comma-separated

### Delivery Slot Management

#### Generate Slots

```bash
wp nycbt logistics generate-slots
wp nycbt logistics generate-slots --start-date=2024-12-15 --end-date=2024-12-31
wp nycbt logistics generate-slots --force
```

#### List Slots

```bash
wp nycbt logistics list-slots
wp nycbt logistics list-slots --date=2024-12-20 --status=active
```

#### Update Slot

```bash
wp nycbt logistics update-slot 5 --capacity=15
wp nycbt logistics update-slot 8 --status=inactive
```

#### Delete Slots for Date

```bash
wp nycbt logistics delete-slots 2024-12-25
```

See [DELIVERY_SLOTS.md](DELIVERY_SLOTS.md) for complete CLI documentation.

## REST API

### Check ZIP Code

**Endpoint**: `POST /wp-json/nycbedtoday-logistics/v1/check-zip`

**Request**:
```json
{
  "zip": "10001"
}
```

**Response**:
```json
{
  "valid": true,
  "zip": "10001",
  "next_available_date": "2024-12-15"
}
```

### Get Available Slots

**Endpoint**: `GET /wp-json/nycbedtoday-logistics/v1/available-slots?date=2024-12-15`

**Response**:
```json
{
  "date": "2024-12-15",
  "slots": [
    {
      "date": "2024-12-15",
      "start": "14:00",
      "end": "16:00",
      "label": "2:00 PM - 4:00 PM",
      "available": 8
    },
    {
      "date": "2024-12-15",
      "start": "16:00",
      "end": "18:00",
      "label": "4:00 PM - 6:00 PM",
      "available": 10
    }
  ]
}
```

### Reserve Slot

**Endpoint**: `POST /wp-json/nycbedtoday-logistics/v1/reserve-slot`

**Request**:
```json
{
  "date": "2024-12-15",
  "start": "14:00",
  "end": "16:00",
  "zip": "10001"
}
```

**Response**:
```json
{
  "success": true,
  "reservation_id": 123,
  "message": "Slot reserved successfully."
}
```

## Database Schema

### Table: `wp_nycbt_delivery_slots`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint(20) | Primary key |
| date | date | Delivery date |
| start_time | time | Slot start time (HH:MM:SS) |
| end_time | time | Slot end time (HH:MM:SS) |
| capacity | int(11) | Maximum deliveries for this slot |
| reserved_count | int(11) | Current number of reservations |
| status | varchar(20) | Slot status (active, inactive) |
| created_at | datetime | Slot creation time |
| updated_at | datetime | Last update time |

Indexes:
- `date` - For fast date-based queries
- `status` - For filtering active slots
- `UNIQUE (date, start_time, end_time)` - Prevents duplicate slots

### Table: `wp_nycbt_slot_reservations`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint(20) | Primary key |
| order_id | bigint(20) | WooCommerce order ID |
| delivery_date | date | Delivery date |
| slot_start | time | Slot start time |
| slot_end | time | Slot end time |
| zip_code | varchar(10) | Delivery ZIP code |
| status | varchar(20) | Reservation status (reserved, confirmed, cancelled) |
| created_at | datetime | Reservation creation time |
| updated_at | datetime | Last update time |

## Testing

The plugin includes unit tests for core functionality:

```bash
# Run all tests
phpunit

# Run specific test file
phpunit tests/test-zip-manager.php
phpunit tests/test-slot-generator.php
phpunit tests/test-slot-reservation.php
```

### Test Coverage

- ZIP code whitelist management
- Slot generation with various configurations
- Blackout date handling
- Slot capacity limits
- Reservation creation and management
- Order status integration

## Hooks and Filters

### Actions

- `nycbedtoday_logistics_slot_reserved`: Fired when a slot is reserved
- `nycbedtoday_logistics_slot_confirmed`: Fired when a reservation is confirmed
- `nycbedtoday_logistics_slot_cancelled`: Fired when a reservation is cancelled

### Filters

- `nycbedtoday_logistics_default_zips`: Filter default ZIP codes
- `nycbedtoday_logistics_slot_label`: Filter slot label format
- `nycbedtoday_logistics_available_capacity`: Filter available capacity calculation

## Troubleshooting

### Slots Not Showing

1. Check if the current time is before the cut-off time
2. Verify ZIP code is in the whitelist
3. Ensure delivery hours are properly configured
4. Check for blackout dates

### Reservations Not Saving

1. Verify database tables were created properly
2. Check for JavaScript errors in browser console
3. Ensure WooCommerce is activated
4. Verify nonce validation is passing

### ZIP Code Validation Failing

1. Clear browser cache
2. Check REST API is accessible
3. Verify ZIP codes are properly seeded
4. Test ZIP code format (5 digits)

## Support

For issues, questions, or feature requests, please contact the NYC Bed Today development team.

## Version History

### 1.0.0
- Initial release
- ZIP code whitelist management
- Time slot generation and booking
- WooCommerce integration
- REST API endpoints
- Shortcodes and Gutenberg blocks
- Admin dashboard
- Unit tests

## License

Proprietary - NYC Bed Today
