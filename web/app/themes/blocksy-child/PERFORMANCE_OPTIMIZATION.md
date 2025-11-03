# Performance Optimization Guide (Task 13)

This document provides guidance on re-enabling performance optimization modules that are currently disabled in the skeleton setup.

## Overview

The following optimization modules were intentionally disabled to create a minimal skeleton setup:
- Critical CSS pipeline
- Font preloading  
- Asset optimization
- Header/footer configuration optimization
- Web Vitals monitoring

All code for these modules is preserved and fully functional. They are simply disabled via comments and disabled require statements.

## Re-enabling Performance Modules

### Phase 1: Enable Module Includes (functions.php)

Uncomment the require statements around line 138-142 in `functions.php`:

```php
require_once BLOCKSY_CHILD_DIR . '/inc/critical-css.php';
require_once BLOCKSY_CHILD_DIR . '/inc/font-preload.php';
require_once BLOCKSY_CHILD_DIR . '/inc/asset-optimization.php';
require_once BLOCKSY_CHILD_DIR . '/inc/header-footer-config.php';
```

### Phase 2: Enable Performance Script (functions.php)

Uncomment the performance script enqueue around line 145-151 in `functions.php`:

```php
wp_enqueue_script(
    'blocksy-child-performance',
    BLOCKSY_CHILD_URI . '/assets/js/performance.js',
    [],
    BLOCKSY_CHILD_VERSION,
    true
);
```

### Phase 3: Re-enable Performance Functions (functions.php)

The following functions are documented but not included in the skeleton setup. To re-enable, copy the original implementations from git history or the original source:

1. **blocksy_child_customize_options()**
   - Purpose: Customize Blocksy theme options
   - Hook: `blocksy:options:general` filter
   - Location: Original functions.php around line 102-106

2. **blocksy_child_header_output()**
   - Purpose: Add custom viewport and theme-color meta tags
   - Hook: `wp_head` action (priority 0)
   - Location: Original functions.php around line 125-132

3. **blocksy_child_clean_head()**
   - Purpose: Remove unnecessary WordPress header meta
   - Hook: `init` action
   - Location: Original functions.php around line 158-167
   - Removes: generator, wlwmanifest, rsd links, shortlinks, adjacent posts, REST links, oembeds

4. **blocksy_child_custom_header_footer()**
   - Purpose: Add custom header/footer logic
   - Hook: `blocksy:header:after` action
   - Location: Original functions.php around line 172-176

5. **blocksy_child_optimize_queries()**
   - Purpose: Optimize archive queries for better performance
   - Hook: `pre_get_posts` action
   - Location: Original functions.php around line 181-189
   - Effect: Limits posts per page to 12 on archives/home

6. **blocksy_child_rest_performance_headers()**
   - Purpose: Add performance headers to REST API responses
   - Hook: `rest_api_init` action
   - Location: Original functions.php around line 194-199
   - Effect: Sets Cache-Control: max-age=3600

7. **blocksy_child_schema_markup()**
   - Purpose: Add schema.org markup for SEO on homepage
   - Hook: `wp_footer` action (priority 99)
   - Location: Original functions.php around line 219-232
   - Effect: Adds WebSite schema for front page/home

## Module Details

### Critical CSS Pipeline (inc/critical-css.php)

**Functions:**
- `blocksy_child_inline_critical_css()`: Inlines CSS from `assets/css/critical.css`
- `blocksy_child_async_css()`: Loads `assets/css/main.css` asynchronously
- `blocksy_child_preload_critical_assets()`: Preloads critical CSS with high importance

**Configuration:**
- Edit `assets/css/critical.css` to include CSS needed for above-the-fold content
- Move remaining CSS to `assets/css/main.css`

### Font Preloading (inc/font-preload.php)

**Functions:**
- `blocksy_child_preload_fonts()`: Preloads fonts specified in filter
- `blocksy_child_optimize_google_fonts()`: Adds font-display: swap to Google Fonts
- `blocksy_child_font_preconnect()`: Preconnects to Google Fonts domains
- `blocksy_child_resource_hints()`: Adds DNS prefetch for font domains

