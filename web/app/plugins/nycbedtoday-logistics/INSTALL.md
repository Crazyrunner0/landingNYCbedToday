# Installation Guide

## Prerequisites

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 5.0 or higher (for checkout integration)
- MySQL 5.6 or higher / MariaDB 10.0 or higher

## Installation Steps

### Option 1: Manual Installation (Bedrock)

1. Copy the `nycbedtoday-logistics` folder to your WordPress plugins directory:
   ```bash
   cp -r nycbedtoday-logistics /path/to/web/app/plugins/
   ```

2. Activate the plugin via WP-CLI:
   ```bash
   wp plugin activate nycbedtoday-logistics
   ```

3. Or activate via WordPress Admin:
   - Navigate to **Plugins** in the WordPress admin
   - Find "NYC Bed Today Same-Day Logistics"
   - Click **Activate**

### Option 2: Standard WordPress Installation

1. Upload the `nycbedtoday-logistics` folder to `/wp-content/plugins/`

2. Activate the plugin through the WordPress admin **Plugins** menu

## Post-Installation

### 1. Verify Database Tables

The plugin automatically creates the following database table on activation:

- `wp_nycbt_slot_reservations` - Stores delivery slot reservations

To verify, run:
```bash
wp db query "SHOW TABLES LIKE 'wp_nycbt_slot_reservations'"
```

### 2. Configure Settings

1. Navigate to **Settings → Same-day Logistics**

2. Configure the following settings:
   - **Order Cut-off Time**: Time after which orders go to the next day (default: 10:00 AM)
   - **Delivery Hours**: Start and end time for delivery windows (default: 2:00 PM - 8:00 PM)
   - **Slot Duration**: Length of each time slot (default: 2 hours)
   - **Slot Capacity**: Maximum deliveries per slot (default: 10)
   - **Blackout Dates**: Dates when delivery is unavailable (optional)

3. Click **Save Changes**

### 3. Verify ZIP Codes

1. Go to the **ZIP Codes** tab

2. The plugin seeds 100+ NYC ZIP codes by default

3. Add or remove ZIP codes as needed

### 4. Test the Integration

#### Test ZIP Validation

1. Add this shortcode to a test page: `[nycbt_check_zip]`
2. Visit the page and test with ZIP code `10001` (should be valid)
3. Test with ZIP code `90210` (should be invalid)

#### Test Slot Display

1. Add this shortcode to a test page: `[nycbt_available_slots]`
2. Visit the page to see available time slots

#### Test WooCommerce Checkout

1. Ensure WooCommerce is active
2. Add a product to cart
3. Go to checkout
4. Enter a valid NYC ZIP code in the shipping address
5. Delivery time slots should appear automatically
6. Select a slot and complete the test order
7. Verify slot appears in:
   - Order confirmation email
   - Admin order details
   - Thank you page

## Verification Checklist

- [ ] Plugin activated successfully
- [ ] Database table created
- [ ] Settings page accessible
- [ ] ZIP codes loaded (100+ default)
- [ ] REST API endpoints responding
- [ ] ZIP checker shortcode working
- [ ] Available slots shortcode working
- [ ] Gutenberg blocks available
- [ ] WooCommerce checkout showing slots
- [ ] Slot reservation persisting to order
- [ ] Delivery info showing in emails
- [ ] Admin dashboard showing reservations

## Troubleshooting

### Plugin Won't Activate

**Error**: "The plugin does not have a valid header"

**Solution**: Ensure the main plugin file is `nycbedtoday-logistics.php` and contains proper plugin header

### Database Table Not Created

**Solution**: Manually run the activation hook:
```bash
wp eval "require_once 'web/app/plugins/nycbedtoday-logistics/nycbedtoday-logistics.php'; nycbedtoday_logistics_activate();"
```

### No ZIP Codes Showing

**Solution**: Manually seed ZIP codes:
```bash
wp eval "require_once 'web/app/plugins/nycbedtoday-logistics/nycbedtoday-logistics.php'; NYCBEDTODAY_Logistics_ZIP_Manager::seed_default_zips();"
```

### Slots Not Appearing on Checkout

**Causes**:
1. WooCommerce not active - Activate WooCommerce
2. JavaScript errors - Check browser console
3. No valid ZIP entered - Enter a whitelisted ZIP code
4. After cut-off time - Orders after cut-off show next day's slots

**Solution**: Check JavaScript console for errors and verify settings

### REST API Endpoints Not Working

**Solution**: 
1. Go to **Settings → Permalinks**
2. Click **Save Changes** to flush rewrite rules
3. Test endpoints again

### Permission Errors

**Solution**: Ensure proper file permissions:
```bash
chmod 755 web/app/plugins/nycbedtoday-logistics
chmod 644 web/app/plugins/nycbedtoday-logistics/*.php
```

## Uninstallation

To remove the plugin and all data:

1. Deactivate the plugin:
   ```bash
   wp plugin deactivate nycbedtoday-logistics
   ```

2. Delete the plugin:
   ```bash
   wp plugin delete nycbedtoday-logistics
   ```

3. (Optional) Remove database table:
   ```bash
   wp db query "DROP TABLE IF EXISTS wp_nycbt_slot_reservations"
   ```

4. (Optional) Remove options:
   ```bash
   wp option delete nycbedtoday_logistics_settings
   wp option delete nycbedtoday_logistics_zip_whitelist
   ```

## Support

For issues or questions, contact the NYC Bed Today development team.
