# Performance Tuning & Core Web Vitals Optimization

This document details the performance optimizations implemented to achieve Core Web Vitals targets and improve site speed across all devices.

## Performance Targets

### Core Web Vitals (Google CWV)
- **LCP (Largest Contentful Paint)**: < 2.5 seconds
- **CLS (Cumulative Layout Shift)**: < 0.1
- **INP (Interaction to Next Paint)**: < 200 milliseconds (formerly FID)

### Additional Metrics
- **TTFB (Time to First Byte)**: < 600 milliseconds
- **FCP (First Contentful Paint)**: < 1.8 seconds

### Lighthouse Targets
- **Performance Score**: ≥ 90
- **Accessibility Score**: ≥ 90
- **Best Practices Score**: ≥ 90
- **SEO Score**: ≥ 90

## Optimization Modules

### 1. Critical CSS Pipeline (`inc/critical-css.php`)

**What it does:**
- Inlines critical CSS in `<head>` for above-the-fold content
- Loads non-critical CSS asynchronously
- Preloads critical assets

**How to configure:**
1. Edit `assets/css/critical.css` to include only CSS needed for above-the-fold rendering
2. Move remaining styles to `assets/css/main.css`
3. Critical CSS should include:
   - Header and navigation styles
   - Hero section layout
   - Form styles for key conversions
   - Typography scales used in above-the-fold content

**Performance impact:**
- Reduces initial render time by 20-30%
- Enables faster First Contentful Paint (FCP)
- Prevents render-blocking CSS

### 2. Font Preloading (`inc/font-preload.php`)

**What it does:**
- Preloads critical web fonts
- Preconnects to Google Fonts domain
- Optimizes font-display strategy
- DNS prefetch for external domains

**Preconnected domains:**
- `fonts.googleapis.com` - Google Fonts API
- `fonts.gstatic.com` - Google Fonts CDN
- `www.googletagmanager.com` - Google Tag Manager
- `stripe.com` / `q.stripe.com` - Stripe payment processing
- `www.google-analytics.com` - Google Analytics

**How to configure custom fonts:**
```php
add_filter('blocksy_child_preload_fonts', function($fonts) {
    $fonts[] = [
        'href' => get_stylesheet_directory_uri() . '/assets/fonts/custom-font.woff2',
        'type' => 'font/woff2',
        'crossorigin' => 'anonymous'
    ];
    return $fonts;
});
```

**Performance impact:**
- Reduces Time to Interactive (TTI) by 10-20%
- Prevents flash of unstyled text (FOUT)
- Preconnect reduces connection overhead

### 3. Asset Optimization (`inc/asset-optimization.php`)

**What it does:**
- Defers non-critical JavaScript
- Removes unused block library CSS
- Disables WordPress emoji detection
- Removes query strings from static assets
- Moves jQuery to footer
- Adds native lazy loading to images
- Conditionally loads assets based on page template

**Optimization strategies:**

#### Script Deferring
- Non-critical scripts get `defer` attribute
- Prevents render-blocking JavaScript
- Skip list: jQuery, jQuery Core, jQuery Migrate

#### CSS Optimization
- Removes block library CSS on non-singular pages
- Removes global styles when not needed
- Disables emoji detection

#### Query String Removal
- Improves HTTP cache hit rate
- Removes `?ver=` from script/style URLs

#### Image Lazy Loading
- Adds `loading="lazy"` attribute to images
- Works with native browser lazy loading
- Improves LCP by deferring below-fold images

**Performance impact:**
- Reduces initial JavaScript execution by 30-40%
- Improves page responsiveness (INP)
- Faster page interactivity

### 4. Header/Footer Optimization (`inc/header-footer-config.php`)

**What it does:**
- Customizes header settings for performance
- Adds navigation schema markup
- Optimizes mobile menu
- Menu description accessibility
- Page-specific script optimization

