# Operations Runbook

Quick reference guide for common operational tasks and troubleshooting.

## Daily Operations

### Database Backups

**Automated Backup**:

```bash
# Using WP-CLI
make wp CMD='db export backup-$(date +%Y%m%d).sql'

# Upload to storage
# Keep daily backups for 7 days
# Keep weekly backups for 4 weeks
```

**Restore from Backup**:

```bash
# Stop WordPress services
make down

# Restore database
docker compose exec db mysql -u wordpress -pwordress wordpress < backup-20241215.sql

# Clear cache
make up
make wp CMD='cache flush'
```

### Cache Management

**Clear All Cache**:

```bash
# WordPress cache
make wp CMD='cache flush'

# Redis cache
docker compose exec redis redis-cli FLUSHALL

# Cloudflare cache (if using)
# Manual: Cloudflare Dashboard → Caching → Purge Cache
```

**Clear Specific Cache**:

```bash
# Clear post cache
make wp CMD='cache flush --all'

# Clear transients
make wp CMD='transient delete-all'

# Clear object cache
docker compose exec redis redis-cli FLUSHDB
```

### Log Monitoring

**View Error Logs**:

```bash
# WordPress debug.log
tail -f web/app/debug.log

# All container logs
make logs

# Specific service
make logs
docker compose logs php
docker compose logs nginx
docker compose logs db
```

**Watch for Errors**:

```bash
# Real-time error monitoring
make logs | grep -i error

# Database connection errors
docker compose logs php | grep "database"

# Plugin errors
docker compose logs php | grep "Fatal"
```

## Maintenance Tasks

### Database Optimization

**Optimize Tables**:

```bash
make wp CMD='db optimize'
```

This:
- Removes fragmentation
- Rebuilds indexes
- Improves query performance
- Typically 5-15% improvement

**Cleanup**:

```bash
# Delete unused post revisions
make wp CMD='post delete --post_type=revision --force'

# Delete spam comments
make wp CMD='comment delete --status=spam --force'

# Clean orphaned options
make wp CMD='option get siteurl'  # This triggers cleanup
```

### Plugin Updates

**List Outdated Plugins**:

```bash
make wp CMD='plugin list --update=available'
```

**Update Plugins**:

```bash
# Update specific plugin
make wp CMD='plugin update plugin-name'

# Update all plugins
make wp CMD='plugin update --all'
```

**After Update**:
```bash
# Clear cache
make wp CMD='cache flush'

# Run health check
make healthcheck

# Monitor logs
tail -f web/app/debug.log
```

### Theme Updates

**Update Child Theme**:

```bash
# Edit in web/app/themes/blocksy-child/
git add web/app/themes/blocksy-child/
git commit -m "chore: update theme"
git push origin main

# Staging deployment auto-triggers
# Production: manual deployment
```

**After Update**:
```bash
# Clear cache
make wp CMD='cache flush'

# Verify styling
# Visit site and check pages render correctly
```

## Performance Tuning

### Monitor Performance

**Database Queries**:

```bash
# Enable query logging (development only)
# In .env: WP_DEBUG_LOG=true WP_DEBUG=true

# View slow queries
tail -100 web/app/debug.log | grep "Slow"
```

**Memory Usage**:

```bash
# Check container memory
docker stats

# PHP memory limit
make wp CMD='eval "echo WP_MEMORY_LIMIT;"'
```

**Page Load Time**:

```bash
# Check Lighthouse
# Use Chrome DevTools → Lighthouse tab
# Or https://pagespeed.web.dev/

# Monitor Core Web Vitals
# Google Search Console → Core Web Vitals report
```

### Optimize Performance

**Disable Unnecessary Plugins**:

```bash
# List all plugins
make wp CMD='plugin list'

# Deactivate unused plugins
make wp CMD='plugin deactivate plugin-name'
```

**Redis Configuration**:

```bash
# Verify Redis is working
docker compose exec redis redis-cli ping
# Should respond: PONG

# Check memory usage
docker compose exec redis redis-cli info memory
```

**Image Optimization**:

1. Compress images before upload
2. Use WebP format where supported
3. Set appropriate image dimensions

**Code Minification**:

```bash
# Build minified assets
npm run build

# Deploy minified files
# Verify in production
```

