# Multibrand Quick Reference

**TL;DR: Switch brands with a single environment variable. Deploy a new brand in <1 hour.**

## Quick Start

### Option 1: Change Brand in Local Development

```bash
# Edit .env
echo "WP_BRAND=nook-mattress-today" > .env

# Restart services to reload brand config
make restart

# Visit http://localhost:8080 - logo, colors, and copy have changed!
```

### Option 2: Deploy New Brand Repository

```bash
# 1. Clone repo
git clone <repo-url> new-brand-repo
cd new-brand-repo

# 2. Configure brand
echo "WP_BRAND=nook-mattress-today" >> .env

# 3. Bootstrap
make bootstrap

# 4. Seed content
make seed-brand-from-template

# 5. Deploy
# (follow docs/multibrand.md for DNS/SSL/Cloudflare setup)
```

## How It Works

```
Environment Variable (WP_BRAND)
         ↓
    config/brands.php (Brand Registry)
         ↓
    config/brands/{brand-id}.php (Brand Config)
         ↓
    Brand_Config Class (API)
         ↓
    Theme/Plugins Read Brand Settings
         ↓
    Visual Identity & Copy Change (No Code Changes!)
```

## Available Brands

| Brand ID | Display Name | Description |
|----------|--------------|-------------|
| `nook-dresser-today` | Nook Dresser Today | Furniture & dressers (DEFAULT) |
| `nook-mattress-today` | Nook Mattress Today | Mattresses & sleep solutions |

## Using Brand Configuration in Code

### In PHP (Plugins/Theme)

```php
<?php
// Get brand config
$brand = Brand_Config::get_brand();

// Get specific values
$primary_color = Brand_Config::get_color('primary');
$cta_text = Brand_Config::get_copy('cta_button');
$logo_url = Brand_Config::get('logo.url');
$phone = Brand_Config::get('contact.phone');

// Use in block registration
register_block_type('custom/hero', [
    'render_callback' => function() {
        $brand = Brand_Config::get_brand();
        return sprintf(
            '<div style="background-color: %s;"><img src="%s" /></div>',
            $brand['colors']['primary'],
            $brand['logo']['url']
        );
    }
]);
```

### In Templates (theme.json)

Colors defined in `config/brands/{brand}.php` are available in WordPress:

```json
{
  "settings": {
    "color": {
      "palette": [
        {
          "color": "var(--brand-primary)",
          "slug": "primary"
        }
      ]
    }
  }
}
```

## Brand Configuration File

Each brand has `config/brands/{brand-id}.php`:

```php
<?php
return array_merge(
    include __DIR__ . '/base.php',  // Inherit base settings
    [
        'id'              => 'nook-mattress-today',
        'display_name'    => 'Nook Mattress Today',
        
        // Override specific settings
        'colors'          => [
            'primary'     => '#FF5722',
            // ... other colors inherit from base
        ],
        
        'copy'            => [
            'cta_button'  => 'Shop Mattresses',
            // ... other copy inherits from base
        ],
        
        // ... other settings
    ]
);
```

## Adding a New Brand

### Step 1: Create Brand Config

```bash
# Create config/brands/your-brand-name.php
cp config/brands/nook-dresser-today.php config/brands/your-brand-name.php
```

Edit the file to customize:
- Brand ID & display name
- Logo & favicon URLs
- Color palette
- Copy & messaging
- Contact info
- Stripe keys
- Analytics IDs

### Step 2: Create/Update Assets

```bash
# Upload brand logos/favicons
web/app/themes/twentytwentyfour/assets/images/
├── your-brand-logo.svg
├── your-brand-favicon.ico
└── your-brand-og.png

# Update config to point to these images
# In config/brands/your-brand-name.php:
'logo' => [
    'url' => '/app/themes/twentytwentyfour/assets/images/your-brand-logo.svg',
],
```

### Step 3: Set Environment Variable

```bash
# In .env or CI/CD secrets
WP_BRAND='your-brand-name'
```

### Step 4: Test Brand Configuration

```bash
# Verify brand config loads
make wp CMD='--allow-root eval-file scripts/test-brand-config.php'

# Verify brand settings are accessible
make wp CMD='--allow-root eval "echo json_encode(Brand_Config::get_brand(), JSON_PRETTY_PRINT);"'
```

## Seed Content for New Brand

```bash
# Export current brand's landing page as template
make seed-brand-template

# Creates: scripts/brand-templates/{brand-id}-template.json

# For new brand instance, import template
# (Set WP_BRAND in .env first)
make seed-brand-from-template

# Creates branded pages:
# - Home page with brand name & colors
# - Features section with brand values
# - CTA section with brand accent color
# - Navigation menus
```

## CI/CD Deployment

### Deploy Specific Brand

In GitHub Actions:
1. Go to Actions → "Deploy Brand"
2. Run workflow
3. Select:
   - Brand: `nook-dresser-today` or `nook-mattress-today`
   - Environment: `staging` or `production`
   - Dry run: check for preview

