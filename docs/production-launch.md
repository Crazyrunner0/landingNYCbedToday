# Production Launch Guide

Complete guide to preparing and executing the production launch for NYCBedToday.

## Table of Contents

1. [Pre-Launch Checklist](#pre-launch-checklist)
2. [Domain & DNS Setup](#domain--dns-setup)
3. [SSL & Security Configuration](#ssl--security-configuration)
4. [WordPress Production Setup](#wordpress-production-setup)
5. [Stripe Live Mode](#stripe-live-mode)
6. [Legal & Analytics](#legal--analytics)
7. [Monitoring & Backups](#monitoring--backups)
8. [Rollout Plan](#rollout-plan)
9. [Post-Launch Validation](#post-launch-validation)
10. [Incident Response](#incident-response)

---

## Pre-Launch Checklist

### Code & Quality
- [ ] All tests passing locally: `npm run format:check && composer test`
- [ ] Code reviewed and approved by team lead
- [ ] All GitHub issues/PRs merged to `main` branch
- [ ] Feature branches deleted
- [ ] No `console.log()` or debug statements in production code

### Staging Validation (48-72 hours before launch)
- [ ] Complete checkout flow tested with test Stripe card
- [ ] Apple Pay / Google Pay tested on checkout
- [ ] All forms validated (no spam/bot issues)
- [ ] Images optimized (WebP/AVIF verified)
- [ ] Performance tested: Lighthouse > 75
- [ ] Mobile responsiveness verified
- [ ] Cross-browser testing (Chrome, Safari, Firefox, Edge)
- [ ] Analytics events firing correctly
- [ ] Email notifications working (order confirmations, etc.)

### Infrastructure
- [ ] Production server provisioned and secured
- [ ] SSH keys configured and tested
- [ ] Database backups tested and verified
- [ ] SSL certificate obtained (Cloudflare Universal SSL)
- [ ] CDN/Cloudflare account created
- [ ] Monitoring/alerts configured
- [ ] Database connection verified
- [ ] Redis cache configured and tested

### Team Preparation
- [ ] Support team trained on common issues
- [ ] Monitoring dashboard accessible to all team members
- [ ] Incident response plan reviewed
- [ ] Rollback procedures documented
- [ ] Owner contact info updated
- [ ] Status page created (statuspage.io or similar)

---

## Domain & DNS Setup

### Step 1: Configure Cloudflare

1. **Add domain to Cloudflare**:
   - Go to https://dash.cloudflare.com
   - Click "Add site"
   - Enter `nycbedtoday.com`
   - Select Free plan (or paid if preferred)
   - Note the nameservers provided

2. **Update nameservers at registrar**:
   - Go to your domain registrar (GoDaddy, Namecheap, etc.)
   - Update nameservers to Cloudflare's nameservers
   - Wait 24-48 hours for propagation
   - Verify: `dig nycbedtoday.com +short`

### Step 2: Configure DNS Records

1. **Apex Domain (nycbedtoday.com)**:
   - Type: `A` record
   - Name: `@` (or root)
   - Content: `<YOUR_PRODUCTION_SERVER_IP>`
   - TTL: Auto
   - Proxy status: ☁️ Proxied (orange cloud)
   - Comment: "Production server"

2. **WWW Subdomain**:
   - Type: `CNAME` record
   - Name: `www`
   - Content: `nycbedtoday.com`
   - TTL: Auto
   - Proxy status: ☁️ Proxied (orange cloud)

3. **Mail Records (if needed)**:
   ```
   Type: MX
   Priority: 10
   Value: mail.nycbedtoday.com (or your mail provider)
   ```

### Step 3: Domain Configuration

In Cloudflare Dashboard:

1. **SSL/TLS**:
   - Mode: "Full (strict)" ⚠️ Requires origin SSL
   - Or use "Full" if origin doesn't have SSL

2. **Redirect www to apex (or vice versa)**:
   - Go to Rules → Page Rules
   - Create rule: `*www.nycbedtoday.com/*`
   - Action: "Forwarding URL"
   - Status code: "301 - Permanent Redirect"
   - Destination: `https://nycbedtoday.com$1`

3. **HSTS Preload (Optional)**:
   - Go to SSL/TLS → Edge Certificates
   - Enable "HSTS (HTTP Strict-Transport-Security)"
   - Wait 48 hours before submitting to HSTS preload list

4. **Verify propagation**:
   ```bash
   # Should return green checkmark in Cloudflare
   curl -I https://nycbedtoday.com
   # Should return 301 redirect
   curl -I http://www.nycbedtoday.com
   ```

---

## SSL & Security Configuration

### Step 1: Enable Cloudflare Universal SSL

1. In Cloudflare Dashboard → SSL/TLS:
   - ✅ Universal SSL is **always enabled** on free plan
   - Status should show: "Universal SSL certificate issued"
   - Validity: Auto-renewed

### Step 2: Configure TLS Settings

1. **Minimum TLS Version**: 1.2
   - Dashboard → SSL/TLS → Edge Certificates
   - Minimum TLS Version: 1.2

2. **TLS 1.3 Support**:
   - Automatically enabled
   - Supports fastest handshakes

3. **Enable HTTP/2 & HTTP/3**:
   - Dashboard → Speed → Protocol Optimization
   - ✅ HTTP/2 Prioritization (enabled)
   - ✅ HTTP/2 Server Push (enabled)  
   - ✅ HTTP/3 (QUIC) (enabled)

4. **Early Hints**:
   - Dashboard → Speed → Optimization
   - ✅ Early Hints (enabled)
   - Preloads critical resources

### Step 3: Enable Performance Features

1. **Brotli Compression**:
   - Dashboard → Speed → Optimization
   - ✅ Brotli (enabled)
   - Better compression than gzip

2. **Caching**:
   - Dashboard → Caching → Configuration
   - Browser Cache Expiration: 4 hours
   - Cache Level: "Cache Everything"
   - ✅ Cache Deception Armor (enabled)
   - ✅ Cache Tag (enabled for purging)

3. **Image Optimization**:
   - Dashboard → Speed → Optimization
   - ✅ Image Optimization (enabled)
   - ✅ Polish (Basic or Lossless if available)
   - ✅ Mirage (enabled)
   - ✅ WebP (enabled for supported browsers)

### Step 4: Enable WAF & Security

1. **Web Application Firewall (WAF)**:
   - Dashboard → Security → WAF
   - ✅ OWASP ModSecurity Core Rule Set (enabled)
   - ✅ Cloudflare Managed Ruleset (enabled)
   - ✅ Cloudflare OWASP Ruleset (enabled)

2. **DDoS Protection**:
   - Dashboard → Security → DDoS Protection
   - Sensitivity Level: "High" (for production)
   - ✅ Sensitivity levels are tuned for false positives

3. **Bot Management**:
   - Dashboard → Security → Bots
   - ✅ Definitely Automated (DA) (enabled)
   - ✅ Likely Automated (LA) (enabled)
   - Action: "Challenge"

4. **Rate Limiting**:
   - Dashboard → Security → Rate limiting
   - Create rules to prevent abuse:
   ```
   Rate: 100 requests per 10 seconds
   Match: All requests
   Action: Challenge
   ```

5. **Access Rules**:
   - Dashboard → Security → Firewall rules
   - Add any IP whitelist/blacklist as needed
   - Example: Block known spam IPs

### Step 5: HTTP → HTTPS Redirect

1. **Configure in Cloudflare**:
   - Dashboard → Visibility → Page Rules
   - Pattern: `http://nycbedtoday.com/*`
   - Action: "Always Use HTTPS"
   - ✅ Applies to all subdomains

2. **Verify in application.php**:
   - Already configured in `/config/application.php`
   - Handles `X-Forwarded-Proto` header

---

## WordPress Production Setup

### Step 1: Deploy .env.production

1. **Copy template**:
   ```bash
   cp .env.production /path/to/production/.env
   ```

2. **Fill in production values**:
   ```bash
   DB_PASSWORD=<strong-random-db-password>
   REDIS_HOST=localhost  # or redis container
   REDIS_PORT=6379
   
   # Security keys (generate: https://roots.io/salts.html)
   AUTH_KEY='unique-key-here'
   SECURE_AUTH_KEY='unique-key-here'
   LOGGED_IN_KEY='unique-key-here'
   NONCE_KEY='unique-key-here'
   AUTH_SALT='unique-salt-here'
   SECURE_AUTH_SALT='unique-salt-here'
   LOGGED_IN_SALT='unique-salt-here'
   NONCE_SALT='unique-salt-here'
   
   # Stripe Live Keys (from Stripe dashboard)
   STRIPE_LIVE_PUBLIC_KEY=pk_live_xxxxx
   STRIPE_LIVE_SECRET_KEY=sk_live_xxxxx
   STRIPE_WEBHOOK_SECRET=whsec_xxxxx
   
   # Analytics
   GA4_MEASUREMENT_ID=G-XXXXXXXXXX
   META_PIXEL_ID=xxxxxxxxxx
   GSC_VERIFICATION_TOKEN=xxxxx
   ```

3. **Secure permissions**:
   ```bash
   chmod 600 /path/to/production/.env
   chown www-data:www-data /path/to/production/.env
   ```

### Step 2: Configure Redis Object Cache

1. **Verify Redis is running**:
   ```bash
   redis-cli ping
   # Should respond: PONG
   ```

2. **Install WP Redis** (if not already):
   ```bash
   composer require wp-redis/wp-redis
   # or via plugins directory
   ```

3. **Activate plugin**:
   ```bash
   make wp CMD='plugin activate redis-cache'
   ```

4. **Verify object cache is working**:
   ```bash
   make wp CMD='redis-cache status'
   # Should show: Status: Connected
   ```

### Step 3: Configure WordPress Cron

1. **Disable WP-Cron in .env**:
   ```bash
   DISABLE_WP_CRON=true
   ```

2. **Add system cron job**:
   ```bash
   crontab -e
   # Add: */5 * * * * curl -s https://nycbedtoday.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
   # Or for better reliability:
   # */5 * * * * /usr/bin/php -r 'define("WP_USE_THEMES", false); require("/var/www/app/web/wp-load.php"); wp_remote_post(admin_url("admin-ajax.php?action=wp-cron"), array("blocking" => false, "sslverify" => apply_filters("https_local_ssl_verify", false)));'
   ```

3. **Verify cron is configured**:
   ```bash
   make wp CMD='cron event list'
   # Should show scheduled events
   ```

### Step 4: Configure Debug Logging

1. **Debug log location**:
   ```bash
   # Should be: web/app/debug.log
   ls -la web/app/debug.log
   chmod 644 web/app/debug.log
   ```

2. **Rotate logs to prevent disk fill**:
   ```bash
   # Add to crontab:
   # Daily at 2 AM: rotate if larger than 10MB
   0 2 * * * test -f /var/www/app/web/app/debug.log && tail -c 10485760 /var/www/app/web/app/debug.log > /var/www/app/web/app/debug.log.tmp && mv /var/www/app/web/app/debug.log.tmp /var/www/app/web/app/debug.log
   ```

### Step 5: Verify Production Configuration

```bash
# Test WordPress loads
curl -I https://nycbedtoday.com/wp-json/wp/v2/posts

# Test Redis cache
make wp CMD='redis-cache status'

# Test scheduled tasks
make wp CMD='cron event list'

# Check PHP version
make wp CMD='eval "echo phpversion();"'

# Verify plugins are active
make wp CMD='plugin list'
```

---

## Stripe Live Mode

### Step 1: Obtain Live API Keys

1. **Log into Stripe Dashboard**:
   - https://dashboard.stripe.com
   - Go to Developers → API Keys
   - Copy live keys (not test keys!)
   - **Live Public Key**: `pk_live_...`
   - **Live Secret Key**: `sk_live_...` (keep secret!)

2. **Get Webhook Secret**:
   - Go to Developers → Webhooks
   - Create new endpoint or copy existing:
   - **Endpoint URL**: `https://nycbedtoday.com/wp-json/wc-stripe/...`
   - Copy signing secret: `whsec_...`

### Step 2: Configure GitHub Secrets

Add to GitHub repository settings (Settings → Secrets and variables → Actions):

| Secret | Value | Source |
|--------|-------|--------|
| `STRIPE_LIVE_PUBLIC_KEY` | `pk_live_...` | Stripe Dashboard |
| `STRIPE_LIVE_SECRET_KEY` | `sk_live_...` | Stripe Dashboard |
| `STRIPE_WEBHOOK_SECRET` | `whsec_...` | Stripe Webhooks |

### Step 3: Deploy to Production

1. **Via CI/CD**:
   - GitHub Actions will inject secrets into `.env` during deployment
   - Environment variables are set securely

2. **Manual deployment**:
   ```bash
   # SSH to production server
   ssh user@nycbedtoday.com
   
   # Update .env with live keys
   nano /var/www/app/.env
   
   # Clear cache
   cd /var/www/app
   make wp CMD='cache flush'
   ```

### Step 4: Configure Stripe Payment Methods

1. **Enable Payment Methods**:
   - Stripe Dashboard → Settings → Payment methods
   - ✅ Card (enabled)
   - ✅ Apple Pay (enabled)
   - ✅ Google Pay (enabled)
   - ✅ Link (enabled)

2. **Apple Pay Domain Verification**:
   - Go to Stripe Dashboard → Apple Pay Domain Verification
   - Add: `nycbedtoday.com`
   - Upload verification file: `.well-known/apple-developer-merchantid-domain-association`
   - Copy content from Stripe dashboard and place on server

3. **Google Pay Configuration**:
   - Google Pay is automatically enabled
   - No additional setup required

### Step 5: Test Live Payments

⚠️ **CRITICAL: Test in production environment only, use real test card**

1. **Use Stripe test card (works on live)**:
   ```
   Card: 4242 4242 4242 4242
   Expiry: Any future date
   CVC: Any 3 digits
   ```

2. **Test $0.50 payment**:
   - Go to https://nycbedtoday.com/checkout
   - Add cheapest product to cart
   - Proceed to checkout
   - Enter test card details
   - Submit payment
   - Verify payment appears in Stripe Dashboard → Payments

3. **Test Refund**:
   - In Stripe Dashboard → Payments
   - Click on transaction
   - Click "Refund"
   - Verify refund processes

4. **Test Apple Pay** (requires Apple device):
   - Go to https://nycbedtoday.com/checkout
   - Click "Apple Pay" button
   - Complete payment with Touch ID/Face ID
   - Verify in Stripe Dashboard

5. **Test Google Pay** (requires Android device or Chrome):
   - Go to https://nycbedtoday.com/checkout
   - Click "Google Pay" button
   - Complete payment
   - Verify in Stripe Dashboard

### Step 6: Configure Webhook Notifications

1. **Verify Stripe Webhooks**:
   - Stripe Dashboard → Developers → Webhooks
   - Endpoint: `https://nycbedtoday.com/wp-json/...`
   - ✅ Events: payment_intent.succeeded, payment_intent.payment_failed
   - Test webhook delivery

2. **Verify Order Emails**:
   - WooCommerce → Settings → Emails
   - ✅ New order emails enabled
   - ✅ Completion emails enabled
   - Test by placing order

---

## Legal & Analytics

### Step 1: Privacy & Terms Pages

1. **Create Privacy Policy**:
   - WordPress Admin → Pages → New
   - Title: "Privacy Policy"
   - Content: Include:
     - Data collection methods
     - Stripe payment processing
     - Google Analytics usage
     - Cookie information
     - GDPR/CCPA compliance statement
   - Publish as draft for legal review

2. **Create Terms of Service**:
   - WordPress Admin → Pages → New
   - Title: "Terms of Service"
   - Content: Include:
     - Limitation of liability
     - Product descriptions
     - Delivery terms
     - Return/refund policy
     - Dispute resolution
   - Publish as draft for legal review

3. **Create Refund Policy**:
   - WordPress Admin → Pages → New
   - Title: "Refund Policy"
   - Content: Detail refund procedures, timeframes, conditions
   - Link from checkout page

4. **Create Contact Page** (if not exists):
   - Include support email/form
   - Business address
   - Phone number if applicable

### Step 2: Consent Banner & Cookies

1. **Enable Consent Banner**:
   - Set in .env: `CONSENT_BANNER_ENABLED=true`

2. **Configure Banner** (assuming ConsentMgmt plugin):
   - WordPress Admin → Consent Settings
   - Required: Cookie banner visible
   - Privacy link: `/privacy-policy/`
   - Terms link: `/terms-of-service/`

3. **Set Privacy Policy Link**:
   - WordPress Admin → Settings → Privacy
   - Privacy policy page: select "Privacy Policy" page

### Step 3: Google Analytics 4 Setup

1. **Create GA4 Property**:
   - Go to https://analytics.google.com
   - Create new property: `nycbedtoday.com`
   - Create web data stream
   - Copy Measurement ID: `G-XXXXXXXXXX`

2. **Add to Production .env**:
   ```bash
   GA4_MEASUREMENT_ID=G-XXXXXXXXXX
   GA4_DEBUG_MODE=false  # Disable for production
   ```

3. **Verify GA4 Events**:
   - Visit https://nycbedtoday.com
   - Go to GA4 → Real-time → Overview
   - Confirm page_view event appears
   - Test checkout flow
   - Verify: purchase, add_to_cart events

4. **Set Up Conversions**:
   - GA4 → Configure → Conversions
   - Create conversion: "purchase"
   - Associated event: "purchase"
   - Value parameter: "value"

5. **Configure Google Search Console**:
   - Go to https://search.google.com/search-console
   - Add property: `https://nycbedtoday.com`
   - Verify via DNS or HTML file
   - Submit sitemap: `https://nycbedtoday.com/sitemap.xml`

### Step 4: Meta Pixel Setup

1. **Create Meta Business Account**:
   - https://business.facebook.com
   - Create pixel for NYCBedToday

2. **Get Pixel ID**:
   - Go to Data Sources → Pixels
   - Copy Pixel ID
   - Add to .env: `META_PIXEL_ID=123456789`

3. **Test Pixel Events**:
   - Visit site with browser
   - Check Meta Ads Manager → Events Manager
   - Verify: PageView event firing
   - Test checkout → verify Purchase event

### Step 5: Third-Party Verification

- [ ] GSC ownership verified
- [ ] Bing Webmaster verified (if desired)
- [ ] Yoast SEO configured with sitemap
- [ ] RankMath configured (if using)
- [ ] Facebook domain verified for conversions

---

## Monitoring & Backups

### Step 1: Uptime Monitoring

1. **Set Up UptimeRobot**:
   - Go to https://uptimerobot.com
   - Sign up / Log in
   - Create monitor:
     - Type: HTTP(s)
     - URL: `https://nycbedtoday.com`
     - Frequency: 5 minutes
     - Alert emails: (your-team-email)
   - Add 2-3 backup recipients

2. **Test Alerting**:
   - Temporarily stop web server
   - Verify alert email received
   - Restart server

### Step 2: Error Logging & Alerts

1. **Configure Error Log**:
   - Already set in `.env.production`
   - Location: `web/app/debug.log`
   - Monitored via: system log tools

2. **Setup Log Monitoring** (e.g., with Papertrail, DataDog, etc.):
   ```bash
   # Option 1: Use Papertrail (cloud logging)
   # Install agent, configure to monitor debug.log
   
   # Option 2: Setup local log rotation
   cat > /etc/logrotate.d/nycbedtoday << 'EOF'
   /var/www/app/web/app/debug.log {
       size 10M
       rotate 7
       compress
       delaycompress
       notifempty
       create 0644 www-data www-data
   }
   EOF
   ```

3. **Setup Sentry (Optional)**:
   - Go to https://sentry.io
   - Create project: WordPress
   - Copy DSN
   - Add to WordPress via Sentry plugin
   - Receive real-time error alerts

### Step 3: Database Backups

1. **Automated Daily Backup**:
   - Add to crontab (runs daily at 2 AM):
   ```bash
   0 2 * * * /home/backup/backup-database.sh
   ```

2. **Create backup script** (`/home/backup/backup-database.sh`):
   ```bash
   #!/bin/bash
   
   DB_NAME="nycbedtoday_prod"
   DB_USER="nycbed_prod"
   BACKUP_DIR="/home/backups/database"
   DATE=$(date +\%Y\%m\%d)
   
   # Create backup directory if not exists
   mkdir -p $BACKUP_DIR
   
   # Backup database
   mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME | gzip > $BACKUP_DIR/backup-$DATE.sql.gz
   
   # Upload to S3 (or other remote storage)
   aws s3 cp $BACKUP_DIR/backup-$DATE.sql.gz s3://your-backup-bucket/nycbedtoday/db/
   
   # Keep local backups for 7 days
   find $BACKUP_DIR -mtime +7 -delete
   
   # Log backup
   echo "Backup completed: $DATE" >> /var/log/nycbedtoday-backup.log
   ```

3. **Upload Backups**:
   - Upload to AWS S3, Google Cloud Storage, or similar
   - Retention: Daily for 30 days, Weekly for 3 months
   - Test restore procedures monthly

4. **Backup Uploads Directory**:
   - Add to crontab (weekly):
   ```bash
   0 3 * * 0 tar -czf /home/backups/uploads-$(date +\%Y\%m\%d).tar.gz /var/www/app/web/app/uploads && aws s3 cp /home/backups/uploads-*.tar.gz s3://your-backup-bucket/nycbedtoday/uploads/
   ```

### Step 4: Backup Restore Procedure

1. **Document restore steps**:
   ```bash
   # 1. Stop WordPress
   cd /var/www/app
   make down
   
   # 2. Restore database
   gunzip < /home/backups/database/backup-YYYYMMDD.sql.gz | mysql -u nycbed_prod -p nycbedtoday_prod
   
   # 3. Restore uploads (if needed)
   tar -xzf /home/backups/uploads-YYYYMMDD.tar.gz -C /
   
   # 4. Clear cache
   make up
   make wp CMD='cache flush'
   make wp CMD='redis flush'
   
   # 5. Verify site
   curl -I https://nycbedtoday.com
   ```

2. **Test restore monthly**:
   - Run backup to staging
   - Verify database restores
   - Verify site functionality

### Step 5: Configure Monitoring Dashboard

1. **Create monitoring dashboard** (UptimeRobot, DataDog, or similar):
   - Site uptime % (Target: 99.9%)
   - Response time (Target: < 2s)
   - Error rate (Target: < 0.1%)
   - Database query performance
   - Redis cache hit rate

2. **Set Alerting Thresholds**:
   - Site down: Alert immediately
   - Response time > 5s: Alert after 5 minutes
   - Error rate > 5%: Alert after 5 minutes
   - Disk space > 80%: Alert once daily

---

## Rollout Plan

### 48 Hours Before Launch

- [ ] Final staging validation completed
- [ ] All team members notified of timeline
- [ ] Monitoring/alerting verified
- [ ] Backup verified and restorable
- [ ] Support team ready
- [ ] Rollback plan reviewed
- [ ] Status page URL shared with team

### 24 Hours Before Launch

- [ ] DNS records created in Cloudflare (allow 24-48h for propagation)
- [ ] SSL certificate verified
- [ ] Production environment final check
- [ ] All secrets injected via CI/CD
- [ ] Final database backup taken
- [ ] Performance test: Lighthouse > 75

### 2 Hours Before Launch (Go Window)

- [ ] Final build deployed to staging
- [ ] E2E tests passing in staging
- [ ] All team members online and available
- [ ] Crisis communication channel open (Slack, etc.)

### Launch Window (30 minutes)

1. **0 min**: 
   - [ ] DNS points to production
   - [ ] Verify DNS propagation: `dig nycbedtoday.com`
   - [ ] Ping production server

2. **5 min**:
   - [ ] Site loads over HTTPS: `curl -I https://nycbedtoday.com`
   - [ ] Check for SSL errors
   - [ ] Verify Cloudflare is proxying

3. **10 min**:
   - [ ] Test homepage loads correctly
   - [ ] Analytics events firing (GA4)
   - [ ] Meta Pixel firing
   - [ ] API endpoints responding

4. **15 min**:
   - [ ] Add item to cart and proceed to checkout
   - [ ] Verify Stripe payment form loads
   - [ ] Test with Stripe test card (if available)

5. **20 min**:
   - [ ] Check error logs for issues
   - [ ] Monitor uptime service
   - [ ] Monitor social media for issues

6. **25 min**:
   - [ ] Performance metrics acceptable
   - [ ] No critical errors in logs

7. **30 min**:
   - [ ] ✅ Launch complete
   - [ ] Post to social media
   - [ ] Notify stakeholders

### Post-Launch Monitoring (First 24 Hours)

- [ ] Hourly monitoring checks (1st 6 hours)
- [ ] Check error logs every 15 minutes
- [ ] Monitor transaction volume
- [ ] Watch uptime service for alerts
- [ ] Monitor social media for issues
- [ ] Ensure 24/7 availability of technical team

---

## Post-Launch Validation

### Technical Checks

- [ ] HTTPS working: Green padlock visible
- [ ] All pages load under 3 seconds
- [ ] Mobile responsive on all breakpoints
- [ ] No console errors in browser
- [ ] No PHP warnings in debug.log
- [ ] Database queries optimized (< 100ms)
- [ ] Images optimized (WebP served)
- [ ] CSS/JS minified and cached

### SEO Checks

- [ ] Homepage indexed by Google
- [ ] Search Console showing no crawl errors
- [ ] XML Sitemap submitted
- [ ] Meta tags correct (title, description)
- [ ] Open Graph tags present
- [ ] Canonical URLs correct

### Performance Checks

- [ ] Lighthouse score > 80
- [ ] First Contentful Paint < 1.5s
- [ ] Largest Contentful Paint < 2.5s
- [ ] Cumulative Layout Shift < 0.1
- [ ] Time to Interactive < 3.5s

### Business Checks

- [ ] All products visible on shop page
- [ ] Product descriptions display correctly
- [ ] Pricing displays correctly
- [ ] Add to cart functionality works
- [ ] Checkout flow completes
- [ ] Order confirmation email received
- [ ] Order visible in WooCommerce admin

### Analytics Checks

- [ ] GA4 page views tracked
- [ ] Meta Pixel events firing
- [ ] Conversion tracking working
- [ ] Goals configured and firing
- [ ] Event parameters captured
- [ ] GSC receiving data

### Payment Checks

- [ ] Stripe payment gateway working
- [ ] Test payment processed successfully
- [ ] Order status updated to "processing"
- [ ] Webhook notifications received
- [ ] Apple Pay visible on mobile (iOS)
- [ ] Google Pay visible on mobile (Android)
- [ ] Payment refund processed successfully

### Security Checks

- [ ] SSL certificate valid (no errors)
- [ ] TLS 1.2 minimum enforced
- [ ] HSTS header present
- [ ] X-Frame-Options header present
- [ ] X-Content-Type-Options header present
- [ ] WAF rules active
- [ ] Rate limiting active
- [ ] No sensitive data in URLs

---

## Incident Response

### If Site is Down

1. **First 5 minutes**:
   - [ ] Check if server is responding: `ping nycbedtoday.com`
   - [ ] Check if DNS is resolving: `dig nycbedtoday.com`
   - [ ] Check Cloudflare status: https://www.cloudflarestatus.com
   - [ ] Post message to status page: "We're investigating an issue"

2. **Next 15 minutes**:
   - [ ] SSH to production server
   - [ ] Check if services running: `docker compose ps` or `systemctl status`
   - [ ] Check system resources: `free -m`, `df -h`
   - [ ] Check logs: `tail -f web/app/debug.log`

3. **Troubleshooting**:
   - [ ] Restart PHP-FPM: `systemctl restart php-fpm`
   - [ ] Restart Nginx: `systemctl restart nginx`
   - [ ] Restart MySQL: `systemctl restart mysql`
   - [ ] Clear all caches: `make wp CMD='cache flush'`
   - [ ] Clear Redis: `redis-cli FLUSHALL`

4. **If not resolved in 30 minutes**:
   - [ ] Execute rollback plan (see below)
   - [ ] Restore from database backup
   - [ ] Notify all stakeholders

### Rollback Plan

1. **Revert code**:
   ```bash
   cd /var/www/app
   git log --oneline -5  # See recent commits
   git reset --hard <previous-commit>
   git clean -fd
   ```

2. **Restart services**:
   ```bash
   systemctl restart php-fpm nginx mysql redis
   # or
   docker compose restart
   ```

3. **Clear caches**:
   ```bash
   make wp CMD='cache flush'
   make wp CMD='redis flush'
   ```

4. **Verify**:
   ```bash
   curl -I https://nycbedtoday.com
   make healthcheck
   ```

### If Checkout is Failing

1. **Check Stripe connection**:
   - [ ] Test Stripe test mode still works
   - [ ] Verify Stripe keys are correct
   - [ ] Check Stripe webhook delivery

2. **Check payment gateway**:
   - [ ] WooCommerce → Settings → Payments
   - [ ] Verify Stripe is enabled
   - [ ] Test payment form renders
   - [ ] Check for JavaScript errors

3. **If Stripe keys are wrong**:
   ```bash
   # Update .env with correct keys
   nano .env
   # Restart services
   systemctl restart php-fpm
   # Clear cache
   make wp CMD='cache flush'
   ```

### If Analytics Not Firing

1. **Check GA4 tag**:
   - [ ] Browser DevTools → Console
   - [ ] Verify gtag is loaded
   - [ ] Check network requests to analytics

2. **Check Meta Pixel**:
   - [ ] Browser DevTools → Network
   - [ ] Look for pixel requests to facebook.com
   - [ ] Verify pixel ID in HTML

3. **Check environment variable**:
   ```bash
   grep GA4_MEASUREMENT_ID /path/to/.env
   grep META_PIXEL_ID /path/to/.env
   ```

---

## Contacts & Escalation

### Technical Team
- **Lead**: [Name] - [Email] - [Phone]
- **Backup**: [Name] - [Email] - [Phone]

### Cloudflare Support
- Account: [Email]
- Ticket URL: https://dash.cloudflare.com/support

### Stripe Support
- Account: [Email]
- Dashboard: https://dashboard.stripe.com

### Hosting/Server Support
- Provider: [Name]
- Phone: [Number]
- Support URL: [URL]

---

## Post-Launch (1-2 Weeks)

- [ ] Monitor performance metrics
- [ ] Analyze user behavior (GA4)
- [ ] Review error logs for patterns
- [ ] Run database optimization
- [ ] Review and process customer feedback
- [ ] Plan feature releases
- [ ] Schedule post-launch review meeting