**Configuration:**
- Filter `blocksy_child_preload_fonts` to add custom fonts
- Fonts should be WOFF2 format for best browser support
- Example configuration:

```php
add_filter('blocksy_child_preload_fonts', function($fonts) {
    $fonts[] = [
        'href' => get_stylesheet_directory_uri() . '/assets/fonts/inter-v12-latin-regular.woff2',
        'type' => 'font/woff2',
        'crossorigin' => 'anonymous'
    ];
    return $fonts;
});
```

### Asset Optimization (inc/asset-optimization.php)

**Functions:**
- `blocksy_child_defer_scripts()`: Adds defer attribute to non-critical scripts
- `blocksy_child_dequeue_unused_assets()`: Removes block library CSS on non-singular pages
- `blocksy_child_disable_emojis()`: Removes WordPress emoji detection
- `blocksy_child_remove_query_strings()`: Removes version query strings from static assets
- `blocksy_child_performance_headers()`: Adds security headers
- `blocksy_child_optimize_jquery()`: Moves jQuery to footer
- `blocksy_child_add_lazy_loading()`: Adds loading="lazy" to images
- `blocksy_child_conditional_assets()`: Removes unused assets on specific pages

**Configuration:**
- Filter `blocksy_child_skip_defer_scripts` to exclude scripts from deferring
- Customize lazy loading behavior via `the_content` and `post_thumbnail_html` filters
- Modify emoji removal if plugins require emoji support

### Header/Footer Optimization (inc/header-footer-config.php)

**Functions:**
- `blocksy_child_header_config()`: Customizes Blocksy header settings
- `blocksy_child_custom_header_elements()`: Adds custom header elements
- `blocksy_child_footer_config()`: Customizes Blocksy footer settings
- `blocksy_child_custom_footer_elements()`: Adds custom footer elements
- `blocksy_child_optimize_header_scripts()`: Page-specific header optimizations
- `blocksy_child_menu_description()`: Adds menu descriptions for accessibility
- `blocksy_child_navigation_schema()`: Adds JSON-LD navigation schema
- `blocksy_child_mobile_menu_config()`: Optimizes mobile menu performance

**Configuration:**
- Customize header/footer appearance via Blocksy settings
- Add custom schema markup for better SEO
- Optimize navigation schema for featured snippets

### Web Vitals Monitoring (assets/js/performance.js)

**Functions:**
- Performance monitoring for Core Web Vitals (CLS, LCP, FID)
- Intersection Observer for lazy-loaded images
- Preconnect optimization for external domains
- Automatic detection and logging in development environment

**Configuration:**
- Monitors CLS, LCP, FID in development (localhost/127.0.0.1)
- Can be extended to send metrics to analytics services
- Lazy loading handled via Intersection Observer with fallback

## Testing Performance After Re-enablement

After re-enabling all modules:

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Clear Cache**
   - WP-CLI: `wp cache flush`
   - WordPress Settings: Appearance > Customize > Publish to clear transients

3. **Run Lighthouse Audit**
   - Desktop: Open page in Chrome, DevTools > Lighthouse
   - Mobile: DevTools > Lighthouse (select Mobile device)
   - Target: Performance, Accessibility, Best Practices, SEO all >90

4. **Check Web Vitals**
   - Use Chrome DevTools > Performance tab
   - Monitor LCP, CLS, FID metrics
   - Compare before/after re-enablement

5. **Verify No Errors**
   - Check browser console for JS errors
   - Check WordPress debug log for PHP errors
   - Monitor server error logs

## Performance Targets

After full optimization:
- **Performance**: >90
- **Accessibility**: >90
- **Best Practices**: >90
- **SEO**: >90

## Rollback Instructions

If performance degrades after re-enablement:

1. Disable modules one at a time to identify issue
2. Check for plugin conflicts
3. Verify font files are loading correctly
4. Clear all caches and try again
5. Consider reverting to skeleton setup and re-enabling more carefully

## References

- Blocksy Theme: https://creativethemes.com/blocksy/
- Web Vitals: https://web.dev/vitals/
- Chrome Lighthouse: https://developers.google.com/web/tools/lighthouse
- WordPress Performance: https://wordpress.org/support/article/optimization/
