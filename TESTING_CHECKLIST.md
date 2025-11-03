# WooCommerce Testing Checklist

## Pre-requisites
- [ ] Docker services running (`make up`)
- [ ] Composer dependencies installed (`make composer CMD='install'`)
- [ ] WordPress installed and configured
- [ ] .env file configured with API keys

## Required Configuration in .env

```bash
# Stripe Test Keys (from https://dashboard.stripe.com/test/apikeys)
STRIPE_PUBLISHABLE_KEY='pk_test_...'
STRIPE_SECRET_KEY='sk_test_...'

# Analytics IDs
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'
META_PIXEL_ID='000000000000000'
```

## Test Stripe Payments

### Test Card Numbers

| Scenario | Card Number | Result |
|----------|-------------|--------|
| Success | `4242 4242 4242 4242` | Payment succeeds |
| 3D Secure | `4000 0025 0000 3155` | Requires authentication |
| Declined | `4000 0000 0000 9995` | Card declined |
| Insufficient Funds | `4000 0000 0000 9995` | Payment fails |

**Additional Details for Testing:**
- **Expiry**: Any future date (e.g., 12/25)
- **CVC**: Any 3 digits (e.g., 123)
- **ZIP**: Any 5 digits (e.g., 12345)

## Products Verification

### Main Products (Mattresses)
- [ ] Twin Mattress - $599.00
- [ ] Full Mattress - $799.00
- [ ] Queen Mattress - $999.00
- [ ] King Mattress - $1,299.00

### Add-ons
- [ ] Old Bed Removal - $99.00
- [ ] Stair Carry Service - $49.00
- [ ] Mattress Protector - $79.00

**Check:** Go to **WP Admin** → **Products** → **All Products**

## Checkout Features Verification

### Reduced Fields
- [ ] No company field
- [ ] No address line 2
- [ ] No state field (optional)
- [ ] No postcode field (optional)
- [ ] Phone field is optional

### One-Page Checkout
- [ ] All checkout steps on single page
- [ ] No separate billing/shipping pages
- [ ] Guest checkout enabled

### Sticky CTA
- [ ] On mobile viewport, sticky "Complete Order" button appears at bottom
- [ ] Button triggers the main place order button
- [ ] Hidden on desktop viewport (>768px)

### Stripe Features
- [ ] Credit card fields appear
- [ ] Payment Request button appears (Apple Pay/Google Pay if supported)
- [ ] Test mode indicator visible

## Analytics Events Testing

### Setup GA4 DebugView
1. Go to GA4 → **Admin** → **DebugView**
2. Keep the window open while testing

### Setup Meta Test Events
1. Go to Meta Events Manager → **Test Events**
2. Get your browser ID or use Chrome extension
3. Enter browser ID in test events tool

### Event Testing Flow

| Action | GA4 Event | Meta Event | Status |
|--------|-----------|------------|--------|
| View product page | `view_item` | `ViewContent` | [ ] |
| Click "Add to Cart" | `add_to_cart` | `AddToCart` | [ ] |
| Go to checkout page | `begin_checkout` | `InitiateCheckout` | [ ] |
| Complete purchase | `purchase` | `Purchase` | [ ] |

### Verify Each Event Contains:
- [ ] **view_item**: product ID, name, price
- [ ] **add_to_cart**: product ID, name, price, quantity
- [ ] **begin_checkout**: cart total, items array
- [ ] **purchase**: transaction ID, total value, items

## Payment Flow Test

### Complete Purchase Flow

1. **Browse Products**
   - [ ] Navigate to shop page
   - [ ] See all 7 products listed
   
2. **View Product**
   - [ ] Click on a product (e.g., Queen Mattress)
   - [ ] Check browser console for tracking events
   - [ ] Verify event in GA4 DebugView
   - [ ] Verify event in Meta Test Events

3. **Add to Cart**
   - [ ] Click "Add to Cart" button
   - [ ] See success message
   - [ ] Check for tracking events
   - [ ] Verify in GA4 and Meta

4. **Go to Cart**
   - [ ] View cart contents
   - [ ] Verify product and price
   - [ ] Update quantity if needed