## Monitoring & Alerts

### Health Check

```bash
# Run health check
make healthcheck

# Verbose output
./scripts/healthcheck.sh --verbose
```

Verifies:
- ✓ All containers running
- ✓ Database accessible
- ✓ WordPress responsive
- ✓ Redis available
- ✓ Disk space adequate

### Uptime Monitoring

**Setup UptimeRobot**:
1. Go to https://uptimerobot.com
2. Create monitor for site URL
3. Set check frequency (5 minutes)
4. Add alert recipients

**Check Status**:

```bash
# Test site availability
curl -I https://example.com
# Should return 200 OK

# Test API endpoints
curl https://example.com/wp-json/wp/v2/posts
# Should return valid JSON
```

### Disk Space Monitoring

**Check Usage**:

```bash
# Overall usage
df -h

# Docker volumes
docker volume ls

# Specific directory
du -sh web/app/uploads/
```

**Cleanup if Needed**:

```bash
# Delete old uploads (be careful!)
find web/app/uploads -type f -mtime +90 -delete

# Clear old backups
find . -name "backup-*.sql" -mtime +30 -delete

# Docker cleanup
docker system prune --volumes
```

## Troubleshooting

### Site Down

**Quick Fix**:

```bash
# 1. Check containers running
docker compose ps

# 2. Restart all services
make restart

# 3. Check logs
make logs

# 4. Run health check
make healthcheck
```

**Database Issues**:

```bash
# Test connection
docker compose exec db mysql -u wordpress -pwordpress -e "SELECT 1"

# Restart database
docker compose restart db
docker compose logs db

# Restore from backup if corrupted
```

**PHP Errors**:

```bash
# Check PHP logs
docker compose logs php

# Restart PHP
docker compose restart php

# Check syntax
docker compose exec php php -l web/index.php
```

**Nginx Errors**:

```bash
# Check nginx config
docker compose exec nginx nginx -t

# Restart nginx
docker compose restart nginx

# Check logs
docker compose logs nginx
```

### Slow Performance

**Diagnose**:

```bash
# Check resource usage
docker stats

# Check top processes
docker compose exec php ps aux

# Check database performance
make wp CMD='db tables with --format=csv --size'
```

**Fixes**:

1. Clear cache: `make wp CMD='cache flush'`
2. Optimize database: `make wp CMD='db optimize'`
3. Disable unnecessary plugins
4. Increase PHP memory limit in docker/.php/php.ini
5. Check for memory leaks in logs

### Memory Issues

**Check Usage**:

```bash
# Container memory
docker stats

# WordPress memory
make wp CMD='eval "echo function_exists('"'"'memory_get_peak_usage'"'"') ? memory_get_peak_usage() / 1024 / 1024 : '"'"'N/A'"'"';"'
```

**Solutions**:

```bash
# Increase PHP memory (docker/php/php.ini)
memory_limit = 256M  # Increase if needed

# Rebuild container
docker compose up --build

# Clear transients
make wp CMD='transient delete-all'

# Uninstall unused plugins
```

### WooCommerce Issues

**Test Products**:

```bash
# List products
make wp CMD='wc product list'

# Check shop page
make wp CMD='eval "echo get_option('"'"'woocommerce_shop_page_id'"'"');"'
```

**Reset WooCommerce**:

```bash
# Deactivate and reactivate
make wp CMD='plugin deactivate woocommerce'
make wp CMD='plugin activate woocommerce'

# Clear WooCommerce cache
make wp CMD='cache flush'
```

## Deployment Checklist

### Before Staging Deployment

- [ ] All tests passing: `composer test && npm run format:check`
- [ ] Code reviewed
- [ ] Database backups configured
- [ ] Staging server ready
- [ ] SSH keys configured
- [ ] GitHub secrets set

### Staging Deployment

```bash
# Automatic (push to main)
git add .
git commit -m "feature: new feature"
git push origin main

# Monitor deployment
# GitHub → Actions → Deploy Staging

# Verify on staging
curl https://staging.example.com
```

### Before Production Deployment