The workflow:
- Validates brand configuration exists
- Runs code quality checks
- Builds frontend assets
- Deploys to target environment
- Sets `WP_BRAND` in production .env
- Clears caches

## Configuration Hierarchy

Values are resolved in this order:

```
1. Brand-specific config (config/brands/{brand-id}.php)
2. Base config (config/brands/base.php)
3. Environment variables (.env)
4. WordPress defaults
```

### Example: Getting Stripe Key

```php
// For nook-mattress-today
Brand_Config::get('stripe.public_key')

// Resolution:
// 1. Check nook-mattress-today.php → stripe.public_key
// 2. If not found, check base.php → stripe.public_key
// 3. If not found, return null
```

## Common Tasks

### View Current Brand

```bash
make wp CMD='--allow-root eval "echo Brand_Config::get_id();"'
```

### List All Brand Settings

```bash
# Comprehensive test with color output
make wp CMD='--allow-root eval-file scripts/test-brand-config.php'
```

### Get Specific Brand Value

```bash
# Logo URL
make wp CMD='--allow-root eval "echo Brand_Config::get('"'"'logo.url'"'"');"'

# CTA button text
make wp CMD='--allow-root eval "echo Brand_Config::get_copy('"'"'cta_button'"'"');"'

# Primary color
make wp CMD='--allow-root eval "echo Brand_Config::get_color('"'"'primary'"'"');"'
```

### Change Brand in Running Instance

```bash
# Edit .env
WP_BRAND='nook-mattress-today'

# Restart PHP container
docker compose restart php

# Verify
make wp CMD='--allow-root eval "echo Brand_Config::get_id();"'
```

## Accessing Brand Data in Custom Code

### In mu-plugins or plugins

```php
<?php
// Load brand config (automatically loaded in config/application.php)
$brand = Brand_Config::get_brand();

// Get values with dot notation
$color = Brand_Config::get('colors.primary');

// Get typed values
$logo = Brand_Config::get_logo();
$name = Brand_Config::get_name();
$id = Brand_Config::get_id();
```

### In theme functions.php

```php
<?php
// Apply filters for brand-specific customization
add_filter('wp_head', function() {
    $brand = Brand_Config::get_brand();
    echo '<meta name="theme-color" content="' . esc_attr($brand['colors']['primary']) . '">';
});
```

### In custom blocks

```php
<?php
register_block_type('custom/cta', [
    'render_callback' => function($attributes) {
        $brand = Brand_Config::get_brand();
        return sprintf(
            '<div style="background-color: %s;"><button>%s</button></div>',
            esc_attr($brand['colors']['primary']),
            esc_html($brand['copy']['cta_button'])
        );
    }
]);
```

## Complete Brand Configuration Properties

```php
[
    // Identity
    'id'              => string,
    'name'            => string,
    'display_name'    => string,
    'description'     => string,

    // URLs
    'domain'          => string,
    'home_url'        => string,

    // Assets
    'logo'            => ['url' => string, 'width' => int, 'height' => int, 'alt_text' => string],
    'favicon'         => ['url' => string],
    'og_image'        => ['url' => string, 'width' => int, 'height' => int],

    // Styling
    'colors'          => ['primary' => string, 'secondary' => string, ...],
    'typography'      => ['heading_font' => string, 'body_font' => string, ...],

    // Content
    'copy'            => ['cta_button' => string, 'view_products' => string, ...],
    'contact'         => ['email' => string, 'phone' => string, 'support_hours' => string],
    'social'          => ['facebook' => string, 'instagram' => string, ...],

    // Business Logic
    'seo'             => ['title_template' => string, 'meta_description' => string, ...],
    'stripe'          => ['public_key' => string, 'secret_key' => string],
    'woocommerce'     => ['currency' => string, ...],
    'analytics'       => ['ga4_measurement_id' => string, 'facebook_pixel_id' => string],
    'shipping'        => ['methods' => array, 'free_threshold' => int, 'service_areas' => array, ...],
]
```

## Troubleshooting

### Brand not changing after .env update

```bash
# Restart PHP container to reload .env
docker compose restart php

# Verify new brand loaded
make wp CMD='--allow-root eval "echo Brand_Config::get_id();"'
```

### Brand config file not found

```bash
# Check if file exists
ls -la config/brands/your-brand-name.php

# Verify syntax
php -l config/brands/your-brand-name.php
```

### Brand settings not accessible in theme

```bash
# Verify Brand_Config is loaded
make wp CMD='--allow-root eval "var_dump(class_exists('"'"'Brand_Config'"'"'));"'

# Check config/application.php includes brands.php
grep -n "require.*brands.php" config/application.php
```

---

**For complete setup instructions:** See [docs/multibrand.md](docs/multibrand.md)

**For adding a new brand:** See [docs/multibrand.md#setting-up-a-new-brand](docs/multibrand.md#setting-up-a-new-brand)
