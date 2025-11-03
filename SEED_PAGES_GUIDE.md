# Core Pages Seeding Guide

## Overview

The Pages Seeding system automatically creates a deterministic site structure with placeholder pages and navigation menus. This ensures consistent site setup across environments and allows quick recovery to a known state.

## What Gets Created

### Core Pages

The seeding process creates four fundamental pages with Gutenberg block content:

1. **Home** (`/`)
   - Front page of the site
   - Contains welcome message and featured products section
   - Set as the WordPress front page

2. **Checkout** (`/checkout/`)
   - WooCommerce checkout page
   - Includes checkout shortcode for transaction processing
   - Integration point for e-commerce functionality

3. **Privacy Policy** (`/privacy-policy/`)
   - Legal compliance page
   - Placeholder text structure ready for customization
   - Includes Introduction, Information Collection, and Contact sections

4. **Terms & Conditions** (`/terms/`)
   - Legal terms and conditions page
   - Placeholder content with standard structure
   - Includes Acceptance of Terms, Use License, and Disclaimer sections

### Navigation Menus

Two primary navigation menus are created and automatically assigned to their theme locations:

1. **Primary Menu** (Header)
   - Location: `primary-menu`
   - Items: Home → Checkout → Terms → Privacy
   - Displays in the header navigation

2. **Footer Menu** (Footer)
   - Location: `footer-menu`
   - Items: Home → Terms → Privacy
   - Displays in the footer

Both menus use the Blocksy child theme styling and are fully responsive.

## Running the Seeding Script

### Method 1: Using the Makefile (Recommended)

```bash
make seed-pages
```

This command runs the PHP seed script via WP-CLI in the Docker container.

### Method 2: Using WP-CLI Directly

```bash
make wp CMD='--allow-root eval-file scripts/seed-pages.php'
```

### Method 3: From Within the PHP Container

```bash
make shell
cd /app
wp --allow-root eval-file scripts/seed-pages.php
```

Or alternatively:

```bash
make shell
./scripts/seed-pages.sh
```

## Idempotent Behavior

The seeding script is **completely idempotent**, meaning:

- ✓ Running it multiple times produces the same result
- ✓ Existing pages are updated, not duplicated
- ✓ Existing menus are cleared and repopulated
- ✓ Safe to re-run after database changes

### How It Works

The script tracks completion via a WordPress option:
- **Option Key:** `seed_pages_script_completed`
- **Value:** Unix timestamp of when seeding completed

When you run the script again, it checks for this option:
- If found, it logs that pages are already seeded
- If not found, it proceeds with full seeding

## Re-seeding Pages

To refresh all pages with the latest template content:

```bash
# Delete the seeding marker
make wp CMD='--allow-root option delete seed_pages_script_completed'

# Re-run the seed script
make seed-pages
```

Or in one command:

```bash
make wp CMD='--allow-root option delete seed_pages_script_completed' && make seed-pages
```

## Page Content Structure

All pages use WordPress Gutenberg blocks for content, making them easy to edit via the block editor.

### Example: Home Page Structure

```html
<!-- wp:heading -->
<h1>Welcome to Our Store</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This is a placeholder home page...</p>
<!-- /wp:paragraph -->

<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":2} -->
<h2>Featured Products</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Your featured products will appear here...</p>
<!-- /wp:paragraph -->
```

All content uses standard WordPress blocks (heading, paragraph, separator) that work with any theme.

## Accessing Created Pages

### Front End URLs

After seeding, pages are immediately accessible:

| Page | URL |
|------|-----|
| Home | http://localhost:8080/ |
| Checkout | http://localhost:8080/checkout/ |
| Privacy | http://localhost:8080/privacy-policy/ |
| Terms | http://localhost:8080/terms/ |

### Admin URLs

Edit pages through the WordPress admin:

```
http://localhost:8080/wp-admin/
```

Navigate to **Pages** to edit content using the Gutenberg block editor.

## Customizing Seeded Content

### Option 1: Edit After Seeding

1. Login to WordPress admin
2. Navigate to Pages
3. Edit each page's content
4. Changes are saved immediately

### Option 2: Modify Script Before Seeding

Edit the content generation functions in `/scripts/seed-pages.php`:

- `get_home_content()` - Home page content
- `get_checkout_content()` - Checkout page content
- `get_privacy_content()` - Privacy policy content
- `get_terms_content()` - Terms page content

Changes to these functions will be used on the next re-seed.

### Option 3: Add Custom Blocks

Add more Gutenberg blocks to the seeded content:

```html
<!-- wp:image {"id":123} -->
<figure class="wp-block-image">
    <img src="..." alt=""/>
</figure>
<!-- /wp:image -->

<!-- wp:columns -->
<div class="wp-block-columns">
    <!-- wp:column -->
    <div class="wp-block-column">
        <!-- wp:paragraph -->
        <p>Left column content</p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->
</div>
<!-- /wp:columns -->
```

