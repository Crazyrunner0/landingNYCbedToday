# SEO & Analytics Integration

Complete guide to SEO, structured data, Google Analytics 4, and Meta Pixel setup.

## SEO Baseline with RankMath

### Overview

The SEO baseline provides:
- **RankMath Plugin** - Professional WordPress SEO tool
- **Automatic Configuration** - Via mu-plugin (`rankmath-setup.php`)
- **Structured Data** - JSON-LD schemas for rich results
- **Sitemaps** - XML sitemap generation
- **Robots.txt** - Search engine crawling directives

### Setup

**Plugin Installation**:
```bash
# Installed via Composer, activated automatically
make wp CMD='plugin list | grep rank-math'
```

**Automatic Configuration**:
The `rankmath-setup.php` mu-plugin automatically:
1. Activates RankMath on first load
2. Configures default settings
3. Enables sitemap generation
4. Enables JSON-LD output

### JSON-LD Structured Data

#### LocalBusiness Schema

Automatically output on homepage to help Google understand your business.

**Data Included**:
- Business name and description
- Physical address (NYC)
- Phone number
- Latitude/Longitude
- Social media links
- Business logo

**Customize via Filter**:

```php
add_filter('nycbedtoday_localbusiness_schema', function($schema) {
    $schema['name'] = 'Your Business Name';
    $schema['address']['streetAddress'] = 'Your Street Address';
    $schema['address']['addressLocality'] = 'Your City';
    $schema['address']['postalCode'] = 'Your ZIP';
    $schema['telephone'] = 'Your Phone';
    $schema['sameAs'][] = 'https://twitter.com/yourbusiness';
    return $schema;
});
```

#### BreadcrumbList Schema

Automatically output on non-homepage pages for navigation breadcrumbs.

**Example**:
- Single posts: Home > Post Title
- Archives: Home > Archive Type
- Categories: Home > Category Name

**Customize via Filter**:

```php
add_filter('nycbedtoday_breadcrumb_schema', function($schema) {
    // Modify breadcrumb items as needed
    return $schema;
});
```

#### FAQPage Schema

Automatically output on homepage with placeholder Q&A.

**Default FAQ Items**:
- What mattress sizes do you offer?
- Do you offer delivery?
- What is your return policy?
- Are your mattresses eco-friendly?

**Customize via Filter**:

```php
add_filter('nycbedtoday_faq_items', function($items) {
    $items[] = [
        'question' => 'Your Question?',
        'answer'   => 'Your Answer',
    ];
    return $items;
});
```

#### Product Schema

Automatically output for WooCommerce products:

```php
// Schema includes:
{
  "@type": "Product",
  "name": "Product Name",
  "description": "Product Description",
  "price": "99.99",
  "priceCurrency": "USD",
  "rating": { "ratingValue": 4.5, "ratingCount": 100 },
  "availability": "InStock",
  "url": "https://example.com/product"
}
```

### XML Sitemaps

**Location**: `/sitemap_index.xml`

RankMath automatically generates:
- Post sitemap
- Page sitemap
- Product sitemap (if WooCommerce)
- Category sitemap
- Tag sitemap

**Customize in RankMath Settings**:
- Which post types to include
- Update frequency
- Priority settings

### Robots.txt

**Location**: `/robots.txt`

RankMath generates SEO-friendly directives:

```
User-agent: *
Disallow: /wp-admin/
Disallow: /wp-includes/
Disallow: /wp-content/plugins/
Allow: /wp-content/themes/
Allow: /wp-content/uploads/

Sitemap: https://example.com/sitemap_index.xml
```

### Rich Results Testing

Verify structured data appears in Google Rich Results Test:

1. Go to https://search.google.com/test/rich-results
2. Enter site URL
3. Should show:
   - ✅ LocalBusiness (homepage)
   - ✅ BreadcrumbList (inner pages)
   - ✅ FAQPage (homepage)
   - ✅ Product (product pages)

### SEO Monitoring

**In WordPress**:
1. Go to RankMath → Dashboard
2. View SEO score for all posts
3. Get suggestions for improvement
4. Track keyword rankings (Pro feature)