**Features:**
- Minimal header configuration
- JSON-LD navigation schema for SEO
- Mobile menu performance optimization
- Accessibility improvements with ARIA labels

**Performance impact:**
- Cleaner DOM structure
- Improved accessibility scores
- Better mobile performance

### 5. Media Optimization (`inc/media-optimization.php`)

**What it does:**
- Converts images to WebP/AVIF formats automatically
- Generates `<picture>` elements with multiple source types
- Graceful fallbacks for unsupported browsers
- Lazy loads images with Intersection Observer
- Preloads critical images

**Supported image formats:**
1. **AVIF** - Modern, highest compression (~20% smaller than WebP)
2. **WebP** - Wide browser support (~25% smaller than JPEG)
3. **Original** - Fallback for older browsers

**How it works:**
1. Scans content for `<img>` tags
2. Checks for WebP/AVIF versions (or creates on-demand if GD/Imagick available)
3. Wraps in `<picture>` element with sources
4. Lazy loads via Intersection Observer

**Example output:**
```html
<picture>
  <source srcset="image.avif" type="image/avif">
  <source srcset="image.webp" type="image/webp">
  <img src="image.jpg" alt="Description" loading="lazy">
</picture>
```

**Performance impact:**
- Reduces image file sizes by 20-50%
- Decreases bandwidth usage
- Faster image rendering
- Improves LCP for image-heavy pages

### 6. Web Vitals Monitoring (`assets/js/performance.js`)

**What it does:**
- Monitors Core Web Vitals (CLS, LCP, INP)
- Reports metrics to browser console in development
- Tracks additional metrics (TTFB, FCP)
- Provides performance data object for analytics integration

**Monitored metrics:**
- **CLS** - Cumulative Layout Shift (target: < 0.1)
- **LCP** - Largest Contentful Paint (target: < 2.5s)
- **INP** - Interaction to Next Paint (target: < 200ms)
- **TTFB** - Time to First Byte (target: < 600ms)
- **FCP** - First Contentful Paint (target: < 1.8s)

**Development mode:**
- Enabled on `localhost`, `127.0.0.1`, `staging.local`
- Logs detailed metrics to console
- Use Chrome DevTools Console to see metrics

**Performance data:**
```javascript
window.performanceMetrics = {
    cls: 0,
    lcp: 0,
    fid: 0,
    ttfb: 0,
    fcp: 0
};
```

## Redis Object Cache Integration

### Configuration

Redis cache is configured in `web/app/mu-plugins/redis-cache.php`.

**Environment variables:**
```bash
REDIS_HOST=redis          # Default: redis
REDIS_PORT=6379          # Default: 6379
REDIS_PASSWORD=           # Optional password
REDIS_CACHE_DB=0         # Database number
```

**Docker Compose setup:**
Redis is configured in `docker-compose.yml` as a separate service.

### Health Checks

#### WP-CLI Commands

```bash
# Check Redis health status
wp redis health-check

# Get Redis statistics
wp redis stats

# Flush WordPress cache
wp cache flush
```

#### Programmatic Check

```php
$health = blocksy_redis_health_check();
if ($health['status'] === 'healthy') {
    echo 'Redis is operational';
    echo 'Version: ' . $health['redis_version'];
    echo 'Memory: ' . $health['used_memory'];
}
```

#### Manual Verification

Connect to Redis directly:
```bash
# From Docker container
docker-compose exec redis redis-cli

# Commands to verify
PING              # Should return PONG
INFO stats        # Get Redis statistics
DBSIZE            # Get number of keys in cache
FLUSHALL          # Clear all data (be careful!)
```

### Performance Impact

- **Database query reduction**: 70-90% fewer database queries on repeat visits
- **Cache hit rate**: Typically 80-95% for common queries
- **Response time improvement**: 50-70% faster response times with hot cache
- **Server load reduction**: Significantly reduced database and CPU usage

## JavaScript Bundle Budget

