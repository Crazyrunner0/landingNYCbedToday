# WooCommerce Quick Start Guide

Get your WooCommerce store up and running in minutes!

## Prerequisites

- Docker and Docker Compose installed
- Make (optional but recommended)

## Installation Steps

### 1. Install Dependencies

```bash
# Start Docker services
make up

# Or without make
docker compose up -d

# Install Composer dependencies (includes WooCommerce and Stripe)
make composer CMD='install'

# Or without make
docker compose run --rm php composer install
```

### 2. Configure Environment

```bash
# Copy example environment file
cp .env.example .env

# Edit .env and add your keys:
# - STRIPE_PUBLISHABLE_KEY (get from https://dashboard.stripe.com/test/apikeys)
# - STRIPE_SECRET_KEY
# - GA4_MEASUREMENT_ID (get from Google Analytics 4)
# - META_PIXEL_ID (get from Meta Events Manager)
```

### 3. Install WordPress

1. Open http://localhost:8080 in your browser
2. Follow the WordPress installation wizard
3. Create your admin account

### 4. Activate Plugins

After WordPress installation:

1. Log in to WordPress admin
2. Go to **Plugins** → **Installed Plugins**
3. Activate **WooCommerce**
4. Activate **WooCommerce Stripe Gateway**
5. Skip or complete the WooCommerce setup wizard

### 5. Verify Setup

Products are automatically seeded! Check:

1. **Products** → **All Products** - You should see 7 products
2. **WooCommerce** → **Settings** → **Payments** → **Stripe** - Should be configured
3. Visit the shop page to see products

## Test Purchase Flow

### Quick Test

1. **View a product**: http://localhost:8080/shop/
2. **Add to cart**: Click any product
3. **Checkout**: Proceed to checkout
4. **Payment**: Use test card `4242 4242 4242 4242`
5. **Complete**: Submit order

### Test Card Details

- **Card Number**: 4242 4242 4242 4242
- **Expiry**: 12/25 (any future date)
- **CVC**: 123 (any 3 digits)
- **ZIP**: 12345 (any 5 digits)

## Verify Analytics

### GA4 Events

1. Open GA4 → **Configure** → **DebugView**
2. Perform actions on your site
3. Watch events appear in real-time:
   - `view_item` - Product page view
   - `add_to_cart` - Add to cart
   - `begin_checkout` - Checkout start
   - `purchase` - Order completion

### Meta Pixel Events

1. Open Meta Events Manager → **Test Events**
2. Use Test Events Chrome extension or enter browser ID
3. Perform actions and watch events:
   - `ViewContent` - Product view
   - `AddToCart` - Add to cart
   - `InitiateCheckout` - Checkout start
   - `Purchase` - Order completion

## What's Included

### Products (Auto-Seeded)

**Mattresses:**
- Twin - $599
- Full - $799
- Queen - $999
- King - $1,299

**Add-ons:**
- Old Bed Removal - $99
- Stair Carry Service - $49
- Mattress Protector - $79

### Features

✅ One-page checkout
✅ Reduced checkout fields
✅ Stripe payments (test mode)
✅ Apple Pay / Google Pay support*
✅ GA4 tracking with debug mode
✅ Meta Pixel tracking
✅ Sticky CTA on mobile
✅ Guest checkout enabled

*Apple Pay/Google Pay require HTTPS. Use ngrok for local testing or deploy to staging with SSL.

## Troubleshooting

### "WooCommerce not activated"

```bash
make wp CMD='plugin activate woocommerce'
make wp CMD='plugin activate woocommerce-gateway-stripe'
```

### "No products found"

Refresh the WordPress admin dashboard. Products are seeded on first admin page load.

Or manually:
```bash
make wp CMD='option delete wc_products_seeded'
# Then refresh admin dashboard
```

### "Stripe not configured"

1. Check `.env` file has correct API keys
2. Restart services: `make restart`
3. Check Stripe settings in WooCommerce admin

### "Analytics not tracking"

1. Verify IDs in `.env` file
2. Check browser console for errors
3. View page source to confirm scripts are loaded
4. Ensure debug mode is enabled for GA4

## Next Steps

1. **Customize Products**: Add images, update descriptions
2. **Configure Shipping**: Set up shipping zones and methods
3. **Email Settings**: Configure email templates
4. **Tax Settings**: Configure tax rules if needed
5. **Test Complete Flow**: End-to-end purchase test
6. **Deploy to Staging**: Test with real SSL for Apple Pay/Google Pay

## Support Resources

- **Detailed Setup**: See `WOOCOMMERCE_SETUP.md`
- **Testing Checklist**: See `TESTING_CHECKLIST.md`
- **Setup Script**: Run `./scripts/setup-woocommerce.sh`

## Common Commands

```bash
# View logs
make logs

# Access container shell
make shell

# List all plugins
make wp CMD='plugin list'

# Check WooCommerce status
make wp CMD='wc status'

# List products
make wp CMD='post list --post_type=product'

# View orders
make wp CMD='wc order list'

# Clear cache
docker compose exec redis redis-cli FLUSHALL
```

## Production Deployment

Before going live:

1. Set `WP_ENV=production` in `.env`
2. Use live Stripe keys (not test keys)
3. Update `WP_HOME` with your domain
4. Enable HTTPS
5. Update GA4 and Meta Pixel IDs for production
6. Test complete purchase flow
7. Disable debug mode in GA4 (remove `debug_mode: true`)

---

**Need help?** Check the detailed documentation in `WOOCOMMERCE_SETUP.md`
