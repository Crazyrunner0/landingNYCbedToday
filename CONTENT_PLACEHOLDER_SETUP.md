# Content Placeholder Setup Guide (Task 18)

## Overview

The home page has been populated with comprehensive placeholder content for all required sections including pricing, reviews, policies, and delivery timelines. All content is easily editable via the WordPress Gutenberg block editor without any hardcoded PHP templates.

## Running the Content Seed Script

### Initial Setup

```bash
make seed-pages
```

This creates the home page with all placeholder content and sets up navigation menus with anchor links.

### Re-seeding (Update Placeholders)

To refresh all pages with the latest template content:

```bash
make wp CMD='--allow-root option delete seed_pages_script_completed'
make seed-pages
```

The script is **completely idempotent** - running it multiple times produces the same result without duplicating blocks.

## Home Page Structure

The home page includes all required sections in a cohesive landing page layout:

### 1. Hero Section (Hero Offer Block)
**Purpose**: Eye-catching banner with main value proposition

**Editable Elements**:
- Headline: "Get Your Perfect Mattress Today"
- Subheadline: "Premium comfort starts here"
- Description: Full paragraph explaining the offering
- CTA Button: Text and link

**How to Edit**:
1. Navigate to WordPress Admin → Pages
2. Edit the Home page
3. Select the "Hero Offer" block (blue section at top)
4. Edit text fields in the block sidebar or directly in the editor
5. Change colors using the Color Palette controls

**Anchor Link**: #shop (for CTAs to reference)

---

### 2. Social Proof Strip
**Purpose**: Display trust signals and customer counts

**Editable Elements**:
- "Trusted by 50,000+ customers"
- "4.9/5 stars average rating"
- "Free same-day delivery on select orders"

**How to Edit**:
1. Select the "Social Proof Strip" block (gray bar with metrics)
2. Click on individual items to edit text
3. Use "Add Proof Item" to add more trust signals
4. Use "Remove" buttons to delete items

**Current Content**:
- 3 placeholder trust signals
- Easily expandable for more metrics

---

### 3. Pricing Packages
**Purpose**: Display product packages with features and pricing

**Editable Elements**:
Pricing table with 4 package tiers:

| Package | Size | Price | Features |
|---------|------|-------|----------|
| Comfort Plus | Queen | $799 | Memory foam, 10-year warranty, free delivery |
| Luxury Pro | Queen | $1,299 | Hybrid construction, advanced cooling, 15-year warranty |
| Elite Choice | Queen | $1,799 | Premium materials, adjustable support, 20-year warranty |
| Sleep Bliss | Queen | $499 | Gel-infused foam, 5-year warranty, 30-night trial |

**How to Edit**:
1. Scroll to "Premium Mattress Packages" section
2. Click on the table to select it
3. Double-click any cell to edit
4. Right-click table for options to add/remove rows
5. Add more size options or packages as needed

**Anchor Link**: #pricing

---

### 4. Value Stack (Why Choose Us)
**Purpose**: Highlight key value propositions

**Editable Elements**:
1. Premium Quality - Expert-selected mattresses from trusted brands
2. Fast Delivery - Same-day and next-day delivery options
3. 100% Satisfaction - 30-night sleep trial and hassle-free returns

**How to Edit**:
1. Select the "Value Stack" block (3-column layout with white cards)
2. Edit item titles and descriptions
3. Add more value items with "Add Value Item" button
4. Remove items with delete button on each card

**Anchor Link**: #why-choose-us

---

### 5. Customer Reviews
**Purpose**: Showcase customer testimonials

**Editable Elements**:
Three sample customer reviews/testimonials:

1. "Best purchase I've made in years! The mattress arrived quickly and is incredibly comfortable. I've been sleeping better than ever." - Sarah M. - Manhattan

2. "The delivery team was professional and friendly. They set up the mattress perfectly. Highly recommend!" - James T. - Brooklyn

3. "Tried the 30-night trial and loved it from day one. Great quality at a reasonable price." - Maria G. - Queens

**How to Edit**:
1. Scroll to "Customer Reviews" section (3-column quote blocks)
2. Click on any quote block to select it
3. Edit the review text and customer attribution
4. Add more reviews by duplicating blocks and updating content

**Anchor Link**: #reviews

---

### 6. How It Works
**Purpose**: Step-by-step process guide

**Editable Elements**:
Four-step process:
1. Browse Selection - Explore our wide variety of premium mattresses online
2. Select & Compare - Compare features, prices, and customer reviews
3. Quick Checkout - Secure payment and schedule your delivery
4. Fast Delivery - Same-day or next-day delivery to your home

**How to Edit**:
1. Select the "How It Works" block (numbered steps)
2. Edit step numbers, titles, and descriptions
3. Add/remove steps using the block controls
4. Customize the process flow for your business

