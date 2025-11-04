# Same-Day Logistics System

Complete guide to the same-day delivery logistics plugin and WooCommerce integration.

## Overview

The nycbedtoday-logistics plugin provides comprehensive same-day delivery management:
- ZIP code whitelist validation
- 2-hour time slot generation
- Capacity management per slot
- Blackout date configuration
- WooCommerce checkout integration
- Reservation tracking and persistence

**Plugin Location**: `web/app/plugins/nycbedtoday-logistics/`

## Core Features

### ZIP Code Whitelist

**Management**:
- Admin Settings page with ZIP code tab
- Add/remove ZIP codes via AJAX
- Pre-seeded with 100+ NYC ZIP codes
- Covers all 5 boroughs (Manhattan, Brooklyn, Queens, Bronx, Staten Island)

**REST API**:
```bash
POST /wp-json/nycbedtoday-logistics/v1/check-zip
{
  "zip": "10001"
}

Response:
{
  "valid": true,
  "zip": "10001",
  "next_available_date": "2024-12-15"
}
```

**Frontend Usage**:
- Shortcode: `[nycbt_check_zip]` - ZIP validation form
- REST endpoint for AJAX validation
- Real-time availability checking

### Time Slot Generation

**Configuration Options**:
- **Cut-off time** (default: 10:00 AM)
  - Orders placed after cut-off → next-day delivery
  - Configurable per hour:minute
  
- **Delivery window** (default: 2:00 PM - 8:00 PM)
  - When deliveries are scheduled
  - Customizable start/end times

- **Slot duration** (default: 2 hours)
  - Each slot covers 2-hour window
  - Standard slots: 2-4 PM, 4-6 PM, 6-8 PM

- **Capacity per slot** (default: 10 deliveries)
  - Maximum orders per time window
  - Prevents overloading

**Blackout Dates**:
- Configure holidays and special closure dates
- Prevents booking on unavailable dates
- Line-separated list in admin settings

**REST API**:
```bash
GET /wp-json/nycbedtoday-logistics/v1/available-slots?date=2024-12-15

Response:
{
  "date": "2024-12-15",
  "slots": [
    {
      "start": "14:00",
      "end": "16:00",
      "available": true,
      "capacity": 8,
      "remaining": 3
    },
    ...
  ]
}
```

**Frontend Usage**:
- Shortcode: `[nycbt_available_slots]` - Slot display
- Gutenberg block: Slot selector block
- Real-time capacity updates

### Reservation System

**Slot Reservation**:
```bash
POST /wp-json/nycbedtoday-logistics/v1/reserve-slot
{
  "date": "2024-12-15",
  "start": "14:00",
  "end": "16:00",
  "zip": "10001"
}

Response:
{
  "success": true,
  "reservation_id": 123,
  "expires_at": "2024-12-15 14:30"
}
```

**Reservation Hold**:
- 20-minute hold on reserved slots
- Prevents overbooking during checkout
- Auto-releases if order not placed in time

**Database Table**: `wp_nycbt_slot_reservations`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| order_id | BIGINT | WooCommerce order (nullable) |
| delivery_date | DATE | Scheduled date |
| slot_start | TIME | Start time (14:00) |
| slot_end | TIME | End time (16:00) |
| zip_code | VARCHAR | Delivery ZIP code |
| status | VARCHAR | reserved, confirmed, cancelled |
| created_at | DATETIME | Reservation time |
| updated_at | DATETIME | Last update |

## WooCommerce Integration

### Checkout Integration

**Automatic Slot Selector**:
1. Customer enters ZIP code in shipping
2. POST/AJAX validates ZIP code
3. Available slots automatically load
4. Customer selects desired slot
5. Slot reserved for 20 minutes

**Required Fields**:
- ZIP code must be whitelisted
- Slot must be selected before checkout
- Both validated before payment

