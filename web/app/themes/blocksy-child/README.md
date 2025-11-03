# Blocksy Child Theme

A high-performance child theme for Blocksy with optimized asset loading, critical CSS pipeline, and theme.json configuration.

## Features

### Performance Optimizations
- **Critical CSS Pipeline**: Inline critical CSS in `<head>` for faster initial render
- **Async CSS Loading**: Non-critical CSS loaded asynchronously
- **Font Preloading**: Preload critical web fonts for faster text rendering
- **Asset Optimization**: Deferred JavaScript, removed query strings, conditional loading
- **Lazy Loading**: Images lazy-loaded with native browser support
- **Web Vitals Monitoring**: Built-in Web Vitals tracking in development mode

### Theme Configuration
- **theme.json**: Complete color palette, typography scale, and spacing system
- **Minimal CSS**: Only essential styles, leveraging Blocksy parent theme
- **Custom Templates**: Optimized landing page template
- **Menu Support**: Primary, footer, and header CTA menus

### Asset Structure
```
web/app/themes/blocksy-child/
├── assets/
│   ├── css/
│   │   ├── critical.css    # Inlined critical CSS
│   │   ├── main.css         # Non-critical CSS (async loaded)
│   │   └── editor-style.css # Editor styles
│   └── js/
│       └── performance.js   # Performance optimizations
├── inc/
│   ├── critical-css.php     # Critical CSS handler
│   ├── font-preload.php     # Font preloading
│   ├── asset-optimization.php # Asset optimization
│   └── header-footer-config.php # Header/footer setup
├── templates/
│   └── landing-page.php     # Landing page template
├── functions.php            # Main theme functions
├── theme.json               # FSE configuration
└── style.css                # Theme header & minimal styles
```

## Installation

1. Install and activate the Blocksy parent theme
2. Ensure this child theme is in `/web/app/themes/blocksy-child/`
3. Activate the child theme from WordPress admin
4. Navigate to Appearance > Menus to configure menus

## Configuration

### Menus
The theme registers three menu locations:
- **Primary Menu**: Main navigation menu
- **Footer Menu**: Footer navigation links
- **Header CTA Menu**: Call-to-action links in header

### Landing Page Template
To use the landing page template:
1. Create/edit a page
2. Select "Landing Page" from the Template dropdown
3. Add content using blocks or custom fields

#### Custom Fields for Landing Page
- `cta_title`: CTA section title
- `cta_text`: CTA section description
- `cta_button_text`: CTA button label
- `cta_button_link`: CTA button URL

### Font Preloading
Edit `inc/font-preload.php` to add your custom fonts:
```php
$fonts_to_preload = [
    [
        'href' => get_stylesheet_directory_uri() . '/assets/fonts/your-font.woff2',
        'type' => 'font/woff2',
        'crossorigin' => 'anonymous'
    ]
];
```

### Theme Colors (theme.json)
- Primary: #2563eb
- Secondary: #64748b
- Accent: #f59e0b
- Base: #ffffff
- Contrast: #0f172a

### Typography Scale
- Extra Small: 0.75rem
- Small: 0.875rem
- Base: 1rem
- Medium: 1.125rem
- Large: 1.25rem
- Extra Large: 1.5rem
- 2XL: 2rem
- 3XL: 2.5rem
- 4XL: 3rem

## Performance Features

### Critical CSS
Critical CSS is automatically inlined in the `<head>` for faster initial paint. Non-critical CSS is loaded asynchronously.

### Asset Optimization
- Defers non-critical JavaScript
- Removes unused WordPress assets (emojis, block styles on non-block pages)
- Removes query strings from static resources
- Optimizes jQuery delivery (moved to footer)
- Adds performance and security headers

### Font Optimization
- Preconnects to Google Fonts domains
- Font-display: swap for Google Fonts
- Resource hints (dns-prefetch, preconnect)

### Image Optimization
- Native lazy loading for images
- Intersection Observer for advanced lazy loading
- Proper loading attributes (eager for hero images)

## Lighthouse Performance Goals

Target metrics for skeleton page:
- **Performance**: >90
- **Accessibility**: >90
- **Best Practices**: >90
- **SEO**: >90

## Development

### Web Vitals Monitoring
In development (localhost), the theme logs Web Vitals to console:
- CLS (Cumulative Layout Shift)
- LCP (Largest Contentful Paint)
- FID (First Input Delay)

### Customization
- Add custom styles to `assets/css/main.css`
- Add custom JavaScript to `assets/js/performance.js`
- Extend functionality via hooks and filters in `functions.php`

## Filters & Hooks

### Available Filters
- `blocksy_child_preload_fonts`: Modify fonts to preload
- `blocksy_child_skip_defer_scripts`: Scripts to skip deferring

### Available Actions
- `blocksy:header:after`: Runs after Blocksy header
- Custom body classes for template-specific styling

## Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Progressive enhancement for older browsers
- Graceful degradation with `<noscript>` fallbacks

## License
GNU General Public License v2 or later

## Credits
Built on the Blocksy theme framework by CreativeThemes
