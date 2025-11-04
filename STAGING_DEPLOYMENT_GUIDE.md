# Staging Deployment Guide

## Overview

This guide explains how to set up and use the automated staging deployment pipeline via GitHub Actions. The pipeline builds theme and plugin assets, validates code quality, and deploys to a staging server using rsync over SSH.

## Architecture

The deployment system consists of three main components:

### 1. GitHub Actions Workflow (`deploy-staging.yml`)

**File**: `.github/workflows/deploy-staging.yml`

**Trigger**: Automatic on push to `main` branch (after CI checks pass)

**Jobs**:
- **validate**: Composer validation, dependency install, security audit, tests
- **code-quality**: PHP CodeSniffer checks
- **format-check**: JS/CSS/PHP/JSON formatting checks
- **deploy**: Build assets, sync to staging server

**Duration**: ~5-10 minutes for full pipeline

### 2. Deployment Script (`deploy-staging.sh`)

**File**: `scripts/deploy-staging.sh`

**Purpose**: Orchestrates the actual deployment process

**Features**:
- Validates required environment variables
- Installs Node and PHP dependencies
- Builds frontend assets (webpack)
- Syncs code to staging via rsync
- Respects `.gitignore` patterns
- Supports dry-run mode for testing
- Color-coded logging for clarity
- Automatic cleanup of development artifacts

**Environment Variables**:
- `STAGING_HOST`: Hostname/IP of staging server (e.g., `staging.example.com`)
- `STAGING_PATH`: Path on staging server (e.g., `/var/www/app`)
- `STAGING_SSH_KEY`: SSH private key for authentication
- `DRY_RUN`: Set to `true` to simulate deployment without making changes

### 3. GitHub Secrets Configuration

Located in: **GitHub Repository Settings → Secrets and variables → Actions**

## Setup Instructions

### Step 1: Generate SSH Key for Staging

Generate a new SSH key dedicated for deployments:

```bash
ssh-keygen -t rsa -b 4096 -f staging_deploy_key -C "staging-deployment"
```

This creates:
- `staging_deploy_key` - Private key (for GitHub secret)
- `staging_deploy_key.pub` - Public key (for staging server)

### Step 2: Configure Staging Server

1. **SSH Access**:
   - Copy `staging_deploy_key.pub` to `~/.ssh/authorized_keys` on staging server
   - Set permissions: `chmod 600 ~/.ssh/authorized_keys`

2. **Create Deployment Directory**:
   ```bash
   mkdir -p /var/www/app
   chmod 755 /var/www/app
   ```

3. **Setup Web Server**:
   - Configure nginx/Apache to serve from `/var/www/app/web`
   - Ensure WordPress can write to cache/tmp directories
   - Copy `.env` file or set environment variables

### Step 3: Configure GitHub Secrets

In your GitHub repository, add these secrets:

| Secret | Value | Example |
|--------|-------|---------|
| `STAGING_HOST` | Staging server hostname | `staging.example.com` |
| `STAGING_PATH` | Deployment path on server | `/var/www/app` |
| `STAGING_SSH_KEY` | SSH private key (full content) | `-----BEGIN RSA PRIVATE KEY-----...` |

**How to add secrets**:
1. Go to **Settings → Secrets and variables → Actions**
2. Click **"New repository secret"**
3. Name: `STAGING_HOST`, Value: your staging hostname
4. Repeat for `STAGING_PATH` and `STAGING_SSH_KEY`

### Step 4: Configure Deployment Script Options

In `.github/workflows/deploy-staging.yml`, edit the `env` section:

```yaml
env:
  DRY_RUN: false  # Set to 'true' to run in dry-run mode
```

## Usage

### Automatic Deployment

Deployment happens automatically when code is pushed to `main` branch:

```bash
git push origin main
```

The workflow:
1. Checks out code
2. Validates composer.json
3. Runs security audit
4. Runs tests
5. Checks code formatting
6. Builds assets (if all checks pass)
7. Deploys to staging server

**View workflow status**: GitHub → Actions → Deploy Staging

### Manual Deployment

To manually trigger deployment (useful for testing):

1. Go to **Actions** tab in GitHub
2. Select **Deploy Staging** workflow
3. Click **Run workflow**
4. Select branch (usually `main`)
5. Click green **"Run workflow"** button

### Dry-Run Mode

To test deployment without making changes:

1. Edit `.github/workflows/deploy-staging.yml`
2. Change `DRY_RUN: false` to `DRY_RUN: true`
3. Trigger deployment (push to main or manual run)
4. Review logs to see what would be synced

**Logs show**:
- Files that would be created
- Files that would be updated
- Files that would be deleted
- Total changes without applying them

### Local Testing

To test deployment locally:

```bash
# Set environment variables
export STAGING_HOST="staging.example.com"
export STAGING_PATH="/var/www/app"
export STAGING_SSH_KEY="$(cat staging_deploy_key)"
export DRY_RUN=true

# Run deployment script
bash scripts/deploy-staging.sh
```

## Cloudflare Cache Purge (Optional)

The deployment pipeline includes placeholders for Cloudflare cache purging. To enable:

### Step 1: Get Cloudflare Credentials