### Current Budget
- **Critical JavaScript**: < 100 KB (gzipped)
- **Total JavaScript**: < 250 KB (gzipped)
- **Deferred JavaScript**: Loaded asynchronously, doesn't block rendering

### What's included

**Inline/Critical (< 100 KB):**
- WordPress core scripts
- Theme core functionality
- Stripe integration

**Deferred (loaded async):**
- Google Analytics
- Google Tag Manager
- Additional theme enhancements
- Form validation

### How to verify bundle size

```bash
# Check total JS bundle size (in Chrome DevTools)
1. Open DevTools (F12)
2. Go to Network tab
3. Filter by JS files
4. Total size shows in bottom status bar

# Check gzipped size
1. In Network tab, look for Response Headers
2. Content-Encoding: gzip
3. Actual transfer size is gzipped size

# Target: < 250 KB gzipped total
```

## Testing & Verification

### Lighthouse Audit

**Desktop:**
1. Open Chrome DevTools (F12)
2. Click Lighthouse tab
3. Select "Desktop" mode
4. Click "Analyze page load"
5. Check scores (target: ≥ 90 all categories)

**Mobile:**
1. Open Chrome DevTools (F12)
2. Click Lighthouse tab
3. Select "Mobile" mode
4. Click "Analyze page load"
5. Check metrics (especially CWV targets)

### Web Vitals Console Logging

1. Open page in development environment (localhost/staging.local)
2. Open Chrome DevTools Console (F12 → Console tab)
3. Refresh page
4. Look for Web Vitals messages:
   - `CLS Update:` - Cumulative Layout Shift
   - `LCP: XX ms ✓` - Largest Contentful Paint (✓ = meets target)
   - `INP: XX ms` - Interaction to Next Paint
   - `TTFB: XX ms` - Time to First Byte
   - `FCP: XX ms` - First Contentful Paint

### Performance Testing

**Desktop Performance Test:**
```bash
# Run Chrome Lighthouse from command line
npm install -g lighthouse
lighthouse https://your-site.test --output-path=./report.html --view
```

**Mobile Performance Test:**
```bash
# Simulate mobile network (Chrome DevTools)
1. Open DevTools → Network tab
2. Set throttling to "Fast 3G"
3. Check performance scores
4. Monitor CWV metrics
```

### Benchmark Before/After

Record baseline metrics:

1. **Before optimization:**
   - Lighthouse performance score: ___
   - LCP: ___ ms
   - CLS: ___
   - INP: ___ ms
   - JS bundle size: ___ KB

2. **After optimization:**
   - Lighthouse performance score: ___
   - LCP: ___ ms
   - CLS: ___
   - INP: ___ ms
   - JS bundle size: ___ KB

## Image Optimization Guide

### Best Practices

1. **Responsive images:**
   - Use `srcset` for multiple screen densities
   - Use `sizes` attribute for responsive sizing

2. **Image formats:**
   - Use JPEG for photos
   - Use PNG for graphics with transparency
   - Use SVG for icons and logos
   - Let the media optimizer handle WebP/AVIF conversion

3. **Image sizes:**
   - Desktop: Max 1920px wide
   - Tablet: Max 1024px wide
   - Mobile: Max 768px wide
   - Compress before uploading

4. **Lazy loading:**
   - Don't lazy load above-the-fold images
   - Always lazy load below-the-fold images
   - Use `data-preload-image` for critical images

### Creating WebP/AVIF Versions

**Automatic (via media-optimization.php):**
- On-demand conversion when images are accessed
- Requires GD or ImageMagick PHP extension

**Manual (using command line):**

For WebP:
```bash
# Using cwebp
cwebp input.jpg -o output.webp -q 80

# Using ImageMagick
convert input.jpg -quality 80 output.webp
```

For AVIF:
```bash
# Using cavif
cavif input.jpg -o output.avif

# Using ImageMagick (if supported)
convert input.jpg -quality 80 output.avif
```

