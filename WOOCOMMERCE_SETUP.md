# WooCommerce One-Page Checkout Setup

This document describes the WooCommerce setup with Stripe payment gateway, product seeding, and analytics tracking.

## Features Implemented

### 1. WooCommerce Installation
- WooCommerce plugin installed via Composer
- WooCommerce Stripe Gateway installed via Composer
- One-page checkout configuration
- Reduced checkout fields for faster completion

### 2. Stripe Payment Gateway
- **Test Mode**: Configured for test payments
- **Apple Pay**: Enabled via Payment Request API
- **Google Pay**: Enabled via Payment Request API
- Configuration via environment variables

### 3. Product Seeding
Products are automatically seeded on first admin access:

**Mattresses:**
- Twin Mattress - $599.00
- Full Mattress - $799.00
- Queen Mattress - $999.00
- King Mattress - $1,299.00

**Add-ons:**
- Old Bed Removal - $99.00
- Stair Carry Service - $49.00
- Mattress Protector - $79.00

### 4. Checkout Customizations
- **Reduced Fields**: Removed unnecessary billing/shipping fields
- **Sticky CTA**: Mobile sticky "Complete Order" button
- **One-Page Flow**: All checkout steps on single page
- **Guest Checkout**: Enabled for faster purchases

### 5. Analytics Tracking

#### GA4 Events (with debug mode enabled):
- `view_item` - When viewing a product page
- `add_to_cart` - When adding items to cart
- `begin_checkout` - When arriving at checkout page
- `purchase` - When order is completed

#### Meta Pixel Events:
- `ViewContent` - Product views
- `AddToCart` - Cart additions
- `InitiateCheckout` - Checkout start
- `Purchase` - Order completion

## Setup Instructions

### 1. Install Dependencies

```bash
cd /home/engine/project
make composer CMD='install'
```

Or manually:

```bash
docker compose run --rm php composer install
```

### 2. Configure Environment Variables

Create a `.env` file from the example:

```bash
cp .env.example .env
```

Update the following variables in `.env`:

```bash
# Stripe Test Keys (get from https://dashboard.stripe.com/test/apikeys)
STRIPE_PUBLISHABLE_KEY='pk_test_your_actual_key_here'
STRIPE_SECRET_KEY='sk_test_your_actual_key_here'

# Google Analytics 4
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'  # Your actual GA4 Measurement ID

# Meta Pixel
META_PIXEL_ID='000000000000000'  # Your actual Meta Pixel ID
```

### 3. Start Services

```bash
make up
```

Or manually:

```bash
docker compose up -d
```

### 4. Install WordPress

1. Navigate to http://localhost:8080
2. Complete the WordPress installation wizard
3. Create an admin account

### 5. Activate WooCommerce

After WordPress installation:

1. Go to **Plugins** → **Installed Plugins**
2. Activate **WooCommerce**
3. Activate **WooCommerce Stripe Gateway**
4. Complete the WooCommerce setup wizard (or skip it)

### 6. Verify Product Seeding

1. Go to **Products** → **All Products**
2. You should see 4 mattress products and 3 add-on products
3. If products aren't seeded, visit the admin dashboard to trigger the seeding

### 7. Verify Stripe Configuration

1. Go to **WooCommerce** → **Settings** → **Payments**
2. Click on **Stripe**
3. Verify:
   - Enabled: Yes
   - Test mode: Yes
   - Test Publishable Key: Should be populated
   - Test Secret Key: Should be populated
   - Payment Request Buttons: Enabled (for Apple Pay/Google Pay)

## Testing

### Test Stripe Payments

Use Stripe test cards:

**Success:**
- Card: `4242 4242 4242 4242`
- Expiry: Any future date
- CVC: Any 3 digits
- ZIP: Any 5 digits

**3D Secure Authentication:**
- Card: `4000 0025 0000 3155`

**Declined:**
- Card: `4000 0000 0000 9995`

### Test Apple Pay/Google Pay

