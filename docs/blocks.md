# Custom Gutenberg Blocks

Complete guide to the custom Gutenberg blocks used on the landing page.

## Overview

Six custom Gutenberg blocks are available for building landing pages:

1. **Hero Offer** - Eye-catching hero section with headline and CTA
2. **Social Proof Strip** - Display trust signals and social proof
3. **Value Stack** - Showcase value propositions in a grid
4. **How It Works** - Step-by-step process guide
5. **Final CTA** - Call-to-action section with dual buttons
6. **Local Neighborhoods** - Display service areas or locations

## Block Build Pipeline

### Building Blocks

```bash
# Development build with watch mode
npm run start

# Production build (minified)
npm run build
```

**Configuration**:
- **Webpack Config**: `webpack.config.cjs` (CommonJS for ES modules compatibility)
- **Output**: `web/app/themes/blocksy-child/build/`
- **Format**: Minified JS, CSS, RTL variants, asset manifests

### Block Structure

Each block follows this structure:

```
blocks/hero-offer/
├── block.json          # Block metadata and configuration
├── index.js            # React component and block registration
├── style.css           # Frontend and editor styles
└── editor.css          # Editor-specific styles
```

## Block Details

### 1. Hero Offer

Eye-catching hero section with headline, subheadline, description, and CTA button.

**File**: `web/app/themes/blocksy-child/blocks/hero-offer/`

**Attributes**:
- `headline` - Main headline (editable)
- `subheadline` - Secondary headline (editable)
- `description` - Supporting text (editable)
- `buttonText` - CTA button label (editable)
- `buttonUrl` - CTA button link (editable)
- `backgroundColor` - Section background color
- `textColor` - Text color

**Editor Features**:
- RichText editing for all text attributes
- URL picker for button link
- Color palette selector
- Live preview

**Frontend Styling**:
- Full-width section
- Blue gradient background (default)
- White text with proper contrast
- Centered content
- Orange CTA button
- Responsive typography (clamp)
- Mobile: Single column, reduced padding

**Example**:
```
[Hero Offer: "Sleep Better, Feel Better"]
Subheading: "Premium mattresses designed for NYC
Description: High-quality mattresses with free NYC delivery
Button: "Order Now" → /checkout/
```

### 2. Social Proof Strip

Display trust signals like customer count, ratings, and benefits.

**File**: `web/app/themes/blocksy-child/blocks/social-proof-strip/`

**Attributes**:
- `items[]` - Array of proof items:
  - `id` - Unique identifier
  - `text` - Proof statement (editable)
  - `icon` - Icon identifier (emoji or icon class)

**Editor Features**:
- Add/remove proof items with buttons
- Inline text editing
- Icon selector
- Live preview of grid layout

**Frontend Styling**:
- Light gray background
- Horizontal grid layout
- Responsive: Mobile (2 cols) → Tablet (3 cols) → Desktop (4+ cols)
- Subtle borders around items
- Large metrics text (1.875rem)
- Secondary label text

**Example**:
```
✓ 10,000+ Happy Customers
✓ 4.9 Star Rating
✓ Free NYC Delivery
✓ 30-Day Money Back
```

### 3. Value Stack

Showcase key value propositions in a responsive grid.

**File**: `web/app/themes/blocksy-child/blocks/value-stack/`

**Attributes**:
- `title` - Section heading (editable)
- `items[]` - Array of value items:
  - `id` - Unique identifier
  - `title` - Item title (editable)
  - `description` - Item description (editable)

**Editor Features**:
- RichText editing for all text
- Add/remove value items
- Drag to reorder items
- Live grid preview

**Frontend Styling**:
- Card-based layout
- Hover effects:
  - Blue border appears
  - Subtle shadow increase
  - Lift animation (translateY)
- 3-column grid on desktop
- Mobile: 1 column, responsive padding
- Icon support (left-aligned)
- Focus-within states for accessibility