1. Log in to [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Go to **Account → API Tokens**
3. Create new token with permissions:
   - Zone → Cache Purge (All)
   - Zone → Read (All)

### Step 2: Add GitHub Secrets

Add these to GitHub repository secrets:

| Secret | Value |
|--------|-------|
| `CLOUDFLARE_API_TOKEN` | Your API token |
| `CLOUDFLARE_ZONE_ID` | Your zone ID (from domain overview) |

### Step 3: Uncomment Cloudflare Steps

In `.github/workflows/deploy-staging.yml`, uncomment the Cloudflare cache purge step:

```yaml
- name: Purge Cloudflare cache
  # Uncomment when Cloudflare credentials are configured
  # run: |
  #   curl -X POST "https://api.cloudflare.com/client/v4/zones/${{ secrets.CLOUDFLARE_ZONE_ID }}/purge_cache" \
  #     -H "Authorization: Bearer ${{ secrets.CLOUDFLARE_API_TOKEN }}" \
  #     -H "Content-Type: application/json" \
  #     --data '{"files":["https://staging.example.com/*"]}'
```

## What Gets Deployed

### Included in Deployment

✅ **Application Code**:
- PHP source files (web/app/)
- Theme files (web/app/themes/)
- Custom plugins (web/app/plugins/nycbedtoday-*)
- Configuration (wp-config.php, etc.)

✅ **Built Assets**:
- Compiled webpack bundles
- Minified CSS/JS
- Compiled PHP dependencies (vendor/)

### Excluded from Deployment

❌ **Not Synced**:
- User uploads (web/app/uploads/)
- Media cache (web/app/cache/)
- Database (WordPress handles separately)
- Git files (.git/, .github/)
- Development tools (node_modules/)
- Build artifacts (before optimization)
- Environment files (.env, .env.local)
- Docker configuration (docker-compose.yml)
- IDE files (.vscode/, .idea/)

**Full exclusion list**: See `scripts/deploy-staging.sh` lines 58-102

## Monitoring & Troubleshooting

### View Deployment Logs

1. Go to **GitHub → Actions → Deploy Staging**
2. Click the workflow run
3. Click **deploy** job
4. View logs for each step

### Common Issues

#### ❌ "SSH key not configured"

**Error**: "Dry Run MODE ENABLED - No files will be synced" or similar

**Solution**:
- Verify `STAGING_SSH_KEY` secret is set
- Check SSH key format (should start with `-----BEGIN RSA PRIVATE KEY-----`)
- Ensure no whitespace issues when copying key

#### ❌ "Permission denied (publickey)"

**Error**: Deployment fails with SSH authentication error

**Solution**:
- Verify `staging_deploy_key.pub` is in `~/.ssh/authorized_keys` on staging
- Check file permissions: `chmod 600 ~/.ssh/authorized_keys`
- Verify `STAGING_HOST` is correct and reachable

#### ❌ "Build failed: npm error"

**Error**: Frontend build fails

**Solution**:
- Check npm logs in workflow output
- Verify `package.json` is valid
- Try locally: `npm install && npm run build`
- Update `webpack.config.cjs` if necessary

#### ❌ "Rsync: command not found"

**Error**: Deployment fails because rsync isn't installed on runner

**Solution**:
- This shouldn't happen on ubuntu-latest (rsync is pre-installed)
- Add to workflow if needed: `- run: sudo apt-get install -y rsync`

### Performance Tips

1. **Use caching**: Workflow caches Composer and NPM packages
2. **Only deploy on main**: Change `branches: [main]` to specific branches
3. **Skip large files**: Add patterns to `.gitignore` for large files
4. **Monitor disk space**: Ensure staging server has enough space for deployments

## Maintenance

### Rotating SSH Keys

Every 3-6 months, rotate the deployment SSH key:

1. Generate new key: `ssh-keygen -t rsa -b 4096 -f staging_deploy_key_new`
2. Add public key to staging: `cat staging_deploy_key_new.pub >> ~/.ssh/authorized_keys`
3. Update GitHub secret with private key
4. Remove old public key from staging after testing
5. Delete old private key file

### Cleanup

Workflow automatically cleans up:
- SSH key files after deployment
- `node_modules/` directory
- Build artifacts (`build/`, `dist/`)
- Composer vendor directory

## Security Best Practices

1. **SSH Key Security**:
   - Use dedicated deployment key (not personal key)
   - Restrict permissions: `chmod 600`
   - Store securely in GitHub Secrets

2. **Server Hardening**:
   - Use key-based SSH authentication (no passwords)
   - Limit SSH access to GitHub runner IPs if possible
   - Monitor server logs for failed deployments
   - Use fail2ban or similar to prevent brute force

3. **Staging Environment**:
   - Don't use staging credentials for production
   - Keep staging separate from production server
   - Use different database for staging
   - Rotate credentials periodically

4. **Workflow Security**:
   - Only deploy from verified branches (main)
   - Require CI checks before deployment
   - Review workflow logs regularly
   - Use GitHub environment protection rules

## Reverting Deployments

If deployment causes issues:

### Option 1: Deploy Previous Version

```bash
git revert <commit-hash>
git push origin main
```

### Option 2: Manual Rollback

SSH to staging server:
```bash
cd /var/www/app
git revert <commit-hash>
git pull origin main
wp cache flush  # Clear WordPress cache if needed
```

### Option 3: Database Rollback

For WordPress database changes:
```bash
# On staging server
wp db reset --yes  # For testing only!
wp core install --url=... --title=... --admin_user=... --admin_email=...
```

## Success Indicators

✅ **Deployment successful when**:
- Workflow shows ✓ for all jobs
- "Staging deployment completed successfully!" appears in logs
- Updated code is available on staging server
- No 403/404 errors on staging site

## Support

For deployment issues:

1. **Check workflow logs**: GitHub → Actions
2. **Test locally**: Run `scripts/deploy-staging.sh` with `DRY_RUN=true`
3. **SSH to staging**: Verify files were synced
4. **Review staging server logs**: Check nginx/PHP-FPM errors

## Next Steps

1. Configure GitHub secrets (Step 1-3 of Setup Instructions)
2. Test with dry-run mode
3. Make a test commit to `main` and monitor workflow
4. Verify files appear on staging server
5. Enable Cloudflare cache purge (optional, when ready)

---

**Last Updated**: 2024
**Version**: 1.0
