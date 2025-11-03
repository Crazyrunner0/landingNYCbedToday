# WooCommerce E-Commerce Setup

This repository contains a complete WooCommerce e-commerce setup with Stripe payments, one-page checkout, and comprehensive analytics tracking.

## 🚀 Features

### E-Commerce
- ✅ **WooCommerce** - Full-featured e-commerce platform
- ✅ **Stripe Gateway** - Secure payment processing (test mode)
- ✅ **Apple Pay / Google Pay** - Express checkout options
- ✅ **One-Page Checkout** - Streamlined checkout experience
- ✅ **Reduced Fields** - Minimal form friction
- ✅ **Sticky CTA** - Mobile-optimized order button
- ✅ **Guest Checkout** - No account required

### Products (Auto-Seeded)
- 🛏️ **4 Mattresses**: Twin, Full, Queen, King ($599-$1,299)
- 📦 **3 Add-ons**: Old Bed Removal, Stair Carry, Mattress Protector ($49-$99)

### Analytics & Tracking
- 📊 **Google Analytics 4** - Complete e-commerce tracking
  - `view_item` - Product views
  - `add_to_cart` - Cart additions
  - `begin_checkout` - Checkout initiation
  - `purchase` - Order completion
- 📈 **Meta Pixel** - Facebook conversion tracking
  - `ViewContent`, `AddToCart`, `InitiateCheckout`, `Purchase`
- 🔍 **Debug Mode** - Real-time event verification

## 📋 Quick Start

```bash
# 1. Start services
make up

# 2. Install dependencies (includes WooCommerce & Stripe)
make composer CMD='install'

# 3. Configure environment
cp .env.example .env
# Edit .env and add your API keys

# 4. Install WordPress
# Visit http://localhost:8080 and complete setup

# 5. Activate plugins
# Go to Plugins → Activate WooCommerce and Stripe Gateway
```

**See [QUICKSTART_WOOCOMMERCE.md](QUICKSTART_WOOCOMMERCE.md) for detailed instructions**

## 🔑 Required Configuration

Add these to your `.env` file:

```bash
# Stripe (get from https://dashboard.stripe.com/test/apikeys)
STRIPE_PUBLISHABLE_KEY='pk_test_...'
STRIPE_SECRET_KEY='sk_test_...'

# Google Analytics 4
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'

# Meta Pixel
META_PIXEL_ID='000000000000000'
```

## 💳 Testing Payments

Use Stripe test cards:

| Card Number | Expiry | CVC | Result |
|-------------|--------|-----|--------|
| 4242 4242 4242 4242 | 12/25 | 123 | ✅ Success |
| 4000 0025 0000 3155 | 12/25 | 123 | 🔐 3D Secure |
| 4000 0000 0000 9995 | 12/25 | 123 | ❌ Declined |

## 📊 Verify Analytics

### GA4 DebugView
1. GA4 → Configure → **DebugView**
2. Perform test purchase
3. Watch events appear in real-time

### Meta Test Events
1. Events Manager → **Test Events**
2. Use browser extension or enter browser ID
3. Verify events fire correctly

## 📁 Project Structure

```
.
├── web/app/
│   ├── mu-plugins/
│   │   ├── woocommerce-custom-setup.php       # Main WC configuration
│   │   └── woocommerce-activation-helper.php  # Activation helper
│   ├── plugins/                               # WooCommerce & Stripe (via Composer)
│   └── themes/                                # WordPress themes
├── composer.json                              # Dependencies (WC + Stripe)
├── .env.example                               # Environment variables template
├── WOOCOMMERCE_SETUP.md                       # Detailed setup guide
├── QUICKSTART_WOOCOMMERCE.md                  # Quick start guide
└── TESTING_CHECKLIST.md                       # Complete testing checklist
```

## 🛠️ Technology Stack

- **WordPress**: Bedrock structure
- **WooCommerce**: 9.x
- **Stripe Gateway**: 8.x
- **PHP**: 8.2-FPM
- **Database**: MariaDB
- **Cache**: Redis
- **Web Server**: Nginx
- **Container**: Docker Compose

## 📖 Documentation

- **[QUICKSTART_WOOCOMMERCE.md](QUICKSTART_WOOCOMMERCE.md)** - Get started in 5 minutes
- **[WOOCOMMERCE_SETUP.md](WOOCOMMERCE_SETUP.md)** - Comprehensive setup guide
- **[TESTING_CHECKLIST.md](TESTING_CHECKLIST.md)** - Complete testing procedures
- **[README.md](README.md)** - WordPress stack documentation

## ✅ Acceptance Criteria

All criteria met:

- [x] WooCommerce installed and configured
- [x] One-page checkout implemented
- [x] Stripe gateway with test mode
- [x] Apple Pay / Google Pay enabled
- [x] Checkout fields reduced
- [x] Sticky CTA on mobile
- [x] Products seeded (4 mattresses + 3 add-ons)
- [x] GA4 tracking implemented
- [x] Meta Pixel tracking implemented
- [x] Test payments succeed
- [x] Events visible in GA4 DebugView
- [x] Events visible in Meta Test Events

## 🔧 Common Commands

```bash
# Start/stop services
make up
make down
make restart

# View logs
make logs

# Access PHP container
make shell

# WP-CLI commands
make wp CMD='plugin list'
make wp CMD='post list --post_type=product'
make wp CMD='wc order list'

# Clear cache
docker compose exec redis redis-cli FLUSHALL
```

## 🐛 Troubleshooting

### Products not showing?
```bash
# Trigger product seeding
make wp CMD='option delete wc_products_seeded'
# Then refresh WordPress admin
```

### Stripe not configured?
1. Check `.env` has correct API keys
2. Restart: `make restart`
3. Verify in WC Settings → Payments → Stripe

### Analytics not tracking?
1. Verify IDs in `.env`
2. Check browser console for errors
3. Confirm scripts in page source

**See [WOOCOMMERCE_SETUP.md](WOOCOMMERCE_SETUP.md) for more troubleshooting**

## 🚀 Deployment

Before production:

1. Set `WP_ENV=production`
2. Use live Stripe keys
3. Update domain in `WP_HOME`
4. Enable HTTPS
5. Use production analytics IDs
6. Disable GA4 debug mode

## 📝 License

MIT

## 🤝 Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md)
