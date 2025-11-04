# Multibrand Rollout Guide

Complete step-by-step instructions for cloning and deploying sibling brands (NookDresserToday, NookMattressToday) from a single codebase.

**Table of Contents**
- [Quick Start](#quick-start)
- [Brand Architecture](#brand-architecture)
- [Setting Up a New Brand](#setting-up-a-new-brand)
- [DNS & Domain Configuration](#dns--domain-configuration)
- [Cloudflare Setup](#cloudflare-setup)
- [SSL/TLS Configuration](#ssltls-configuration)
- [Stripe Integration](#stripe-integration)
- [Content & Templates](#content--templates)
- [CI/CD Deployment](#cicd-deployment)
- [Environment Configuration](#environment-configuration)
- [Brand-Specific Settings](#brand-specific-settings)
- [Testing & Validation](#testing--validation)
- [Rollout Checklist](#rollout-checklist)

## Quick Start

To deploy a new brand in under 1 hour:

```bash
# 1. Clone repository with BRAND environment variable
git clone <repo-url> nook-dresser-today-prod
cd nook-dresser-today-prod

# 2. Create .env from example
cp .env.example .env

# 3. Set brand (only change needed!)
echo "WP_BRAND=nook-dresser-today" >> .env

# 4. Install and setup
make bootstrap

# 5. Seed content from template
make seed-pages

# 6. Configure Cloudflare & DNS (see sections below)
# 7. Deploy to production
make deploy
```

**Result:** Full brand instance live in ~45 minutes, DNS propagation + 15 minutes.

## Brand Architecture

### Configuration Structure

```
config/
├── brands.php                        # Brand registry & loader
├── brands/
│   ├── base.php                      # Base template (inherited by all)
│   ├── nook-dresser-today.php       # Dresser brand config
│   └── nook-mattress-today.php      # Mattress brand config
```

### How Brand Selection Works

1. **Environment Variable Priority:**
   ```bash
   # Check in order:
   # 1. WP_BRAND constant (defined in config/application.php)
   # 2. WP_BRAND env variable (.env file)
   # 3. Default: 'nook-dresser-today'
   ```

2. **Configuration Loading:**
   ```php
   // In config/application.php
   define('WP_BRAND', env('WP_BRAND') ?: 'nook-dresser-today');
   
   // config/brands.php loads the brand config
   Brand_Config::get_brand();           // Get full config
   Brand_Config::get('colors.primary'); // Get specific setting
   ```

3. **Accessing Brand Data in Theme/Plugins:**
   ```php
   // Get brand configuration
   $brand = Brand_Config::get_brand();
   
   // Get specific settings
   $logo_url = Brand_Config::get('logo.url');
   $primary_color = Brand_Config::get_color('primary');
   $cta_text = Brand_Config::get_copy('cta_button');
   ```

## Setting Up a New Brand

### Step 1: Create Brand Configuration

Create `config/brands/your-brand-name.php`:

```php
<?php
return array_merge(
    include __DIR__ . '/base.php',
    [
        'id'           => 'your-brand-name',
        'name'         => 'Your Brand Name',
        'display_name' => 'Your Brand Display Name',
        
        // Override specific settings
        'colors'       => [
            'primary'   => '#FF5722',  // Your brand color
            // Other colors inherit from base
        ],
        
        'copy'         => [
            'cta_button' => 'Shop Now',
            // Other copy inherits from base
        ],
        
        // ... other overrides
    ]
);
```

### Step 2: Update Environment Variable

In `.env` (or CI/CD secrets):
```bash
WP_BRAND='your-brand-name'
```

### Step 3: Update Brand Assets

Upload brand-specific assets to theme:
```
web/app/themes/twentytwentyfour/assets/images/
├── your-brand-logo.svg
├── your-brand-favicon.ico
└── your-brand-og.png
```

Update `config/brands/your-brand-name.php` to reference these:
```php
'logo' => [
    'url' => '/app/themes/twentytwentyfour/assets/images/your-brand-logo.svg',
],
```

### Step 4: Seed Content

```bash
# Export current template
make wp CMD='--allow-root eval-file scripts/seed-brand-template.php'

# For new brand repo, import template
make wp CMD='--allow-root eval-file scripts/seed-brand-from-template.php'
```

## DNS & Domain Configuration

### Prerequisites

- Domain registrar access (Namecheap, GoDaddy, etc.)
- Cloudflare account (free tier works)
- Target server IP address

### Step 1: Point Domain to Cloudflare

1. **In Domain Registrar:**
   - Log in to your domain registrar
   - Find DNS/Nameserver settings
   - Replace nameservers with Cloudflare's:
     ```
     ns1.cloudflare.com
     ns2.cloudflare.com
     ```

2. **In Cloudflare:**
   - Add site to Cloudflare dashboard
   - Confirm nameserver setup (usually 5-10 minutes)

### Step 2: Configure DNS Records

In Cloudflare dashboard:

```
Type    Name                   Content                  TTL    Proxy
──────────────────────────────────────────────────────────────────────────
A       nookdressertoday.com   YOUR_SERVER_IP          Auto   Proxied (orange cloud)
CNAME   www                    nookdressertoday.com    Auto   Proxied
MX      @                      mail.provider.com       Auto   DNS only (gray cloud)
TXT     @                      v=spf1 include:...      Auto   DNS only
```

**Important:**
- Set A record to **Proxied** (orange cloud) for Cloudflare features
- Keep MX/TXT records as **DNS only** (gray cloud)

### Step 3: Verify DNS Propagation

```bash
# Check DNS propagation
dig nookdressertoday.com +short
nslookup nookdressertoday.com

# Should return your server IP within 5-30 minutes
```

## Cloudflare Setup

### Step 1: Basic Security

In Cloudflare Dashboard → Security:

1. **SSL/TLS:**
   - Mode: **Full (strict)**
   - Minimum TLS Version: **1.3**
   - Opportunistic Encryption: **On**

2. **DDoS Protection:**
   - Sensitivity Level: **Medium**
   - Enable: Challenge Passage (default)

3. **Bot Management:**
   - Verified Bots: **Challenge**
   - Super Bot Fight Mode: **Definitely Automated**

### Step 2: Performance Optimization

In Cloudflare Dashboard → Caching:

```
Setting                          Value
───────────────────────────────────────────────
Cache Level                      Cache Everything
Browser Cache TTL                30 minutes
Cache on Cookie                  wordpress_*
Compression                      On (Gzip + Brotli)
HTTP/2 Prioritization           On
HTTP/3 (QUIC)                   On
IPv6 Compatibility              On
Minify                          Enabled
  - Minify JavaScript           On
  - Minify CSS                  On
  - Minify HTML                 On
```

### Step 3: Firewall Rules

In Cloudflare Dashboard → Security → WAF Rules:

```
Rule: Block XML-RPC attacks
Condition: URI Path contains /xmlrpc.php
Action: Block

Rule: Rate limit login attempts
Condition: URI Path contains /wp-login.php
Action: Challenge
Rate: > 10 per minute
```

### Step 4: Page Rules (deprecated, use Rules)

In Cloudflare Dashboard → Rules → Page Rules:

```
https://nookdressertoday.com/wp-admin/*
  - Disable Performance Optimization
  - Disable Caching
  - TTL: Respect existing headers
```

## SSL/TLS Configuration

### Step 1: Issue Certificate

**Option A: Cloudflare Universal SSL (Recommended)**

Cloudflare automatically issues free SSL certificates:
- In Cloudflare Dashboard → SSL/TLS
- Certificate Status shows issued certificates
- Auto-renews 30 days before expiration
- **No action needed** - Cloudflare handles this

**Option B: Let's Encrypt (Self-managed)**

```bash
# SSH into server
ssh user@your-server.com

# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Issue certificate
sudo certbot certonly --nginx -d nookdressertoday.com -d www.nookdressertoday.com

# Set up auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

### Step 2: Configure WordPress

In `.env`:
```bash
# If using HTTPS
WP_HOME='https://nookdressertoday.com'
WP_SITEURL='https://nookdressertoday.com/wp'
```

In `config/application.php`:
```php
// Already configured - detects HTTPS from Cloudflare proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
```

### Step 3: HTTP → HTTPS Redirect

**Option A: Cloudflare Rules**

In Cloudflare Dashboard → Rules → Page Rules:
```
URL: http://nookdressertoday.com/*
Action: Always Use HTTPS
```

**Option B: Nginx Configuration**

In `docker/nginx/conf.d/default.conf`:
```nginx
server {
    listen 80;
    server_name _;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    # ... SSL config
}
```

## Stripe Integration

### Step 1: Create Brand-Specific Stripe Account

**For Each Brand:**

1. Create Stripe account (or use organization structure)
2. Get API keys:
   - Publishable Key: `pk_live_...` or `pk_test_...`
   - Secret Key: `sk_live_...` or `sk_test_...`
3. Get Webhook Secret: `whsec_...`

### Step 2: Configure Environment Variables

In `.env` (or CI/CD secrets):

```bash
# Test Mode (development/staging)
STRIPE_TEST_PUBLIC_KEY='pk_test_dresser_xxxxx'
STRIPE_TEST_SECRET_KEY='sk_test_dresser_xxxxx'
STRIPE_WEBHOOK_SECRET='whsec_test_xxxxx'

# Production Mode
STRIPE_LIVE_PUBLIC_KEY='pk_live_dresser_xxxxx'
STRIPE_LIVE_SECRET_KEY='sk_live_dresser_xxxxx'
STRIPE_LIVE_WEBHOOK_SECRET='whsec_live_xxxxx'
```

### Step 3: Configure Brand-Specific Stripe Keys

In `config/brands/your-brand-name.php`:

```php
'stripe' => [
    'public_key'  => env('STRIPE_TEST_PUBLIC_KEY', 'pk_test_'),
    'secret_key'  => env('STRIPE_TEST_SECRET_KEY', 'sk_test_'),
],
```

### Step 4: Setup Stripe Webhooks

In Stripe Dashboard → Developers → Webhooks:

1. **Add endpoint:**
   - URL: `https://nookdressertoday.com/wp-json/wc/v3/webhooks`
   - Events to send:
     - `charge.succeeded`
     - `charge.failed`
     - `charge.refunded`
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`

2. **Test webhook:**
   ```bash
   # Get webhook secret and test
   wp plugin list --allow-root | grep woocommerce
   ```

### Step 5: Enable Payment Methods

In Cloudflare/Stripe integration:

**For Stripe Live (Production):**

```php
// Auto-enabled in production
// WP_ENV='production' + STRIPE_LIVE keys = live mode

// Enables:
// - Apple Pay
// - Google Pay
// - Payment Request Buttons
```

See `web/app/mu-plugins/stripe-live-switch.php` for implementation.

## Content & Templates

### Brand Templates

Templates are stored in `scripts/brand-templates/`:

```bash
# Export current brand template
make wp CMD='--allow-root eval-file scripts/seed-brand-template.php'

# Creates: scripts/brand-templates/{brand-id}-template.json
```

### Seeding Pages for New Brand

```bash
# For new brand with different WP_BRAND
make wp CMD='--allow-root eval-file scripts/seed-brand-from-template.php'

# Creates branded pages:
# - Home page with brand name & copy
# - Features section with brand-specific values
# - CTA section with brand colors
# - Footer with contact info
```

### Customizing Content

Templates use brand placeholders:

```php
// In template blocks
{brand_name}         → Brand display name
{primary_color}      → Primary brand color
{cta_button}         → CTA button text
{email}              → Contact email
{phone}              → Contact phone
{support_hours}      → Support hours
{shipping_threshold} → Free shipping threshold
```

These are automatically replaced when seeding new brands.

### Accessing Brand Data in Blocks

In custom block registration:

```php
// functions.php or plugin
$brand = Brand_Config::get_brand();

register_block_type('custom/hero', [
    'render_callback' => function() {
        $brand = Brand_Config::get_brand();
        $logo = $brand['logo']['url'];
        $color = $brand['colors']['primary'];
        
        return sprintf(
            '<div style="background-color: %s;"><img src="%s" alt="%s" /></div>',
            esc_attr($color),
            esc_url($logo),
            esc_attr($brand['logo']['alt_text'])
        );
    }
]);
```

## CI/CD Deployment

### GitHub Actions Workflow

Use the provided `.github/workflows/deploy-brand.yml`:

```bash
# Trigger deployment manually
# 1. Go to GitHub Actions tab
# 2. Select "Deploy Brand" workflow
# 3. Click "Run workflow"
# 4. Select:
#    - Brand: nook-dresser-today
#    - Environment: staging or production
#    - Dry run: check to preview
```

### Workflow Steps

1. **Validate:** Check brand config exists and is valid
2. **Code Quality:** Run PHP CodeSniffer
3. **Build:** Compile frontend assets
4. **Deploy:** 
   - SSH to target server
   - Rsync code + build artifacts
   - Set WP_BRAND in .env
   - Clear caches
   - Run migrations

### Required GitHub Secrets

For each environment, configure these secrets:

```
DEPLOY_STAGING_HOST=staging.example.com
DEPLOY_STAGING_USER=deploy
DEPLOY_STAGING_PATH=/home/deploy/nook-dresser-prod
DEPLOY_STAGING_SSH_KEY=<private-key>

DEPLOY_PRODUCTION_HOST=prod.example.com
DEPLOY_PRODUCTION_USER=deploy
DEPLOY_PRODUCTION_PATH=/home/deploy/nook-dresser-prod
DEPLOY_PRODUCTION_SSH_KEY=<private-key>

SLACK_WEBHOOK=https://hooks.slack.com/services/...
```

### Manual Deployment

```bash
# SSH to server
ssh deploy@prod.example.com

# Navigate to app
cd /home/deploy/nook-dresser-prod

# Pull latest code
git pull origin main

# Set brand
echo "WP_BRAND=nook-dresser-today" >> .env

# Install/update dependencies
docker compose exec php composer install --no-dev

# Run migrations
docker compose exec php wp db migrate --allow-root

# Clear caches
docker compose exec php wp cache flush --allow-root
docker compose exec php wp rewrite flush --allow-root

# Verify
docker compose logs -f
```

## Environment Configuration

### Development (.env)

```bash
WP_ENV='development'
WP_BRAND='nook-dresser-today'

# Local development URLs
WP_HOME='http://localhost:8080'
WP_SITEURL="${WP_HOME}/wp"

# Debug settings
WP_DEBUG_DISPLAY='false'
WP_DEBUG_LOG='false'

# Stripe test keys
STRIPE_TEST_PUBLIC_KEY='pk_test_...'
STRIPE_TEST_SECRET_KEY='sk_test_...'

# Analytics (test IDs)
GA4_MEASUREMENT_ID='G-TEST123'
META_PIXEL_ID='123456789'
```

### Staging (.env.staging)

```bash
WP_ENV='staging'
WP_BRAND='nook-dresser-today'

# Staging URLs
WP_HOME='https://staging-dresser.example.com'
WP_SITEURL="${WP_HOME}/wp"

# Disable indexing on staging
DISALLOW_INDEXING='true'

# Stripe test keys (staging)
STRIPE_TEST_PUBLIC_KEY='pk_test_...'
STRIPE_TEST_SECRET_KEY='sk_test_...'

# Analytics (staging IDs)
GA4_MEASUREMENT_ID='G-STAGING123'
META_PIXEL_ID='123456789'
```

### Production (.env.production)

```bash
WP_ENV='production'
WP_BRAND='nook-dresser-today'

# Production URLs
WP_HOME='https://nookdressertoday.com'
WP_SITEURL="${WP_HOME}/wp"

# Security
WP_DEBUG_DISPLAY='false'
WP_DEBUG_LOG='true'
DISALLOW_FILE_EDIT='true'
DISALLOW_FILE_MODS='true'

# Redis caching
REDIS_HOST='redis'
REDIS_PORT='6379'

# Stripe LIVE keys (set via CI/CD secrets)
STRIPE_LIVE_PUBLIC_KEY='pk_live_...'
STRIPE_LIVE_SECRET_KEY='sk_live_...'

# Production Analytics
GA4_MEASUREMENT_ID='G-PROD123'
META_PIXEL_ID='987654321'
```

## Brand-Specific Settings

### Available Brand Configuration Options

```php
$brand = Brand_Config::get_brand();

// Brand Identifier
$brand['id']              // 'nook-dresser-today'
$brand['name']            // 'NookDresserToday'
$brand['display_name']    // 'Nook Dresser Today'

// Branding
$brand['logo']['url']
$brand['favicon']['url']
$brand['og_image']['url']

// Colors
$brand['colors']['primary']
$brand['colors']['secondary']
// ... all colors

// Copy/Messaging
$brand['copy']['cta_button']
$brand['copy']['view_products']
$brand['copy']['learn_more']
// ... all copy

// Contact
$brand['contact']['email']
$brand['contact']['phone']
$brand['contact']['support_hours']

// Shipping
$brand['shipping']['methods']
$brand['shipping']['free_threshold']
$brand['shipping']['service_areas']

// Analytics
$brand['analytics']['ga4_measurement_id']
$brand['analytics']['facebook_pixel_id']

// Stripe
$brand['stripe']['public_key']
$brand['stripe']['secret_key']
```

### Theme Integration

Update theme `functions.php` to use brand colors:

```php
// web/app/themes/twentytwentyfour/functions.php

function generate_brand_css() {
    $brand = Brand_Config::get_brand();
    
    return sprintf(
        ':root {
            --brand-primary: %s;
            --brand-secondary: %s;
            --brand-accent: %s;
        }',
        $brand['colors']['primary'],
        $brand['colors']['secondary'],
        $brand['colors']['tertiary']
    );
}

add_action('wp_head', function() {
    echo '<style>' . generate_brand_css() . '</style>';
});
```

## Testing & Validation

### Pre-Launch Checklist

```bash
# 1. Verify brand configuration
make wp CMD='--allow-root eval-file config/brands.php'

# 2. Check pages are created
make wp CMD='page list --allow-root'

# 3. Verify brand settings
make wp CMD='--allow-root eval 'echo json_encode(Brand_Config::get_brand(), JSON_PRETTY_PRINT);''

# 4. Test WP-CLI
make wp CMD='plugin list --allow-root'

# 5. Check database
make wp CMD='db query "SELECT option_name, option_value FROM wp_options WHERE option_name LIKE '\''%brand%'\''" --allow-root'
```

### Post-Deployment Validation

1. **Visit site:**
   - Check homepage loads
   - Verify brand logo displays
   - Check brand colors applied
   - Verify navigation menus

2. **Check analytics:**
   - GA4 fires correctly
   - Facebook Pixel fires
   - No console errors

3. **Test commerce:**
   - Product pages load
   - Add to cart works
   - Checkout processes
   - Stripe payment accepted

4. **SEO checks:**
   - Meta tags correct
   - OG images display
   - Canonical URLs set
   - XML sitemap accessible

5. **Security checks:**
   - HTTPS/SSL working
   - Security headers present
   - No exposed config files
   - WAF rules active

## Rollout Checklist

### 72 Hours Before Launch

- [ ] Brand configuration created and tested
- [ ] DNS records prepared (not yet active)
- [ ] SSL certificate ordered (or Cloudflare SSL ready)
- [ ] Stripe account configured
- [ ] Analytics accounts created
- [ ] Content templates reviewed
- [ ] Team trained on new brand

### 24 Hours Before Launch

- [ ] Code merged to main branch
- [ ] CI/CD tests passing
- [ ] Staging deployment successful
- [ ] Content review completed
- [ ] Stakeholders approve
- [ ] Backup of existing data created

### Launch Day (2-Hour Window)

- [ ] Point DNS to Cloudflare (30 min for propagation)
- [ ] Cloudflare security rules active
- [ ] SSL certificate active/verified
- [ ] Deploy to production
- [ ] Smoke tests pass (pages load, checkout works)
- [ ] Monitor error logs
- [ ] Verify analytics firing
- [ ] Announce go-live

### Post-Launch (24 Hours)

- [ ] Monitor error rates
- [ ] Check conversion metrics
- [ ] Verify email notifications
- [ ] Test customer support flows
- [ ] Monitor server performance
- [ ] Verify backups working

---

**Need Help?**

See related documentation:
- [Production Launch Guide](production-launch.md)
- [Production Rollout Checklist](production-rollout-checklist.md)
- [Deployment Guide](deployment.md)
- [Architecture Guide](architecture.md)
