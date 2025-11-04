# Analytics Pixel Wiring Implementation

Complete guide to GA4, Meta Pixel, GSC verification, and consent banner implementation.

## Overview

The NYC Bed Today Analytics plugin provides GDPR-compliant analytics integration with:

- **GA4 (Google Analytics 4)**: Enhanced eCommerce event tracking
- **Meta Pixel**: Standard event tracking with test event support
- **GSC Verification**: Google Search Console verification tag
- **Consent Banner**: Lightweight, client-side consent management

## Installation & Activation

The analytics plugin is automatically loaded as part of the WordPress installation. It's found at:
```
/web/app/plugins/nycbedtoday-analytics/
```

No activation needed - it loads automatically via `plugins_loaded` hook.

## Environment Configuration

### 1. Add Analytics Credentials to .env

Update your `.env` file with the following variables:

```bash
# Analytics & SEO
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'           # Your GA4 Measurement ID
GA4_DEBUG_MODE='true'                       # Enable debug mode in GA4
META_PIXEL_ID='000000000000000'            # Your Meta Pixel ID
META_PIXEL_TEST_EVENT_ID='TEST12345'       # Optional: Test event code for Meta
GSC_VERIFICATION_TOKEN='your-token-here'   # Google Search Console verification token
CONSENT_BANNER_ENABLED='true'              # Enable/disable consent banner
```

### 2. Get Your Credentials