---

### 7. Delivery & Return Policies
**Purpose**: Communicate key policies clearly

**Editable Sections** (Three-column layout):

**Delivery**:
- Same-day delivery available for orders placed before 2 PM
- Next-day delivery for all service areas
- Free delivery on orders over $600
- White glove service available for premium packages
- Real-time tracking for all orders

**Returns & Warranties**:
- 30-night sleep trial on all mattresses
- Free returns within 30 days
- 5-20 year warranties depending on mattress model
- Lifetime customer support
- Hassle-free returns process

**Shipping Information**:
- Ships within 1-2 business days of order
- Insured for full replacement value
- Delivery times: 1-2 business days in NYC area
- International shipping available on select items
- Special handling for mattress delivery

**How to Edit**:
1. Scroll to "Our Policies" section
2. Click on each list to edit bullet points
3. Add or remove policies as needed
4. Each column is independently editable

**Anchor Link**: #policies

---

### 8. Local Neighborhoods (Service Areas)
**Purpose**: Show service coverage areas

**Editable Elements**:
Four NYC boroughs with service descriptions:

1. **Manhattan** - All neighborhoods with same-day delivery available
2. **Brooklyn** - All neighborhoods with fast next-day delivery
3. **Queens** - Select areas with next-day delivery service
4. **Bronx** - Select areas with flexible delivery options

**How to Edit**:
1. Select the "Local Neighborhoods" block (bottom section with light background)
2. Edit neighborhood names and service descriptions
3. Add or remove neighborhoods using the block controls
4. Update to match your actual service areas

---

### 9. Final Call-to-Action
**Purpose**: Convert visitors with final compelling offer

**Editable Elements**:
- Headline: "Ready to Find Your Perfect Mattress?"
- Description: "Join thousands of satisfied customers who have transformed their sleep..."
- Primary Button: "Shop Now" linking to #shop
- Secondary Button: "Learn More" linking to /privacy-policy/
- Colors: Dark blue background with white text

**How to Edit**:
1. Select the "Final CTA" block (dark section at bottom)
2. Edit headline and description text
3. Change button text and links
4. Modify colors using Color Palette controls

---

## Navigation & Anchor Links

