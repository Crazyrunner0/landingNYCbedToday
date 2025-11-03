# Slot Integration Complete Guide

This guide provides a comprehensive overview of the checkout slot integration implementation, including architecture, testing, and troubleshooting.

## Quick Start

1. **Access settings:** WooCommerce → Settings → Same-day Delivery
2. **Configure ZIP codes:** Add NYC ZIP codes to whitelist
3. **Set delivery hours:** 10:00 AM to 8:00 PM (example)
4. **Set cut-off time:** 2:00 PM (orders after this see next day's slots)
5. **Test checkout:** Visit shop, add product, proceed to checkout
6. **Place test order:** Enter ZIP code, select delivery slot, complete payment

## System Architecture

### Overview

The slot integration is built on a single mu-plugin: `woocommerce-sameday-logistics.php`

This plugin handles:
- Checkout slot selection field
- Capacity management with hold system
- Order metadata storage
- Email integration
- Admin order display
- Edge case handling (cancellation, refund, failure)

### Key Design Patterns

#### 1. Singleton Pattern
```php
public static function get_instance() {
    if (null === self::$instance) {
        self::$instance = new self();
    }
    return self::$instance;
}
```

#### 2. Session-Based Holds
- Each session gets unique token
- Holds are 20-minute temporary reservations
- Automatic cleanup of expired holds
- Prevents race conditions

#### 3. Real-Time Availability Calculation
```php
$available = $capacity - ($order_count + $active_holds)
```

### Core Classes

**WooCommerce_Sameday_Logistics**

Main implementation class. Implements singleton pattern.

**Key Properties:**
- `OPTION_KEY` - Storage key for settings
- `OPTION_HOLDS_KEY` - Storage key for session holds
- `SESSION_SLOT_KEY` - Session key for selected slot
- `SESSION_TOKEN_KEY` - Session key for customer token
- `HOLD_DURATION_MINUTES` - 20 minutes (default hold time)
- `SLOT_INTERVAL_MINUTES` - 120 minutes (2-hour slots)

## Data Flow

### 1. Checkout Page Load

```
Customer visits checkout
    ↓
Checkout fields loaded (including delivery_timeslot)
    ↓
WooCommerce loads cart and customer data
    ↓
JavaScript initialized (slot selector logic)
```

### 2. ZIP Code Entry

```
Customer enters ZIP code
    ↓
AJAX: POST /admin-ajax.php?action=sameday_get_slots
    ↓
Server validates ZIP is whitelisted
    ↓
Server checks if current time is before cut-off
    ↓
Server calculates available slots (considering holds)
    ↓
Server returns slot list with available counts
    ↓
JavaScript populates dropdown
    ↓
Customer sees: "10:00 AM - 12:00 PM (2 spots left)"
```

### 3. Slot Selection

```
Customer clicks slot
    ↓
Session token created (if not exists)
    ↓
Hold placed (expires in 20 minutes)
    ↓
Slot marked as selected (highlighted in UI)
    ↓
JavaScript stores slot in form hidden fields
```

### 4. Checkout Form Submission

```
Customer clicks "Place Order"
    ↓
Validation triggered: woocommerce_checkout_process
    ↓
  - Check ZIP is in whitelist
  - Check slot value is valid format
  - Check slot still available (capacity > 0)
    ↓
  If validation fails:
    - Add error notice
    - Prevent order creation
    - Return to checkout form
    ↓
  If validation passes:
    - Continue to order creation
```

### 5. Order Creation

```
WooCommerce creates order
    ↓
Hook: woocommerce_checkout_create_order triggered
    ↓
add_order_meta() called:
  - Extract slot from POST/session
  - Save to order metadata:
    * _sameday_delivery_slot_key
    * _sameday_delivery_date
    * _sameday_delivery_slot
    * _sameday_delivery_display
    * _sameday_delivery_zip
  - Release hold (slot now confirmed)
    ↓
Order object saved to database
```

### 6. Email Sending

```
WooCommerce prepares email
    ↓
Hook: woocommerce_email_order_meta triggered
    ↓
render_email_order_meta() adds:
  - HTML: <p><strong>Delivery Window:</strong> [Formatted Slot]</p>
  - Plain text: "Delivery Window: [Formatted Slot]"
    ↓
Email sent to customer and admin
```

### 7. Order Status Changes

```
Order status changes (e.g., manual change in admin)
    ↓
Hook: woocommerce_order_status_changed triggered
    ↓
  If new status is "completed" or "processing":
    - Order confirmed (no action needed)
    ↓
  If new status is "cancelled", "refunded", or "failed":
    - Release hold on slot
    - Slot becomes available again
    ↓
Capacity freed for other customers
```

## Capacity Reservation Logic

### Calculation

```
Available Capacity = Default Capacity - Reserved Orders - Active Holds
```

**Example:**
- Default capacity per slot: 4 deliveries
- Orders already placed: 2
- Active holds (customers in checkout): 1
- Available: 4 - 2 - 1 = 1 slot remaining

### Hold System

Holds serve two purposes:

1. **Prevent Double-Booking** - Once customer selects slot, hold prevents another customer from selecting same slot before payment
2. **Prevent Oversell** - If multiple customers check availability simultaneously, each counts toward capacity

### Hold Lifecycle

```
1. Customer opens checkout
   → Session token generated

2. Customer enters ZIP, slots load
   → No hold yet

3. Customer selects slot
   → Hold placed on slot
   → Hold set to expire in 20 minutes
   → Holds stored in: wp_options (key: sameday_slot_holds)

4. Customer completes checkout → order created
   → Hold released
   → Order meta saved

   OR

   Customer abandons checkout
   → Hold expires (20 minutes later)
   → Automatic cleanup removes expired hold
   → Slot available again
```

### Concurrent Request Handling

```
Two customers select LAST available slot simultaneously:

Customer A                          Customer B
1. Select slot                      1. Select slot
   ↓                                   ↓
2. Hold created (A)                2. Hold created (B)
   Capacity check: 1 - 1 = 0          Capacity check: 1 - 2 = -1
   Status: AVAILABLE                  Status: NOT AVAILABLE
   ↓                                   ↓
3. Checkout process                3. Checkout validation
   Validation passes                   "No longer available" error
   ↓                                   ↓
4. Order created                    4. Retry with different slot
   Hold released (A)
   ↓
5. Slot now full
   (Back to capacity: 4)
```

## Metadata Schema

### Order Metadata

Each order stores delivery information in 5 fields:

| Key | Value | Example | Purpose |
|-----|-------|---------|---------|
| `_sameday_delivery_slot_key` | Slot identifier | "2024-11-15\|10:00-12:00" | Internal reference |
| `_sameday_delivery_date` | Delivery date | "2024-11-15" | Date component |
| `_sameday_delivery_slot` | Time range | "10:00-12:00" | Time component |
| `_sameday_delivery_display` | Formatted display | "Friday, November 15, 2024 — 10:00 AM - 12:00 PM" | For UI/emails |
| `_sameday_delivery_zip` | Customer ZIP | "10001" | Audit trail |

### Holds Storage

```php
// In wp_options table (option_name: sameday_slot_holds)
$holds = [
    "2024-11-15|10:00-12:00" => [
        "uuid-token-1" => 1731712345 (timestamp),
        "uuid-token-2" => 1731712567 (timestamp),
    ],
    "2024-11-15|14:00-16:00" => [
        "uuid-token-3" => 1731712890 (timestamp),
    ],
];
```

## API Reference

### WooCommerce Hooks Used

#### Filters

| Hook | Purpose | When |
|------|---------|------|
| `woocommerce_checkout_fields` | Add/modify checkout fields | Checkout page loads |
| `wp_enqueue_scripts` | Load JS/CSS assets | Every page (filtered to checkout) |

#### Actions

| Hook | Purpose | When |
|------|---------|------|
| `plugins_loaded` | Initialize plugin | Early WordPress boot |
| `admin_init` | Register settings | Admin area initialization |
| `admin_menu` | Add settings page | Admin menu generation |
| `wp_enqueue_scripts` | Load assets | Frontend page load (checkout) |
| `woocommerce_checkout_update_order_review` | Real-time validation | Checkout form update |
| `woocommerce_checkout_process` | Final validation | Before order creation |
| `woocommerce_checkout_create_order` | Store slot metadata | During order creation |
| `woocommerce_admin_order_data_after_billing_address` | Display in admin | Admin order page |
| `woocommerce_email_order_meta` | Include in emails | Email generation |
| `woocommerce_order_details_after_order_table` | Display on frontend | Customer order page |
| `woocommerce_order_status_changed` | Handle cancellations | Order status updates |
| `wp_ajax_sameday_get_slots` | AJAX slot retrieval | Customer ZIP entry |
| `wp_ajax_nopriv_sameday_get_slots` | AJAX (no login) | Customer ZIP entry |
| `wp_thankyou` | Clear session | After successful order |

### AJAX Endpoints

**Get Available Slots**

```
POST /wp-admin/admin-ajax.php
Parameters:
  - action: "sameday_get_slots"
  - nonce: <nonce-token>
  - zip: <customer-zip-code>

Response (success):
{
  "success": true,
  "data": {
    "date": "2024-11-15",
    "date_label": "Today (November 15, 2024)",
    "slots": [
      {
        "value": "2024-11-15|10:00-12:00",
        "label": "10:00 AM - 12:00 PM (2 spots left)"
      },
      {
        "value": "2024-11-15|12:00-14:00",
        "label": "12:00 PM - 2:00 PM (3 spots left)"
      }
    ],
    "selected": "2024-11-15|10:00-12:00"
  }
}

Response (error):
{
  "success": false,
  "data": {
    "message": "Same-day delivery is not available for this ZIP code."
  }
}
```

## Testing Strategy

### Unit Tests (In `/web/app/mu-plugins/tests/test-checkout-slot-integration.php`)

Tests verify:
- ✓ Checkout field is required
- ✓ Valid ZIP codes allow slot selection
- ✓ Invalid ZIP codes reject selection
- ✓ Checkout validation requires slot selection
- ✓ Capacity prevents oversell
- ✓ Order metadata stored correctly
- ✓ Slot info appears in emails
- ✓ Slot info appears in admin
- ✓ Cancelled orders release capacity
- ✓ Refunded orders release capacity
- ✓ Failed orders release capacity
- ✓ Double-reservation on retry prevented
- ✓ Cutoff time enforced
- ✓ Blackout dates enforced
- ✓ Concurrent slot selection handled
- ✓ Thank you page shows info
- ✓ Plain text emails include info

### Manual Testing Checklist

See CHECKOUT_SLOT_INTEGRATION.md for comprehensive manual testing steps.

### Running Tests

```bash
# Set up WordPress test environment
export WP_TESTS_DIR=/path/to/wordpress-tests-lib
export DB_NAME=wordpress_tests
export DB_USER=root
export DB_PASSWORD=password
export DB_HOST=localhost

# Run tests
cd web/app/mu-plugins/tests
phpunit
```

## Configuration

### Settings Page

Navigate to: WooCommerce → Settings → Same-day Delivery

#### ZIP Whitelist
- List of ZIP codes eligible for delivery
- Format: One per line or comma-separated
- Example:
  ```
  10001,10002,10003
  ```

#### Default Slot Capacity
- Orders allowed per slot (default: 4)
- Numeric value (1-100)

#### Slot Window
- Start time: 10:00 (10 AM)
- End time: 20:00 (8 PM)
- Slots generated automatically at 2-hour intervals

#### Daily Cut-off Time
- Example: 14:00 (2 PM)
- After this time, customer sees next day's slots

#### Blackout Dates
- Dates with no deliveries
- Format: YYYY-MM-DD (one per line)
- Example:
  ```
  2024-12-25
  2024-12-26
  2024-01-01
  ```

#### Per-slot Capacity Overrides
- Override default capacity for specific slots
- Format: Slot time: capacity
- Example:
  ```
  10:00-12:00: 6
  12:00-14:00: 8
  14:00-16:00: 4
  16:00-18:00: 4
  18:00-20:00: 3
  ```

## Troubleshooting

### Issue: Slots Not Appearing

**Diagnosis:**
1. Check if current time is before cut-off
2. Check if ZIP code is whitelisted
3. Check if date is not blackout
4. Check if slots are configured

**Solution:**
```php
// Check via WP-CLI
wp option get sameday_logistics_settings

// Example output:
Array (
    [zip_whitelist] => Array ( [0] => 10001 [1] => 10002 )
    [default_capacity] => 4
    [slot_start] => 10:00
    [slot_end] => 20:00
    [cutoff_time] => 14:00
    [blackout_dates] => Array ( )
    [slot_capacities] => Array ( )
)
```

### Issue: "Slot No Longer Available" on Checkout

**Causes:**
1. Another customer booked the last spot
2. Administrator changed capacity
3. Concurrent request won race condition

**Resolution:**
- Normal behavior in high-demand scenarios
- Customer should select different slot
- System prevents oversell (this is working correctly)

### Issue: Delivery Info Not in Email

**Diagnosis:**
1. Check if order metadata exists:
   ```php
   wp post meta get <order-id> _sameday_delivery_display
   ```
2. Check if mu-plugin is active:
   ```bash
   ls -la /path/to/wordpress/wp-content/mu-plugins/
   ```
3. Check if hook is firing (enable debug logging)

**Solution:**
- Verify mu-plugin exists and is readable
- Resend email via admin:
  ```php
  wp wc_order_note <order_id> <message> --customer
  ```
- Check error logs for PHP warnings

### Issue: Capacity Not Releasing

**Diagnosis:**
1. Check order status:
   ```php
   wp post get <order_id> --field=post_status
   ```
2. Check if hold was created:
   ```php
   wp option get sameday_slot_holds
   ```

**Solution:**
- Verify order actually changed status:
  - Admin: Edit order → Change status → Save
  - Via WP-CLI:
    ```php
    wp post update <order_id> --post_status=cancelled
    ```

## Performance Metrics

### Expected Performance

| Operation | Duration |
|-----------|----------|
| ZIP validation | < 50ms |
| Slot calculation | < 100ms |
| Hold creation | < 10ms |
| Order creation with slot | < 200ms |
| Email generation | < 300ms |

### Optimization Considerations

- Holds stored in options table (single option)
- Automatic cleanup prevents unbounded growth
- Slot calculation uses simple math (no complex queries)
- Session-based approach minimizes database queries

### Database Impact

- **New fields:** None (uses post meta)
- **New tables:** None
- **New options:** 2 (settings + holds)
- **Query count:** +1 to +2 per checkout (holds management)

## Security Considerations

### Input Validation

- ZIP codes: Regex `/^\d{1,10}$/`
- Dates: Regex `/^\d{4}-\d{2}-\d{2}$/`
- Times: Regex `/^\d{2}:\d{2}-\d{2}:\d{2}$/`
- Nonces: WP nonce verification

### Data Protection

- No sensitive data exposed to frontend
- Slot information only shown during checkout
- Order metadata restricted to order owners + admins
- Session data cleared after successful order

### Rate Limiting

- No built-in rate limiting
- Recommend adding via security plugin
- Natural rate limiting: 20-minute holds prevent spam

### Admin Settings

- Only users with `manage_woocommerce` can access settings
- Settings sanitized on save
- Values escaped on output

## Integration with Other Systems

### Shipping Methods

- Slot selection is separate from shipping method
- Can be combined: shipping method determines DELIVERY method (method), slot selects TIME
- Example: "Scheduled delivery (2 hours)" → customer picks 10-12 AM slot

### Payment Gateways

- Works with all payment gateways
- Slot held during checkout (not after payment)
- Payment failure doesn't affect slot availability

### Email Systems

- Slot information included in standard WooCommerce emails
- Works with custom email templates (uses hooks)
- Works with email customization plugins

### Frontend Theme

- CSS classes: `.woocommerce-order-section`, `.sameday-slot-message`
- Compatible with any theme
- Can be customized via CSS

## Common Customizations

### Change Hold Duration

```php
// In woocommerce-sameday-logistics.php, change:
private const HOLD_DURATION_MINUTES = 20;
// To:
private const HOLD_DURATION_MINUTES = 30; // 30 minutes
```

### Change Slot Duration

```php
// In woocommerce-sameday-logistics.php, change:
private const SLOT_INTERVAL_MINUTES = 120;
// To:
private const SLOT_INTERVAL_MINUTES = 60; // 1-hour slots
```

### Add Custom Slot Validation

```php
// Add filter in your custom plugin:
add_filter('sameday_logistics_is_slot_available', function($available, $date, $slot_key) {
    // Custom logic here
    return $available;
}, 10, 3);
```

### Custom Email Template

```html
<!-- In your email template file -->
<?php
$delivery = get_post_meta($order->get_id(), '_sameday_delivery_display', true);
if ($delivery) {
    echo '<p>Your delivery is scheduled for: <strong>' . esc_html($delivery) . '</strong></p>';
}
?>
```

## Monitoring & Debugging

### Enable Debug Logging

```php
// In wp-config.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Logs will appear in: wp-content/debug.log
```

### Check Current Holds

```bash
wp option get sameday_slot_holds --format=json
```

### Check Order Slot Data

```bash
wp post meta get <order_id> _sameday_delivery_display
wp post meta get <order_id> _sameday_delivery_slot_key
wp post meta get <order_id> _sameday_delivery_zip
```

### Manually Clean Up Holds

```bash
wp option delete sameday_slot_holds
```

## Maintenance

### Regular Tasks

**Daily:**
- Monitor orders for slot issues
- Check if blackout dates need updating

**Weekly:**
- Review capacity utilization
- Check for abandoned holds (shouldn't accumulate)

**Monthly:**
- Review customer satisfaction scores
- Analyze slot demand patterns
- Adjust capacity if needed

### Database Maintenance

```sql
-- Check holds size (if they grow unexpectedly)
SELECT option_id, option_name, LENGTH(option_value) as size
FROM wp_options
WHERE option_name = 'sameday_slot_holds';

-- Clean expired holds manually if needed
-- (Normally automatic, but can be done manually)
DELETE FROM wp_postmeta
WHERE meta_key LIKE '_sameday_delivery_%'
AND post_id IN (
    SELECT ID FROM wp_posts WHERE post_type = 'shop_order' AND post_status = 'trash'
);
```

## Support & Resources

### Documentation
- Main Guide: `CHECKOUT_SLOT_INTEGRATION.md`
- This Guide: `SLOT_INTEGRATION_GUIDE.md`
- WooCommerce Docs: https://docs.woocommerce.com/

### Code Location
- Main Implementation: `web/app/mu-plugins/woocommerce-sameday-logistics.php`
- Tests: `web/app/mu-plugins/tests/test-checkout-slot-integration.php`

### Debugging
- Enable debug logging (see above)
- Check WooCommerce → Settings → Logs
- Review error logs regularly
