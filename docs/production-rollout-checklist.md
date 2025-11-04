# Production Rollout Checklist

Complete checklist for executing the production launch of NYCBedToday.

Print this out and use it during the launch window. Keep it accessible throughout the day.

---

## Pre-Launch Checklist (72 Hours Before)

### Code Quality
- [ ] All GitHub branches merged to `main`
- [ ] CI/CD pipeline passing (all checks green)
- [ ] Code review completed and approved
- [ ] No `console.log()` or `debugger` statements in code
- [ ] Git log reviewed for unexpected commits
- [ ] Last commit message is descriptive

### Testing
- [ ] All unit tests passing: `composer test`
- [ ] Format check passing: `npm run format:check`
- [ ] E2E tests pass on staging environment
- [ ] Responsive design tested on all breakpoints
- [ ] Cross-browser tested (Chrome, Safari, Firefox)
- [ ] Mobile testing completed (iOS, Android)

### Performance
- [ ] Lighthouse score > 75 (all categories)
- [ ] WebP images verified
- [ ] JS/CSS minified and versioned
- [ ] Critical CSS inlined
- [ ] Lazy loading configured
- [ ] Bundle size within budget

### Security
- [ ] SSL certificate obtained (Cloudflare Universal SSL)
- [ ] All secrets generated and secured
- [ ] API keys stored in CI secrets (not in code)
- [ ] Database password changed from defaults
- [ ] File permissions reviewed
- [ ] Database user has minimal required permissions

### Infrastructure
- [ ] Production server provisioned
- [ ] SSH access verified and tested
- [ ] Database created and populated
- [ ] Backup system configured
- [ ] Redis cache configured
- [ ] Nginx/web server configured
- [ ] SSL configured on origin (if using Full Strict)
- [ ] Log rotation configured

### Backups
- [ ] Database backup verified and restorable
- [ ] Uploads directory backed up
- [ ] Remote backup storage tested (S3, etc.)
- [ ] Backup restoration procedure documented
- [ ] Restore test completed on staging

---

## 24 Hours Before Launch

### Cloudflare Setup
- [ ] Domain added to Cloudflare
- [ ] Nameservers updated at registrar
- [ ] DNS propagation verified: `dig nycbedtoday.com +short`
- [ ] SSL mode set to "Full" or "Full (strict)"
- [ ] HTTP/2, HTTP/3, Brotli enabled
- [ ] Caching rules configured
- [ ] WAF rules enabled
- [ ] Bot management enabled
- [ ] Rate limiting configured

### DNS Records
- [ ] A record for apex domain points to server IP
- [ ] CNAME record for www points to apex
- [ ] TTL set appropriately (300-3600)
- [ ] DNS records propagated: `dig nycbedtoday.com`
- [ ] WWW redirect configured

### WordPress Configuration
- [ ] `.env.production` created with all required values
- [ ] Security keys generated and unique
- [ ] Database credentials set correctly
- [ ] Redis connection tested
- [ ] File permissions correct (644 files, 755 dirs)
- [ ] `.env` file permissions secured (600)
- [ ] Owner ownership set correctly (www-data:www-data)

### Stripe Configuration
- [ ] Live API keys obtained from Stripe Dashboard
- [ ] Webhook endpoint configured
- [ ] Webhook secret obtained
- [ ] Test payment processed successfully
- [ ] Apple Pay domain verified in Stripe
- [ ] Google Pay enabled
- [ ] Payment methods configured

### GitHub Secrets
- [ ] `STRIPE_LIVE_PUBLIC_KEY` added
- [ ] `STRIPE_LIVE_SECRET_KEY` added
- [ ] `STRIPE_WEBHOOK_SECRET` added
- [ ] Database password added (if using)
- [ ] All other environment secrets added
- [ ] Secrets tested in test deployment

### Analytics Setup
- [ ] GA4 property created
- [ ] GA4 Measurement ID obtained
- [ ] Meta Pixel created
- [ ] Meta Pixel ID obtained
- [ ] Google Search Console property created
- [ ] GSC verification token obtained
- [ ] Bing Webmaster verified (if using)
- [ ] Sentry configured (if using error tracking)

### Legal Pages
- [ ] Privacy Policy created and reviewed by legal
- [ ] Terms of Service created and reviewed by legal
- [ ] Refund Policy created and reviewed by legal
- [ ] Contact page created
- [ ] About page created (if needed)
- [ ] Links added to footer

### Monitoring & Alerts
- [ ] UptimeRobot monitor created
- [ ] Test alert sent to email
- [ ] Slack notifications configured
- [ ] Status page created
- [ ] Error logging configured
- [ ] Log monitoring setup (Papertrail, DataDog, etc.)
- [ ] Backup monitoring configured

