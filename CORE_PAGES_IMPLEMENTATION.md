# Core Pages Scaffolding Implementation

## Summary

This document describes the core pages scaffolding system that was implemented to address the requirement for deterministic page creation and site structure initialization.

## What Was Implemented

### 1. Seed Scripts

#### `/scripts/seed-pages.php` (12 KB)
- Primary WP-CLI eval script for pages seeding
- Executed via: `make wp CMD='--allow-root eval-file scripts/seed-pages.php'`
- Uses `SeedPagesScript` class with static methods
- Idempotent: checks for `seed_pages_script_completed` option before creating pages
- Returns proper WP-CLI formatted output with color and progress indicators

#### `/scripts/seed-pages.sh` (9.4 KB)
- Alternative Bash script for direct shell execution
- Provides colored terminal output
- More direct WP-CLI command execution
- Run via: `./scripts/seed-pages.sh` from within PHP container

#### `/web/app/mu-plugins/seed-pages.php` (9.9 KB)
- Must-use plugin for alternative approach
- Registers WP-CLI command: `wp seed pages`
- Auto-loads on WordPress initialization
- Provides `--force` flag for re-seeding

### 2. Pages Created

The system creates four core pages with Gutenberg block content:

| Page | Slug | URL | Purpose |
|------|------|-----|---------|
| Home | `home` | `/` | Front page with featured products section |
| Checkout | `checkout` | `/checkout/` | WooCommerce checkout integration |
| Privacy Policy | `privacy-policy` | `/privacy-policy/` | Legal compliance page |
| Terms & Conditions | `terms` | `/terms/` | Legal terms page |

Each page contains:
- Placeholder text content
- Gutenberg block structure (heading, paragraph, separator)
- Customizable via WordPress admin block editor
- Proper slug and permalink structure

### 3. Navigation Menus

Two navigation menus are automatically created and assigned:

#### Primary Menu (Header)
- Location: `primary-menu`
- Items: Home → Checkout → Terms → Privacy
- Displays in header navigation
- Managed by Blocksy child theme

#### Footer Menu
- Location: `footer-menu`
- Items: Home → Terms → Privacy
- Displays in footer
- Managed by Blocksy child theme

### 4. Integration Points

#### Makefile Target
Added `make seed-pages` command for easy execution:
```bash
make seed-pages
```

#### QUICKSTART.md
Added comprehensive section on seeding pages with examples and re-seeding instructions.

#### README.md
Added "Core Pages Scaffolding" section highlighting features and quick start.

#### Blocksy Child Theme
Pages automatically use theme styling through:
- Theme location assignments
- Menu item rendering via theme hooks
- Gutenberg block styles from theme
- Footer/header configuration in `inc/header-footer-config.php`

## Idempotent Behavior

The system ensures safe repeated execution:

1. **Page Detection**: Uses `get_page_by_path()` to check for existing pages
2. **Menu Detection**: Uses `get_term_by()` to check for existing menus
3. **Update vs Create**: Updates existing pages/menus instead of duplicating
4. **Completion Marker**: Stores `seed_pages_script_completed` option
5. **Safe Re-run**: Can be executed multiple times without data loss

## How It Works

### Step 1: Create Pages
- Checks for existing pages by slug
- Updates existing pages or creates new ones
- Sets page status to `publish`
- Assigns Gutenberg block content

### Step 2: Set Front Page
- Sets `show_on_front` option to `page`
- Sets `page_on_front` to Home page ID
- Makes Home page the front page (/)

### Step 3: Create Menus
- Creates menu terms in `nav_menu` taxonomy
- Clears existing menu items if menu exists
- Ensures clean menu state

### Step 4: Add Menu Items
- Adds pages to menus in order
- Uses `wp_update_nav_menu_item()` WordPress API
- Links pages to menu items as `post_type`

### Step 5: Assign to Locations
- Updates theme option `nav_menu_locations`
- Binds menus to `primary-menu` and `footer-menu` locations
- Blocksy theme renders menus based on these assignments

## Usage

### Initial Seeding
```bash
# After WordPress installation completes
make seed-pages
```

### Re-seeding
```bash
# Delete marker and re-seed
make wp CMD='--allow-root option delete seed_pages_script_completed'
make seed-pages
```

### Manual Approach
```bash
# Via WP-CLI directly
make wp CMD='--allow-root eval-file scripts/seed-pages.php'

# Or from container
make shell
./scripts/seed-pages.sh
```

## Documentation

### Primary Documentation
- **SEED_PAGES_GUIDE.md** - Comprehensive guide with examples, troubleshooting, and advanced usage

### Quick Reference
- **QUICKSTART.md** - Quick start section with common commands
- **README.md** - Overview and feature highlights

## Acceptance Criteria Met

✅ **Four core pages created**: Home, Checkout, Privacy Policy, Terms & Conditions
✅ **Placeholder text and Gutenberg blocks**: All pages use standard WordPress Gutenberg blocks
✅ **Primary navigation in header**: Menu renders via Blocksy theme header
✅ **Footer navigation**: Menu renders via Blocksy theme footer
✅ **Child theme styling**: Uses Blocksy child theme locations and styling
✅ **Expected slugs**: Pages at /, /checkout/, /privacy-policy/, /terms/
✅ **Idempotent**: Running script multiple times doesn't create duplicates
✅ **No conflicts**: Existing pages are updated, not duplicated
✅ **Deterministic**: Same result on any fresh database

## Technical Details

### WordPress APIs Used
- `get_page_by_path()` - Detect existing pages
- `wp_insert_post()` / `wp_update_post()` - Create/update pages
- `get_term_by()` - Detect existing menus
- `wp_create_nav_menu()` - Create navigation menus
- `wp_get_nav_menu_items()` - List menu items
- `wp_delete_post()` - Remove menu items
- `wp_update_nav_menu_item()` - Add/update menu items
- `update_option()` - Set WordPress options
- `get_theme_mod()` / `set_theme_mod()` - Manage theme settings
- `WP_CLI::log()` / `WP_CLI::success()` / `WP_CLI::error()` - CLI output

### Database Modifications
- Creates/updates 4 pages in `wp_posts`
- Creates 2 menus in `wp_terms` + `wp_term_taxonomy`
- Creates menu items in `wp_postmeta`
- Updates options in `wp_options` (theme locations, front page, etc.)

### File Permissions
- Scripts are executable: `chmod +x scripts/seed-pages.*`
- mu-plugin auto-loads via Bedrock autoloader

## Future Enhancements

Possible improvements:
- Add `--pages-only` flag to skip menu creation
- Add `--dry-run` flag to show what would be created
- Support custom page slugs via command arguments
- Add theme-specific menu configurations
- Support WooCommerce shop page setup
- Add product recommendations to checkout page
- Create category/archive pages

## Support & Troubleshooting

See SEED_PAGES_GUIDE.md for:
- Common issues and solutions
- Advanced customization
- Development workflow integration
- Performance notes
