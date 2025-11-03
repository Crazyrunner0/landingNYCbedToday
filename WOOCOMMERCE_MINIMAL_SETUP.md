# WooCommerce Minimal Setup

This document outlines the minimal WooCommerce setup for the mattress e-commerce store with US locale, single-page checkout, and seeded products.

## Overview

The WooCommerce minimal setup provides:

- **Automatic Activation**: WooCommerce plugin is automatically activated on first install
- **US Configuration**: Store currency set to USD, default country set to US
- **Simplified Checkout**: Single-page checkout experience with minimal required fields
- **Seeded Products**: Four mattress products (Twin, Full, Queen, King) with placeholder pricing
- **Automatic Pages**: WooCommerce shop, cart, checkout, and account pages are created automatically
- **No Payment Gateway**: Payments are NOT enabled by default (can be added later if needed)

## Setup Process

### 1. Initial Installation

The setup happens automatically through the WooCommerce Minimal Setup mu-plugin (`web/app/mu-plugins/woocommerce-minimal-setup.php`).

**Steps:**
1. Run `make bootstrap` to install dependencies and start services
2. Complete WordPress installation at http://localhost:8080
3. The mu-plugin will:
   - Automatically activate WooCommerce
   - Configure store settings (USD, US)
   - Create WooCommerce pages
   - Seed products on first admin page load

### 2. Seed Products Manually (Optional)

If you need to re-run product seeding or verify products were created:

```bash
# Using the shell script
./scripts/seed-woocommerce-products.sh

# Or directly with WP-CLI
make wp CMD='eval-file scripts/seed-woocommerce-products.php'
```

### 3. Complete Setup Script

For a fresh start, you can use the setup script:

```bash
./scripts/setup-woocommerce-minimal.sh
```

## Configuration

### Store Settings

Configured automatically by the mu-plugin:

```
Currency: USD
Country: US
State: NY
City: New York
Address: 123 Main Street
Postcode: 10001
Measurement Units: Pounds (lbs), Inches (in)
```

### Checkout Configuration

The minimal setup simplifies the checkout experience:

- **Guest Checkout**: Enabled
- **Order Notes**: Disabled
- **Account Creation**: Allowed (optional)
- **Terms & Conditions**: Required checkbox
- **Removed Fields**: Company, Address 2, optional Phone
- **Single-Page**: No page reload during checkout

### Seeded Products

Four products are automatically created:

| Product | Price | SKU | Stock |
|---------|-------|-----|-------|
| Twin Mattress | $599.00 | auto | 50 |
| Full Mattress | $799.00 | auto | 50 |
| Queen Mattress | $999.00 | auto | 50 |
| King Mattress | $1,299.00 | auto | 50 |

**Notes:**
- Products are published and visible in the catalog
- Stock is managed (50 units available)
- Descriptions include placeholder text
- Products can be edited in WordPress admin

## Accessing the Store

After setup:

1. **Shop Page**: http://localhost:8080/shop/
2. **Checkout Page**: http://localhost:8080/checkout/
3. **Cart Page**: http://localhost:8080/cart/
4. **Account Page**: http://localhost:8080/my-account/

## Admin Access

1. **Products**: WordPress Admin → Products
2. **Settings**: WordPress Admin → WooCommerce → Settings
3. **Orders**: WordPress Admin → Orders (once orders are placed)

## Testing Checkout

1. Visit http://localhost:8080/shop/
2. Select a mattress product
3. Click "Add to Cart"
4. Visit http://localhost:8080/cart/ to review
5. Click "Proceed to Checkout"
6. Fill in customer details
7. Review order summary
8. Note: Checkout form will show but payments are NOT configured

## Customization

### Adding Payment Gateway

To add a payment gateway:

1. **Stripe** (recommended):
   - Add `STRIPE_PUBLISHABLE_KEY` and `STRIPE_SECRET_KEY` to `.env`
   - Activate plugin: `make wp CMD='plugin activate woocommerce-gateway-stripe'`
   - Configure in WooCommerce Settings → Payments

2. **Other Gateways**:
   - Install via Composer or WordPress plugins directory
   - Activate and configure in WooCommerce Settings

### Modifying Store Address

Edit in WordPress Admin:
1. WooCommerce → Settings → General
2. Update Store Address, City, Postcode, State

### Adding More Products

Method 1 - WordPress Admin:
1. WooCommerce → Products → Add New
2. Fill in details and publish

Method 2 - WP-CLI:
```bash
make wp CMD='product create --name="New Mattress" --description="Description" --regular_price=999'
```

### Customizing Checkout Fields

Edit in WordPress Admin:
1. WooCommerce → Settings → Checkout
2. Configure which fields are required

Or programmatically in a custom plugin/mu-plugin using the `woocommerce_checkout_fields` filter.

## Mu-Plugins

### woocommerce-minimal-setup.php

Main setup mu-plugin that handles:
- WooCommerce automatic activation
- Store settings configuration (US, USD)
- Checkout field simplification
- Product seeding
- WooCommerce page creation

**File**: `web/app/mu-plugins/woocommerce-minimal-setup.php`

### Other WooCommerce Mu-Plugins

- `woocommerce-activation-helper.php` - Helper for setup notices (optional)
- `woocommerce-custom-setup.php` - Advanced features like Stripe, GA4 (optional, only activates if keys provided)

## Scripts

### seed-woocommerce-products.php

WP-CLI script for product seeding. Safe to re-run - checks for existing products.

**Usage**:
```bash
make wp CMD='eval-file scripts/seed-woocommerce-products.php'
```

### seed-woocommerce-products.sh

Shell wrapper for the WP-CLI script with Docker support.

**Usage**:
```bash
./scripts/seed-woocommerce-products.sh
```

### setup-woocommerce-minimal.sh

Complete setup script for WooCommerce minimal configuration.

**Usage**:
```bash
./scripts/setup-woocommerce-minimal.sh
```

## Troubleshooting

### WooCommerce Not Showing

1. Verify WordPress is installed: `make wp CMD='core is-installed'`
2. Check if WooCommerce is activated: `make wp CMD='plugin is-active woocommerce'`
3. Check logs: `make logs`

### Products Not Appearing

1. Verify products exist: `make wp CMD='post list --post_type=product'`
2. Re-seed products: `./scripts/seed-woocommerce-products.sh`
3. Check for errors in logs

### Checkout Page Not Working

1. Verify checkout page exists: `make wp CMD='post list --post_type=page'`
2. Check checkout option is set: `make wp CMD='option get woocommerce_checkout_page_id'`
3. Verify WooCommerce shortcodes are on the page

### Database Issues

1. Reset database: `make clean` (warning: this deletes everything)
2. Re-run bootstrap: `make bootstrap`

## Notes

- This is a **minimal setup** - advanced features like analytics, payment gateways, and shipping are not enabled by default
- Products are simple physical products suitable for mattress sales
- Stock management is enabled (important for inventory tracking)
- Guest checkout is enabled for faster transactions
- The setup is **idempotent** - running setup multiple times is safe

## Next Steps

After initial setup:

1. **Customize Products**: Update product descriptions, images, attributes
2. **Add Shipping**: Configure shipping zones and methods
3. **Add Payment Gateway**: Enable Stripe or other payment processor
4. **Setup Analytics**: Add GA4 and Facebook Pixel if needed
5. **Branding**: Customize store colors and layout with Blocksy theme

## Related Documentation

- [README.md](README.md) - Project overview
- [QUICKSTART.md](QUICKSTART.md) - Quick start guide
- [WooCommerce Documentation](https://docs.woocommerce.com/)
- [Blocksy Theme](https://www.blocksy.com/) - Storefront theme
