# Blocksy Child Theme

A minimal skeleton child theme for Blocksy with essential theme features and deferred performance optimizations.

## Current Features (Skeleton Setup)

### Core Functionality
- Theme metadata and branding
- Parent/child style enqueuing
- Menu registration (Primary, Footer, Header CTA)
- Basic theme support (responsive embeds, custom spacing, etc.)
- theme.json configuration with color palette, typography scale, and spacing system
- Editor styles support
- Accessibility features (skip link)

### Theme Configuration
- **theme.json**: Complete color palette, typography scale, and spacing system
- **Minimal CSS**: Only essential styles, leveraging Blocksy parent theme
- **Menu Support**: Primary, footer, and header CTA menus

### Asset Structure
```
web/app/themes/blocksy-child/
├── assets/
│   ├── css/
│   │   ├── critical.css       # [Task 13] Inlined critical CSS
│   │   ├── main.css           # [Task 13] Non-critical CSS
│   │   └── editor-style.css   # Editor styles
│   └── js/
│       └── performance.js     # [Task 13] Performance optimizations
├── inc/
│   ├── critical-css.php       # [Task 13] Critical CSS handler
│   ├── font-preload.php       # [Task 13] Font preloading
│   ├── asset-optimization.php # [Task 13] Asset optimization
│   └── header-footer-config.php # [Task 13] Header/footer optimization
├── templates/
│   └── landing-page.php       # Landing page template
├── functions.php              # Main theme functions
├── theme.json                 # FSE configuration
└── style.css                  # Theme header & minimal styles
```

## Installation

1. Ensure Blocksy parent theme is installed (managed via Composer)
2. This child theme should be at `/web/app/themes/blocksy-child/`
3. Activate the child theme from WordPress admin
4. Navigate to Appearance > Menus to configure menus

### Activation via WP-CLI

```bash
wp theme activate blocksy-child
```

## Configuration

### Menus
The theme registers three menu locations:
- **Primary Menu**: Main navigation menu
- **Footer Menu**: Footer navigation links
- **Header CTA Menu**: Call-to-action links in header

## Performance Optimizations (Deferred to Task 13)

The following advanced performance optimization modules are currently **disabled** for the skeleton setup and will be re-enabled in **Task 13: Performance Optimization Pass**:

### Disabled Modules

1. **Critical CSS Pipeline** (`inc/critical-css.php`)
   - Inlines critical CSS in `<head>` for faster initial render
   - Loads non-critical CSS asynchronously

2. **Font Preloading** (`inc/font-preload.php`)
   - Preloads critical web fonts for faster text rendering
   - Configures font-display: swap for Google Fonts
   - DNS prefetch and preconnect optimization

3. **Asset Optimization** (`inc/asset-optimization.php`)
   - Defers non-critical JavaScript
   - Removes emoji detection scripts
   - Conditional asset loading based on page type
   - Query string removal from static resources
   - jQuery footer optimization
   - Native lazy loading for images

4. **Header/Footer Optimization** (`inc/header-footer-config.php`)
   - Advanced header/footer configuration
   - Navigation schema markup
   - Mobile menu optimization

5. **Web Vitals Monitoring** (`assets/js/performance.js`)
   - CLS, LCP, FID monitoring in development
   - Performance event tracking

### Disabled Functions in functions.php

The following functions are also disabled:
- `blocksy_child_customize_options()`: Blocksy option customization
- `blocksy_child_header_output()`: Viewport and theme-color meta tags
- `blocksy_child_clean_head()`: Removal of unnecessary header meta
- `blocksy_child_custom_header_footer()`: Custom header/footer logic
- `blocksy_child_optimize_queries()`: Archive query optimization
- `blocksy_child_rest_performance_headers()`: REST API performance headers
- `blocksy_child_schema_markup()`: Schema.org markup for SEO

## Re-enabling Performance Modules (Task 13)

To re-enable performance optimizations in Task 13:

### Step 1: Enable Module Includes
In `functions.php`, uncomment the require statements around line 138-142:

```php
require_once BLOCKSY_CHILD_DIR . '/inc/critical-css.php';
require_once BLOCKSY_CHILD_DIR . '/inc/font-preload.php';
require_once BLOCKSY_CHILD_DIR . '/inc/asset-optimization.php';
require_once BLOCKSY_CHILD_DIR . '/inc/header-footer-config.php';
```

### Step 2: Enable Performance Script
Uncomment the performance script enqueue around line 145-151:

```php
wp_enqueue_script(
    'blocksy-child-performance',
    BLOCKSY_CHILD_URI . '/assets/js/performance.js',
    [],
    BLOCKSY_CHILD_VERSION,
    true
);
```

### Step 3: Re-enable Functions
Uncomment the disabled functions referenced in the `Disabled Performance Functions` section of `functions.php`.

### Step 4: Verify Performance
- Run `composer install` to ensure all dependencies are resolved
- Activate theme if not already active
- Test on desktop and mobile
- Run Lighthouse audit to verify performance scores >90

## Lighthouse Performance Baseline

Target metrics for skeleton setup (with minimal assets):
- **Performance**: >90
- **Accessibility**: >90
- **Best Practices**: >90
- **SEO**: >90

## Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Progressive enhancement for older browsers

## Development

### Customization
- Add custom styles to `assets/css/main.css` (Task 13 activation required)
- Add custom JavaScript to `assets/js/performance.js` (Task 13 activation required)
- Extend functionality via hooks and filters in `functions.php`

### Available Hooks & Filters
- `wp_enqueue_scripts`: Load additional assets
- `wp_body_open`: Add content at start of body
- `body_class`: Filter body element classes
- `nav_menu_link_attributes`: Customize menu link attributes

## License
GNU General Public License v2 or later

## Credits
Built on the Blocksy theme framework by CreativeThemes
