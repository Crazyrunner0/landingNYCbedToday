# Staging Deployment Quick Start

## 5-Minute Setup

### Prerequisites
- SSH access to staging server
- GitHub repository admin access
- Staging server with `/var/www/app` directory

### Step 1: Create SSH Key (1 min)

```bash
# Generate SSH key
ssh-keygen -t rsa -b 4096 -f staging_deploy_key -C "staging-deployment"

# Display private key (copy this)
cat staging_deploy_key
```

### Step 2: Configure Staging Server (2 min)

SSH to staging server:

```bash
# Add public key
cat staging_deploy_key.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Create deployment directory
mkdir -p /var/www/app
```

### Step 3: Add GitHub Secrets (2 min)

1. Go to **GitHub → Settings → Secrets and variables → Actions**
2. Add three new secrets:

| Name | Value |
|------|-------|
| `STAGING_HOST` | Your staging hostname (e.g., `staging.example.com`) |
| `STAGING_PATH` | `/var/www/app` |
| `STAGING_SSH_KEY` | Full content of `staging_deploy_key` file |

### Step 4: Test Deployment (Optional)

```bash
# Push to main
git push origin main

# Watch workflow at: GitHub → Actions → Deploy Staging
```

**Done!** 🎉 Future pushes to `main` will automatically deploy to staging.

## Test Without Secrets

If you don't have staging server credentials yet:

1. Workflow will run in **dry-run mode**
2. All steps complete successfully
3. No files are actually synced
4. Review logs to verify build process

This is perfect for testing CI/CD pipeline before staging is ready.

## Common Commands

### View Deployment Status
```
GitHub → Actions → Deploy Staging
```

### Manual Deploy
1. **Actions** tab
2. **Deploy Staging** workflow
3. **Run workflow**

### Local Dry Run
```bash
export STAGING_HOST="staging.example.com"
export STAGING_PATH="/var/www/app"
export STAGING_SSH_KEY="$(cat staging_deploy_key)"
export DRY_RUN=true
bash scripts/deploy-staging.sh
```

### Check Staging Server
```bash
ssh staging.example.com
cd /var/www/app
git log -1
ls -la
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| SSH key rejected | Verify `.pub` key is in `~/.ssh/authorized_keys` |
| Build fails | Run `npm install && npm run build` locally to debug |
| Files not syncing | Check `STAGING_PATH` exists and has write permissions |
| Need to redo? | Run workflow with `DRY_RUN=true` to test first |

## Next Steps

See [STAGING_DEPLOYMENT_GUIDE.md](./STAGING_DEPLOYMENT_GUIDE.md) for:
- Full configuration details
- Cloudflare integration
- Security best practices
- Troubleshooting guide
- Rollback procedures