**Order Metadata**:
When order is placed, stores:
- `_delivery_date` - Scheduled delivery date
- `_delivery_slot_start` - Start time
- `_delivery_slot_end` - End time
- `_delivery_zip_code` - Confirmed ZIP
- `_delivery_reservation_id` - Reservation tracking ID

### Order Status Handling

**Order Confirmation**:
- Slot reservation changes to "confirmed"
- Order metadata populated
- Customer receives delivery confirmation

**Order Cancellation/Refund**:
- Slot reservation changes to "cancelled"
- Capacity freed up for other orders

**Order Failure**:
- If payment fails, reservation remains active for 20 minutes
- Customer can retry checkout with same slot

### Email Integration

**Customer Email**:
- Delivery date included in order confirmation
- Slot time window displayed
- ZIP code confirmation

**Admin Email**:
- Delivery details in order notification
- Quick overview of scheduled delivery

**Thank You Page**:
- Displays reserved delivery slot
- Shows time window and ZIP code
- Confirms next business day delivery

### Order Edit Page

Admin sees in order details:
- Delivery date as formatted string
- Delivery time slot
- Delivery ZIP code
- Reservation ID for troubleshooting

## Admin Settings

**Location**: Settings → Same-Day Logistics

### Settings Tab

**Time Configuration**:
- Order cut-off time (hour and minute)
- Delivery window start time
- Delivery window end time
- Slot duration in hours
- Capacity per slot

**Default Configuration**:
```
Cut-off: 10:00 AM
Window: 2:00 PM - 8:00 PM
Duration: 2 hours
Capacity: 10 orders per slot
```

**Blackout Dates**:
- Enter dates in YYYY-MM-DD format
- One per line
- Prevents booking on these dates

### ZIP Codes Tab

**Whitelist Management**:
- Display all valid ZIP codes
- Search/filter ZIP codes
- Add new ZIP code (text input + button)
- Remove ZIP code (delete button per row)
- Bulk upload (paste list)
- AJAX operations (no page reload)

**Default NYC ZIP Codes**:
- Manhattan: 10001-10035
- Brooklyn: 11201-11235
- Queens: 11354-11435
- Bronx: 10451-10475
- Staten Island: 10301-10320

### Reservations Tab

**Reservation View**:
- Table of all reservations
- Columns: ID, Order ID, ZIP, Date, Time Slot, Status, Created
- Filter by status (reserved, confirmed, cancelled)
- Search by order ID or ZIP
- Pagination (50 per page)
- Export to CSV (optional)

## Frontend Display

### ZIP Validation Shortcode

```wordpress
[nycbt_check_zip]
```

Renders:
- Input field for ZIP code
- "Check Availability" button
- Success message with next available date
- Error message if ZIP invalid

### Slot Display Shortcode

```wordpress
[nycbt_available_slots]
```

Renders:
- Date selector
- Available slots in grid or list
- Capacity indicator per slot
- "Select Slot" button
- Reserves slot and shows confirmation

### Custom Block: Slot Selector

- Editable in Gutenberg block editor
- Renders ZIP validator + slot selector
- Full-width responsive layout
- Customizable styling via theme.json
- Mobile-friendly interface

## Configuration

### WordPress Options

**nycbedtoday_logistics_settings**:
```php
array(
  'cutoff_hour'         => 10,
  'cutoff_minute'       => 0,
  'slot_duration_hours' => 2,
  'slot_capacity'       => 10,
  'start_hour'          => 14,
  'end_hour'            => 20,
  'blackout_dates'      => "2024-12-25\n2024-12-26"
)
```

**nycbedtoday_logistics_zip_whitelist**:
```php
array(
  '10001', '10002', '10003', // Manhattan
  '11201', '11202',          // Brooklyn
  // ... 100+ ZIP codes
)
```

### Database Activation

