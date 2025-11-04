# Deployment Guide

Complete guide to deploying to staging and production environments.

## Overview

The deployment architecture consists of:
1. **Local Development** - Changes made and tested locally
2. **GitHub Repository** - Code pushed to `main` or `staging` branch
3. **CI/CD Pipeline** - Automated tests and quality checks via GitHub Actions
4. **Staging Server** - Auto-deployed preview environment
5. **Production Server** - Manual or automated deployment

## Staging Deployment (Automated)

### Setup (One-Time Configuration)

#### Step 1: Generate SSH Key

Create a dedicated SSH key for deployments:

```bash
ssh-keygen -t rsa -b 4096 -f ~/.ssh/staging_deploy_key -C "staging-deployment"
```

This creates:
- `~/.ssh/staging_deploy_key` - Private key (for GitHub)
- `~/.ssh/staging_deploy_key.pub` - Public key (for staging server)

#### Step 2: Configure Staging Server

On your staging server:

1. **Add public key to authorized_keys**:
```bash
cat ~/.ssh/staging_deploy_key.pub | ssh user@staging.example.com \
  "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"
chmod 600 ~/.ssh/authorized_keys
```

2. **Create deployment directory**:
```bash
mkdir -p /var/www/app
chmod 755 /var/www/app
```

3. **Configure web server** (nginx example):
```nginx
server {
    listen 80;
    server_name staging.example.com;
    root /var/www/app/web;
    
    index index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

4. **Setup .env on staging server**:
```bash
cd /var/www/app
cp .env.example .env

# Edit .env with staging values:
# - Set WP_ENV=staging
# - Database credentials for staging
# - Analytics keys
# - Disable file modifications: DISALLOW_FILE_MODS=true
# - Enable cache headers
```

#### Step 3: Configure GitHub Secrets

In GitHub repository settings:

1. Go **Settings → Secrets and variables → Actions**
2. Create new secrets:

| Secret | Value | Example |
|--------|-------|---------|
| `STAGING_HOST` | Staging server hostname | `staging.example.com` |
| `STAGING_PATH` | Deployment path on server | `/var/www/app` |
| `STAGING_SSH_KEY` | SSH private key content | `-----BEGIN RSA PRIVATE KEY-----...` |

**How to copy SSH key to GitHub**:
```bash
cat ~/.ssh/staging_deploy_key | pbcopy  # macOS
# or
cat ~/.ssh/staging_deploy_key | xclip -i -selection clipboard  # Linux
```

### Deploying to Staging

#### Automatic Deployment

Push code to `main` branch:

```bash
git add .
git commit -m "Feature: Add awesome feature"
git push origin main
```

The deployment pipeline automatically:
1. Runs all CI checks (validate, code-quality, format-check, docker)
2. Builds frontend assets
3. Deploys to staging via rsync/SSH
4. Clears cache on staging

**Monitor deployment**: GitHub → Actions → Deploy Staging workflow

#### Manual Deployment

Trigger deployment manually from GitHub:

1. Go to **Actions** tab
2. Select **Deploy Staging** workflow
3. Click **Run workflow**
4. Select `main` branch
5. Click green **Run workflow** button

#### Dry-Run Mode

Test deployment without making changes:

```bash
# Test locally
export STAGING_HOST="staging.example.com"
export STAGING_PATH="/var/www/app"
export STAGING_SSH_KEY="$(cat ~/.ssh/staging_deploy_key)"
export DRY_RUN=true

bash scripts/deploy-staging.sh
```

Output shows:
- Files that would be created/updated/deleted
- Total changes without applying them

### What Gets Deployed

**Included**:
- ✅ PHP source code (`web/app/`)
- ✅ Theme files (`web/app/themes/blocksy-child/`)
- ✅ Custom plugins (`web/app/plugins/nycbedtoday-*`)
- ✅ Configuration files
- ✅ Built assets (webpack bundles)
- ✅ Composer dependencies (`vendor/`)

**Excluded**:
- ❌ User uploads (`web/app/uploads/`)
- ❌ Cache directory (`web/app/cache/`)
- ❌ Database
- ❌ Git files (`.git/`, `.github/`)
- ❌ Development files (`node_modules/`, `.env`)
- ❌ Docker files (`docker-compose.yml`)

## Production Deployment

### Pre-Deployment Checklist

Before deploying to production:

- [ ] All tests pass locally: `composer test && npm run format:check`
- [ ] Code reviewed and merged to `main`
- [ ] Staging environment tested and approved
- [ ] Database backups configured
- [ ] SSL certificate configured
- [ ] CDN/Cloudflare configured (if using)
- [ ] DNS points to production server
- [ ] Monitoring and alerts configured

### Setup (One-Time)

Similar to staging:

1. Generate SSH key: `ssh-keygen -t rsa -b 4096 -f ~/.ssh/prod_deploy_key`
2. Configure production server
3. Setup `.env` with production values
4. Configure web server (nginx/Apache)
5. Test connectivity and permissions

### Deployment Methods

#### Option 1: Manual rsync/SSH (Safest)

For first-time or critical deployments:

```bash
# From local machine
export PROD_HOST="production.example.com"
export PROD_PATH="/var/www/app"
export PROD_SSH_KEY="$(cat ~/.ssh/prod_deploy_key)"

