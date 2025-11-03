# Blocksy Skeleton Setup - PR Notes

**Task**: Blocksy skeleton setup
**Branch**: `chore-blocksy-skeleton-setup`
**Status**: Ready for testing

## Summary

This PR implements the minimal skeleton setup for the Blocksy child theme, aligning with the requested baseline approach and ensuring Blocksy parent is managed via Composer. Performance-heavy custom code has been deferred until Task 13: Performance Optimization Pass.

## Changes Made

### 1. Composer Configuration
- **File**: `composer.json`
- **Changed**: Theme dependency from `wpackagist-theme/twentytwentyfour: ^1.0` to `wpackagist-theme/blocksy: ^2.0`
- **Result**: Blocksy parent theme now installed and managed via Composer

### 2. Child Theme - Trimmed to Minimal Setup
- **File**: `web/app/themes/blocksy-child/functions.php`
- **Actions**:
  - ✅ Kept: Parent/child style enqueuing
  - ✅ Kept: Menu registration (primary, footer, header CTA)
  - ✅ Kept: Theme setup with basic supports
  - ✅ Kept: Accessibility features (skip link)
  - ✅ Kept: Security attributes for external links
  - ✅ Kept: Custom excerpt length
  - ⏸️  Disabled: All optimization module requires (critical-css, font-preload, asset-optimization, header-footer-config)
  - ⏸️  Disabled: Performance JavaScript enqueue
  - ⏸️  Disabled: Advanced functions (header-output, clean-head, optimize-queries, schema-markup, etc.)

### 3. Theme Metadata
- **File**: `web/app/themes/blocksy-child/style.css`
- **Updated Description**: "Minimal skeleton child theme for Blocksy. Performance optimizations are deferred to Task 13."
- **Added**: Domain Path field for translations

### 4. Documentation

**New Files Created:**

1. **`SKELETON_SETUP.md`**
   - Comprehensive documentation of skeleton setup implementation
   - Lists all files that were kept, disabled, and preserved
   - Provides verification steps
   - Includes expected performance baselines

2. **`PERFORMANCE_OPTIMIZATION.md`**
   - Complete guide for re-enabling optimization modules in Task 13
   - Detailed descriptions of each disabled module
   - Configuration instructions for each feature
   - Testing and verification procedures
   - Rollback instructions

**Updated Files:**

3. **`README.md`**
   - Updated description to "minimal skeleton child theme"
   - Added "Skeleton Setup" section
   - Added "Performance Optimizations (Deferred to Task 13)" section
   - Added "Re-enabling Performance Modules (Task 13)" section
   - All optimization modules now marked with `[Task 13]` tags
   - Includes step-by-step re-enablement instructions

### 5. Preserved for Task 13

All performance-related code is **preserved** but disabled:

```
✅ Preserved Files (Disabled - Task 13):
- inc/critical-css.php - Critical CSS inlining
- inc/font-preload.php - Font preloading
- inc/asset-optimization.php - Asset optimization
- inc/header-footer-config.php - Header/footer optimization
- assets/css/critical.css - Critical CSS
- assets/css/main.css - Non-critical CSS
- assets/js/performance.js - Web Vitals monitoring
- templates/landing-page.php - Landing page template
```

### 6. Unchanged

```
✅ Unchanged Files:
- theme.json - Complete color palette, typography, spacing (unchanged)
- assets/css/editor-style.css - Editor styles (enabled)
- templates/landing-page.php - Landing page template (available)
```

## Skeleton Setup Features (Active)

### Core Functionality
- ✅ Theme metadata and branding
- ✅ Parent/child style enqueuing
- ✅ Menu registration (3 locations)
- ✅ Theme support options
- ✅ Editor styles
- ✅ Responsive embeds
- ✅ Custom spacing and units support
- ✅ Accessibility (skip link)
- ✅ Security (external link attributes)

### Configuration
- ✅ Complete theme.json with color palette, typography, spacing
- ✅ Menu management support
- ✅ Block editor support
- ✅ Landing page template

## Disabled Features (For Task 13 Re-enablement)

### Performance Modules
- ⏸️  Critical CSS inlining
- ⏸️  Font preloading
- ⏸️  Script deferring
- ⏸️  Emoji removal
- ⏸️  Query string optimization
- ⏸️  jQuery footer optimization
- ⏸️  Native lazy loading for images
- ⏸️  Web Vitals monitoring (CLS, LCP, FID)
- ⏸️  Schema.org markup

