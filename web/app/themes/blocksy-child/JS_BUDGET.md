# JavaScript Bundle Budget & Monitoring

This document details the JavaScript bundle size budget and strategies to keep performance targets met.

## Bundle Budget Overview

### Primary Targets
- **Total JS (gzipped)**: < 250 KB
- **Critical JS (gzipped)**: < 100 KB
- **Deferred JS (gzipped)**: < 150 KB

### Why These Limits?

On a typical 3G connection (1 Mbps):
- 100 KB Critical JS: ~0.8 seconds to download and parse
- 250 KB Total JS: ~2 seconds to download and parse
- Leaves headroom for other resources (CSS, fonts, images)

### CWV Impact

- **LCP** (Largest Contentful Paint): JS execution blocks rendering
  - Every 100 KB adds ~100-200ms to LCP
  - Goal: < 2.5s → Keep JS < 250 KB
  
- **INP** (Interaction to Next Paint): JS affects responsiveness
  - Larger bundles = longer main thread blocking
  - Goal: < 200ms → Minimize JS execution time
  
- **CLS** (Cumulative Layout Shift): Dynamic JS can cause layout shifts
  - Measure JS that modifies DOM on load

## Current Bundle Composition

### Critical Path (loaded immediately)

```
blocksy-parent-style.css       ~45 KB (uncompressed)
blocksy-child-style.css        ~2 KB (uncompressed)
inline-critical.css            ~8 KB (inlined)
blocksy-child-performance.js   ~5 KB (uncompressed)
───────────────────────────────────────────
Total Critical (uncompressed): ~60 KB
Total Critical (gzipped):      ~15 KB ✓
```

### Deferred (loaded async)

```
wp-embed.js                    ~2 KB
wp-api-fetch.js                ~4 KB
wp-block-library.js            ~40 KB
google-analytics.js            ~20 KB
google-tag-manager.js          ~15 KB
stripe.js                      ~30 KB (from Stripe CDN)
woocommerce.js                 ~25 KB
───────────────────────────────────────────
Total Deferred (uncompressed): ~136 KB
Total Deferred (gzipped):      ~40 KB ✓
```

### Total Bundle

```
Critical (gzipped):            ~15 KB
Deferred (gzipped):            ~40 KB
───────────────────────────────────────────
Total (gzipped):               ~55 KB ✓
```

**Status:** ✅ Well under budget (55 KB < 250 KB)

## Monitoring Bundle Size

### Chrome DevTools Method

1. **Open DevTools** (F12)
2. **Go to Network tab**
3. **Filter for JS files**: Ctrl+Shift+P → "filter" → type "js"
4. **Check file sizes:**
   - "Size" = uncompressed
   - "Transferred" = gzipped (what user downloads)
5. **Total shows at bottom**

### Command Line Method

```bash
# Navigate to project
cd /home/engine/project

# Check individual file size
stat -c%s web/app/themes/blocksy-child/assets/js/performance.js

# Convert to KB
stat -c%s web/app/themes/blocksy-child/assets/js/performance.js | awk '{print $1/1024 " KB"}'

# Check all enqueued JS
find web/app/themes/blocksy-child/assets/js -name "*.js" -exec stat -c%s {} + | awk '{sum+=$1} END {print sum/1024 " KB"}'
```

### Lighthouse Analysis

1. **Run Lighthouse** audit (Chrome DevTools → Lighthouse)
2. **Look for "Reduce JavaScript execution time"**
3. **Check "Unused JavaScript" warning** (if present)
4. **View JavaScript coverage:**
   - DevTools → More tools → Coverage
   - Filter for JS files
   - Shows what % of JS is actually used

### Analyze Bundle with webpack-bundle-analyzer

```bash
# If webpack is set up in project
webpack-bundle-analyzer dist/stats.json

# Shows visual breakdown of bundle composition
```

## Keeping Within Budget

### 1. Script Deferring

All non-critical scripts should have `defer` attribute:

```html
<!-- GOOD: Deferred, doesn't block rendering -->
<script src="analytics.js" defer></script>

<!-- BAD: Render-blocking -->
<script src="analytics.js"></script>

<!-- OK: Inline critical scripts only -->
<script>console.log('critical');</script>
```

**Configuration in `inc/asset-optimization.php`:**
```php
$skip_defer = apply_filters('blocksy_child_skip_defer_scripts', [
    'jquery',
    'jquery-core',
    'jquery-migrate'
]);
```

### 2. Script Dequeue Strategies

**Remove unused scripts:**

```php
// In inc/asset-optimization.php
wp_dequeue_style('wp-block-library');        // ~40 KB
wp_dequeue_style('wp-block-library-theme');  // ~5 KB
wp_dequeue_style('wc-block-style');          // ~10 KB
```

**Conditional loading:**

```php
// Only load on specific pages
if (is_front_page()) {
    wp_enqueue_script('homepage-banner');
}

// Remove on non-product pages
if (!is_product() && !is_shop()) {
    wp_dequeue_script('woocommerce-cart-fragments');
}
```

### 3. Third-Party Script Management

**Defer or async third-party scripts:**

```html
<!-- Google Analytics - Add defer -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_ID"></script>

<!-- Stripe - Load only on checkout -->
<?php if (is_checkout()) { ?>
    <script src="https://js.stripe.com/v3/" defer></script>
<?php } ?>

<!-- Facebook Pixel - Lazy load -->
<script>
    window.addEventListener('load', function() {
        var fbPixel = document.createElement('script');
        fbPixel.src = 'https://connect.facebook.net/...';
        fbPixel.async = true;
        document.head.appendChild(fbPixel);
    });
</script>
```

### 4. Code Splitting

