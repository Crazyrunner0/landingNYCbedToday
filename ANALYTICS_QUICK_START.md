# Analytics Implementation - Quick Start Guide

Get GA4, Meta Pixel, and consent banner up and running in minutes.

## TL;DR - 5 Minute Setup

### 1. Get Your Credentials

- **GA4**: [Google Analytics 4](https://analytics.google.com) → Admin → Data Streams → Copy Measurement ID (G-...)
- **Meta Pixel**: [Meta Business Suite](https://business.facebook.com) → Data Sources → Copy Pixel ID
- **GSC Token** (optional): [Google Search Console](https://search.google.com/search-console) → Settings → Copy verification token
- **Meta Test Event ID** (optional): Meta Pixels → Test Events → Create test event code

### 2. Update .env

```bash
# Copy .env.example if you haven't
cp .env.example .env

# Edit .env and add:
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'
META_PIXEL_ID='000000000000000'
GSC_VERIFICATION_TOKEN='your-token-here'  # Optional
META_PIXEL_TEST_EVENT_ID='TEST12345'     # Optional, for development/testing
CONSENT_BANNER_ENABLED='true'
```

### 3. Done!

The plugin loads automatically. No activation needed.

## Testing Events

### GA4 - See events in real-time

1. Set `GA4_DEBUG_MODE='true'` in .env
2. Go to [GA4 Admin → DebugView](https://analytics.google.com)
3. Navigate your site
4. Watch events appear in real-time

### Meta Pixel - See events in helper

1. Install [Meta Pixel Helper Extension](https://chrome.google.com/webstore/detail/meta-pixel-helper/)
2. Navigate your site
3. Check the extension popup for events

## What Gets Tracked?

### GA4 Events
✅ **view_item** - When user views a product
✅ **add_to_cart** - When user adds item to cart
✅ **begin_checkout** - When user goes to checkout
✅ **purchase** - When order is completed

### Meta Pixel Events
✅ **PageView** - Every page
✅ **ViewContent** - Product views
✅ **AddToCart** - Add to cart
✅ **InitiateCheckout** - Checkout started
✅ **Purchase** - Order completed

### GSC
✅ Meta tag added to `<head>` for Search Console verification

## Consent Banner

The banner automatically appears on first visit with:

- **Accept All** - Enable both analytics and marketing
- **Reject All** - Disable both
- **Learn More** - See detailed options
- **Customize** - Control each category separately

User preferences saved in localStorage and respected on subsequent visits.

## Disable Features

Want to turn something off?

```bash
# Disable consent banner
CONSENT_BANNER_ENABLED='false'

# Disable GA4
GA4_MEASUREMENT_ID=''

# Disable Meta Pixel
META_PIXEL_ID=''

# Disable GSC verification
GSC_VERIFICATION_TOKEN=''
```

## Environment-Specific Settings

### Development
```bash
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'
GA4_DEBUG_MODE='true'
META_PIXEL_TEST_EVENT_ID='TEST12345'  # Use test event ID
CONSENT_BANNER_ENABLED='true'
```

### Production
```bash
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'
GA4_DEBUG_MODE='false'
META_PIXEL_TEST_EVENT_ID=''  # Don't use test events
CONSENT_BANNER_ENABLED='true'
```

## Troubleshooting

### Events not appearing in GA4?

1. **Check config**: `echo $GA4_MEASUREMENT_ID` should output `G-XXXXXXXXXX`
2. **Enable debug**: Set `GA4_DEBUG_MODE='true'`
3. **Check consent**: Open DevTools → Console → `window.nycbedtodayConsentState`
4. **View page source**: Should see gtag.js script from Google

### Events not appearing in Meta?

1. **Check Pixel ID**: `echo $META_PIXEL_ID` should output your ID
2. **Install Pixel Helper**: See events in extension popup
3. **Check consent**: `window.nycbedtodayConsentState.marketing` should be true

### GSC tag not working?

1. **Check token**: `echo $GSC_VERIFICATION_TOKEN` should output token
2. **View source**: Should see `<meta name="google-site-verification" content="...">` in `<head>`
3. **Wait**: Can take 24-48 hours for Google to verify

## Full Documentation

See `ANALYTICS_PIXEL_IMPLEMENTATION.md` for:
- Detailed setup instructions
- Event data structures
- Advanced configuration
- GDPR compliance info
- Browser support
- Performance details

## Files Modified/Created

```
New Plugin:
web/app/plugins/nycbedtoday-analytics/
├── nycbedtoday-analytics.php
├── README.md
├── includes/
│   ├── class-consent-banner.php
│   ├── class-ga4-tracker.php
│   ├── class-meta-pixel-tracker.php
│   └── class-gsc-verification.php
└── assets/
    ├── js/consent-banner.js
    └── css/consent-banner.css

Modified:
.env.example                                    # Added analytics config
web/app/mu-plugins/woocommerce-custom-setup.php # Removed duplicate tracking

Documentation:
ANALYTICS_PIXEL_IMPLEMENTATION.md               # Complete guide
ANALYTICS_QUICK_START.md                        # This file
```

## Key Files Explained

| File | Purpose |
|------|---------|
| `nycbedtoday-analytics.php` | Main plugin loader |
| `class-consent-banner.php` | Consent UI & storage |
| `class-ga4-tracker.php` | GA4 event tracking |
| `class-meta-pixel-tracker.php` | Meta Pixel tracking |
| `class-gsc-verification.php` | Search Console tag |
| `consent-banner.js` | Client-side consent logic |
| `consent-banner.css` | Consent banner styling |

## Support Resources

- [GA4 Help](https://support.google.com/analytics/topic/9282939)
- [Meta Pixel Docs](https://developers.facebook.com/docs/facebook-pixel)
- [Google Search Console](https://support.google.com/webmasters)

## Architecture

The implementation follows WordPress best practices:

- **Hooks-based**: Uses `wp_head`, `woocommerce_*` hooks
- **No external deps**: Only uses WordPress + GA4/Meta APIs
- **Consent-first**: Analytics waits for user consent
- **GDPR-ready**: Granular consent control
- **Performance**: Minimal local overhead
- **Security**: All secrets in environment variables

---

For detailed documentation, see `ANALYTICS_PIXEL_IMPLEMENTATION.md`