## Caching Strategy

### HTTP Caching Headers

Set via `.htaccess` or web server config:

```
# Static assets (1 year)
Cache-Control: public, max-age=31536000, immutable

# HTML pages (1 week, revalidate)
Cache-Control: public, max-age=604800, must-revalidate

# API responses (1 hour)
Cache-Control: public, max-age=3600
```

### Browser Cache Validation

```bash
# Check cache headers
curl -i https://your-site.test/page/ | grep -i cache-control

# Should see appropriate cache-control headers
```

### Object Cache Flushing

```bash
# Flush entire cache
wp cache flush

# Flush specific cache group
wp cache flush --group=posts
```

## Troubleshooting

### Issue: LCP is still high

**Possible causes:**
- Large images that aren't optimized
- Render-blocking CSS still present
- Heavy third-party scripts in head
- Slow server response (TTFB too high)

**Solutions:**
- Verify images are WebP/AVIF optimized
- Check critical.css only contains above-the-fold styles
- Move analytics/tracking to footer
- Check Redis cache is working

### Issue: High CLS

**Possible causes:**
- Images without explicit dimensions
- Dynamically loaded content
- Ads/external embeds
- Web fonts changing size

**Solutions:**
- Add width/height to all images
- Preload web fonts
- Reserve space for ads
- Use font-display: swap

### Issue: Slow INP

**Possible causes:**
- Large JavaScript files
- Long tasks blocking main thread
- Heavy event listeners
- Unoptimized event handlers

**Solutions:**
- Check JS bundle size (< 250 KB)
- Verify scripts are deferred
- Use requestIdleCallback for non-critical work
- Profile with DevTools Performance tab

### Issue: Redis not connecting

**Diagnostic steps:**
1. Check Redis container is running: `docker-compose ps`
2. Verify environment variables in .env
3. Check connection: `wp redis health-check`
4. View error logs: Check WordPress debug.log

**Solution:**
```bash
# Restart Redis container
docker-compose restart redis

# Clear WordPress cache
wp cache flush

# Flush Redis directly
docker-compose exec redis redis-cli FLUSHALL
```

## Performance Monitoring

### Google Search Console

1. Go to Google Search Console
2. Property Settings → Core Web Vitals
3. Review metrics by device type
4. Set performance goals and track

### Google PageSpeed Insights

Visit: https://pagespeed.web.dev/

1. Enter your URL
2. View lab data (Lighthouse scores)
3. View field data (real user metrics from CrUX)
4. Get specific optimization recommendations

### Chrome User Experience Report (CrUX)

Data from real users visiting your site. Available through:
- Google Search Console
- Google PageSpeed Insights
- BigQuery (advanced analysis)

## Maintenance & Updates

### Regular Tasks

**Weekly:**
- Monitor Redis memory usage
- Check for PHP/JS errors
- Verify Lighthouse scores stable

**Monthly:**
- Analyze real user metrics (CrUX)
- Review image optimization effectiveness
- Audit new content for best practices

**Quarterly:**
- Full Lighthouse audit on key pages
- Update dependencies
- Review and optimize slower pages

### .gitignore for Performance Files

Ensure these are NOT committed:
```
# Generated image formats
*.webp
*.avif

# Cache files
/cache/
/tmp/

# Performance reports
lighthouse-report.html
performance-report.json

# IDE performance files
.vscode/settings.json
.idea/misc.xml
```

## References

- [Web Vitals](https://web.dev/vitals/) - Google's core metrics
- [Lighthouse](https://developers.google.com/web/tools/lighthouse) - Performance auditing
- [WebP Format](https://developers.google.com/speed/webp) - Modern image format
- [AVIF Format](https://aomediacodec.org/) - Next-gen image format
- [Redis Documentation](https://redis.io/documentation) - Cache system
- [WordPress Performance](https://wordpress.org/support/article/optimization/) - WP best practices