Plugin creates table on activation:
```sql
CREATE TABLE wp_nycbt_slot_reservations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED,
  delivery_date DATE NOT NULL,
  slot_start TIME NOT NULL,
  slot_end TIME NOT NULL,
  zip_code VARCHAR(10),
  status VARCHAR(20) DEFAULT 'reserved',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  KEY (order_id),
  KEY (delivery_date),
  KEY (status)
)
```

## Testing

### Manual Testing

**ZIP Code Validation**:
1. Create page with `[nycbt_check_zip]` shortcode
2. Test valid ZIP: 10001 → Success message
3. Test invalid ZIP: 90210 → Error message
4. View admin settings to verify ZIP list

**Slot Display**:
1. Create page with `[nycbt_available_slots]` shortcode
2. Verify 3 slots display (2-4 PM, 4-6 PM, 6-8 PM)
3. Each shows capacity indicator
4. Select slot and confirm reservation

**Checkout Integration**:
1. Add product to cart
2. Go to checkout page
3. Enter valid NYC ZIP in shipping address
4. Verify slots load automatically
5. Select time slot
6. Complete order with Stripe test card
7. Verify order meta contains delivery details
8. Check order confirmation email
9. Review admin order page for delivery info
10. Check thank you page displays slot info

**Order Management**:
1. Cancel order → Verify reservation status changes
2. Refund order → Verify capacity is freed
3. Change order status → Verify no errors

### Unit Tests

```bash
cd web/app/plugins/nycbedtoday-logistics
phpunit
```

Test coverage:
- ✅ ZIP code validation
- ✅ Time slot generation
- ✅ Capacity calculations
- ✅ Blackout date handling
- ✅ Reservation creation/update
- ✅ WooCommerce integration
- ✅ Order metadata storage

## API Endpoints

### Check ZIP Code

```
Endpoint: POST /wp-json/nycbedtoday-logistics/v1/check-zip
Permission: Public
Body: { "zip": "10001" }
Response: {
  "valid": true,
  "zip": "10001",
  "next_available_date": "2024-12-15"
}
```

### Get Available Slots

```
Endpoint: GET /wp-json/nycbedtoday-logistics/v1/available-slots?date=2024-12-15
Permission: Public
Response: {
  "date": "2024-12-15",
  "slots": [
    {
      "start": "14:00",
      "end": "16:00",
      "available": true,
      "capacity": 8,
      "remaining": 3
    }
  ]
}
```

### Reserve Slot

```
Endpoint: POST /wp-json/nycbedtoday-logistics/v1/reserve-slot
Permission: Public
Body: {
  "date": "2024-12-15",
  "start": "14:00",
  "end": "16:00",
  "zip": "10001"
}
Response: {
  "success": true,
  "reservation_id": 123,
  "expires_at": "2024-12-15 14:30"
}
```

## Security

- ✅ Nonce verification on all AJAX requests
- ✅ Capability checks for admin functions
- ✅ Input sanitization using WordPress functions
- ✅ Output escaping (esc_html, esc_attr, esc_url)
- ✅ Prepared SQL statements ($wpdb->prepare)
- ✅ REST API permission callbacks
- ✅ ABSPATH checks in all PHP files

## Troubleshooting

**No slots showing**:
- Check cut-off time hasn't passed
- Verify blackout dates configuration
- Check capacity isn't full
- Ensure time window covers current time

**ZIP validation failing**:
- Verify ZIP in admin whitelist
- Check REST endpoint is accessible
- Test with known-valid ZIP (10001)

**Order metadata missing**:
- Verify plugin activated
- Check WooCommerce integration enabled
- Review error logs for save failures

**Slots not reserving**:
- Check database table exists and is accessible
- Verify 20-minute hold time not expired
- Test REST endpoint directly

## Related Documentation

- [Architecture Guide](architecture.md) - Plugin structure and configuration
- [Deployment Guide](deployment.md) - Deploying to staging/production
- [Operations Runbook](ops-runbook.md) - Common operational tasks