1. **Apple Pay**: 
   - Use Safari on macOS or iOS device
   - Ensure Apple Pay is set up in your wallet
   - Apple Pay button should appear at checkout

2. **Google Pay**:
   - Use Chrome with Google account
   - Ensure Google Pay is set up
   - Google Pay button should appear at checkout

**Note**: Payment Request buttons (Apple Pay/Google Pay) may only appear on HTTPS sites. For local testing with these features, you may need to:
- Set up local HTTPS with mkcert
- Use ngrok or similar to expose localhost via HTTPS
- Or test on a staging environment with valid SSL

### Test Analytics Events

#### GA4 DebugView:

1. Navigate to GA4 → **Configure** → **DebugView**
2. Perform actions on your site:
   - View a product page → Check for `view_item` event
   - Add to cart → Check for `add_to_cart` event
   - Go to checkout → Check for `begin_checkout` event
   - Complete test purchase → Check for `purchase` event
3. Events should appear in real-time in DebugView

#### Meta Test Events:

1. Navigate to Meta Events Manager → **Test Events**
2. Enter your browser identifier or use the Test Events Chrome extension
3. Perform actions on your site:
   - View product → Check for `ViewContent`
   - Add to cart → Check for `AddToCart`
   - Go to checkout → Check for `InitiateCheckout`
   - Complete purchase → Check for `Purchase`
4. Events should appear within a few seconds

## Checkout Flow

1. **Shop Page**: Browse mattresses and add-ons
2. **Product Page**: 
   - View product details
   - Fires `view_item` and `ViewContent` events
3. **Add to Cart**: 
   - Click "Add to Cart"
   - Fires `add_to_cart` and `AddToCart` events
4. **Cart Page**: Review items
5. **Checkout Page** (One-Page):
   - Simplified form (reduced fields)
   - Fires `begin_checkout` and `InitiateCheckout` events
   - Stripe payment options
   - Apple Pay / Google Pay buttons
   - Sticky CTA on mobile
6. **Complete Order**:
   - Submit payment
   - Fires `purchase` and `Purchase` events
7. **Thank You Page**: Order confirmation

## Troubleshooting

### Stripe Not Appearing

- Ensure Stripe plugin is activated
- Check that API keys are set in `.env`
- Verify WooCommerce settings show Stripe as enabled

### Products Not Seeded

- Visit the WordPress admin dashboard
- Products are seeded on first `admin_init` action
- Check **Products** → **All Products** to verify

### Analytics Not Tracking

- Verify measurement IDs are correct in `.env`
- Check browser console for JavaScript errors
- Ensure tracking scripts are in page source (View → Source)
- For GA4: Make sure debug mode is enabled
- For Meta: Ensure Test Events mode is active

### Payment Request Buttons Not Showing

- Apple Pay/Google Pay require HTTPS in production
- For local testing, you may need to:
  - Use a tunneling service (ngrok)
  - Set up local SSL certificate
  - Test on a staging server with SSL

### Session Issues

- Ensure Redis is running: `docker compose ps`
- Clear Redis cache: `docker compose exec redis redis-cli FLUSHALL`

## Files Modified/Created

- `composer.json` - Added WooCommerce and Stripe plugin dependencies
- `.env.example` - Added Stripe and analytics environment variables
- `web/app/mu-plugins/woocommerce-custom-setup.php` - Custom WooCommerce configuration plugin

## Support

For issues with:
- **Stripe**: https://stripe.com/docs/testing
- **GA4**: https://support.google.com/analytics/answer/7201382
- **Meta Pixel**: https://developers.facebook.com/docs/meta-pixel
- **WooCommerce**: https://woocommerce.com/documentation/

## Next Steps

1. Customize product images and descriptions
2. Configure shipping options if needed
3. Set up email notifications
4. Configure tax settings if required
5. Test complete purchase flow
6. Deploy to staging/production with proper SSL for Apple Pay/Google Pay