5. **Proceed to Checkout**
   - [ ] Click "Proceed to Checkout"
   - [ ] Check for `begin_checkout` event
   - [ ] Verify in analytics tools

6. **Fill Checkout Form**
   - [ ] First Name: Test
   - [ ] Last Name: User
   - [ ] Email: test@example.com
   - [ ] Address: 123 Test St
   - [ ] City: Test City
   - [ ] Country: United States

7. **Enter Payment Details**
   - [ ] Card: 4242 4242 4242 4242
   - [ ] Expiry: 12/25
   - [ ] CVC: 123
   - [ ] ZIP: 12345

8. **Complete Order**
   - [ ] Click "Place Order" or sticky CTA
   - [ ] Payment processes successfully
   - [ ] Redirected to thank you page
   - [ ] Check for `purchase` event
   - [ ] Verify in GA4 and Meta

9. **Verify Order**
   - [ ] Go to WP Admin → WooCommerce → Orders
   - [ ] See the new order
   - [ ] Status should be "Processing" or "Completed"
   - [ ] Payment method shows "Stripe"

## Apple Pay / Google Pay Testing

### Apple Pay (Safari on macOS/iOS)
- [ ] Use Safari browser
- [ ] Ensure Apple Pay is configured in wallet
- [ ] Navigate to checkout
- [ ] See Apple Pay button
- [ ] Click and authenticate
- [ ] Payment processes successfully

### Google Pay (Chrome)
- [ ] Use Chrome browser with Google account
- [ ] Ensure Google Pay is set up
- [ ] Navigate to checkout
- [ ] See Google Pay button
- [ ] Click and authenticate
- [ ] Payment processes successfully

**Note**: Payment Request buttons may require HTTPS in production. For local testing:
- Use ngrok: `ngrok http 8080`
- Set up local SSL with mkcert
- Test on staging with valid SSL certificate

## Troubleshooting

### Products Not Appearing
```bash
# Check if products were seeded
docker compose exec php wp post list --post_type=product --allow-root

# Manually trigger seeding
docker compose exec php wp option delete wc_products_seeded --allow-root
# Then refresh WordPress admin dashboard
```

### Stripe Not Configured
```bash
# Check Stripe settings
docker compose exec php wp option get woocommerce_stripe_settings --allow-root

# Verify .env file has correct keys
cat .env | grep STRIPE
```

### Analytics Not Tracking
```bash
# Check if measurement IDs are set
cat .env | grep GA4
cat .env | grep META

# View page source to see if scripts are injected
# Look for gtag and fbq functions
```

### Cache Issues
```bash
# Clear Redis cache
docker compose exec redis redis-cli FLUSHALL

# Restart services
make restart
```

## Success Criteria

All of the following must be verified:

- [x] WooCommerce installed and activated
- [x] Stripe gateway installed and configured
- [x] 7 products seeded (4 mattresses + 3 add-ons)
- [x] One-page checkout functional
- [x] Reduced checkout fields implemented
- [x] Sticky CTA appears on mobile
- [ ] Test payment succeeds with Stripe test card
- [ ] `view_item` event appears in GA4 DebugView
- [ ] `add_to_cart` event appears in GA4 DebugView
- [ ] `begin_checkout` event appears in GA4 DebugView
- [ ] `purchase` event appears in GA4 DebugView
- [ ] `ViewContent` event appears in Meta Test Events
- [ ] `AddToCart` event appears in Meta Test Events
- [ ] `InitiateCheckout` event appears in Meta Test Events
- [ ] `Purchase` event appears in Meta Test Events

## Additional Notes

- All events include proper e-commerce data (product IDs, names, prices)
- GA4 debug mode is enabled by default for testing
- Events fire on page load after the action (due to redirect behavior)
- Session storage is used to persist event data across redirects
- Mobile sticky CTA is hidden on desktop (CSS media query at 768px)

## Quick Commands

```bash
# View logs
make logs

# Access PHP container
make shell

# Run WP-CLI commands
make wp CMD='plugin list'

# Check WooCommerce status
make wp CMD='wc status'

# List products
make wp CMD='post list --post_type=product'

# View orders
make wp CMD='wc order list'
```