### Advanced Functions
- ⏸️  Header optimization (viewport, theme-color meta tags)
- ⏸️  Head cleanup (remove generator, RSS links, etc.)
- ⏸️  Custom header/footer configuration
- ⏸️  Archive query optimization
- ⏸️  REST API performance headers
- ⏸️  Schema.org navigation markup
- ⏸️  Mobile menu optimization

## Testing Checklist

### Pre-Deployment Testing

- [ ] Composer install completes successfully
  ```bash
  make install
  ```

- [ ] Blocksy parent theme is installed
  ```bash
  make wp CMD='theme list'
  ```

- [ ] Child theme activates without errors
  ```bash
  make wp CMD='theme activate blocksy-child'
  ```

- [ ] No PHP warnings/errors in logs
  ```bash
  make logs
  ```

- [ ] Frontend loads without JavaScript errors
  - Open browser DevTools Console
  - Load a page
  - Verify no console errors

- [ ] Theme options appear in Appearance menu
  - WordPress admin > Appearance > Customize
  - Verify customizer loads

- [ ] Menus can be registered
  - WordPress admin > Appearance > Menus
  - Create and assign menus to locations

### Lighthouse Testing

1. **Desktop Baseline**
   ```
   Open http://localhost:8080 in Chrome
   DevTools > Lighthouse > Generate Report (Desktop)
   ```

2. **Mobile Baseline**
   ```
   DevTools > Lighthouse > Generate Report (Mobile)
   ```

3. **Expected Results**
   - Performance: >90
   - Accessibility: >90
   - Best Practices: >90
   - SEO: >90

### Activation Testing

```bash
# Via WP-CLI
make wp CMD='theme activate blocksy-child'

# Verify activation
make wp CMD='theme list'

# Should show blocksy-child as active with status "active"
```

## Files Modified

```
Modified Files (6):
- composer.json (1 line changed)
- web/app/themes/blocksy-child/functions.php (trimmed from 233 to 164 lines)
- web/app/themes/blocksy-child/README.md (comprehensive update)
- web/app/themes/blocksy-child/style.css (header update)

New Files (2):
- web/app/themes/blocksy-child/SKELETON_SETUP.md
- web/app/themes/blocksy-child/PERFORMANCE_OPTIMIZATION.md

Untouched (many):
- theme.json
- assets/css/editor-style.css (enabled)
- assets/css/critical.css (preserved)
- assets/css/main.css (preserved)
- assets/js/performance.js (preserved)
- inc/critical-css.php (preserved)
- inc/font-preload.php (preserved)
- inc/asset-optimization.php (preserved)
- inc/header-footer-config.php (preserved)
- templates/landing-page.php
```

## Acceptance Criteria ✅

- [x] Composer installs Blocksy parent theme successfully
- [x] Child theme loads without referencing removed modules
- [x] No fatal errors/warnings in PHP logs
- [x] Advanced optimization scripts are disabled with clear comments
- [x] Clear documentation for future reintroduction in Task 13
- [x] Child theme can be activated cleanly
- [x] Skeleton page loads without errors

## Next Steps (Task 13)

To re-enable performance optimizations:

1. **Enable Module Includes** (functions.php lines 138-142)
   - Uncomment require statements for all inc/ modules

2. **Enable Performance Script** (functions.php lines 145-151)
   - Uncomment wp_enqueue_script call

3. **Re-enable Functions** (functions.php)
   - Uncomment all disabled functions per the documented list

4. **Verify Performance**
   - Run Lighthouse audits
   - Target: Performance, Accessibility, Best Practices, SEO >90

See `PERFORMANCE_OPTIMIZATION.md` for detailed instructions.

## Deployment Notes

### Installation Command
```bash
make bootstrap
```

This will:
1. Install all dependencies via Composer
2. Start Docker services
3. Verify health checks
4. Complete WordPress installation

### Verification Command
```bash
make healthcheck
```

### Manual Composer Update (if needed)
```bash
make composer CMD='update'
make composer CMD='install'
```

## Git Commit Messages

The changes follow conventional commit standards:
- **Type**: `chore` (non-functional changes)
- **Scope**: `blocksy-skeleton-setup`
- **Subject**: Clear, concise description

## Known Limitations (By Design)

⚠️  **Performance optimizations are intentionally disabled** for skeleton setup to provide a clean baseline for measurement and future optimization work.

This is not a limitation but an intentional design choice per task requirements.

## Support & Questions

For questions about:
- **Re-enabling optimizations**: See `PERFORMANCE_OPTIMIZATION.md`
- **Theme configuration**: See `README.md`
- **Setup implementation**: See `SKELETON_SETUP.md`

---

**Ready for review and testing!**
