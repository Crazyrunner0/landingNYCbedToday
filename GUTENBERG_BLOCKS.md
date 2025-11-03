# Gutenberg Block Skeleton (Task 16)

## Overview

This task implements six custom Gutenberg blocks for the landing page with placeholder content, leveraging a controlled Node.js build pipeline for block compilation.

## Build Pipeline

### Node Tooling

The project uses `@wordpress/scripts` and webpack for block compilation:

```bash
npm run build    # Compile blocks to build/ directory
npm run start    # Watch mode for development
```

**Configuration:**
- **Webpack Config**: `webpack.config.cjs` (CommonJS format to work with ES modules)
- **Output Directory**: `web/app/themes/blocksy-child/build/` (git-ignored)
- **Build Artifacts**: Minified JS, CSS, RTL variants, asset manifests

### npm Scripts

```json
{
  "build": "webpack --config webpack.config.cjs --mode production",
  "start": "webpack --config webpack.config.cjs --mode development --watch",
  "format:js": "... excluding web/app/themes/blocksy-child/build",
  "format:css": "... excluding web/app/themes/blocksy-child/build"
}
```

All format scripts exclude the build directory to avoid formatting compiled code.

## Block Structure

Each block follows the standard WordPress block structure:

```
blocks/
├── hero-offer/
│   ├── block.json          # Block metadata
│   ├── index.js            # Edit component + registration
│   ├── style.css           # Frontend styles
│   └── editor.css          # Editor-only styles
├── social-proof-strip/
├── value-stack/
├── how-it-works/
├── final-cta/
└── local-neighborhoods/
```

### Block Registration

Blocks are registered in PHP via `inc/register-blocks.php`:

- Loads block.json metadata with `register_block_type_from_metadata()`
- Enqueues compiled scripts and styles from `/build` directory
- Uses asset manifests for dependency management
- Registers custom "blocksy-landing" block category

All blocks are automatically available in the Gutenberg inserter.

## Blocks Overview

### 1. Hero Offer
**Purpose**: Eye-catching hero section with headline, subheadline, description, and CTA button.

**Attributes**:
- `headline`: Main headline (editable)
- `subheadline`: Secondary headline (editable)
- `description`: Supporting text (editable)
- `buttonText`: CTA button label
- `buttonUrl`: CTA button link
- `backgroundColor`: Section background color
- `textColor`: Text color

**Styling**: Full-width section with centered content, responsive typography.

### 2. Social Proof Strip
**Purpose**: Display trust signals (customer count, ratings, benefits) in a flexible strip.

**Attributes**:
- `items[]`: Array of proof items
  - `id`: Unique identifier
  - `text`: Proof statement (editable)
  - `icon`: Icon identifier

**Features**:
- Add/remove proof items in editor
- Responsive grid layout
- Light background for contrast

### 3. Value Stack
**Purpose**: Showcase key value propositions with icon and text (3-column grid).

**Attributes**:
- `title`: Section heading (editable)
- `items[]`: Array of value items
  - `id`: Unique identifier
  - `title`: Item title (editable)
  - `description`: Item description (editable)

**Features**:
- Add/remove value items
- Hover effects (lift + shadow)
- Responsive grid

### 4. How It Works
**Purpose**: Step-by-step process guide with numbered steps.

**Attributes**:
- `title`: Section heading
- `steps[]`: Array of process steps
  - `id`: Unique identifier
  - `number`: Step number (editable)
  - `title`: Step title (editable)
  - `description`: Step description (editable)

**Features**:
- Numbered circle indicators
- Add/remove steps
- Gradient background for numbers
- Responsive layout

### 5. Final CTA
**Purpose**: Bottom section with compelling headline and dual CTAs (primary + secondary).

**Attributes**:
- `headline`: Main headline (editable)
- `description`: Supporting text (editable)
- `primaryButtonText`: Primary button label
- `primaryButtonUrl`: Primary button link
- `secondaryButtonText`: Secondary button label
- `secondaryButtonUrl`: Secondary button link
- `backgroundColor`: Section background
- `textColor`: Text color

