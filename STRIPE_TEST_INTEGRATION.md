# Stripe Test Integration

This document covers the Stripe test mode integration, including setup, configuration, and testing procedures.

## Overview

The WordPress stack includes:
- **WooCommerce Stripe Gateway Plugin** (`woocommerce-gateway-stripe`) - Official Stripe payment processor for WooCommerce
- **Automatic Setup** - Stripe is automatically configured when keys are provided via environment variables
- **Test Mode** - Configured for Stripe test mode by default for safe development
- **Payment Request Buttons** - Apple Pay and Google Pay support (where available)
- **Simplified Checkout** - Streamlined checkout form for better conversion

## Environment Configuration

### Required Environment Variables

Add the following to your `.env` file in the project root:

```bash
# Stripe Test Mode Keys
STRIPE_TEST_PUBLIC_KEY='pk_test_your_key_here'
STRIPE_TEST_SECRET_KEY='sk_test_your_key_here'

# Stripe Webhook Secret (optional, for webhook processing)
STRIPE_WEBHOOK_SECRET='whsec_test_your_secret_here'
```

### Getting Test Keys from Stripe

1. Go to [Stripe Dashboard](https://dashboard.stripe.com)
2. Make sure you're in **Test Mode** (toggle in the top-right)
3. Navigate to **Developers → API Keys**
4. Copy your **Publishable key** (starts with `pk_test_`)
5. Copy your **Secret key** (starts with `sk_test_`)

### Optional: Webhook Configuration

For webhook support:
1. In Stripe Dashboard, go to **Developers → Webhooks**
2. Click **Add endpoint**
3. URL: `https://your-site.com/wp-json/wc/v3/webhooks` (or your WooCommerce API endpoint)
4. Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`
5. Copy the **Signing secret** (starts with `whsec_`)

## Automatic Setup

The system includes two mu-plugins for automatic Stripe configuration:

### 1. Stripe Test Setup (`stripe-test-setup.php`)

This mu-plugin:
- Automatically activates the Stripe gateway plugin
- Configures test mode
- Enables Payment Request Buttons (Apple Pay/Google Pay)
- Pulls keys from environment variables

**Location**: `/web/app/mu-plugins/stripe-test-setup.php`

### 2. WooCommerce Custom Setup (`woocommerce-custom-setup.php`)

This mu-plugin provides additional features:
- Fallback Stripe configuration (for backward compatibility)
- Product seeding
- GA4 and Meta Pixel analytics tracking
- Simplified checkout fields

**Location**: `/web/app/mu-plugins/woocommerce-custom-setup.php`

## Checkout Field Configuration

The checkout is simplified to essential fields only:

### Removed Fields
- Company (billing & shipping)
- Address Line 2 (billing & shipping)
- State (billing - optional, can re-enable if needed)
- Postcode (billing - optional, can re-enable if needed)

### Optional Fields
- Phone number (optional but available)

This streamlined approach improves conversion by reducing form friction.

## Testing Stripe Integration

### Prerequisites

1. **Bootstrap the environment**:
   ```bash
   make bootstrap
   ```

2. **Complete WordPress installation** (if not already done)

3. **Add Stripe test keys to `.env`**:
   ```bash
   STRIPE_TEST_PUBLIC_KEY='pk_test_...'
   STRIPE_TEST_SECRET_KEY='sk_test_...'
   ```

4. **Verify Stripe is active**:
   - Open WordPress admin
   - Go to **WooCommerce → Settings → Payments**
   - Confirm **Stripe** is enabled (green toggle)

### Test Credit Cards

Stripe provides test cards for various scenarios:

| Card Number | Expiry | CVC | Scenario |
|------------|--------|-----|----------|
| `4242 4242 4242 4242` | Any future date | Any 3 digits | Successful charge |
| `4000 0000 0000 0002` | Any future date | Any 3 digits | Card declined |
| `4000 0000 0000 3220` | Any future date | Any 3 digits | Requires authentication |
| `5555 5555 5555 4444` | Any future date | Any 3 digits | MasterCard success |
| `3782 822463 10005` | Any future date | Any 4 digits | American Express |

**Note**: Use any future expiration date and any 3-digit CVC for all test cards.

### Manual Testing Steps

#### 1. Test Basic Checkout

```bash
# Open the shop
curl http://localhost:8080/shop/

# Add a product to cart
# Visit http://localhost:8080/cart/
# Proceed to checkout at http://localhost:8080/checkout/
```

#### 2. Test Card Payment

1. Navigate to `/checkout/`
2. Fill in customer details:
   - Email, First/Last Name
   - Address, City, State, ZIP
3. Under **Payment**, enter test card details:
   - Card: `4242 4242 4242 4242`
   - Expiry: Any future month/year
   - CVC: Any 3 digits
4. Click **Place Order**
5. Verify order appears in WordPress admin with payment status

#### 3. Test Payment Request Button (Apple Pay/Google Pay)

**Requirements**:
- HTTPS connection (localhost may bypass this in development)
- Supported browser:
  - **Apple Pay**: Safari on macOS/iOS
  - **Google Pay**: Chrome on Android/Linux or any Chromium browser

**Steps**:
1. Go to `/checkout/` in supported browser
2. Look for **Apple Pay** or **Google Pay** button above card form
3. Click the button
4. Complete payment in native payment UI
5. Verify order in WordPress admin

**Note**: Payment Request Buttons may not appear on localhost HTTP. Use HTTPS for full testing.

#### 4. Test Failed Payment

1. Go to `/checkout/`
2. Use test card: `4000 0000 0000 0002` (always declines)
3. Click **Place Order**
4. Verify error message appears
5. Verify order is NOT created in admin

#### 5. Test Authentication Required

1. Go to `/checkout/`
2. Use test card: `4000 0000 0000 3220`
3. Click **Place Order**
4. Complete 3D Secure authentication (Stripe's test popup)
5. Verify order is created and marked as paid

### Verifying in Stripe Dashboard

After testing:

1. Go to [Stripe Dashboard](https://dashboard.stripe.com)
2. Make sure you're in **Test Mode**
3. Go to **Payments** to see all test transactions
4. Each payment should show:
   - Amount and currency
   - Status (Succeeded, Failed, etc.)
   - Customer email
   - Payment method (Card, Apple Pay, Google Pay)

### Verifying in WordPress Admin

1. Go to WordPress admin dashboard
2. Navigate to **WooCommerce → Orders**
3. Click an order to view details:
   - **Order Status**: Should be "Processing" or "Completed"
   - **Payment Method**: Should show "Stripe"
   - **Order Notes**: Should include Stripe transaction ID
   - **Order Items**: Should show products and amounts

## Test Mode Indicators

The following confirms test mode is active:

1. **Admin notice**: "Stripe test mode is active" (if enabled by Stripe plugin)
2. **Settings**: `WooCommerce → Settings → Payments → Stripe → Test mode` = checked
3. **Keys**: Publishable key starts with `pk_test_`, secret key starts with `sk_test_`
4. **Dashboard**: Stripe Dashboard shows "Test Mode" in top-right

## Troubleshooting

### "Stripe plugin not found"

**Issue**: Stripe payment gateway not appearing at checkout

**Solution**:
1. Verify plugin installed: `composer install`
2. Check mu-plugin is active: `web/app/mu-plugins/stripe-test-setup.php` exists
3. Check environment variables are set in `.env`
4. Clear WP cache: `make wp CMD='cache flush'`
5. Check WordPress admin → WooCommerce → Payments for errors

### "Payment Request Button not showing"

**Issue**: Apple Pay/Google Pay not available

**Causes**:
- Browser doesn't support it (only Safari for Apple Pay, Chromium for Google Pay)
- HTTP connection (HTTPS required, except localhost in some cases)
- Device not configured (no payment method saved in OS)
- Button locations not enabled in settings

**Solution**:
1. Verify settings: `WooCommerce → Settings → Payments → Stripe → Payment Request Button`
2. Check button type is set to "Buy"
3. Test in supported browser (Safari for Apple Pay, Chrome for Google Pay)
4. Ensure HTTPS connection (or localhost)

### "Keys not loading from environment"

**Issue**: Stripe settings empty despite `.env` keys

**Solution**:
1. Check keys are in `.env` (not `.env.example`)
2. Verify variable names: `STRIPE_TEST_PUBLIC_KEY`, `STRIPE_TEST_SECRET_KEY`
3. Restart Docker containers: `make down && make up`
4. Check .env is being loaded: `make wp CMD='eval "echo env(\"STRIPE_TEST_PUBLIC_KEY\")"'`

### "Card declined even with valid test card"

**Issue**: Test card failing when it shouldn't

**Solution**:
1. Ensure you're using correct test card for scenario
2. Verify expiry date is in future
3. Check amount is > $0.50 (some restrictions apply)
4. Try different test card: `4242 4242 4242 4242`
5. Check Stripe Dashboard for error details

### "Order created but marked as pending payment"

**Issue**: Payment succeeded but order not marked as paid

**Solution**:
1. Check webhook configuration if needed
2. Verify Stripe test keys are correct
3. Check WooCommerce payment settings → "Mark as paid automatically"
4. Review error logs: `docker logs <php-container>`

## Configuration Options

### Advanced Stripe Settings

Edit `/web/app/mu-plugins/stripe-test-setup.php` to customize:

```php
$stripe_settings = [
    'title' => 'Credit Card',  // Payment method display name
    'description' => 'Pay securely...',  // Payment method description
    'testmode' => 'yes',  // Enable test mode
    'payment_request' => 'yes',  // Enable Apple/Google Pay
    'payment_request_button_type' => 'buy',  // Button type: buy, default, or donate
    'payment_request_button_theme' => 'dark',  // Theme: dark or light
    'statement_descriptor' => 'NYC Bed Today',  // Description on card statement
];
```

### Additional Hooks

To further customize Stripe or checkout:

```php
// Filter Stripe settings before saving
add_filter('woocommerce_stripe_settings', function($settings) {
    // Customize as needed
    return $settings;
});

// Filter checkout fields
add_filter('woocommerce_checkout_fields', function($fields) {
    // Add/remove fields as needed
    return $fields;
});
```

## Switching to Live Mode

When ready to go live:

1. **Get Live Keys** from Stripe Dashboard (make sure you're NOT in Test Mode)
2. **Update `.env`**:
   ```bash
   STRIPE_TEST_PUBLIC_KEY='pk_live_...'  # Change pk_test_ to pk_live_
   STRIPE_TEST_SECRET_KEY='sk_live_...'  # Change sk_test_ to sk_live_
   ```
3. **Update mu-plugin** (`stripe-test-setup.php`):
   ```php
   'testmode' => 'no',  // Change from 'yes' to 'no'
   ```
4. **Test with real card** (small amount, $1 is typical)
5. **Verify** in [Stripe Dashboard](https://dashboard.stripe.com) (make sure NOT in Test Mode)

**⚠️ WARNING**: Live mode processes real charges. Always test thoroughly in test mode first.

## Support & Resources

- [WooCommerce Stripe Documentation](https://woocommerce.com/document/stripe/)
- [Stripe API Documentation](https://stripe.com/docs/api)
- [Stripe Test Mode Guide](https://stripe.com/docs/testing)
- [Apple Pay Integration](https://stripe.com/docs/apple-pay)
- [Google Pay Integration](https://stripe.com/docs/google-pay)

## Notes

- Test transactions are NOT charged to any payment method
- Test data is automatically cleaned up by Stripe
- Each test run can be verified in both Stripe Dashboard and WordPress Admin
- Webhooks are optional for test mode (but recommended for production)