bash scripts/deploy-staging.sh  # Can reuse script, just update env vars
```

Or use rsync directly:

```bash
rsync -avz --delete \
  --exclude-from=.gitignore \
  --exclude=.env \
  --exclude=node_modules \
  --exclude=vendor \
  -e "ssh -i ~/.ssh/prod_deploy_key" \
  ./ user@production.example.com:/var/www/app/
```

#### Option 2: GitHub Actions Workflow (Recommended)

Create `.github/workflows/deploy-production.yml`:

```yaml
name: Deploy Production

on:
  workflow_dispatch:  # Manual trigger only
  push:
    branches: [main]
    paths-ignore:
      - 'docs/**'
      - '**.md'

jobs:
  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    environment: production  # Require approval

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Deploy to production
        env:
          PROD_HOST: ${{ secrets.PROD_HOST }}
          PROD_PATH: ${{ secrets.PROD_PATH }}
          PROD_SSH_KEY: ${{ secrets.PROD_SSH_KEY }}
          DRY_RUN: false
        run: bash scripts/deploy-staging.sh  # Use existing script
```

Then add secrets to GitHub:
- `PROD_HOST`
- `PROD_PATH`
- `PROD_SSH_KEY`

#### Option 3: Container Registry Deployment

Push Docker image to registry and pull on production:

```bash
# Build and push image
docker build -t registry.example.com/wordpress:latest .
docker push registry.example.com/wordpress:latest

# On production server
docker pull registry.example.com/wordpress:latest
docker-compose -f docker-compose.prod.yml up -d
```

### Production Environment Configuration

Set these in `.env` on production:

```bash
# Core
WP_ENV=production
WP_DEBUG=false

# URLs
WP_HOME=https://example.com
WP_SITEURL=${WP_HOME}/wp

# Security
DISALLOW_FILE_MODS=true
DISALLOW_UNFILTERED_HTML=true

# Cache
REDIS_HOST=redis
REDIS_PORT=6379

# Database (strong credentials!)
DB_HOST=db
DB_NAME=wordpress_prod
DB_USER=wordpress_prod
DB_PASSWORD=<strong-random-password>

# Analytics
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
META_PIXEL_ID=xxxxxxxxxx

# Stripe (Live keys)
STRIPE_PUBLISHABLE_KEY=pk_live_xxxxx
STRIPE_SECRET_KEY=sk_live_xxxxx
```

### Post-Deployment Tasks

1. **Verify Site**:
```bash
curl https://example.com/wp-json/wp/v2/posts
# Should return JSON without errors
```

2. **Clear Cache**:
```bash
ssh user@production.example.com
cd /var/www/app
wp cache flush
wp redis flush
```

3. **Check Logs**:
```bash
ssh user@production.example.com
tail -f /var/www/app/web/app/debug.log
# Monitor for errors
```

4. **Monitor Performance**:
- Check Lighthouse scores
- Monitor PageSpeed Insights
- Check error logs in Google Search Console
- Monitor uptime monitoring service

## Cloudflare Integration (Optional)

### Setup Cloudflare

1. Add domain to Cloudflare
2. Update nameservers at domain registrar
3. Wait for DNS propagation (24-48 hours)

### Cache Purge on Deploy

Automatically purge Cloudflare cache after deployment:

```bash
# Add GitHub secrets
CLOUDFLARE_API_TOKEN
CLOUDFLARE_ZONE_ID