### Header Navigation (Primary Menu)
- Home
- Why Choose Us (#why-choose-us)
- Pricing (#pricing)
- Reviews (#reviews)
- Checkout

### Footer Navigation (Footer Menu)
- Home
- Policies (#policies)
- Terms
- Privacy Policy

### Smooth Scrolling
All anchor links enable smooth scrolling to sections:
- Click "Pricing" in header → Scrolls to pricing table
- Click "Reviews" → Scrolls to customer reviews
- Click "Why Choose Us" → Scrolls to value proposition section
- Click "Policies" → Scrolls to policy information

### Adding More Anchors
To add custom anchor links:
1. Edit home page and add a heading where you want an anchor
2. In the heading block, set the Anchor field (Advanced panel)
3. Create menu item linking to that anchor
4. Add to Primary Menu or Footer Menu

---

## Content Editing Workflow

### Step-by-Step: Editing a Section

1. **Log in to WordPress**
   - Navigate to http://localhost:8080/wp-admin/

2. **Go to Pages**
   - Click "Pages" in the left sidebar
   - Click "Edit" on the Home page

3. **Locate Section**
   - Use the WordPress editor sidebar to find your section
   - Or scroll to the section in the main editor

4. **Select Block**
   - Click the section/block you want to edit
   - The block controls appear in the sidebar

5. **Edit Content**
   - Edit text directly in the editor
   - Use sidebar controls for colors, fonts, layouts
   - Preview changes in real-time

6. **Publish Changes**
   - Click "Update" button
   - Changes appear immediately on frontend

### Tips for Content Editors

- **RichText fields**: Support bold, italic, links, and formatting
- **Color controls**: Use color palette (defined in theme.json) for consistency
- **Block options**: Each block has its own controls in the sidebar
- **Responsive**: All content automatically adapts to mobile/tablet
- **Duplicate blocks**: Right-click block to duplicate for similar sections
- **Hide/show**: Use block visibility options to show/hide sections
- **Undo/Redo**: Use Ctrl+Z / Ctrl+Shift+Z for changes

---

## All-Editable Content (No Hardcoded PHP)

### ✅ Everything is Editable

All placeholder content resides in:
- Gutenberg block attributes (block.json)
- Page content (post_content in database)
- Menu items and links
- No hardcoded PHP strings to edit

### ✅ Block-Based Architecture

Each section uses dedicated Gutenberg blocks:
- Hero Offer block - Hero section
- Social Proof Strip block - Trust signals
- Value Stack block - Value propositions
- How It Works block - Process steps
- Local Neighborhoods block - Service areas
- Final CTA block - Bottom call-to-action
- Standard blocks - Pricing table, reviews, policies

### ✅ Easily Customizable

Customize by:
1. Editing block content in WordPress editor
2. Adjusting colors via color palette
3. Adding/removing items via block controls
4. Updating links in menu items
5. No code changes needed

---

## Database Storage

### Home Page Content
- **Page Title**: "Home"
- **Page Slug**: "home"
- **Post Type**: page
- **Status**: publish
- **Content Type**: Gutenberg blocks (stored as HTML comments)

### Navigation Menus
- **Primary Menu**: Header navigation with anchor links
- **Footer Menu**: Footer navigation with anchor links
- **Menu Locations**: 
  - primary-menu → Header
  - footer-menu → Footer

### Content Updates
When you edit the home page and click "Update":
- Content automatically saved to WordPress database
- Blocks validate content on save
- Changes appear immediately on frontend
- No cache clearing needed for block editor changes

---

## Placeholder Content Philosophy

The home page uses **meaningful placeholder content** that:

1. ✅ Represents real business value
2. ✅ Shows complete sections with realistic data
3. ✅ Uses contextual examples (NYC neighborhoods, mattress products)
4. ✅ Demonstrates all block features and layouts
5. ✅ Ready for immediate editing by non-technical admins
6. ✅ Maintains brand consistency throughout

---

## Common Customizations

### Update Pricing
1. Click the pricing table
2. Update price values
3. Add new product tiers by adding rows
4. Remove rows you don't need

### Add Customer Reviews
1. Duplicate a review block (right-click)
2. Update quote text
3. Change customer name and location
4. Repeat for each review

### Modify Service Areas
1. Edit the Local Neighborhoods block
2. Change neighborhood names
3. Update service descriptions
4. Add new neighborhoods

### Change Colors
1. Select any block
2. Look for color controls in sidebar
3. Click color to open palette
4. Choose from theme colors or custom color

### Update Links
1. Select text or button
2. Look for link control in toolbar
3. Update URL
4. Save

---

## Maintenance & Best Practices

### Regular Updates
- Review and update pricing quarterly
- Keep testimonials fresh and current
- Update service area coverage as it changes
- Monitor and update policies as needed

### Backup Content
- WordPress auto-saves drafts every few minutes
- Revision history available: Tools → Revisions
- Export pages: Tools → Export to backup

### Version Control
- Home page content tracked in Git via seed-pages.php
- All placeholder content visible in script for auditing
- Safe to re-run seeding script to reset to defaults

### Performance
- Blocks load efficiently with optimized CSS/JS
- Anchor links don't require page reload
- No external dependencies for blocks
- Smooth scrolling works on all modern browsers

---

## Anchor Navigation Reference

Quick reference for all anchor links on home page:

| Anchor | Section | Menu Item |
|--------|---------|-----------|
| #shop | Hero Offer CTA | Shop Now (CTA) |
| #why-choose-us | Why Choose Us | Why Choose Us |
| #pricing | Pricing Packages | Pricing |
| #reviews | Customer Reviews | Reviews |
| #policies | Delivery Policies | Policies |

Use these to create custom buttons or links to any section.

---

## Troubleshooting

### Content Not Appearing
1. Verify home page is set as front page: Settings → Reading
2. Publish/Update home page: Publish button
3. Clear browser cache: Hard refresh (Ctrl+Shift+R)

### Formatting Lost
1. Undo last change (Ctrl+Z)
2. Try editing in different browser
3. Check block is not locked

### Links Not Working
1. Verify anchor exists (heading with anchor set)
2. Check menu item URL is correct
3. Test with # prefix: #pricing

### Colors Not Showing
1. Verify theme colors are set: Appearance → Customize → Colors
2. Clear WordPress cache if using cache plugin
3. Try editing directly in block controls

---

## Support & Further Help

For questions about:
- **Block editing**: See WordPress Gutenberg documentation
- **Theme customization**: See Blocksy theme documentation
- **Re-seeding process**: See SEED_PAGES_GUIDE.md
- **Block development**: See GUTENBERG_BLOCKS.md

---

## Summary

✅ **Comprehensive placeholder content** for all landing page sections
✅ **No hardcoded PHP** - everything is editable via Gutenberg
✅ **Idempotent seeding** - safe to re-run without duplicates
✅ **Anchor navigation** - smooth scrolling between sections
✅ **Easy editing** - non-technical admins can update content
✅ **Fully documented** - inline comments guide content editors
✅ **Responsive** - works on all devices
✅ **SEO-friendly** - proper heading hierarchy and structure