### Documentation
- [ ] Rollback procedure documented
- [ ] Incident response plan reviewed
- [ ] Contact info updated
- [ ] Support team trained
- [ ] Monitoring dashboard accessible to team
- [ ] Status page URL shared with team

---

## 2 Hours Before Launch (Final Prep)

### Team Coordination
- [ ] All team members online and available
- [ ] Crisis communication channel active (Slack, etc.)
- [ ] Roles assigned (who will monitor, who will communicate)
- [ ] Incident response lead identified
- [ ] Backup technical person identified

### Final Checks
- [ ] Production server is responsive
- [ ] Database connection verified: `mysql -u user -p -h localhost -e "SELECT 1"`
- [ ] Redis cache responsive: `redis-cli ping`
- [ ] Web server (Nginx) configuration validated
- [ ] PHP is correctly installed and working
- [ ] All environment variables set correctly
- [ ] Latest backup taken

### Staging Final Test
- [ ] Full checkout flow tested on staging
- [ ] All analytics events verified firing
- [ ] Stripe test payment processed
- [ ] Email notifications working
- [ ] Mobile checkout tested
- [ ] Performance acceptable (< 3s load time)

### Documentation & Access
- [ ] Production server SSH access tested
- [ ] Cloudflare dashboard accessible
- [ ] Stripe dashboard accessible
- [ ] GA4 dashboard accessible
- [ ] Status page accessible and updatable
- [ ] Team has all necessary access credentials

---

## Launch Window (30 Minutes)

### 0-5 Minutes: DNS & Connectivity

**Person Responsible: [Name]**

- [ ] Enable DNS pointing to production server in Cloudflare
- [ ] Wait 30 seconds
- [ ] Verify DNS resolution: `dig nycbedtoday.com +short` (should show server IP)
- [ ] Test site loads: `curl -I https://nycbedtoday.com` (should return 200 and green padlock)
- [ ] Ping server: `ping nycbedtoday.com` (should respond)
- [ ] Post to team Slack: "✅ DNS live and resolving"

### 5-10 Minutes: Site Functionality

**Person Responsible: [Name]**

- [ ] Homepage loads completely in browser
- [ ] Check for SSL errors (should be green padlock)
- [ ] No console errors in DevTools
- [ ] Verify all images load
- [ ] Check mobile responsiveness
- [ ] Verify header/navigation renders correctly
- [ ] Verify footer content displays
- [ ] Post to team Slack: "✅ Site loading correctly"

### 10-15 Minutes: Key Features

**Person Responsible: [Name]**

- [ ] Browse to shop page
- [ ] Add item to cart
- [ ] Navigate to checkout
- [ ] Verify checkout form appears
- [ ] Verify Stripe payment form embedded (if using embedded form)
- [ ] Verify Apple Pay button visible (on iOS if available)
- [ ] Verify Google Pay button visible (on Android if available)
- [ ] Do NOT complete transaction (save for next step)
- [ ] Post to team Slack: "✅ Checkout flow operational"

### 15-20 Minutes: Analytics & Tracking

**Person Responsible: [Name]**

- [ ] Open GA4 Real-time Dashboard
- [ ] Verify page_view event appears (may take 30 sec - 1 min)
- [ ] Check browser DevTools → Network for gtag requests
- [ ] Verify Meta Pixel firing: check Facebook Ads Manager → Events Manager
- [ ] Check browser DevTools → Console for any tracking errors
- [ ] Post to team Slack: "✅ Analytics tracking live"

### 20-25 Minutes: Error Monitoring

**Person Responsible: [Name]**

- [ ] SSH to production server
- [ ] Check debug log for errors: `tail -50 web/app/debug.log`
- [ ] Check system resources: `free -m`, `df -h`
- [ ] Verify Redis cache is working: `redis-cli ping` (should return PONG)
- [ ] Check Nginx error log: `tail -50 /var/log/nginx/error.log`
- [ ] Verify no critical PHP errors
- [ ] Post to team Slack: "✅ Error logs clean, system healthy"

### 25-30 Minutes: Business Validation

**Person Responsible: [Name]**

- [ ] Complete test transaction with Stripe test card
  - Card: 4242 4242 4242 4242
  - Expiry: Any future date
  - CVC: Any 3 digits
- [ ] Verify order created in WooCommerce admin
- [ ] Check order status is "pending payment" or "processing"
- [ ] Verify Stripe shows transaction in dashboard
- [ ] Verify order confirmation email received
- [ ] Check for Stripe webhook delivery
- [ ] Post to team Slack: "✅ Payment flow working end-to-end"

### 30 Minutes: Launch Complete