Break large JavaScript into smaller pieces:

```php
// Instead of one large admin.js (50 KB)
wp_enqueue_script('admin-forms', get_template_directory_uri() . '/js/admin-forms.js');  // 10 KB
wp_enqueue_script('admin-tables', get_template_directory_uri() . '/js/admin-tables.js'); // 15 KB
// Load only when needed
```

### 5. Minification & Compression

Ensure all JS is minified:

```bash
# Check if file is minified
# Minified: has no spaces/newlines, uses short variable names
grep -c ";" wordpress.js | wc -l  # Many semicolons = likely minified

# Minify new files
uglifyjs input.js -o output.min.js -c -m

# Verify compression works
gzip --verbose output.min.js
```

### 6. Library Optimization

Choose lightweight alternatives:

| Instead of | Use | Size |
|-----------|-----|------|
| jQuery (85 KB) | Vanilla JS or Alpine.js | <15 KB |
| Lodash (70 KB) | Specific lodash modules | <5 KB |
| Moment.js (70 KB) | date-fns or dayjs | <5 KB |
| Bootstrap (50 KB) | Tailwind or Blocksy | <20 KB |

## Budget Breakdown by Page Type

### Homepage
```
Critical CSS:     8 KB (gzipped)
Performance JS:   5 KB (gzipped)
───────────────────────────────
Subtotal:        13 KB ✓
```

### Product Page
```
Critical CSS:     8 KB (gzipped)
Performance JS:   5 KB (gzipped)
WooCommerce JS:  25 KB (gzipped, deferred)
───────────────────────────────
Subtotal (critical): 13 KB ✓
Subtotal (total):    38 KB ✓
```

### Checkout Page
```
Critical CSS:     8 KB (gzipped)
Performance JS:   5 KB (gzipped)
WooCommerce JS:  25 KB (gzipped, deferred)
Stripe JS:       30 KB (from Stripe CDN)
───────────────────────────────
Subtotal (critical): 13 KB ✓
Subtotal (total):    68 KB ✓
```

## Exceeding Budget: Action Plan

### If Total Bundle > 250 KB

1. **Identify heavy scripts:**
```bash
# In DevTools Network tab, sort by Size
# Find largest files
```

2. **Audit for unused code:**
```bash
# DevTools → Coverage tab
# Identify scripts with <50% usage
```

3. **Defer non-critical:**
```php
// Move to defer or conditional load
wp_dequeue_script('heavy-lib');
add_action('wp_footer', 'load_heavy_lib_if_needed');
```

4. **Consider CDN only:**
```php
// Load only if user needs it
if (user_needs_feature()) {
    wp_enqueue_script('heavy-lib');
}
```

5. **Replace with lighter alternative:**
```php
// Instead of jQuery UI (70 KB)
// Use Alpine.js (15 KB) for interactivity
```

### If Critical JS > 100 KB

1. **Review performance.js**
   - Remove unnecessary monitoring
   - Defer non-critical metrics

2. **Inline only essential CSS**
   - Move advanced styles to main.css
   - Keep critical.css minimal

3. **Delay third-party initialization**
   - Load analytics after page interactive
   - Stripe can load on demand

## Monitoring Script Performance

### Runtime Performance

```javascript
// Measure script execution time
performance.mark('my-script-start');
// ... do work ...
performance.mark('my-script-end');
performance.measure('my-script', 'my-script-start', 'my-script-end');

// Log results
const measure = performance.getEntriesByName('my-script')[0];
console.log(`Script took ${measure.duration}ms`);
```

### Main Thread Blocking

Check if scripts are blocking user interaction:

1. **Open DevTools → Performance tab**
2. **Record page load** (click Record button)
3. **Look for yellow/red bars** in the timeline
4. **These indicate long tasks** blocking main thread
5. **Click to see which script** caused the block

### Long Tasks API

Automatically detect scripts blocking main thread:

```javascript
if ('PerformanceObserver' in window) {
    const observer = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            console.warn('Long task detected:', entry.duration, 'ms');
        }
    });
    observer.observe({entryTypes: ['longtask']});
}
```

## Best Practices

### ✅ DO

- ✅ Defer non-critical scripts
- ✅ Use async for analytics/tracking
- ✅ Lazy load heavy libraries
- ✅ Tree-shake unused code
- ✅ Minify and compress
- ✅ Monitor regularly
- ✅ Use code splitting
- ✅ Prefer native browser APIs

### ❌ DON'T

- ❌ Load heavy libraries on every page
- ❌ Put third-party scripts in critical path
- ❌ Use multiple jQuery versions
- ❌ Inline large scripts
- ❌ Forget about gzip compression
- ❌ Ignore long tasks
- ❌ Use blocking synchronous scripts
- ❌ Bundle unused polyfills

## Testing Checklist

Before deploying new JavaScript:

- [ ] Total bundle size < 250 KB (gzipped)
- [ ] Critical JS < 100 KB (gzipped)
- [ ] All non-critical scripts have `defer`
- [ ] Third-party scripts are async/deferred
- [ ] Lighthouse Performance > 90
- [ ] No JavaScript errors in console
- [ ] LCP < 2.5s
- [ ] INP < 200ms
- [ ] No unused JavaScript warnings
- [ ] Mobile performance tested

## References

- [JavaScript and Core Web Vitals](https://web.dev/vitals/)
- [Reduce JavaScript execution time](https://web.dev/reduce-javascript-execution-time/)
- [Unused JavaScript](https://web.dev/unused-javascript/)
- [Code splitting in webpack](https://webpack.js.org/guides/code-splitting/)
- [Performance budgets](https://web.dev/performance-budgets-101/)