**Example**:
```
Value Stack: "Why Choose Us?"

Card 1: Quality
"Premium materials sourced from top manufacturers"

Card 2: Delivery
"Fast, free delivery to all NYC neighborhoods"

Card 3: Satisfaction
"30-day trial period with no questions asked"
```

### 4. How It Works

Step-by-step process guide with numbered steps and connectors.

**File**: `web/app/themes/blocksy-child/blocks/how-it-works/`

**Attributes**:
- `title` - Section heading (editable)
- `steps[]` - Array of process steps:
  - `id` - Unique identifier
  - `number` - Step number (auto-incremented, editable)
  - `title` - Step title (editable)
  - `description` - Step description (editable)

**Editor Features**:
- RichText editing
- Add/remove steps
- Drag to reorder
- Number auto-increments
- Live step visualization

**Frontend Styling**:
- Numbered circles (gradient background)
- Desktop: Horizontal connector lines between steps
- Mobile: Lines hidden, stacked layout
- 4-step process (customizable)
- Centered alignment
- Responsive spacing
- Numbers: 70px on desktop → 50px on mobile

**Example**:
```
How It Works:

① Browse
"Explore our collection of premium mattresses"

② Select
"Choose the perfect size and firmness for you"

③ Checkout
"Quick, secure checkout in under 2 minutes"

④ Delivery
"Delivered and set up in your bedroom today"
```

### 5. Final CTA

Bottom section with compelling headline and dual CTAs (primary + secondary).

**File**: `web/app/themes/blocksy-child/blocks/final-cta/`

**Attributes**:
- `headline` - Main headline (editable)
- `description` - Supporting text (editable)
- `primaryButtonText` - Primary button label (editable)
- `primaryButtonUrl` - Primary button link (editable)
- `secondaryButtonText` - Secondary button label (editable)
- `secondaryButtonUrl` - Secondary button link (editable)
- `backgroundColor` - Section background
- `textColor` - Text color

**Editor Features**:
- RichText editing for all text
- URL pickers for both buttons
- Color selection
- Live preview with both button styles

**Frontend Styling**:
- Full-width section
- Dark background with white text (default)
- Centered content
- Two button styles:
  - Primary: Solid orange button
  - Secondary: Outlined blue button
- Responsive button layout:
  - Desktop: Side-by-side
  - Mobile: Stacked, full-width
- Clear visual hierarchy

**Example**:
```
Final CTA: "Ready to Sleep Better?"
Description: "Join thousands of happy sleepers in NYC"

[Primary Button: "Order Now"] [Secondary Button: "Learn More"]
```

### 6. Local Neighborhoods

Display service areas or neighborhoods with descriptions.

**File**: `web/app/themes/blocksy-child/blocks/local-neighborhoods/`

**Attributes**:
- `title` - Section heading (editable)
- `description` - Section introduction text (editable)
- `neighborhoods[]` - Array of service areas:
  - `id` - Unique identifier
  - `name` - Area name (editable)
  - `description` - Service details (editable)

**Editor Features**:
- RichText editing
- Add/remove neighborhood items
- Drag to reorder
- Live preview of card layout

**Frontend Styling**:
- Cards with emoji/icon support
- Hover effects:
  - Blue border
  - Shadow increase
  - Lift animation
- 4-column grid on desktop
- Responsive: Mobile (1 col) → Tablet (2 cols) → Desktop (4 cols)
- Light gray background
- Icon: 2.5rem on desktop → 2rem on mobile

**Example**:
```
Local Neighborhoods: "We Serve All NYC"
"Delivering to boroughs and neighborhoods citywide"

Manhattan
"Premium delivery to Manhattan apartments and lofts"

Brooklyn
"Rapid delivery across all Brooklyn neighborhoods"

Queens
"Fast service to Queens including all areas"

Bronx
"Complete coverage of Bronx neighborhoods"
```

## Using Blocks in the Editor

### Inserting a Block

