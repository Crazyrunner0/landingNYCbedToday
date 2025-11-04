# Analytics Implementation - Validation Checklist

Complete checklist for validating GA4, Meta Pixel, GSC, and consent banner implementation.

## Pre-Deployment Checklist

### Configuration
- [ ] `.env` file updated with all analytics credentials
- [ ] GA4_MEASUREMENT_ID set to valid format (G-XXXXXXXXXX)
- [ ] META_PIXEL_ID set to valid format (000000000000000)
- [ ] GSC_VERIFICATION_TOKEN set (or intentionally empty)
- [ ] META_PIXEL_TEST_EVENT_ID set for development (optional)
- [ ] CONSENT_BANNER_ENABLED set to 'true'
- [ ] All secrets stored only in .env, not in code
- [ ] No hardcoded tokens or IDs in PHP files

### Plugin Installation
- [ ] Plugin directory created: `/web/app/plugins/nycbedtoday-analytics/`
- [ ] Main plugin file exists: `nycbedtoday-analytics.php`
- [ ] All include files present in `includes/`:
  - [ ] class-consent-banner.php
  - [ ] class-ga4-tracker.php
  - [ ] class-meta-pixel-tracker.php
  - [ ] class-gsc-verification.php
- [ ] All assets present:
  - [ ] assets/js/consent-banner.js
  - [ ] assets/css/consent-banner.css
- [ ] Plugin loads without errors
- [ ] No activation hook needed (automatic via plugins_loaded)

### Old Code Cleanup
- [ ] WooCommerce Custom Setup mu-plugin updated
- [ ] Analytics methods removed from old mu-plugin
- [ ] Session tracking methods removed from old mu-plugin
- [ ] No duplicate tracking code remains
- [ ] Description updated in mu-plugin header

## GA4 Validation

### Script Loading
- [ ] View page source (Ctrl+U)
- [ ] Search for "googletagmanager.com"
- [ ] gtag.js script is present in `<head>`
- [ ] Measurement ID in script matches .env value

### Consent Mode
- [ ] Page source contains `gtag('consent', 'default', ...)`
- [ ] Consent defaults set to 'denied' initially
- [ ] Consent mode update happens on user acceptance

### Debug View (with GA4_DEBUG_MODE='true')
- [ ] Go to GA4 Property → Admin → DebugView
- [ ] Open site in new browser
- [ ] DebugView shows real-time events
- [ ] Wait ~30 seconds for events to appear

### Event Validation

#### View Item Event
- [ ] Navigate to product page
- [ ] GA4 DebugView shows `view_item` event
- [ ] Event includes:
  - [ ] item_id
  - [ ] item_name
  - [ ] price
  - [ ] currency

#### Add to Cart Event
- [ ] Click "Add to Cart" on product
- [ ] GA4 DebugView shows `add_to_cart` event
- [ ] Event includes:
  - [ ] item_id
  - [ ] item_name
  - [ ] price
  - [ ] quantity
  - [ ] currency

#### Begin Checkout Event
- [ ] Go to checkout page
- [ ] GA4 DebugView shows `begin_checkout` event
- [ ] Event includes:
  - [ ] items (array)
  - [ ] value (total)
  - [ ] currency

#### Purchase Event
- [ ] Complete an order
- [ ] Go to order confirmation page
- [ ] GA4 DebugView shows `purchase` event
- [ ] Event includes:
  - [ ] transaction_id
  - [ ] value
  - [ ] currency
  - [ ] items (array)

### Debug Mode Toggle
- [ ] Enable GA4_DEBUG_MODE='true'
- [ ] Verify 'debug_mode': true in gtag config
- [ ] Disable GA4_DEBUG_MODE='false'
- [ ] Verify debug_mode removed

## Meta Pixel Validation

### Script Loading
- [ ] View page source (Ctrl+U)
- [ ] Search for "fbevents.js"
- [ ] Meta Pixel script is present
- [ ] fbq('init', ...) called with correct Pixel ID
- [ ] Pixel ID in script matches .env value

