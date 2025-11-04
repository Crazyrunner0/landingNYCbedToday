# Staging Deployment Workflow

This directory contains the GitHub Actions workflow for automated staging deployments.

## Files

- **`deploy-staging.yml`** - Main workflow configuration
- **`../scripts/deploy-staging.sh`** - Deployment script (executed by workflow)

## Quick Overview

### What It Does

1. **Validates Code** - Runs composer validation, security audits, tests
2. **Checks Quality** - PHP CodeSniffer, format checking
3. **Builds Assets** - Webpack compilation, composer dependencies
4. **Deploys** - Syncs code to staging server via rsync/SSH

### When It Runs

- **Automatic**: On every push to `main` branch (if CI passes)
- **Manual**: Via GitHub Actions UI
- **Trigger**: `git push origin main`

### What Gets Deployed

✅ **Included**:
- Application code (PHP, theme files)
- Custom plugins (nycbedtoday-*)
- Compiled assets (webpack bundles)
- Production dependencies (composer)

❌ **Excluded**:
- User uploads
- Database
- Development files (node_modules, .env)
- Git history
- Docker configuration

## Configuration

### Required GitHub Secrets

Set these in **Settings → Secrets and variables → Actions**:

```
STAGING_HOST       = staging.example.com
STAGING_PATH       = /var/www/app
STAGING_SSH_KEY    = (SSH private key content)
```

See `../../STAGING_SECRETS_TEMPLATE.md` for detailed setup.

### Optional GitHub Secrets (Cloudflare)

```
CLOUDFLARE_API_TOKEN   = (your API token)
CLOUDFLARE_ZONE_ID    = (your zone ID)
```

Uncomment Cloudflare cache purge step in `deploy-staging.yml` to enable.

## Usage

### Automatic Deployment

```bash
# Make changes and push to main
git add .
git commit -m "Update feature"
git push origin main

# Workflow automatically runs, see progress in:
# GitHub → Actions → Deploy Staging
```

### Manual Deployment

1. Go to **Actions** tab
2. Select **Deploy Staging**
3. Click **Run workflow**
4. Select branch and click green **Run workflow**

### Dry Run (Test Without Deploying)

In `deploy-staging.yml`, change:
```yaml
env:
  DRY_RUN: false  # ← Change to true
```

Then trigger deployment. No files will be modified.

## Monitoring

### View Deployment Progress

1. **GitHub → Actions**
2. Click **Deploy Staging** workflow
3. Click the workflow run (shows timestamp)
4. Click **deploy** job to see details
5. Expand each step to see logs

### Understand Job Status

| Status | Meaning |
|--------|---------|
| ✅ Passed | All checks passed, deployment complete |
| ❌ Failed | Build/lint/deploy failed, check logs |
| 🔄 Running | Workflow is in progress |
| ⏭️ Skipped | Workflow skipped (e.g., not on main branch) |

### Common Logs

**Success**:
```
✓ Deployment rsync completed successfully
✓ Staging deployment completed successfully!
```

**In Dry Run**:
```
[WARN] DRY RUN MODE ENABLED - No files will be synced
[WARN] This was a DRY RUN. No changes were made to the staging server.
```

**Build Error**:
```
[ERROR] npm build failed
```

## Troubleshooting

### "SSH key rejected" or "Permission denied"

1. Verify `STAGING_SSH_KEY` secret is set
2. Check public key in staging server: `cat ~/.ssh/authorized_keys`
3. Verify key format (should start with `-----BEGIN RSA PRIVATE KEY-----`)

### "rsync: command not found"

Rsync should be pre-installed on ubuntu-latest. If not, add:
```yaml
- run: sudo apt-get install -y rsync
```

### "Build failed: npm error"

1. Check workflow logs for specific error
2. Test locally: `npm install && npm run build`
3. Fix issue, push to main
4. Workflow runs automatically

### "Deployment starts but hangs"

- Check network connectivity from GitHub runner to staging
- Verify `STAGING_HOST` is reachable
- Check SSH key permissions on staging server
- Try with `DRY_RUN=true` first

### View Full Logs

Click any step in the workflow UI to expand and see full output.