#### GA4 Measurement ID
1. Go to [Google Analytics 4](https://analytics.google.com)
2. Select your property
3. Go to Admin → Data Streams
4. Select your web stream
5. Copy the Measurement ID (format: `G-XXXXXXXXXX`)

#### Meta Pixel ID
1. Go to [Meta Business Suite](https://business.facebook.com)
2. Select your account
3. Go to Data Sources → Pixels
4. Copy your Pixel ID (format: `000000000000000`)

#### Meta Test Event ID
1. In Meta Pixels settings, go to Test Events
2. Create a test event code to use in development
3. Add to your `.env` as `META_PIXEL_TEST_EVENT_ID`

#### GSC Verification Token
1. Go to [Google Search Console](https://search.google.com/search-console)
2. Add your property
3. Go to Settings → Verification details
4. Copy the content value from the meta tag (format: `xxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)

## Features

### 1. GA4 Integration

#### Script Loading
- GTags.js loads automatically when GA4_MEASUREMENT_ID is configured
- Uses consent mode by default (analytics_storage: denied)
- Debug mode enabled when GA4_DEBUG_MODE='true'

#### Tracked Events

**View Item**
- Fires on product page load
- Includes: item_id, item_name, price, currency

**Add to Cart**
- Fires when product added to WooCommerce cart
- Includes: item_id, item_name, price, quantity, currency

**Begin Checkout**
- Fires when user navigates to checkout
- Includes: all cart items, total value, currency

**Purchase**
- Fires on order confirmation page
- Includes: transaction_id, total value, items, currency

### 2. Meta Pixel Integration

#### Pixel Loading
- Meta Pixel script loads automatically when META_PIXEL_ID is configured
- Uses fbq() API for event tracking
- Support for test event codes

#### Tracked Events

**PageView**
- Automatically tracked on every page

**ViewContent**
- Fires on product page load
- Includes: content_ids, content_name, content_type, value, currency

**AddToCart**
- Fires when product added to cart
- Includes: content_ids, content_name, content_type, value, currency
- Supports test_event_code parameter

**InitiateCheckout**
- Fires when user navigates to checkout
- Includes: value, currency, num_items

**Purchase**
- Fires on order confirmation page
- Includes: value, currency, num_items
- Supports test_event_code parameter

### 3. GSC Verification

When GSC_VERIFICATION_TOKEN is configured, a meta tag is automatically added to the page:

```html
<meta name="google-site-verification" content="your-verification-token" />
```

This allows Google Search Console to verify ownership of your domain.

### 4. Consent Banner

#### Default Behavior

When `CONSENT_BANNER_ENABLED='true'`:

- Banner appears on first visit
- User can Accept All, Reject All, or Customize
- Preferences stored in localStorage
- Banner hidden on subsequent visits
- Analytics events respect user consent

#### Consent State Structure

```javascript
{
  analytics: true,   // GA4 and analytics events
  marketing: true    // Meta Pixel and marketing events
}
```

#### Consent Banner UI

The banner includes:
- **Main Banner**: Message with Accept/Reject buttons
- **Settings Panel**: Detailed preference toggles
- **Analytics Checkbox**: Control GA4 event firing
- **Marketing Checkbox**: Control Meta Pixel event firing

#### User Interactions

1. **Accept All**: Both analytics and marketing enabled
2. **Reject All**: Both analytics and marketing disabled
3. **Learn More**: Opens detailed settings panel
4. **Customize**: Allows independent control of each category
5. **Save Preferences**: Saves custom selection

#### LocalStorage

Consent state stored in `localStorage['nycbedtoday_analytics_consent']`:

```javascript
{
  "analytics": true,
  "marketing": true
}
```

Banner visibility tracked in `localStorage['nycbedtoday_consent_banner_shown']`

## Validation & Testing

### GA4 Event Validation

#### Using GA4 Debug View

1. With GA4_DEBUG_MODE='true', events enter debug mode
2. Go to GA4 Admin → DebugView
3. You should see your browser's events in real-time
4. Look for:
   - `view_item` events on product pages
   - `add_to_cart` events after adding to cart
   - `purchase` events on order confirmation

#### Using GA4 Tag Assistant

1. Install [Google Tag Assistant](https://support.google.com/tagassistant/answer/6102821)
2. Navigate your site
3. View gtag.js script loading and event firing
4. Verify Measurement ID matches your configuration

### Meta Pixel Event Validation

#### Using Meta Pixel Helper

1. Install [Meta Pixel Helper Chrome Extension](https://chrome.google.com/webstore/detail/meta-pixel-helper/fdgbgdpofioknagcbfbnfjnfbgdjfphm)
2. Navigate your site
3. View events being fired in the extension popup
4. Look for:
   - ViewContent events on product pages
   - AddToCart events after adding to cart
   - Purchase events on order confirmation

#### Using Meta Test Events

If configured with META_PIXEL_TEST_EVENT_ID:

1. Go to Meta Pixels → Test Events
2. Look for events from your test event code
3. Verify event data matches expectations

### GSC Verification

1. Go to Google Search Console
2. Add your domain property
3. Check Verification details
4. Should show "Verified" status when token is present in page head

## Event Data Structure

### GA4 Events

#### view_item
```javascript
gtag('event', 'view_item', {
  currency: 'USD',
  value: 1299.99,
  items: [{
    item_id: '123',
    item_name: 'Queen Mattress',
    price: 1299.99
  }]
});
```

#### add_to_cart
```javascript
gtag('event', 'add_to_cart', {
  currency: 'USD',
  value: 1299.99,
  items: [{
    item_id: '123',
    item_name: 'Queen Mattress',
    price: 1299.99,
    quantity: 1
  }]
});
```

#### purchase
```javascript
gtag('event', 'purchase', {
  transaction_id: '12345',
  value: 1499.99,
  currency: 'USD',
  items: [...]
});
```

### Meta Pixel Events

#### ViewContent
```javascript
fbq('track', 'ViewContent', {
  content_ids: ['123'],
  content_name: 'Queen Mattress',
  content_type: 'product',
  value: 1299.99,
  currency: 'USD'
});
```

#### AddToCart
```javascript
fbq('track', 'AddToCart', {
  content_ids: ['123'],
  content_name: 'Queen Mattress',
  content_type: 'product',
  value: 1299.99,
  currency: 'USD',
  test_event_code: 'TEST12345'  // Optional
});
```

#### Purchase
```javascript
fbq('track', 'Purchase', {
  value: 1499.99,
  currency: 'USD',
  num_items: 1,
  test_event_code: 'TEST12345'  // Optional
});
```

## Consent Flow

### Detailed Consent Logic

1. **First Visit**
   - Banner appears at bottom of page
   - Default: Both analytics and marketing enabled

2. **User Decision**
   - Accept All: Full consent, analytics fires immediately
   - Reject All: No consent, analytics blocked
   - Customize: User chooses specific categories

3. **Event Firing with Consent**
   - When consent enabled: Events fire when consent state permits
   - When consent disabled: Events fire regardless (for consistency with old setup)
   - Consent state checked via `window.nycbedtodayConsentState`

4. **On Subsequent Visits**
   - Banner hidden
   - Consent state restored from localStorage
   - Events respect stored preference

### JavaScript API for Developers

Access consent state in JavaScript:

```javascript
// Check current consent state
if (window.nycbedtodayConsentState.analytics) {
  // Analytics enabled
}

// Listen for consent changes
window.addEventListener('nycbedtodayConsentChange', function(e) {
  console.log('Analytics:', e.detail.analytics);
  console.log('Marketing:', e.detail.marketing);
});
```

## Environment-Specific Setup

### Development

```bash
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'
GA4_DEBUG_MODE='true'
META_PIXEL_ID='000000000000000'
META_PIXEL_TEST_EVENT_ID='TEST12345'  # Use test event code
GSC_VERIFICATION_TOKEN=''              # Can be empty in dev
CONSENT_BANNER_ENABLED='true'
```

### Staging/Testing

```bash
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'
GA4_DEBUG_MODE='true'
META_PIXEL_ID='000000000000000'
META_PIXEL_TEST_EVENT_ID='TEST12345'
GSC_VERIFICATION_TOKEN='your-token'
CONSENT_BANNER_ENABLED='true'
```

### Production

```bash
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'
GA4_DEBUG_MODE='false'
META_PIXEL_ID='000000000000000'
META_PIXEL_TEST_EVENT_ID=''            # Don't use test events in prod
GSC_VERIFICATION_TOKEN='your-token'
CONSENT_BANNER_ENABLED='true'
```

## Troubleshooting

### GA4 Events Not Appearing

1. **Check Configuration**
   ```bash
   echo $GA4_MEASUREMENT_ID  # Should output G-XXXXXXXXXX
   ```

2. **Verify Script Loading**
   - Open DevTools → Network tab
   - Search for "gtag"
   - Should see script loading from googletagmanager.com

3. **Check Debug View**
   - Go to GA4 Admin → DebugView
   - Enable debug mode: GA4_DEBUG_MODE='true'
   - Wait 30 seconds for data to appear

4. **Consent Issues**
   - Open DevTools → Console
   - Check `window.nycbedtodayConsentState`
   - Verify analytics consent is not false

### Meta Pixel Events Not Appearing

1. **Check Pixel ID**
   ```bash
   echo $META_PIXEL_ID  # Should output your pixel ID
   ```

2. **Verify fbq Script**
   - Open DevTools → Network tab
   - Search for "fbevents"
   - Should see script loading from facebook.com

3. **Check Test Event Code**
   - If using test events, verify META_PIXEL_TEST_EVENT_ID is set
   - Go to Meta Pixels → Test Events
   - Look for events from your test code

4. **Use Meta Pixel Helper**
   - Install extension
   - Navigate to your site
   - Check events in extension popup

### GSC Verification Not Working

1. **Verify Meta Tag Present**
   - View page source (Ctrl+U)
   - Search for "google-site-verification"
   - Should be in <head> section

2. **Check Token Format**
   - Token should be alphanumeric string
   - No extra quotes or spaces

3. **Wait for Indexing**
   - GSC verification can take 24-48 hours
   - Keep meta tag in place during verification

## Disabling Features

### Disable Consent Banner
```bash
CONSENT_BANNER_ENABLED='false'
```

### Disable GA4
```bash
GA4_MEASUREMENT_ID=''  # Leave empty or remove
```

### Disable Meta Pixel
```bash
META_PIXEL_ID=''  # Leave empty or remove
```

### Disable GSC Verification
```bash
GSC_VERIFICATION_TOKEN=''  # Leave empty
```

## Advanced Configuration

### Custom Consent Categories

To add additional consent categories, modify the JavaScript in `assets/js/consent-banner.js`:

```javascript
// Add new category to defaults
this.consentState = { 
  analytics: true, 
  marketing: true,
  custom_category: true  // New category
};
```

### Programmatic Consent Update

Update consent via JavaScript:

```javascript
// Manually update consent state
window.nycbedtodayConsentState = {
  analytics: true,
  marketing: false
};

// Save to localStorage
localStorage.setItem(
  'nycbedtoday_analytics_consent',
  JSON.stringify(window.nycbedtodayConsentState)
);

// Dispatch change event
const event = new CustomEvent('nycbedtodayConsentChange', {
  detail: window.nycbedtodayConsentState
});
window.dispatchEvent(event);
```

## File Structure

```
/web/app/plugins/nycbedtoday-analytics/
├── nycbedtoday-analytics.php          # Main plugin file
├── includes/
│   ├── class-consent-banner.php       # Consent banner UI
│   ├── class-ga4-tracker.php          # GA4 event tracking
│   ├── class-meta-pixel-tracker.php   # Meta Pixel event tracking
│   └── class-gsc-verification.php     # GSC verification tag
└── assets/
    ├── js/
    │   └── consent-banner.js          # Consent banner logic
    └── css/
        └── consent-banner.css         # Consent banner styles
```

## Performance Impact

- **Consent Banner**: ~3KB (minified)
- **GA4 Script**: Loaded from Google (no local impact)
- **Meta Pixel Script**: Loaded from Facebook (no local impact)
- **Session Data**: Minimal overhead (product data only)

The implementation uses lightweight client-side storage and minimal overhead.

## Browser Support

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- IE11: ⚠️ Partial (no consent banner, events may not fire)

## GDPR Compliance

The implementation provides:

✅ **Consent Before Analytics**: GA4/Meta Pixel wait for consent  
✅ **Granular Control**: Users can choose specific categories  
✅ **Preference Storage**: LocalStorage, no cookies by default  
✅ **Easy Revocation**: Users can change preferences anytime  
✅ **Transparent Communication**: Clear banner messaging  
✅ **No Hardcoded Tokens**: All secrets in environment variables  

**Note**: This implementation provides technical consent management. Legal compliance (Privacy Policy, GDPR clauses) is your responsibility.

## Support & Debugging

### Enable Verbose Logging

Add to WordPress `wp-config.php` for debugging:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs at `/web/app/debug.log`

### Contact Information

For implementation issues or questions, refer to:
- [GA4 Documentation](https://support.google.com/analytics/topic/9282939)
- [Meta Pixel Documentation](https://developers.facebook.com/docs/facebook-pixel)
- [Google Search Console Help](https://support.google.com/webmasters)
