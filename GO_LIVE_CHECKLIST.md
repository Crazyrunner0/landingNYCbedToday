# Go-Live Checklist

This document provides an operational checklist for launching NYC Bed Today to production. Follow each section sequentially to ensure a successful deployment while minimizing downtime and risk.

**Timeline Expectation**: 2-4 hours for full deployment process (can be executed in phases)

---

## Table of Contents

1. [Pre-Deployment Planning](#pre-deployment-planning)
2. [DNS & Domain Configuration](#dns--domain-configuration)
3. [SSL/TLS Certificate Deployment](#ssltls-certificate-deployment)
4. [Cloudflare Configuration](#cloudflare-configuration)
5. [Hosting Environment Setup](#hosting-environment-setup)
6. [Database Preparation](#database-preparation)
7. [Stripe Live Mode Activation](#stripe-live-mode-activation)
8. [Payment Gateway Integration](#payment-gateway-integration)
9. [Legal & Compliance](#legal--compliance)
10. [Analytics & Tracking](#analytics--tracking)
11. [Monitoring & Logging](#monitoring--logging)
12. [Pre-Launch Testing](#pre-launch-testing)
13. [Deployment Workflow](#deployment-workflow)
14. [Post-Launch Verification](#post-launch-verification)
15. [Rollback Procedures](#rollback-procedures)

---

## Pre-Deployment Planning

### Prerequisites
- [ ] Hosting provider account confirmed and accessible
- [ ] Domain name registered and DNS records editable
- [ ] SSL certificate obtained or DNS validation ready
- [ ] Stripe business account approved for live mode
- [ ] Team access credentials shared securely (password manager)
- [ ] Backup strategy documented
- [ ] Monitoring services configured

### Communication
- [ ] Stakeholders notified of launch window
- [ ] Support team briefed on go-live procedures
- [ ] Escalation contacts identified
- [ ] Deployment checklist reviewed by team lead

### Documentation
- [ ] All configuration files updated for production
- [ ] Runbooks prepared for common issues
- [ ] Team has access to this checklist and related documentation

**Related Files:**
- Environment config: `.env.example` (reference for variables needed)
- Deployment process: `STAGING_DEPLOYMENT_GUIDE.md`

---

## DNS & Domain Configuration

### Domain Preparation

- [ ] **Confirm domain registrar access**: Verify you can edit DNS records at your registrar
- [ ] **Choose DNS provider**: Cloudflare (recommended) or standard registrar DNS
- [ ] **Plan DNS migration**:
  - For **Cloudflare**: Add domain to Cloudflare, update registrar nameservers
  - For **standard DNS**: Update A records directly at registrar

### DNS Records Setup

Create or update these records at your DNS provider (replace `example.com` with your domain):

```
TYPE    NAME                CONTENT                      TTL
A       example.com         <your-production-ip>         3600
CNAME   www                 example.com                  3600
CNAME   mail                mail.example.com             3600
TXT     _acme-challenge     (for Let's Encrypt)          60
```

### DNS Configuration Steps

1. **If using Cloudflare:**
   - [ ] Add domain to Cloudflare account
   - [ ] Update nameservers at domain registrar to Cloudflare's:
     - `ns1.cloudflare.com`
     - `ns2.cloudflare.com`
   - [ ] Wait for nameserver propagation (5-48 hours)
   - [ ] Create A record pointing to production server IP
   - [ ] Enable Cloudflare proxy (orange cloud icon) for SSL

2. **If using registrar DNS:**
   - [ ] Create A record: `example.com` → production IP address
   - [ ] Create CNAME: `www.example.com` → `example.com`
   - [ ] Propagation typically 15-30 minutes

### DNS Verification

```bash
# Test DNS resolution (from command line)
nslookup example.com
dig example.com

# Verify all records
nslookup -type=A example.com
nslookup -type=MX example.com
nslookup -type=TXT example.com
```

**Acceptance**: DNS should resolve to production IP within 30 minutes

---

## SSL/TLS Certificate Deployment

### SSL Certificate Preparation

Choose one of these approaches:

#### Option A: Cloudflare SSL (Recommended)
- [ ] Enable **Flexible SSL** or **Full SSL** in Cloudflare dashboard:
  - Go to **SSL/TLS → Overview**
  - Select **Full** (if you have origin certificate) or **Flexible** (auto-managed)
  - Cloudflare automatically provisions free SSL certificate

#### Option B: Let's Encrypt (Free, Auto-Renewal)
- [ ] Install certbot on production server:
  ```bash
  sudo apt-get install certbot python3-certbot-nginx
  ```
- [ ] Generate certificate:
  ```bash
  sudo certbot certonly --standalone -d example.com -d www.example.com
  ```
- [ ] Configure auto-renewal (cron):
  ```bash
  sudo certbot renew --quiet --pre-hook "systemctl stop nginx" --post-hook "systemctl start nginx"
  ```

#### Option C: Commercial Certificate
- [ ] Purchase SSL certificate from provider (DigiCert, Sectigo, etc.)
- [ ] Generate CSR on production server
- [ ] Download certificate files
- [ ] Install on Nginx/Apache (see below)

### Production Server SSL Configuration

**For Nginx** (update `/etc/nginx/sites-available/default`):
```nginx
server {
    listen 443 ssl http2;
    server_name example.com www.example.com;

    ssl_certificate /etc/ssl/certs/certificate.crt;
    ssl_certificate_key /etc/ssl/private/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name example.com www.example.com;
    return 301 https://$server_name$request_uri;
}
```

**For Apache** (update `.htaccess` or `httpd.conf`):
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

<IfModule mod_ssl.c>
    <VirtualHost *:443>
        SSLEngine on
        SSLCertificateFile /path/to/certificate.crt
        SSLCertificateKeyFile /path/to/private.key
        SSLCertificateChainFile /path/to/chain.crt
        
        SSLProtocol TLSv1.2 TLSv1.3
        SSLCipherSuite HIGH:!aNULL:!MD5
        SSLHonorCipherOrder on
    </VirtualHost>
</IfModule>
```

### SSL Verification

- [ ] Test SSL certificate:
  ```bash
  # SSL Labs test (online tool)
  https://www.ssllabs.com/ssltest/analyze.html?d=example.com

  # Command-line test
  openssl s_client -connect example.com:443
  ```
- [ ] Verify certificate is valid (no warnings in browser)
- [ ] Check certificate expiration date and set renewal reminder

**Certificate Details to Verify:**
- Domain matches (*.example.com or example.com)
- Issuer trusted by browsers
- Not expired
- Signature algorithm: SHA256 or higher

---

## Cloudflare Configuration

### Cloudflare Setup (if not already done)

- [ ] Create Cloudflare account (if needed)
- [ ] Add domain to Cloudflare
- [ ] Change registrar nameservers to Cloudflare
- [ ] Wait for domain verification

### Caching & Performance

1. **Caching Rules** (Cloudflare Dashboard → Caching → Configuration):
   - [ ] **Cache Level**: Set to "Cache Everything"
   - [ ] **Browser Cache TTL**: 1 hour to 1 day (production-grade content)
   - [ ] **Cache TTL**: 1 day (static assets)
   - [ ] **Cache on Cookie**: Exclude WordPress auth cookies from cache:
     ```
     wordpress_logged_in_*
     wp-settings-*
     wp-postpass_*
     ```

2. **Cache Rules** (More granular control):
   - [ ] **Static Assets** (CSS, JS, images):
     ```
     Path: /wp-content/themes/* OR /wp-content/plugins/*
     Browser TTL: 1 day
     Cache Level: Cache Everything
     ```
   - [ ] **WooCommerce Product Pages**:
     ```
     Path: /product/* OR /shop/*
     Browser TTL: 4 hours
     Cache Level: Cache Everything
     Cache Deception Armor: On
     ```
   - [ ] **Homepage**:
     ```
     Path: / OR /index.php
     Browser TTL: 1 hour
     Cache Level: Cache Everything
     ```

3. **Bypass Cache** (Dynamic content):
   - [ ] **Checkout/Cart**:
     ```
     Path: /checkout OR /cart OR /my-account*
     Bypass all caching
     ```
   - [ ] **REST API** (if used):
     ```
     Path: /wp-json/*
     Cache Level: Bypass
     ```

### Page Rules (Legacy, or use Cache Rules above)

- [ ] **Bypass cache for sensitive pages**:
  ```
  Pattern: example.com/checkout*
  Cache Level: Bypass
  ```
- [ ] **Increase cache TTL for static assets**:
  ```
  Pattern: example.com/wp-content/uploads/*
  Cache Level: Cache Everything
  Browser Cache TTL: 1 day
  ```

### Security & Performance Settings

1. **SSL/TLS**:
   - [ ] **Encryption Mode**: Set to "Full" (if origin has valid SSL) or "Flexible"
   - [ ] **Minimum TLS Version**: 1.2
   - [ ] **Always Use HTTPS**: Enable

2. **Security**:
   - [ ] **Bot Management**: Enable for bot traffic detection
   - [ ] **Rate Limiting**: Set rules to prevent abuse:
     ```
     Threshold: 10 requests per 10 seconds per IP
     Action: Challenge
     ```
   - [ ] **WAF** (Web Application Firewall): Enable OWASP Core Ruleset
   - [ ] **DDoS Protection**: Enable (automatic)

3. **Performance**:
   - [ ] **Brotli Compression**: Enable
   - [ ] **HTTP/2 Push**: Enable
   - [ ] **Rocket Loader** (JavaScript optimization): Enable with caution (test first)
   - [ ] **Minify**: Enable CSS, JavaScript, HTML

### DNS Configuration in Cloudflare

- [ ] Create A record for root domain:
  ```
  Name: example.com
  Type: A
  Content: <production-server-ip>
  TTL: Auto
  Proxy Status: Proxied (Orange Cloud) for DDoS protection
  ```
- [ ] Create CNAME for www:
  ```
  Name: www
  Type: CNAME
  Content: example.com
  TTL: Auto
  Proxy Status: Proxied
  ```
- [ ] Set up any additional subdomains (cdn, api, etc.)

### Monitor Cloudflare Dashboard

- [ ] Check **Analytics** tab for traffic patterns
- [ ] Review **Security** events for blocked threats
- [ ] Monitor **Performance** metrics (response times, cache hit ratio)

**Expected Metrics:**
- Cache Hit Ratio: >50% for static sites
- Response Time: <500ms average
- DNS TTL: 3600 seconds

---

## Hosting Environment Setup

### Server Requirements

**Recommended Specifications:**
- OS: Ubuntu 20.04+ (LTS) or similar Linux
- PHP: 8.1+ (as specified in `composer.json`)
- Database: MySQL 5.7+ or MariaDB 10.3+
- Web Server: Nginx (recommended) or Apache 2.4+
- Cache: Redis 6.0+
- Disk Space: Minimum 20GB (50GB recommended)
- RAM: Minimum 2GB (4GB recommended)
- CPU: Minimum 1-2 cores

### Environment Configuration

1. **SSH Access**:
   - [ ] Configure SSH key-based authentication
   - [ ] Disable password login: Set `PasswordAuthentication no` in `/etc/ssh/sshd_config`
   - [ ] Restrict SSH to specific IPs (optional but recommended)

2. **Firewall Setup**:
   ```bash
   # Allow only necessary ports
   ufw allow 22/tcp    # SSH
   ufw allow 80/tcp    # HTTP
   ufw allow 443/tcp   # HTTPS
   ufw enable
   ```

3. **Create Application User**:
   ```bash
   sudo useradd -m -s /bin/bash wordpress
   sudo usermod -aG www-data wordpress
   ```

### Deploy Application Code

1. **Clone Repository**:
   ```bash
   cd /var/www/app
   git clone <repository-url> .
   git checkout main
   ```

2. **Install Dependencies**:
   ```bash
   # Using Composer (from Makefile reference)
   composer install --no-dev --optimize-autoloader
   
   # Or via make command
   make composer CMD='install --no-dev --optimize-autoloader'
   ```

3. **Set File Permissions**:
   ```bash
   sudo chown -R www-data:www-data /var/www/app/web
   sudo chmod -R 755 /var/www/app/web
   sudo chmod -R 775 /var/www/app/web/app/uploads
   sudo chmod -R 775 /var/www/app/web/app/cache
   ```

### Environment Variables

1. **Create Production `.env` File**:
   ```bash
   cp .env.example .env
   ```

2. **Update `.env` with Production Values**:
   
   **Database**:
   ```bash
   DB_NAME='wordpress_prod'
   DB_USER='wp_prod_user'
   DB_PASSWORD='<strong-password>'
   DB_HOST='db.internal'
   DB_PREFIX='wp_'
   ```

   **WordPress URLs** (update to production domain):
   ```bash
   WP_ENV='production'
   WP_HOME='https://example.com'
   WP_SITEURL='${WP_HOME}/wp'
   ```

   **Security Keys** (generate from https://roots.io/salts.html):
   ```bash
   AUTH_KEY='<unique-key>'
   SECURE_AUTH_KEY='<unique-key>'
   LOGGED_IN_KEY='<unique-key>'
   NONCE_KEY='<unique-key>'
   AUTH_SALT='<unique-salt>'
   SECURE_AUTH_SALT='<unique-salt>'
   LOGGED_IN_SALT='<unique-salt>'
   NONCE_SALT='<unique-salt>'
   ```

   **Redis Cache**:
   ```bash
   REDIS_HOST='redis.internal'
   REDIS_PORT='6379'
   REDIS_PASSWORD=''
   REDIS_CACHE_DB='0'
   ```

   **Debug Mode** (disable in production):
   ```bash
   WP_DEBUG='false'
   WP_DEBUG_DISPLAY='false'
   WP_DEBUG_LOG='false'
   ```

   **Search Engine Visibility**:
   ```bash
   DISALLOW_INDEXING='false'  # Set to 'true' only if site should not be indexed
   ```

3. **Secure `.env` File**:
   ```bash
   sudo chmod 640 /var/www/app/.env
   sudo chown www-data:www-data /var/www/app/.env
   ```

**Reference Files:**
- `.env.example` - Template with all available variables
- `.env.local.example` - Local development overrides (not used in production)

---

## Database Preparation

### Database Setup

1. **Create Production Database**:
   ```bash
   mysql -u root -p
   CREATE DATABASE wordpress_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'wp_prod_user'@'localhost' IDENTIFIED BY '<strong-password>';
   GRANT ALL PRIVILEGES ON wordpress_prod.* TO 'wp_prod_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

2. **Import Data** (if migrating from staging):
   ```bash
   # Dump from staging
   mysqldump -u staging_user -p staging_db > backup.sql
   
   # Import to production (with search/replace for URLs)
   wp search-replace 'https://staging.example.com' 'https://example.com' --network
   wp search-replace 'http://staging.example.com' 'https://example.com' --network
   ```

### Database Optimization

```bash
# Optimize tables
wp db optimize

# Check database integrity
wp db check

# View table sizes
wp db info
```

### Backup Before Launch

- [ ] Create full database backup:
  ```bash
  mysqldump -u wp_prod_user -p wordpress_prod > /backup/wordpress_prod_backup.sql
  ```
- [ ] Store backup in secure location (off-site)
- [ ] Document backup location and restore procedure

---

## Stripe Live Mode Activation

### Prerequisites

- [ ] Stripe business account verified and approved
- [ ] Business identity verification complete
- [ ] Bank account connected and verified
- [ ] Stripe dashboard accessible

### Obtain Live API Keys

1. **Access Stripe Dashboard**:
   - Go to https://dashboard.stripe.com/
   - Ensure you're in **Live Mode** (not Test Mode)
   - Navigate to **Developers → API Keys**

2. **Find or Generate Live Keys**:
   - [ ] **Publishable Key** (starts with `pk_live_`)
   - [ ] **Secret Key** (starts with `sk_live_`)

   **⚠️ SECURITY WARNING**: Never commit live keys to version control. Use environment variables only.

### Update Environment Variables

In production `.env` file, replace test keys with live keys:

```bash
# Previous (test mode)
STRIPE_TEST_PUBLIC_KEY='pk_test_...'
STRIPE_TEST_SECRET_KEY='sk_test_...'

# Update to (live mode)
STRIPE_TEST_PUBLIC_KEY='pk_live_...'  # Keep variable name for code compatibility
STRIPE_TEST_SECRET_KEY='sk_live_...'   # Code uses these env vars
```

**Alternative**: If your code uses `STRIPE_PUBLIC_KEY` and `STRIPE_SECRET_KEY`:
```bash
STRIPE_PUBLIC_KEY='pk_live_...'
STRIPE_SECRET_KEY='sk_live_...'
```

### Webhook Configuration

1. **Create Webhook Endpoint**:
   - Go to **Developers → Webhooks**
   - Click **Add an endpoint**
   - Enter endpoint URL:
     ```
     https://example.com/wp-json/stripe/webhook
     ```

2. **Select Events** to listen for:
   - [ ] `charge.failed`
   - [ ] `charge.succeeded`
   - [ ] `charge.refunded`
   - [ ] `invoice.payment_failed`
   - [ ] `payment_intent.succeeded`
   - [ ] `payment_intent.payment_failed`

3. **Get Webhook Secret**:
   - After creating webhook, view it
   - Copy **Signing secret** (starts with `whsec_live_`)
   - Update environment variable:
     ```bash
     STRIPE_WEBHOOK_SECRET='whsec_live_...'
     ```

4. **Test Webhook**:
   ```bash
   # Send test event
   # Use Stripe Dashboard → Webhooks → [Your endpoint] → Send test
   ```

### Live Mode Testing

- [ ] Process test transaction with real card (may charge small amount)
- [ ] Verify transaction appears in Stripe Dashboard
- [ ] Verify order created in WordPress admin

**Acceptance**: Live transaction appears in Stripe Dashboard within 30 seconds

---

## Payment Gateway Integration

### WooCommerce Stripe Plugin Configuration

1. **Access WooCommerce Settings**:
   - WordPress Admin → **WooCommerce → Settings → Payments**

2. **Configure Stripe Gateway**:
   - [ ] Click **Stripe** to expand settings
   - [ ] **Enable Stripe**: Check "Enable payment via Stripe"
   - [ ] **Title**: "Credit/Debit Card" or similar
   - [ ] **Description**: "Pay securely with your credit or debit card"

3. **API Keys Configuration**:
   - [ ] **Publishable Key**: Paste `pk_live_...` value
   - [ ] **Secret Key**: Paste `sk_live_...` value (from `.env` if using env-driven setup)
   - [ ] **Webhook Secret**: Paste `whsec_live_...` value
   - [ ] **Test Mode**: Set to "No" (production mode)

4. **Advanced Options**:
   - [ ] **Statement Descriptor**: Set to "NYC Bed Today" (appears on customer billing)
   - [ ] **Capture**: Choose "Yes" (charge immediately) or "No" (authorize only)
   - [ ] **Payment Request**: Enable if supported

### Payment Request Buttons (Apple Pay / Google Pay)

1. **Configure in WooCommerce**:
   - [ ] **Payment Request Buttons**: Enable
   - [ ] **Button Theme**: Select "Dark" or "Light"
   - [ ] **Button Type**: Select "buy" or "default"
   - [ ] **Button Locations**: Check where to display (product, cart)

2. **Add Live Domain to Stripe** (for Payment Request):
   - Go to Stripe Dashboard → **Settings → Payment method details**
   - Add domain to **Associated domains for payment requests**:
     ```
     example.com
     www.example.com
     ```

3. **Test Payment Buttons**:
   - [ ] Apple Pay: Test from Safari on iPhone/Mac
   - [ ] Google Pay: Test from Chrome on Android
   - [ ] Verify buttons appear and function

### Checkout Configuration

1. **Required Fields Only** (reduce friction):
   - WordPress Admin → **WooCommerce → Settings → Checkout**
   - Remove optional fields:
     - [ ] Company (not required)
     - [ ] Address Line 2 (not required)
   - Make optional:
     - [ ] Phone (if not critical)
     - [ ] State (if international)
     - [ ] Postcode

2. **One-Page Checkout** (if implemented):
   - [ ] Verify checkout form displays all fields
   - [ ] Test form submission
   - [ ] Verify redirect to payment processor

### Test Live Payments

1. **Process Real Transaction**:
   - Add product to cart
   - Go to checkout
   - Use real payment method (small charge)
   - Complete transaction

2. **Verify Transaction**:
   - [ ] Order created in WordPress Admin
   - [ ] Order status: "Processing" or "Completed"
   - [ ] Transaction appears in Stripe Dashboard
   - [ ] Payment appears in Stripe balance

3. **Check Order Details**:
   - [ ] Customer email received order confirmation
   - [ ] Order contains all items
   - [ ] Shipping address captured correctly

**Reference Files:**
- `STRIPE_TEST_INTEGRATION.md` - Detailed Stripe configuration guide
- `STRIPE_INTEGRATION_SUMMARY.md` - Integration summary

---

## Legal & Compliance

### Website Legal Pages

Create or update these pages in WordPress Admin (Pages section):

1. **Privacy Policy**:
   - [ ] Created and published
   - [ ] Includes:
     - Data collection practices
     - Cookie usage
     - Third-party services (Stripe, Google Analytics)
     - GDPR/CCPA compliance statements
     - User data retention policy
   - [ ] Link added to footer
   - [ ] URL: `/privacy-policy/` or similar

2. **Terms of Service**:
   - [ ] Created and published
   - [ ] Includes:
     - Product/service description
     - Pricing and payment terms
     - Refund policy
     - Limitation of liability
     - Governing law
   - [ ] Link added to footer
   - [ ] URL: `/terms-of-service/` or similar

3. **Refund Policy**:
   - [ ] Created and published
   - [ ] Clarifies:
     - Return window (e.g., 30 days)
     - Return process
     - Restocking fees (if applicable)
     - Non-refundable items (if applicable)
   - [ ] Link added to footer
   - [ ] URL: `/refund-policy/` or similar

4. **Shipping Policy** (for e-commerce):
   - [ ] Created and published
   - [ ] Includes:
     - Estimated delivery times
     - Shipping costs
     - Tracking information
     - Handling of delays
   - [ ] Link added to footer

### WooCommerce Compliance

1. **Tax Settings**:
   - [ ] **Tax Calculations Enabled**: WordPress Admin → WooCommerce → Settings → Tax
   - [ ] **Tax Rate Configured**:
     - Add tax class for jurisdiction (e.g., 8.875% for NY)
     - Configure tax rates per product type
   - [ ] **Tax Display**: Choose "Excluding Tax" or "Including Tax" (must be consistent)
   - [ ] **Tax in Checkout**: Verify tax displays correctly

2. **Currency & Pricing**:
   - [ ] **Store Currency**: Set to USD (or relevant currency)
   - [ ] **Price Format**: Configure currency symbol placement
   - [ ] **All prices reviewed** for accuracy

3. **Returns & Refunds**:
   - [ ] **WooCommerce returns/refunds settings configured**
   - [ ] Staff trained on refund process
   - [ ] Refund procedure documented

### Accessibility Compliance

1. **WCAG 2.1 AA Compliance Check**:
   - [ ] Use WAVE (https://wave.webaim.org/) to check for accessibility issues
   - [ ] Run automated tests with Axe (https://www.deque.com/axe/)
   - [ ] Check contrast ratios (minimum 4.5:1 for normal text)
   - [ ] Verify keyboard navigation works
   - [ ] Test with screen readers (NVDA, JAWS)

2. **Alt Text & Media**:
   - [ ] All product images have alt text
   - [ ] All decorative images marked as decorative
   - [ ] Videos have captions or transcripts
   - [ ] PDFs are accessible

3. **Forms & Navigation**:
   - [ ] Form labels properly associated with inputs
   - [ ] Error messages clear and associated with fields
   - [ ] Skip navigation links present
   - [ ] Focus indicators visible

4. **Checkout Process**:
   - [ ] Form fields have proper labels
   - [ ] Error messages are descriptive
   - [ ] Form validation is clear
   - [ ] Payment process is keyboard accessible

### Data Protection & GDPR

1. **Consent Management**:
   - [ ] Cookie banner present (if tracking enabled)
   - [ ] Consent management plugin installed (ConsentKit, OneTrust, etc.)
   - [ ] Users can opt-out of analytics
   - [ ] Users can opt-out of marketing cookies

2. **Privacy Policy Updated**:
   - [ ] Mentions all third-party services:
     - Stripe (payment processing)
     - Google Analytics (analytics)
     - Meta Pixel (retargeting)
     - CDN provider (if used)
   - [ ] Explains data retention
   - [ ] Provides data deletion request procedure

3. **Data Subject Rights** (GDPR/CCPA):
   - [ ] Procedure for data export requests
   - [ ] Procedure for data deletion requests
   - [ ] Contact email for privacy inquiries documented

### PCI Compliance (Stripe)

- [ ] Verify Stripe handles all payment data (no sensitive data stored locally)
- [ ] Confirm SSL/TLS encryption enabled
- [ ] Verify no payment card data in logs
- [ ] Stripe responsibility documented

**Security Checkpoint:**
- No passwords in `.env` file
- No API keys in code/comments
- No customer payment data stored in database
- SSL encryption enforced site-wide

---

## Analytics & Tracking

### Google Analytics 4 (GA4)

1. **Verify GA4 Property Created**:
   - [ ] GA4 property ID available (starts with `G-`)
   - [ ] Measurement ID matches `GA4_MEASUREMENT_ID` in `.env`

2. **Update Environment Variable**:
   ```bash
   GA4_MEASUREMENT_ID='G-XXXXXXXXXX'  # From GA4 Admin
   GA4_DEBUG_MODE='false'              # Disable debug in production
   ```

3. **Verify Installation** (after deployment):
   - [ ] Access WordPress site
   - [ ] Open browser DevTools → Network tab
   - [ ] Search for `gtag` requests to `google-analytics.com`
   - [ ] Verify requests are sent successfully

4. **GA4 Event Configuration** (should be auto-tracked):
   - [ ] `page_view` - All page views
   - [ ] `view_item` - Product page views
   - [ ] `add_to_cart` - Add to cart actions
   - [ ] `begin_checkout` - Checkout initiation
   - [ ] `purchase` - Order completion

5. **Verify in GA4 Real-time Report**:
   - [ ] GA4 Dashboard → Real-time
   - [ ] Make test purchase
   - [ ] Verify events appear in real-time dashboard

### Meta Pixel (Facebook)

1. **Verify Meta Pixel Created**:
   - [ ] Pixel ID available (10-16 digits)
   - [ ] Pixel ID matches `META_PIXEL_ID` in `.env`

2. **Update Environment Variable**:
   ```bash
   META_PIXEL_ID='000000000000000'
   META_PIXEL_TEST_EVENT_ID=''  # For test events (optional)
   ```

3. **Verify Installation**:
   - [ ] Access WordPress site
   - [ ] Open browser DevTools → Network tab
   - [ ] Search for `facebook.com` requests
   - [ ] Verify pixel fires on page load

4. **Verify Events** (should be auto-tracked):
   - [ ] `PageView` - All pages
   - [ ] `ViewContent` - Product views
   - [ ] `AddToCart` - Add to cart
   - [ ] `InitiateCheckout` - Checkout start
   - [ ] `Purchase` - Order completion

5. **Test Meta Events**:
   - [ ] Meta Events Manager → Test Events
   - [ ] Make test purchase
   - [ ] Verify events appear in Events Manager

### Google Search Console (GSC)

1. **Verify Site Registered**:
   - [ ] Site added to GSC (https://search.google.com/search-console)
   - [ ] Domain verified (DNS or HTML file)

2. **Update Environment Variable**:
   ```bash
   GSC_VERIFICATION_TOKEN='<verification-token>'
   ```

3. **Submit Sitemap**:
   - [ ] Go to GSC → **Sitemaps**
   - [ ] Submit:
     ```
     https://example.com/sitemap.xml
     https://example.com/sitemap-products.xml (if WooCommerce)
     ```

4. **Monitor Indexing**:
   - [ ] Check **Coverage** report for crawl errors
   - [ ] Verify URLs are being indexed
   - [ ] Monitor **Core Web Vitals** report

### Consent Management

1. **Cookie/Consent Banner**:
   - [ ] Banner appears on first visit
   - [ ] Users can opt-out of analytics
   - [ ] Consent choices respected across site
   - [ ] Banner closes properly

2. **Environment Variable**:
   ```bash
   CONSENT_BANNER_ENABLED='true'  # Enable for production
   ```

**Reference Files:**
- `ANALYTICS_PIXEL_IMPLEMENTATION.md` - Tracking implementation guide
- `ANALYTICS_VALIDATION_CHECKLIST.md` - Analytics validation checklist

---

## Monitoring & Logging

### Uptime Monitoring

1. **Select Monitoring Service**:
   - [ ] Choose provider: UptimeRobot, Ping, StatusPage, etc.
   - [ ] Set up account and verify email

2. **Configure Monitors**:
   - [ ] **HTTP Monitoring**:
     ```
     URL: https://example.com
     Interval: 5 minutes
     Alert threshold: 3 failures before alert
     ```
   - [ ] **Homepage Load Check**:
     ```
     URL: https://example.com/
     Expected: HTTP 200
     Response time alert: >5 seconds
     ```
   - [ ] **Checkout Page**:
     ```
     URL: https://example.com/checkout
     Expected: HTTP 200
     ```

3. **Alert Configuration**:
   - [ ] Email notification on downtime
   - [ ] SMS/Slack notification for critical outages
   - [ ] Status page for public communication

### Application Logging

1. **WordPress Debug Logging**:
   - [ ] **Ensure production has logging disabled**:
     ```bash
     WP_DEBUG='false'
     WP_DEBUG_DISPLAY='false'
     WP_DEBUG_LOG='false'
     ```
   - [ ] For troubleshooting (temporary):
     ```bash
     WP_DEBUG='true'
     WP_DEBUG_LOG='true'
     WP_DEBUG_DISPLAY='false'  # Never show to users
     ```

2. **Server Logs**:
   - [ ] **Nginx/Apache Logs**: Monitor at `/var/log/nginx/` or `/var/log/apache2/`
   - [ ] Set up log rotation to prevent disk space issues
   - [ ] Example logrotate config:
     ```bash
     /var/log/nginx/*.log {
         daily
         rotate 14
         compress
         delaycompress
         notifempty
         create 640 www-data adm
         sharedscripts
     }
     ```

3. **PHP Error Logging**:
   - [ ] Configure PHP to log errors:
     ```bash
     error_log = /var/log/php/error.log
     log_errors = On
     display_errors = Off
     ```

### WooCommerce Order Monitoring

1. **Daily Order Check**:
   - [ ] Review orders in WordPress Admin → **WooCommerce → Orders**
   - [ ] Verify order status progression (Pending → Processing → Completed)
   - [ ] Check for failed payments

2. **Stripe Webhook Monitoring**:
   - [ ] Go to Stripe Dashboard → **Developers → Webhooks**
   - [ ] Review webhook delivery status
   - [ ] Verify no failed event deliveries
   - [ ] Check delivery latency

3. **Failed Payment Handling**:
   - [ ] Implement retry logic (if using Stripe)
   - [ ] Customer notification on payment failure
   - [ ] Manual review process for suspicious transactions

### Backup & Recovery Monitoring

1. **Automated Backups**:
   - [ ] Database backup runs daily:
     ```bash
     # Cron job (add to crontab)
     0 2 * * * mysqldump -u wp_prod_user -p'password' wordpress_prod | gzip > /backup/wordpress_prod_$(date +\%Y\%m\%d).sql.gz
     ```
   - [ ] Verify backup files are created
   - [ ] Store backups offsite (AWS S3, Backblaze, etc.)

2. **File Backups**:
   - [ ] Backup uploads directory regularly
   - [ ] Backup plugins and themes
   - [ ] Version control handles code backups

3. **Backup Restoration Test**:
   - [ ] Monthly: Test database restore from backup
   - [ ] Verify restore process works
   - [ ] Document restore procedure

### Performance Monitoring

1. **Real User Monitoring (RUM)**:
   - [ ] Core Web Vitals visible in analytics
   - [ ] Average page load time: <3 seconds
   - [ ] Largest Contentful Paint (LCP): <2.5 seconds
   - [ ] Cumulative Layout Shift (CLS): <0.1

2. **Redis Cache Health**:
   - [ ] Monitor cache hit ratio
   - [ ] Command: `make wp CMD='redis health-check'`
   - [ ] Expected: "Redis cache is operational"

3. **Database Performance**:
   - [ ] Monitor slow query log
   - [ ] Check database size growth
   - [ ] Optimize tables if needed: `wp db optimize`

### Alert Configuration

Set up alerts for:
- [ ] Site downtime (immediate)
- [ ] High error rate (>10% errors/1000 requests)
- [ ] Database errors
- [ ] Payment processing failures
- [ ] SSL certificate expiration (14 days before)
- [ ] Disk space low (<10% remaining)
- [ ] Memory usage >80%

---

## Pre-Launch Testing

### Functionality Testing

1. **Homepage & Navigation**:
   - [ ] Homepage loads completely
   - [ ] Navigation menu works
   - [ ] Links navigate to correct pages
   - [ ] Mobile menu responsive

2. **Product Pages**:
   - [ ] Product page displays correctly
   - [ ] Product images load
   - [ ] Product description visible
   - [ ] Price displays in USD

3. **Shopping Cart**:
   - [ ] Add to cart button works
   - [ ] Cart updates quantity
   - [ ] Cart shows correct totals
   - [ ] Remove from cart works
   - [ ] Continue shopping links work

4. **Checkout Process**:
   - [ ] Checkout page loads
   - [ ] Form fields display
   - [ ] Address validation works (if applicable)
   - [ ] Tax calculates correctly
   - [ ] Shipping calculated (if applicable)
   - [ ] Payment button visible

5. **Payment Processing** (Live):
   - [ ] Process test transaction with real payment
   - [ ] Order created in WordPress
   - [ ] Transaction appears in Stripe Dashboard
   - [ ] Confirmation email sent
   - [ ] Order status shows "Processing"

### Performance Testing

1. **Page Load Speed**:
   - [ ] Homepage: <3 seconds
   - [ ] Product page: <3 seconds
   - [ ] Checkout: <3 seconds
   - Use: Google PageSpeed Insights, GTmetrix

2. **Mobile Responsiveness**:
   - [ ] Test on iPhone 12, iPhone SE, Android phone
   - [ ] Navigation works on mobile
   - [ ] Checkout works on mobile
   - [ ] Payment buttons visible on mobile
   - [ ] Forms accessible on mobile

3. **Browser Compatibility**:
   - [ ] Chrome (latest)
   - [ ] Firefox (latest)
   - [ ] Safari (latest)
   - [ ] Edge (latest)

### Security Testing

1. **SSL/HTTPS**:
   - [ ] All pages use HTTPS
   - [ ] No mixed content (HTTP + HTTPS)
   - [ ] SSL certificate valid
   - [ ] Browser shows secure padlock

2. **Form Security**:
   - [ ] Forms have CSRF tokens (nonces)
   - [ ] Data submitted securely
   - [ ] No sensitive data in URL

3. **Password & Access**:
   - [ ] WordPress admin requires login
   - [ ] Weak passwords prevented
   - [ ] Session timeout configured

### Analytics Validation

1. **GA4 Events**:
   - [ ] Make test purchase
   - [ ] Events appear in GA4 Real-time
   - [ ] Purchase value captures correctly
   - [ ] User ID tracking works (if applicable)

2. **Meta Pixel**:
   - [ ] Meta Pixel fires on page load
   - [ ] Purchase event sends correct value
   - [ ] Events appear in Events Manager

3. **Search Console**:
   - [ ] No crawl errors
   - [ ] Mobile usability passed
   - [ ] Core Web Vitals passed

### Content Verification

1. **Copy & Typography**:
   - [ ] All page content correct and complete
   - [ ] No placeholder text visible
   - [ ] Formatting correct (bold, italics, etc.)
   - [ ] Links all working

2. **Images & Media**:
   - [ ] All product images visible
   - [ ] No broken image icons
   - [ ] Images load quickly
   - [ ] Alt text present

3. **Legal & Compliance**:
   - [ ] Privacy Policy published and linked
   - [ ] Terms of Service published and linked
   - [ ] Refund Policy published and linked
   - [ ] Footer links all working

---

## Deployment Workflow

### Pre-Deployment Checklist

- [ ] All changes merged to `main` branch
- [ ] Code review completed and approved
- [ ] Tests passing in CI/CD
- [ ] Staging deployment verified
- [ ] Database backup taken
- [ ] Rollback plan documented
- [ ] Team notified of deployment time
- [ ] Emergency contacts available

### Single-PR Deployment Process

This project uses a single-PR deployment workflow documented in `STAGING_DEPLOYMENT_GUIDE.md`:

1. **Feature Development** (on feature branch):
   - [ ] Create feature branch from `main`
   - [ ] Make code changes
   - [ ] Test locally: `make bootstrap` / `make up`
   - [ ] Verify changes work

2. **Pull Request & Code Review**:
   - [ ] Create PR with clear description
   - [ ] CI checks must pass
   - [ ] Get team approval
   - [ ] Address feedback if any

3. **Staging Deployment**:
   - [ ] Merge PR to `main`
   - [ ] CI/CD automatically deploys to staging
   - [ ] Deployment duration: ~5-10 minutes
   - [ ] Verify staging deployment successful

4. **Staging Verification**:
   - [ ] Access staging site: https://staging.example.com
   - [ ] Run full test suite manually
   - [ ] Verify all features working
   - [ ] Get stakeholder sign-off

5. **Production Deployment**:
   - [ ] Scheduled for off-peak hours (if possible)
   - [ ] Run database backup: `MAKE_BACKUP=1 ./scripts/deploy-prod.sh`
   - [ ] Deploy to production via deployment script
   - [ ] Deployment should take <5 minutes

### Deployment Commands

**Using Makefile** (from `STAGING_DEPLOYMENT_GUIDE.md`):

```bash
# Staging deployment (automatic on merge to main)
# See GitHub Actions: .github/workflows/deploy-staging.yml

# Production deployment (manual via deploy script)
./scripts/deploy-production.sh

# Check deployment status
./scripts/check-deployment.sh

# Dry-run to see what would be deployed
DRY_RUN=true ./scripts/deploy-production.sh
```

### Deployment Environment Variables

For production deployment, ensure these are set:

```bash
# For deploy-staging.sh (from STAGING_DEPLOYMENT_GUIDE.md)
STAGING_HOST='staging.example.com'
STAGING_PATH='/var/www/staging'
STAGING_SSH_KEY='<private-key>'
DRY_RUN='false'
MAKE_BACKUP='true'

# For production
PROD_HOST='example.com'
PROD_PATH='/var/www/app'
PROD_SSH_KEY='<private-key>'
PROD_DB_NAME='wordpress_prod'
PROD_DB_USER='wp_prod_user'
```

### Deployment Timing

- [ ] Schedule for low-traffic period (e.g., 2 AM - 4 AM)
- [ ] Notify support team 24 hours in advance
- [ ] Have team available during deployment window
- [ ] Monitor site for 30 minutes after deployment

### Post-Deployment Workflow

1. **Verification** (immediately after deployment):
   - [ ] Site loads without errors
   - [ ] Homepage displays correctly
   - [ ] Checkout process works
   - [ ] Payment processing works
   - [ ] Admin panel accessible
   - [ ] Analytics tracking active

2. **Monitoring** (first 24 hours):
   - [ ] Monitor error logs
   - [ ] Check uptime monitoring dashboard
   - [ ] Review payment transactions
   - [ ] Monitor server resource usage
   - [ ] Check performance metrics

3. **Customer Communication**:
   - [ ] Send deployment notification if applicable
   - [ ] Monitor for customer issues/tickets
   - [ ] Be ready for quick rollback if needed

---

## Post-Launch Verification

### Immediate Verification (First 30 Minutes)

1. **Site Accessibility**:
   ```bash
   # Test site is accessible
   curl -I https://example.com
   # Should return HTTP 200
   
   # Test SSL certificate
   openssl s_client -connect example.com:443
   ```

2. **WordPress Admin**:
   - [ ] Access WordPress admin at https://example.com/wp-admin
   - [ ] Login with credentials works
   - [ ] Dashboard displays without errors

3. **Frontend**:
   - [ ] Homepage loads completely
   - [ ] No console errors in browser (F12)
   - [ ] Navigation works
   - [ ] Product pages load

4. **E-Commerce**:
   - [ ] Add to cart works
   - [ ] Checkout page loads
   - [ ] Payment processing available

5. **Analytics**:
   - [ ] GA4 events firing (check Real-time)
   - [ ] Meta Pixel firing
   - [ ] No tracking errors

### Short-Term Verification (First 24 Hours)

1. **Daily Tasks**:
   - [ ] Monitor error logs: `tail -f /var/log/nginx/error.log`
   - [ ] Check uptime monitoring dashboard
   - [ ] Review payment transactions in Stripe
   - [ ] Verify orders created in WordPress
   - [ ] Check server resource usage (CPU, memory, disk)

2. **Analytics Verification**:
   - [ ] GA4 reports showing data
   - [ ] Conversion tracking working
   - [ ] Custom events recorded
   - [ ] No tracking errors

3. **Email Verification**:
   - [ ] Order confirmation emails sending
   - [ ] Customer receives emails within 2 minutes
   - [ ] Email formatting correct

4. **Database Health**:
   ```bash
   # Check database integrity
   wp db check
   
   # View database size
   wp db info
   
   # Check Redis cache
   wp redis health-check
   ```

5. **Backup Verification**:
   - [ ] Automated backup runs (check backup directory)
   - [ ] Backup file size reasonable
   - [ ] Backup created within expected time window

### Ongoing Monitoring (Week 1+)

1. **Weekly Tasks**:
   - [ ] Review uptime metrics (target: 99.9%+)
   - [ ] Check error logs for patterns
   - [ ] Review performance metrics
   - [ ] Monitor disk space growth
   - [ ] Review conversion metrics

2. **Monitor Reports**:
   - [ ] Google Analytics 4 reports
   - [ ] Stripe payment reports
   - [ ] Server resource usage trends
   - [ ] Page speed metrics

3. **Customer Support**:
   - [ ] No customer reports of issues
   - [ ] Checkout working smoothly
   - [ ] No payment failures
   - [ ] Site performance acceptable

### Success Criteria

The launch is considered successful when:

✅ **Site Accessibility**:
- Site accessible via HTTPS 24/7
- DNS resolves correctly globally
- SSL certificate valid and trusted

✅ **Functionality**:
- All pages load without errors
- Shopping cart functional
- Checkout process works end-to-end
- Payment processing successful

✅ **Analytics**:
- GA4 tracking active and recording events
- Meta Pixel firing correctly
- Conversion events captured accurately

✅ **Performance**:
- Page load time <3 seconds
- Core Web Vitals passing
- No performance degradation vs. staging

✅ **Payments**:
- Stripe live mode active
- Test transactions processing successfully
- Webhook delivering events
- Orders created in WordPress

✅ **Monitoring**:
- Uptime monitoring active
- Error logs clean
- Backup running automatically
- Alert system working

✅ **Compliance**:
- SSL/HTTPS enforced
- Privacy policy published
- Terms of service linked
- GDPR cookie consent functional

---

## Rollback Procedures

### When to Rollback

Rollback immediately if:
- [ ] Site completely inaccessible (HTTP 500)
- [ ] Payment processing broken
- [ ] Database connectivity lost
- [ ] Critical security vulnerability discovered
- [ ] Performance degraded >50%
- [ ] Data loss occurring

### Rollback Steps

1. **Stop Bleeding** (First 30 seconds):
   ```bash
   # Put site in maintenance mode
   wp maintenance-mode activate
   ```

2. **Revert Code**:
   ```bash
   # Go back to previous stable commit
   cd /var/www/app
   git revert HEAD
   git pull
   
   # Or restore from backup deployment
   rsync -avz /backup/deployment-backup/ ./
   ```

3. **Restart Services**:
   ```bash
   # Restart web server
   sudo systemctl restart nginx
   
   # Restart PHP-FPM
   sudo systemctl restart php-fpm
   
   # Clear caches
   wp cache flush
   wp redis flush-cache
   ```

4. **Database Rollback** (if needed):
   ```bash
   # Restore from backup
   mysql -u wp_prod_user -p wordpress_prod < /backup/wordpress_prod_backup.sql
   
   # Verify restoration
   wp db check
   ```

5. **Verify Rollback**:
   - [ ] Site accessible
   - [ ] Admin panel works
   - [ ] Previous version running
   - [ ] Disable maintenance mode: `wp maintenance-mode deactivate`

6. **Communication**:
   - [ ] Notify team of rollback
   - [ ] Update status page
   - [ ] Assess what went wrong
   - [ ] Plan fix before next deployment

### Rollback Testing

- [ ] **Monthly**: Practice rollback procedure (dry-run)
- [ ] **Before Launch**: Test complete rollback procedure
- [ ] **Document**: Current version hash for quick reference
- [ ] **Backup**: Keep previous deployment backed up for 7 days

---

## Quick Reference

### Key Commands

```bash
# View status
make healthcheck

# Deploy to staging
git push main  # Automatic via CI/CD

# Deploy to production
./scripts/deploy-production.sh

# Emergency stop
make down

# Check logs
make logs

# WordPress CLI
make wp CMD='plugin list'
make wp CMD='db info'

# Cache operations
make wp CMD='cache flush'
make wp CMD='redis health-check'

# Backups
mysqldump -u wp_prod_user -p wordpress_prod > backup.sql
```

### Important URLs

- **WordPress Admin**: `https://example.com/wp-admin`
- **WooCommerce Settings**: `https://example.com/wp-admin/admin.php?page=wc-settings`
- **Stripe Dashboard**: `https://dashboard.stripe.com`
- **Google Analytics**: `https://analytics.google.com`
- **Cloudflare Dashboard**: `https://dash.cloudflare.com`
- **Uptime Monitor**: (Your monitoring service URL)

### Contact Information

- **Tech Lead**: [Name] - [Email/Phone]
- **DevOps**: [Name] - [Email/Phone]
- **Support**: [Email/Phone]
- **Emergency Escalation**: [Contact]

### Related Documentation

- **Stripe Configuration**: `STRIPE_TEST_INTEGRATION.md`
- **Staging Deployment**: `STAGING_DEPLOYMENT_GUIDE.md`
- **Analytics Setup**: `ANALYTICS_PIXEL_IMPLEMENTATION.md`
- **Performance**: `PERFORMANCE_SETUP_QUICK_START.md`
- **Cloudflare Tunnel**: `CLOUDFLARE_TUNNEL_PREVIEW.md`

---

## Appendix: Environment Variables Reference

### Critical Variables (Production)

```bash
# WordPress Core
WP_ENV='production'
WP_HOME='https://example.com'
WP_SITEURL='${WP_HOME}/wp'
WP_DEBUG='false'
WP_DEBUG_DISPLAY='false'
WP_DEBUG_LOG='false'

# Database
DB_NAME='wordpress_prod'
DB_USER='wp_prod_user'
DB_PASSWORD='<strong-password>'
DB_HOST='db.internal'
DB_PREFIX='wp_'

# Security (Generate from https://roots.io/salts.html)
AUTH_KEY='<unique-value>'
SECURE_AUTH_KEY='<unique-value>'
LOGGED_IN_KEY='<unique-value>'
NONCE_KEY='<unique-value>'
AUTH_SALT='<unique-value>'
SECURE_AUTH_SALT='<unique-value>'
LOGGED_IN_SALT='<unique-value>'
NONCE_SALT='<unique-value>'

# Cache
REDIS_HOST='redis.internal'
REDIS_PORT='6379'
REDIS_PASSWORD=''
REDIS_CACHE_DB='0'

# Payments (LIVE KEYS - Never commit)
STRIPE_TEST_PUBLIC_KEY='pk_live_...'  # Note: env var name for compatibility
STRIPE_TEST_SECRET_KEY='sk_live_...'
STRIPE_WEBHOOK_SECRET='whsec_live_...'

# Analytics
GA4_MEASUREMENT_ID='G-XXXXXXXXXX'
GA4_DEBUG_MODE='false'
META_PIXEL_ID='000000000000000'
CONSENT_BANNER_ENABLED='true'

# Search Engine Indexing
DISALLOW_INDEXING='false'  # Set to 'true' if site should not be indexed
```

---

## Sign-Off

**Launch Team**:
- [ ] Tech Lead: _________________ Date: _________
- [ ] DevOps: _________________ Date: _________
- [ ] QA Lead: _________________ Date: _________
- [ ] Product Manager: _________________ Date: _________

**Post-Launch Monitoring** (First 72 hours):
- [ ] Day 1: Issues checked: _________ Verification: _________
- [ ] Day 2: Issues checked: _________ Verification: _________
- [ ] Day 3: Issues checked: _________ Verification: _________

**Launch Status**: ☐ Successful ☐ Rolled Back ☐ Delayed

**Notes**: 

---

*Last Updated: 2024*
*For questions or updates, contact the development team*