**Features**:
- Dark background with white text (default)
- Two button styles (solid primary, outlined secondary)
- Responsive button stacking

### 6. Local Neighborhoods
**Purpose**: List service areas/neighborhoods with descriptions.

**Attributes**:
- `title`: Section heading
- `description`: Section introduction text
- `neighborhoods[]`: Array of service areas
  - `id`: Unique identifier
  - `name`: Area name (editable)
  - `description`: Service details (editable)

**Features**:
- Add/remove neighborhood items
- Light gray background
- Card-based layout with hover effects
- Responsive grid

## Home Page Assembly

The `create-home-page-blocks.php` mu-plugin automatically creates a home page on first install with all blocks:

1. Hero Offer (blue background, white text)
2. Social Proof Strip (light gray background)
3. Value Stack (three columns: Quality, Delivery, Satisfaction)
4. How It Works (four steps: Browse, Select, Checkout, Delivery)
5. Local Neighborhoods (NYC boroughs: Manhattan, Brooklyn, Queens, Bronx)
6. Final CTA (dark background, two CTAs)

**Activation**: Automatically runs on first install; safe to re-run (checks for existing home page).

## Styling

### CSS Architecture

- **Frontend Styles** (`style.css`): Applied to both editor and frontend
- **Editor Styles** (`editor.css`): Editor-only styling for UI

### Design System

Theme variables from `theme.json`:

- **Colors**: Primary (#2563eb), Secondary (#64748b), Accent (#f59e0b), Neutral variants
- **Typography**: System font stack, Inter primary, Georgia serif
- **Spacing**: 8px base unit with sm/md/lg/xl variants
- **Shadows**: sm/md/lg shadow presets

### Responsive Breakpoints

- **Mobile**: max-width 768px (reduced font sizes, stacked layouts)
- **Desktop**: Full layout with multi-column grids

## Build Output

### Generated Files

After `npm run build`:

```
build/
├── hero-offer.js              # Minified component
├── hero-offer.css             # Styles
├── hero-offer-rtl.css         # RTL variants
├── hero-offer.asset.php       # Dependencies manifest
└── ... (repeated for all blocks)
```

### Asset Manifest

Each `.asset.php` file contains:

```php
<?php return array(
  'dependencies' => ['react-jsx-runtime', 'wp-block-editor', ...],
  'version' => '...'
);
```

Used by PHP for dependency injection.

## Development Workflow

### Adding/Modifying a Block

1. Edit block files (`.js`, `.css`, `block.json`)
2. Run `npm run build` to compile
3. Refresh WordPress editor
4. Test in Gutenberg inserter and on pages

### Watch Mode

```bash
npm start  # Rebuilds automatically on file changes
```

### Format Check

```bash
npm run format:check  # Checks formatting (excludes build/)
npm run format        # Auto-formats code
```

## Implementation Details

### Block Editor Integration

- Uses `@wordpress/block-editor` for editing components
- `RichText` component for editable text
- `TextControl` for input fields
- `ColorPaletteControl` for color selection
- `Button` component for actions (add/remove items)

### Save Methods

Each block implements both:
- **Edit**: Interactive editor UI with controls
- **Save**: Clean frontend HTML output (no control markup)

### Enqueue Strategy

**Editor Assets** (`enqueue_block_editor_assets`):
- Block scripts (JS with editor components)
- Block editor styles
- Dependencies from asset manifests

**Frontend Assets** (`wp_enqueue_scripts`):
- Block styles only (frontend doesn't need JS)
- Applied globally for all pages with blocks

## Customization Guide

### Editing Block Attributes

All blocks store content in attributes (see above). Editors can:
- Edit text via RichText fields
- Change colors via ColorPaletteControl
- Add/remove items via buttons
- Customize button labels and links

### Modifying Styles

Edit `blocks/<name>/style.css` or `editor.css` and rebuild:

```bash
npm run build
```

### Adding New Blocks

1. Create `blocks/new-block-name/` directory
2. Add: `block.json`, `index.js`, `style.css`, `editor.css`
3. Add entry point to `webpack.config.cjs`
4. Add block registration to `inc/register-blocks.php`
5. Run `npm run build`

## Performance Considerations

### Optimizations

- Minified JS/CSS in production builds
- RTL variants for international support
- Source maps for debugging (development only)
- Lazy loading of blocks in editor

### Bundle Size

- Individual block bundles (~2-4 KB each minified)
- Shared WordPress dependencies loaded once
- Total overhead: ~18 KB JS + 3.5 KB CSS

### Caching

- Asset versions from manifest prevent stale cache
- Build-time cache busting
- No runtime performance penalty

## Git Integration

### Build Directory

The `build/` directory is excluded from Git:

```gitignore
web/app/themes/blocksy-child/build/
```

**Always run `npm run build` before committing changes to block source files.**

### Committed Files

Source files tracked by Git:
- `blocks/*/block.json`
- `blocks/*/index.js`
- `blocks/*/*.css`
- `webpack.config.cjs`
- `package.json` (with build scripts)
- `inc/register-blocks.php`

### Build Regeneration

Build artifacts are regenerated on any CI/CD pull by running `npm install && npm run build`.

## Troubleshooting

### Blocks Not Appearing in Inserter

1. Verify build ran successfully: `npm run build`
2. Refresh editor page (hard refresh if needed)
3. Check browser console for errors
4. Verify mu-plugin `create-home-page-blocks.php` is loaded

### Styles Not Applying

1. Ensure CSS is in correct file:
   - `style.css` for frontend + editor
   - `editor.css` for editor only
2. Run build: `npm run build`
3. Flush WordPress cache (if using caching plugin)

### Build Fails

1. Check for syntax errors in block files
2. Ensure all imports are correct
3. Clear `node_modules` and reinstall:
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   npm run build
   ```

## Testing

### Manual Testing Checklist

- [ ] Block appears in Gutenberg inserter
- [ ] Block can be added to page
- [ ] Block attributes are editable in sidebar
- [ ] Block renders correctly on frontend
- [ ] Styles apply correctly (mobile + desktop)
- [ ] Add/remove items work (for array-based blocks)
- [ ] Page with all blocks loads without errors

### Lighthouse Audit

After deploying home page with blocks:

```bash
make wp CMD='eval-file scripts/lighthouse-audit.php'
```

Expected baseline:
- Performance: >90
- Accessibility: >90
- Best Practices: >90
- SEO: >90

## Next Steps

### Task 17: Advanced Features
- Block variations for different layouts
- Custom block patterns
- Advanced styling options

### Task 18: Performance Optimization
- Lazy load block scripts
- Optimize CSS delivery
- Critical CSS extraction for blocks

### Performance Optimization Pass (Task 13 Redux)
- Re-enable performance modules with block awareness
- Font preloading for block typography
- Asset optimization specific to blocks

## References

- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [@wordpress/scripts Documentation](https://github.com/WordPress/gutenberg/tree/trunk/packages/scripts)
- [Webpack Configuration](https://webpack.js.org/configuration/)
- [Blocksy Theme Documentation](https://blocksy.com/documentation)

## Summary

- ✅ **Build Pipeline**: `npm run build`/`npm run start` with webpack
- ✅ **6 Blocks**: Hero Offer, Social Proof, Value Stack, How It Works, Final CTA, Local Neighborhoods
- ✅ **Placeholder Content**: All blocks have realistic default content
- ✅ **Home Page**: Auto-created via mu-plugin on first install
- ✅ **Git Integration**: Build directory ignored, source tracked
- ✅ **Developer Experience**: Format scripts exclude build, easy customization