- [ ] Staging verified and tested
- [ ] Team approval obtained
- [ ] Database backup created: `make wp CMD='db export backup-before-deploy.sql'`
- [ ] Rollback plan documented
- [ ] Monitoring/alerts active
- [ ] Customer support notified if needed

### Production Deployment

```bash
# Manual deployment (or webhook)
export PROD_HOST="production.example.com"
export PROD_PATH="/var/www/app"
export PROD_SSH_KEY="$(cat ~/.ssh/prod_deploy_key)"

bash scripts/deploy-staging.sh
```

### After Deployment

```bash
# 1. Verify site is up
curl -I https://example.com

# 2. Clear cache
ssh user@production.example.com
cd /var/www/app
wp cache flush

# 3. Monitor logs
tail -f web/app/debug.log

# 4. Verify analytics
# Check GA4 and Meta Pixel events

# 5. Smoke test
# Visit homepage, add to cart, checkout with test card
```

## Security Tasks

### Security Updates

**Check for Vulnerabilities**:

```bash
# Composer audit
make composer CMD='audit'

# NPM audit
npm audit

# WordPress security updates
make wp CMD='core check-update'
```

**Apply Updates**:

```bash
# Update WordPress core
make wp CMD='core update'

# Update all plugins
make wp CMD='plugin update --all'

# Update composer packages
make composer CMD='update'
```

### Access Control

**WordPress Users**:

```bash
# List users
make wp CMD='user list'

# Create admin
make wp CMD='user create username email@example.com --role=administrator'

# Change password
make wp CMD='user update admin_user --prompt=user_pass'

# Remove old users
make wp CMD='user delete username'
```

**Database Access**:

```bash
# Change database password
docker compose exec db mysql -u root -p

# CREATE NEW USER 'wordpress'@'%' IDENTIFIED BY 'new-password';
# GRANT ALL ON wordpress.* TO 'wordpress'@'%';
# FLUSH PRIVILEGES;
```

## Emergency Procedures

### Critical Issue

**Steps**:

1. **Assess**: Check logs, site status, error messages
2. **Notify**: Alert team and customers if needed
3. **Isolate**: Stop traffic if necessary (DNS, load balancer)
4. **Fix**: Apply temporary or permanent fix
5. **Verify**: Confirm site is working
6. **Communicate**: Update status to stakeholders
7. **Document**: Record issue and resolution

**Example - Site Down**:

```bash
# 1. Check status
make healthcheck

# 2. Restart services
make restart

# 3. Check logs
make logs | tail -50

# 4. If database issue, restore backup
docker compose down
docker compose exec db mysql... < backup.sql
docker compose up

# 5. Notify team
# Slack: "Site recovered from incident at 2:30 PM EST"

# 6. Post-incident review
# What caused it? How to prevent? Update runbook?
```

## Scheduled Tasks

### Daily

- [ ] Monitor error logs
- [ ] Check site availability
- [ ] Verify backups completed

### Weekly

- [ ] Database optimization: `make wp CMD='db optimize'`
- [ ] Review performance metrics
- [ ] Check for plugin updates

### Monthly

- [ ] Security audit: `make composer CMD='audit'`
- [ ] Full backup verification
- [ ] Update WordPress security keys (optional but recommended)
- [ ] Review analytics and performance

### Quarterly

- [ ] Major version updates
- [ ] Performance optimization review
- [ ] Security assessment
- [ ] Capacity planning

## Quick Command Reference

```bash
# Service Management
make up                          # Start all services
make down                        # Stop all services
make restart                     # Restart services
make logs                        # View logs
make shell                       # Access PHP container

# WordPress Operations
make wp CMD='option get siteurl'           # Check site URL
make wp CMD='plugin list'                  # List plugins
make wp CMD='cache flush'                  # Clear cache
make wp CMD='db optimize'                  # Optimize database

# Development
make install                     # Install dependencies
make bootstrap                   # Full setup
make healthcheck                 # Verify setup
make clean                       # Clean everything

# Monitoring
./scripts/healthcheck.sh --verbose          # Detailed health check
docker compose ps                           # Container status
docker stats                                # Resource usage
```

## Related Documentation

- [Architecture Guide](architecture.md) - System overview
- [Deployment Guide](deployment.md) - Staging and production deployment
- [Setup Guide](setup-local.md) - Local development environment