# In .github/workflows/deploy-staging.yml, add:
- name: Purge Cloudflare cache
  run: |
    curl -X POST "https://api.cloudflare.com/client/v4/zones/${{ secrets.CLOUDFLARE_ZONE_ID }}/purge_cache" \
      -H "Authorization: Bearer ${{ secrets.CLOUDFLARE_API_TOKEN }}" \
      -H "Content-Type: application/json" \
      --data '{"files":["https://staging.example.com/*"]}'
```

### Cloudflare Tunnel for Preview

Share a public preview URL without deploying:

```bash
make preview.up
# Returns: https://xxx.trycloudflare.com

# Update .env.local
WP_HOME=https://xxx.trycloudflare.com
WP_SITEURL=${WP_HOME}/wp
DISALLOW_INDEXING=true

make wp CMD='cache flush'

# Share URL for live preview
make preview.down  # Stop when done
```

## Rollback Procedures

### Rollback via Git

If deployment causes issues:

```bash
# Revert last commit and deploy
git revert HEAD
git push origin main

# Staging deployment will automatically trigger
```

### Manual Rollback

SSH to server and revert:

```bash
ssh user@production.example.com
cd /var/www/app

# View recent deployments
git log --oneline -5

# Revert to previous version
git reset --hard <commit-hash>

# Restart PHP if needed
sudo systemctl restart php-fpm

# Clear cache
wp cache flush
```

### Database Rollback

If you have backups:

```bash
# Restore from backup
mysqldump -u user -p database_backup.sql | mysql -u user -p wordpress_prod

# Or using WP-CLI
wp db import database_backup.sql

# Clear caches
wp cache flush
```

## Monitoring & Alerts

### Health Checks

After deployment, verify:

```bash
# HTTP status
curl -I https://example.com
# Should return 200 OK

# WordPress installation
curl https://example.com/wp-json/wp/v2/posts
# Should return valid JSON

# Check plugins
wp plugin list
# Should show no errors

# Database queries
wp db query "SELECT COUNT(*) as post_count FROM wp_posts"
```

### Error Monitoring

Setup error tracking:

1. **PHP Errors**: Monitor `web/app/debug.log`
2. **Server Logs**: Monitor nginx/Apache error logs
3. **Uptime Monitoring**: Use service like UptimeRobot
4. **Error Tracking**: Integrate Sentry or similar
5. **Performance**: Google Search Console, Lighthouse

### Automatic Alerts

Set up alerts for:
- Site down/unresponsive
- Database connection failures
- Disk space critical
- Memory usage high
- CPU usage high

## Troubleshooting Deployments

### Common Issues

#### "Permission denied" during deployment

**Solution**:
```bash
# Check SSH key permissions on staging
chmod 600 ~/.ssh/authorized_keys

# Check deploy key in GitHub is correct
# Verify STAGING_SSH_KEY secret matches ~/.ssh/staging_deploy_key (not .pub)
```

#### "Rsync: command not found"

**Solution**: Rsync is usually pre-installed on GitHub runners. If not:
```yaml
- name: Install rsync
  run: sudo apt-get install -y rsync
```

#### "Build failed: npm error"

**Solution**:
```bash
# Test locally
npm install
npm run build

# Check package.json and webpack.config.cjs for errors
```

#### "Database connection failed"

**Solution**:
```bash
# Check database is running on staging
docker compose ps db

# Verify DATABASE credentials in .env
# Check database user has correct permissions
```

## Performance Optimization

### Before Deploying to Production

1. **Optimize database**:
```bash
wp db optimize
```

2. **Pre-generate static assets**:
```bash
npm run build
```

3. **Warm up cache**:
```bash
# Create WP-CLI script to warm cache
wp plugin list  # Loads WordPress
```

4. **Configure CDN**:
- Use Cloudflare or similar CDN
- Cache static assets (CSS, JS, images)
- Set cache expiration appropriate to content

## Advanced: Continuous Deployment

For maximum automation:

1. Every push to `main` → Auto-deploys to staging
2. Tag release: `git tag v1.0.0 && git push origin v1.0.0`
3. Webhook triggers auto-deploy to production
4. Automatically runs database backups before deploy
5. Automatic rollback if health checks fail

Setup requires:
- Advanced GitHub Actions workflows
- Webhook integrations
- Automated backup system
- Health check monitoring

## Related Documentation

- [Architecture Guide](architecture.md) - Project structure and configuration
- [Setup Guide](setup-local.md) - Local development setup
- [Operations Runbook](ops-runbook.md) - Common operational tasks