**In Google Search Console**:
1. Add property (https://search.google.com/search-console)
2. Submit sitemap
3. Monitor indexing
4. Check for errors

## Google Analytics 4 (GA4)

### Setup

**Configure via Environment**:

```bash
# In .env
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
```

Or set in WordPress settings.

### Automatic Event Tracking

The `analytics-integration.php` mu-plugin automatically tracks:

#### Ecommerce Events

**View Item**:
- Triggered: When user views product page
- Data: Product name, price, category, product ID
- Use case: Understand product interest

**Add to Cart**:
- Triggered: When user adds item to cart
- Data: Product name, price, quantity, product ID
- Use case: Track purchase intent

**Begin Checkout**:
- Triggered: When user starts checkout
- Data: Cart items, total value
- Use case: Identify checkout abandonment

**Purchase**:
- Triggered: After successful order
- Data: Order ID, total, items, payment method
- Use case: Track conversions and revenue

#### Debug Mode

Enable GA4 debug mode in WordPress:

```php
// In wp-cli or settings
define('GA4_DEBUG', true);
```

When enabled:
- Events sent to both production and debug stream
- Check in GA4 Admin → Events → Debug events
- Useful for testing events before going live

#### Implementation Details

Events are tracked via:
1. **Server-side PHP hooks** - Via `analytics-integration.php`
2. **Automatic data collection** - No manual implementation needed
3. **WooCommerce integration** - Hooks into order events
4. **Fallback support** - Uses env vars if constants not set

### GA4 Configuration

**Connect to Property**:
1. Create property in Google Analytics 4
2. Copy Measurement ID (format: G-XXXXXXXXXX)
3. Add to `.env` or WordPress settings
4. Wait 24 hours for data to appear

**Verify Events**:
1. Go to Admin → Events
2. Create custom events as needed
3. View real-time data in Reports

**Conversion Tracking**:
1. Admin → Conversions → New conversion event
2. Select "Purchase" event
3. Enable as conversion
4. Track revenue in Acquisition reports

## Meta Pixel

### Setup

**Configure via Environment**:

```bash
# In .env
META_PIXEL_ID=xxxxxxxxxx
```

### Automatic Event Tracking

The `analytics-integration.php` mu-plugin automatically tracks:

#### Ecommerce Events

**ViewContent**:
- Triggered: Product page view
- Data: Product ID, name, price, category
- Use case: Retargeting to users who viewed products

**AddToCart**:
- Triggered: Item added to cart
- Data: Product ID, name, price, quantity
- Use case: Retargeting abandoned carts

**InitiateCheckout**:
- Triggered: Checkout started
- Data: Cart items, total value
- Use case: Retarget checkout abandoners

**Purchase**:
- Triggered: Order completed
- Data: Order ID, amount, currency, items
- Use case: Track conversions and revenue

### Meta Pixel Configuration

**Connect Pixel**:
1. Create pixel in Facebook Business Manager
2. Copy Pixel ID (numeric, e.g., 123456789)
3. Add to `.env` or WordPress settings
4. Pixel code automatically injected on all pages

**Verify Implementation**:
1. Install Meta Pixel Helper Chrome extension
2. Visit site, add to cart, complete checkout
3. Should see events firing in extension
4. Check Meta Ads Manager → Pixels for data

**Conversion Tracking**:
1. Ads Manager → Data Sources → Pixels
2. Settings → Conversions
3. Enable "Purchase" conversion
4. Use for custom audiences and optimization

## Analytics Implementation

### Events Architecture

**Server-Side (PHP)**:
- Hooks into WooCommerce actions
- Collects order data
- Sends to GA4 and Meta Pixel

**Client-Side (JavaScript)**:
- Cookie-based user tracking
- Page views and events
- Automatic Pixel/gtag injection

### Event Data Collection

**Product Data**:
```javascript
{
  product_id: "123",
  product_name: "Premium Mattress",
  price: "1299.99",
  category: "Mattresses",
  currency: "USD"
}
```

**Order Data**:
```javascript
{
  order_id: "WC-456",
  value: "1299.99",
  currency: "USD",
  items: [
    {
      product_id: "123",
      product_name: "Premium Mattress",
      price: "1299.99",
      quantity: 1,
      category: "Mattresses"
    }
  ]
}
```

### Revenue Reporting

**GA4**:
1. Reports → Monetization → eCommerce purchases
2. View revenue by product, source, user
3. Create custom reports

**Meta**:
1. Ads Manager → Results
2. View purchase conversions and ROAS
3. Optimize campaigns based on data

### Testing Events

**GA4 Debug Mode**:
```bash
# Set in .env
GA4_DEBUG=true

# Events appear in:
# GA4 Admin → Debug → Events tab
```

**Meta Pixel Helper**:
1. Install Chrome extension
2. Visit site
3. View events in extension popup
4. Verify data accuracy

## Privacy & Compliance

### Data Collection Notice

Add to privacy policy:

```markdown
We use Google Analytics 4 and Meta Pixel to track:
- Page views and user interactions
- Purchase conversions
- Product performance
- Campaign effectiveness

This data is used to improve our service and show relevant ads.
```

### Cookie Consent

If required by jurisdiction:

1. Install cookie consent plugin (e.g., Cookiebot)
2. Require user consent before GA4/Meta Pixel
3. Disable analytics for opt-out users

### GDPR Compliance

- Anonymize IP addresses in GA4
- Allow user data deletion requests
- Maintain data retention policies

## Troubleshooting

### GA4 Not Tracking

**Check**:
1. Measurement ID is correct (G-XXXXXXXXXX format)
2. GA4 property exists in Google Analytics
3. Correct property ID in `.env`
4. Wait 24 hours for data to appear
5. Check in GA4 Realtime reports

**Debug**:
```bash
# Enable GA4 debug mode
make wp CMD='config get GA4_DEBUG'

# Check events in GA4 Admin → Events
```

### Meta Pixel Not Firing

**Check**:
1. Pixel ID is correct (numeric)
2. Pixel installed in Meta Ads Manager
3. Correct ID in `.env`
4. Use Meta Pixel Helper to verify

**Debug**:
```bash
# Install Meta Pixel Helper extension
# Visit site and check for events
```

### Events Not Showing Revenue

**Check**:
1. Order contains products
2. Product has valid price
3. Order status is "processing" or "completed"
4. Currency is set correctly
5. Wait for events to be transmitted

## Related Documentation

- [Architecture Guide](architecture.md) - Plugin structure and configuration
- [Deployment Guide](deployment.md) - GA4/Pixel setup in production
- [Operations Runbook](ops-runbook.md) - Monitoring and troubleshooting