- [ ] ✅ All checks passed
- [ ] Update status page: "✅ NYCBedToday is live!"
- [ ] Post announcement to social media
- [ ] Notify stakeholders/clients
- [ ] Begin post-launch monitoring
- [ ] Document any issues for retrospective

**Post to team Slack**: "🚀 LAUNCH COMPLETE - Monitoring for 24 hours"

---

## First Hour Post-Launch Monitoring

**Person Responsible: [Name]** - Monitor continuously

- [ ] Every 5 minutes: Refresh homepage, verify it loads
- [ ] Every 10 minutes: Check error logs
- [ ] Every 5 minutes: Check uptime service for alerts
- [ ] Every 15 minutes: Check GA4 for unusual traffic
- [ ] Every 15 minutes: Check Stripe dashboard for payment issues
- [ ] Monitor team Slack for user issues

### If Alert Triggered During This Hour

1. **Immediately**:
   - [ ] Post to Slack that you're investigating
   - [ ] Assess severity (critical vs. minor)

2. **Critical Issues** (site down, checkout broken, payment errors):
   - [ ] Execute rollback plan (see production-launch.md)
   - [ ] Notify team immediately
   - [ ] Update status page

3. **Minor Issues** (slow page, missing image, etc.):
   - [ ] Investigate root cause
   - [ ] Attempt fix if straightforward
   - [ ] Document for later review
   - [ ] If unresolved, escalate

---

## First 24 Hours: Continued Monitoring

**Rotation: [Names of team members on 24-hour shifts]**

### Hourly Checks (First 6 Hours)
- [ ] Site accessibility (curl, browser)
- [ ] Error logs checked
- [ ] Performance acceptable (< 3s TTFB)
- [ ] No payment processing errors
- [ ] No database errors

### Every 4 Hours (Next 18 Hours)
- [ ] Run full health check
- [ ] Review analytics trends
- [ ] Check backup completion
- [ ] Verify no unusual errors

### Daily Metrics to Track
- [ ] Uptime: _______%
- [ ] Error count: ______
- [ ] Page load time (avg): ______s
- [ ] Transaction count: ______
- [ ] Transaction success rate: _____%
- [ ] Support tickets: ______

---

## Rollback Procedure (If Needed)

If critical issues occur that cannot be resolved:

1. **Notify team immediately** (in Slack, by phone)

2. **Execute rollback**:
   ```bash
   ssh user@nycbedtoday.com
   cd /var/www/app
   
   # Stop services if needed
   docker compose down
   # or
   systemctl stop php-fpm nginx mysql
   
   # Revert to previous code version
   git log --oneline -5
   git reset --hard <previous-commit-hash>
   git clean -fd
   
   # Restore database if needed
   # (use backup procedure from production-launch.md)
   
   # Restart services
   docker compose up -d
   # or
   systemctl start mysql nginx php-fpm
   
   # Clear caches
   make wp CMD='cache flush'
   make wp CMD='redis flush'
   
   # Verify
   curl -I https://nycbedtoday.com
   ```

3. **Restore DNS** (if needed):
   - Update Cloudflare to point to previous server
   - Update A record to previous IP

4. **Communicate**:
   - Post to Slack: "Rollback initiated - reverting to [previous-version]"
   - Update status page
   - Notify affected users

5. **Document**:
   - What went wrong?
   - How was it fixed?
   - What should be done differently next time?

---

## Post-Launch (Day 2+)

### Daily Tasks
- [ ] Review error logs for patterns
- [ ] Monitor performance metrics
- [ ] Check backup completion
- [ ] Review payment transactions
- [ ] Monitor social media for issues

### Weekly Tasks (First 2 Weeks)
- [ ] Analyze user behavior (GA4)
- [ ] Review core web vitals
- [ ] Check database optimization
- [ ] Review security logs
- [ ] Plan first hotfix if needed

### Retrospective Meeting (1 Week)
- [ ] What went well?
- [ ] What could be improved?
- [ ] Lessons learned?
- [ ] Update procedures based on feedback

---

## Emergency Contacts

**Keep phone numbers accessible during launch**

| Role | Name | Phone | Email |
|------|------|-------|-------|
| Technical Lead | | | |
| Backup Lead | | | |
| DevOps | | | |
| Product Manager | | | |
| Support Lead | | | |
| Executive | | | |

---

## Important URLs

- Production Site: https://nycbedtoday.com
- Cloudflare Dashboard: https://dash.cloudflare.com
- Stripe Dashboard: https://dashboard.stripe.com
- GA4 Dashboard: https://analytics.google.com
- Status Page: (URL TBD)
- Server IP: ______________________
- SSH Command: `ssh user@nycbedtoday.com`

---

## Notes

(Space for real-time notes during launch)

```
[Notes area for launch team]
```