1. Open page/post editor
2. Click the "+" button or press "/" to open block inserter
3. Search for the block name (e.g., "Hero Offer")
4. Click to insert

Blocks appear under "Landing Page Blocks" category.

### Editing Block Content

Click on text or elements to edit inline:

```
- Click headline to edit
- Click button text to edit
- Click button URL to change link
- Use toolbar above content for formatting (bold, italic, link)
```

### Adding/Removing Items

For blocks with repeating items (e.g., Value Stack):

1. Use the "Add Item" button to add a new item
2. Edit the item content
3. Use the trash icon or "Remove Item" to delete

### Reordering Items

Blocks with multiple items support drag-and-drop:

1. Click and hold on item (or drag handle if present)
2. Drag to new position
3. Release to drop

## Block Registration

Blocks are registered in PHP via `inc/register-blocks.php`:

```php
register_block_type_from_metadata(
    __DIR__ . '/blocks/hero-offer/block.json',
    array(
        'render_callback' => 'blocksy_child_render_hero_offer_block',
    )
);
```

Features:
- Loads metadata from `block.json`
- Enqueues compiled scripts from `/build`
- Uses asset manifests for dependency management
- Automatic category registration

## Build Output

After `npm run build`:

```
web/app/themes/blocksy-child/build/
├── hero-offer/
│   ├── index.js          # Compiled block script
│   ├── style.css         # Block styles
│   ├── editor.css        # Editor styles
│   └── index.asset.php   # Dependencies and version
├── social-proof-strip/
├── value-stack/
├── how-it-works/
├── final-cta/
├── local-neighborhoods/
└── hero-offer_index.asset.php  # Shared assets
```

## Styling

### Design System Integration

All blocks use design tokens from `theme.json`:

- **Colors**: Primary blue, accent orange, status colors
- **Typography**: Responsive font sizes with clamp()
- **Spacing**: 8px-based spacing scale
- **Shadows**: Multi-level shadow system

### Responsive Design

Blocks are mobile-first:
- Mobile: 375px width, single column, small padding
- Tablet: 768px width, 2-3 columns
- Desktop: 1024px+, full multi-column layout

### Animations

Smooth animations for better UX:
- Hover effects: 0.2s ease transitions
- Focus indicators: Blue outline ring
- Button states: Color and opacity changes

## Example Home Page

The `create-home-page-blocks.php` mu-plugin automatically creates a home page with all blocks on first install:

1. **Hero Offer** - Blue gradient, white text, "Sleep Better" headline
2. **Social Proof Strip** - 4 trust signals
3. **Value Stack** - 3 value propositions
4. **How It Works** - 4-step process
5. **Local Neighborhoods** - 4 NYC boroughs
6. **Final CTA** - Dark background, dual buttons

This can be recreated or customized by:
1. Creating a new page
2. Adding blocks in desired order
3. Editing content
4. Setting as home page

## Performance

- **CSS-in-JS**: Minimal performance impact
- **Build Size**: ~15KB gzipped total
- **Lazy Loading**: Blocks load only when added to page
- **Asset Caching**: Built files cached by browsers
- **RTL Support**: Automatic RTL CSS generation

## Customization

### Creating New Blocks

To add a new block:

1. Create directory: `blocks/my-new-block/`
2. Create `block.json` with metadata
3. Create `index.js` with component
4. Create `style.css` and `editor.css`
5. Build: `npm run build`
6. Register in `inc/register-blocks.php`

### Modifying Existing Blocks

Edit files in `blocks/<block-name>/`:

1. Edit `index.js` to change structure or attributes
2. Edit `style.css` for frontend styles
3. Edit `editor.css` for editor appearance
4. Build: `npm run build`
5. Refresh page editor to see changes

## Related Documentation

- [Design System](design-system.md) - Design tokens and components
- [Architecture Guide](architecture.md) - Theme and plugin structure
- [Setup Guide](setup-local.md) - Local development environment