## Understanding the Workflow

### Validation Phase (Runs First)

```yaml
jobs:
  validate:
    - Composer validation
    - Install dependencies
    - Security audit
    - Run tests
```

⏱️ **Duration**: 2-3 minutes

### Code Quality Phase (Parallel)

```yaml
jobs:
  code-quality:
    - PHP CodeSniffer checks
    
  format-check:
    - JS/CSS/PHP/JSON formatting
```

⏱️ **Duration**: 1-2 minutes

### Deployment Phase (Depends on Above)

```yaml
jobs:
  deploy:
    - Only runs if above jobs pass
    - Builds frontend assets
    - Compiles production dependencies
    - Syncs to staging via rsync
```

⏱️ **Duration**: 2-5 minutes

**Total**: ~5-10 minutes from push to deployment

## Advanced Configuration

### Change Trigger Branches

Edit `deploy-staging.yml`:
```yaml
on:
  push:
    branches: [main, staging]  # Add more branches
```

### Add Additional Build Steps

Add to `deploy` job:
```yaml
- name: Custom build step
  run: npm run custom-build
```

### Change Build Tools

Edit `deploy` job commands:
```yaml
- name: Build frontend assets
  run: npm run build  # ← Modify here
```

### Increase Timeout

Add to jobs:
```yaml
jobs:
  deploy:
    timeout-minutes: 30  # Increase if needed
```

## Debugging

### Enable Verbose Logging

Add to workflow:
```yaml
- name: Deploy with verbose logging
  run: bash -x scripts/deploy-staging.sh
```

### SSH Debug

Test SSH connection manually:
```bash
ssh -vvv -i ~/.ssh/staging_key staging.example.com ls -la /var/www/app
```

### Local Testing

```bash
export STAGING_HOST="staging.example.com"
export STAGING_PATH="/var/www/app"
export STAGING_SSH_KEY="$(cat staging_deploy_key)"
export DRY_RUN=true
bash scripts/deploy-staging.sh
```

## Security

- ✅ Secrets are masked in logs
- ✅ SSH keys never appear in output
- ✅ Deployment only runs on `main` branch
- ✅ CI checks required before deployment
- ✅ SSH key has no passphrase (for CI use)
- ❌ Never commit secrets to repository
- ❌ Never copy credentials into workflow file

## Rollback

If deployment causes issues:

```bash
# On your local machine
git revert <commit-hash>
git push origin main

# Workflow runs automatically with reverted code
```

Or revert on staging server:
```bash
ssh staging.example.com
cd /var/www/app
git revert <commit-hash>
git pull origin main
```

## Performance

- **Caching**: NPM and Composer packages are cached
- **Parallel**: Code quality jobs run in parallel
- **Conditional**: Deploy job only runs if checks pass
- **Cleanup**: Artifacts cleaned up after deployment

## Documentation

For detailed information:

1. **Quick Start**: See `../../STAGING_DEPLOYMENT_QUICK_START.md`
2. **Full Guide**: See `../../STAGING_DEPLOYMENT_GUIDE.md`
3. **Secrets Setup**: See `../../STAGING_SECRETS_TEMPLATE.md`
4. **Deployment Script**: See `../../scripts/deploy-staging.sh`

## Support

- **GitHub Actions Docs**: https://docs.github.com/en/actions
- **Rsync Manual**: https://linux.die.net/man/1/rsync
- **SSH Keys**: https://docs.github.com/en/authentication/connecting-to-github-with-ssh

## Status

Current implementation status:

- ✅ GitHub Actions workflow created
- ✅ Deployment script implemented
- ✅ Dry-run mode supported
- ✅ Documentation complete
- ✅ Secret configuration documented
- ⏳ Awaiting GitHub secrets configuration
- ⏳ Awaiting staging server setup
- ⏳ First deployment test

## Next Steps

1. Configure GitHub secrets (see `../../STAGING_SECRETS_TEMPLATE.md`)
2. Setup staging server SSH access
3. Test with dry-run mode
4. Monitor first deployment
5. Enable Cloudflare integration (optional)

---

**Version**: 1.0  
**Last Updated**: 2024