### Install Meta Pixel Helper
- [ ] Install [Meta Pixel Helper](https://chrome.google.com/webstore/detail/meta-pixel-helper/)
- [ ] Enable extension
- [ ] Extension icon shows in Chrome toolbar

### Event Validation with Helper

#### PageView Event
- [ ] Reload any page
- [ ] Meta Pixel Helper shows PageView event
- [ ] Event appears in helper popup

#### ViewContent Event
- [ ] Navigate to product page
- [ ] Meta Pixel Helper shows ViewContent event
- [ ] Event includes:
  - [ ] content_ids
  - [ ] content_name
  - [ ] content_type
  - [ ] value
  - [ ] currency

#### AddToCart Event
- [ ] Click "Add to Cart"
- [ ] Meta Pixel Helper shows AddToCart event
- [ ] Event includes:
  - [ ] content_ids
  - [ ] content_name
  - [ ] content_type
  - [ ] value
  - [ ] currency

#### InitiateCheckout Event
- [ ] Go to checkout
- [ ] Meta Pixel Helper shows InitiateCheckout event
- [ ] Event includes:
  - [ ] value
  - [ ] currency
  - [ ] num_items

#### Purchase Event
- [ ] Complete order
- [ ] Go to order confirmation page
- [ ] Meta Pixel Helper shows Purchase event
- [ ] Event includes:
  - [ ] value
  - [ ] currency
  - [ ] num_items

### Test Event Code (if configured)
- [ ] META_PIXEL_TEST_EVENT_ID set in .env
- [ ] Events include test_event_code parameter
- [ ] Go to Meta Pixels → Test Events
- [ ] See events with matching test event code
- [ ] Event data visible in Meta's interface

## GSC Verification

### Meta Tag Presence
- [ ] View page source (Ctrl+U)
- [ ] Search for "google-site-verification"
- [ ] Meta tag present in `<head>` section
- [ ] Token matches GSC_VERIFICATION_TOKEN

### If GSC_VERIFICATION_TOKEN set to empty
- [ ] Meta tag should not appear in page source
- [ ] No errors in console

### GSC Verification
- [ ] Go to [Google Search Console](https://search.google.com/search-console)
- [ ] Add property for your domain
- [ ] Select "Meta tag" verification method
- [ ] Copy verification token
- [ ] Add to .env as GSC_VERIFICATION_TOKEN
- [ ] Go to property settings
- [ ] Check "Verified" status

## Consent Banner Validation

### First Visit Behavior
- [ ] Open site in private/incognito window
- [ ] Banner appears at bottom of page
- [ ] Banner contains:
  - [ ] Title "We value your privacy"
  - [ ] Description text
  - [ ] "Learn more" button
  - [ ] "Reject" button
  - [ ] "Accept All" button

### Accept All Button
- [ ] Click "Accept All"
- [ ] Banner closes
- [ ] Open DevTools → Console
- [ ] `window.nycbedtodayConsentState.analytics` should be true
- [ ] `window.nycbedtodayConsentState.marketing` should be true
- [ ] Events fire normally

### Reject All Button
- [ ] Open site in new private window
- [ ] Click "Reject All"
- [ ] Banner closes
- [ ] In Console: `window.nycbedtodayConsentState.analytics` should be false
- [ ] In Console: `window.nycbedtodayConsentState.marketing` should be false
- [ ] Events do NOT fire (or are gated by consent check)

### Learn More / Settings Button
- [ ] Open site in new private window
- [ ] Click "Learn more" button
- [ ] Settings panel opens
- [ ] Panel shows:
  - [ ] Analytics checkbox (default: checked)
  - [ ] Marketing checkbox (default: checked)
  - [ ] Description for each
  - [ ] "Back" button
  - [ ] "Save Preferences" button

### Customize Preferences
- [ ] In settings panel, uncheck "Analytics"
- [ ] Click "Save Preferences"
- [ ] Panel closes
- [ ] In Console: `window.nycbedtodayConsentState.analytics` should be false
- [ ] In Console: `window.nycbedtodayConsentState.marketing` should be true
- [ ] Analytics events should not fire

### Banner Persistence
- [ ] Accept/Reject on first visit
- [ ] Reload page
- [ ] Banner should NOT appear
- [ ] Preferences should be remembered (check Console)
- [ ] Reload multiple times - banner still doesn't appear
- [ ] Open different pages - banner still hidden
- [ ] Events still respect previous consent choice

### Banner Reset
- [ ] Open DevTools → Application → LocalStorage
- [ ] Find `nycbedtoday_analytics_consent` key
- [ ] Delete it
- [ ] Reload page
- [ ] Banner should appear again
- [ ] Clear `nycbedtoday_consent_banner_shown` key
- [ ] Reload page
- [ ] Banner appears again

### Consent Change Event
- [ ] Open Console
- [ ] Paste:
```javascript
window.addEventListener('nycbedtodayConsentChange', function(e) {
  console.log('Consent changed:', e.detail);
});
```
- [ ] Make a consent choice in banner
- [ ] Event should fire in console with new consent state
- [ ] GA4/Meta Pixel should update consent accordingly

## Styling & UX

### Mobile Responsiveness
- [ ] Open site on mobile device
- [ ] Banner displays correctly on small screens
- [ ] Buttons are easy to tap (not too small)
- [ ] Text is readable
- [ ] No layout breakage
- [ ] Settings panel responsive

### Desktop Display
- [ ] Banner displays at bottom of page
- [ ] Does not cover important content
- [ ] Buttons are properly spaced
- [ ] Text is clear

### Accessibility
- [ ] All buttons have clear labels
- [ ] Checkboxes are properly labeled
- [ ] Tab through all interactive elements
- [ ] Focus indicators visible

## Consent-Gated Events

### Analytics Disabled Behavior
- [ ] Set consent to reject analytics
- [ ] Navigate to product page
- [ ] GA4 DebugView should NOT show view_item event
- [ ] (Or should show with consent check failing)

### Marketing Disabled Behavior
- [ ] Set consent to reject marketing
- [ ] Navigate to product page
- [ ] Meta Pixel Helper should NOT show ViewContent event

### Consent Enabled Behavior
- [ ] Set consent to accept all
- [ ] Navigate to product page
- [ ] GA4 DebugView shows view_item event
- [ ] Meta Pixel Helper shows ViewContent event

## Performance Testing

### Page Load Time
- [ ] Measure page load time
- [ ] Analytics scripts don't significantly impact load
- [ ] Compare with/without analytics credentials

### Script Sizes
- [ ] consent-banner.js is small (~4KB)
- [ ] consent-banner.css is small (~4KB)
- [ ] External scripts (gtag, fbevents) lazy-loaded

### LocalStorage Usage
- [ ] Open DevTools → Application → LocalStorage
- [ ] Check size of stored consent data
- [ ] Should be minimal (< 1KB)

## Browser Compatibility

### Chrome/Chromium
- [ ] ✅ Banner appears
- [ ] ✅ All buttons work
- [ ] ✅ Events fire
- [ ] ✅ Preferences persist

### Firefox
- [ ] ✅ Banner appears
- [ ] ✅ All buttons work
- [ ] ✅ Events fire
- [ ] ✅ Preferences persist

### Safari
- [ ] ✅ Banner appears
- [ ] ✅ All buttons work
- [ ] ✅ Events fire
- [ ] ✅ Preferences persist

### Edge
- [ ] ✅ Banner appears
- [ ] ✅ All buttons work
- [ ] ✅ Events fire
- [ ] ✅ Preferences persist

### Mobile Safari
- [ ] ✅ Banner displays correctly
- [ ] ✅ Touch interactions work
- [ ] ✅ Events fire

## Environment-Specific Validation

### Development Environment
- [ ] GA4_DEBUG_MODE='true'
- [ ] Events appear in DebugView
- [ ] META_PIXEL_TEST_EVENT_ID set
- [ ] Test events visible in Meta Test Events
- [ ] All features working

### Staging Environment
- [ ] Credentials point to staging GA4/Pixel
- [ ] GA4_DEBUG_MODE can be 'false'
- [ ] Test event ID still active for validation
- [ ] All features working

### Production Environment
- [ ] Credentials point to production GA4/Pixel
- [ ] GA4_DEBUG_MODE='false'
- [ ] META_PIXEL_TEST_EVENT_ID='' (empty)
- [ ] No sensitive data in debug output
- [ ] Events appearing in production accounts

## Documentation Verification

- [ ] ANALYTICS_PIXEL_IMPLEMENTATION.md exists
- [ ] ANALYTICS_QUICK_START.md exists
- [ ] web/app/plugins/nycbedtoday-analytics/README.md exists
- [ ] Documentation covers:
  - [ ] Setup instructions
  - [ ] Event validation
  - [ ] Troubleshooting
  - [ ] Configuration options
  - [ ] Browser support

## Final Validation

### Code Quality
- [ ] No JavaScript console errors
- [ ] No PHP warnings/notices
- [ ] No mixed content warnings (if HTTPS)
- [ ] No CORS errors

### Security Review
- [ ] No hardcoded secrets in code
- [ ] All tokens in .env only
- [ ] Proper escaping/sanitization
- [ ] No XSS vulnerabilities
- [ ] No CSRF vulnerabilities

### Functionality Summary
- [ ] GA4 tracking: ✅ WORKING
- [ ] Meta Pixel tracking: ✅ WORKING
- [ ] GSC verification: ✅ WORKING
- [ ] Consent banner: ✅ WORKING
- [ ] Consent enforcement: ✅ WORKING

### Ready for Production
- [ ] All checks above passed
- [ ] All credentials verified
- [ ] Events validated in platforms
- [ ] No errors in console/logs
- [ ] Documentation complete

## Sign-Off

- [ ] Validated by: ________________
- [ ] Date: ________________
- [ ] Ready for deployment: YES / NO

---

**Note**: This checklist should be completed before deploying to production. Each item must be verified to ensure proper functionality of the analytics implementation.