## Menu Configuration

The menus are automatically assigned to their theme locations in the Blocksy child theme:

**Registered Locations** (in `functions.php`):
- `primary-menu` - Primary Menu for header navigation
- `footer-menu` - Footer Menu for footer navigation
- `header-cta-menu` - Optional header CTA menu

**Assignment in Header** (`header-footer-config.php`):
- Primary Menu displays via `has_nav_menu('primary-menu')`
- Footer Menu displays via `has_nav_menu('footer-menu')`

### Customizing Menu Display

Edit menu display in the theme:

1. **Header Navigation:** `/web/app/themes/blocksy-child/inc/header-footer-config.php`
2. **Footer Navigation:** Same file, footer section
3. **Menu Templates:** `/web/app/themes/blocksy-child/templates/`

### Adding Menu Items Manually

After seeding, add more items via admin:

```bash
# Get menu ID
make wp CMD='--allow-root menu list --field=term_id'

# Get page ID
make wp CMD='--allow-root post list --post_type=page --field=ID'

# Add menu item
make wp CMD='--allow-root menu item add-post MENU_ID PAGE_ID'
```

## Database Tracking

Pages are stored as WordPress posts with:
- **Type:** `page`
- **Status:** `publish`
- **Content:** Gutenberg blocks (HTML comments)

Menus are stored as terms:
- **Taxonomy:** `nav_menu`
- **Meta:** Menu item relationships

Menu assignments are stored as theme options:
- **Option:** `nav_menu_locations`
- **Value:** Array of `location => menu_id` mappings

## Troubleshooting

### Issue: "Pages already seeded" message

**Cause:** Seeding marker exists

**Solution:**
```bash
make wp CMD='--allow-root option delete seed_pages_script_completed'
make seed-pages
```

### Issue: Pages not appearing in navigation

**Cause:** Menu not assigned to theme location

**Solution:**
```bash
# Check menu assignments
make wp CMD='--allow-root menu list --format=table'

# Re-assign menus
make seed-pages
```

### Issue: WooCommerce checkout shortcode not working

**Cause:** WooCommerce plugin not activated

**Solution:**
```bash
# Activate WooCommerce
make wp CMD='--allow-root plugin activate woocommerce'

# Re-seed pages
make seed-pages
```

### Issue: Pages show in backend but not frontend

**Cause:** Front page setting not correct

**Solution:**
```bash
# Check front page setting
make wp CMD='--allow-root option get page_on_front'

# Re-seed pages
make seed-pages
```

## Development Workflow

### Fresh Setup

```bash
# Full setup including page seeding
make bootstrap
make seed-pages
```

### After Database Reset

```bash
# If using a backup or fresh database
make seed-pages
```

### Theme Development

```bash
# Seed pages to have content to work with
make seed-pages

# Work on theme templates in /web/app/themes/blocksy-child/
# Pages will use seeded content with your new styling
```

### Content Customization

```bash
# Seed with placeholder content
make seed-pages

# Edit in WordPress admin
# http://localhost:8080/wp-admin/edit.php?post_type=page

# Content changes persist, even if you re-seed core structure
```

## WP-CLI Integration

The script uses WP-CLI commands internally:

```bash
# Create page
wp post create --post_title='Page Title' --post_type='page' --post_status='publish'

# Create menu
wp menu create 'Menu Name'

# Add menu item
wp menu item add-post MENU_ID PAGE_ID

# Assign menu to location
wp menu location assign MENU_ID location-name

# Check pages
wp post list --post_type=page --format=table
```

All commands can be used independently for advanced customization.

## Advanced: Manual Page Creation

If needed, create pages manually:

```bash
make wp CMD='--allow-root post create \
  --post_title="Custom Page" \
  --post_type=page \
  --post_status=publish \
  --post_content="<p>Custom content</p>"'
```

## Performance Notes

- Seeding typically completes in < 2 seconds
- All operations run in PHP container (no network overhead)
- Script uses prepared statements and WordPress APIs
- Idempotent design prevents duplicate data

## Further Customization

For more advanced page setups, see:

- `QUICKSTART.md` - Quick setup guide
- `WOOCOMMERCE_SETUP.md` - E-commerce configuration
- WordPress Gutenberg documentation: https://wordpress.org/support/article/wordpress-editor/
- Blocksy theme documentation: https://creativethemes.com/blocksy/

## Support

For issues or questions:

1. Check troubleshooting section above
2. Review logs: `make logs`
3. Verify WordPress is installed: `make wp CMD='--allow-root core is-installed'`
4. Check theme is active: `make wp CMD='--allow-root theme list'`
