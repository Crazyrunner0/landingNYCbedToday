# Staging Deployment Secrets Template

This document describes all GitHub Secrets required for the staging deployment pipeline.

## Required Secrets

### 1. STAGING_HOST
**Type**: String  
**Required**: Yes  
**Format**: Hostname or IP address  
**Examples**:
```
staging.example.com
192.168.1.100
app-staging.cloud.example.com
```
**Description**: The hostname or IP address of the staging server where code will be deployed.

---

### 2. STAGING_PATH
**Type**: String  
**Required**: Yes  
**Format**: Absolute path on staging server  
**Examples**:
```
/var/www/app
/home/deploy/app
/opt/applications/staging
```
**Description**: The absolute path on the staging server where the application code will be synced. This should:
- Be an empty directory or contain previous deployments
- Have write permissions for the deployment user
- Be configured as the web root in nginx/Apache

---

### 3. STAGING_SSH_KEY
**Type**: SSH Private Key  
**Required**: Yes  
**Format**: RSA private key (PEM format)  
**Example**:
```
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA2xP4q8N5k...
[many more lines...]
-----END RSA PRIVATE KEY-----
```
**Description**: The private SSH key used to authenticate with the staging server.

**How to generate**:
```bash
ssh-keygen -t rsa -b 4096 -f staging_deploy_key -C "staging-deployment"
cat staging_deploy_key  # Copy entire output to GitHub
```

**Important**: 
- Must be in PEM format (starts with `-----BEGIN RSA PRIVATE KEY-----`)
- No passphrase (leave empty when generating)
- Keep the corresponding `.pub` key in `~/.ssh/authorized_keys` on staging server

---

## Optional Secrets (Cloudflare)

These are only needed if you want automatic cache purging after deployments.

### CLOUDFLARE_API_TOKEN
**Type**: String  
**Required**: No  
**Format**: Cloudflare API token  
**Description**: API token for Cloudflare cache purge operations.

**How to get**:
1. Log in to [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Go to **My Profile → API Tokens**
3. Click **Create Token**
4. Use template "Cache Purge" or create custom with:
   - Permissions: `Cache Purge (All)`, `Zone (Read)`
   - Zone Resources: Select your zone

---

### CLOUDFLARE_ZONE_ID
**Type**: String  
**Required**: No  
**Format**: Cloudflare Zone ID  
**Description**: Your Cloudflare Zone ID for cache purge operations.

**How to find**:
1. Log in to [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Select your domain
3. Go to **Overview**
4. Scroll to **API** section
5. Copy **Zone ID**

---

## Environment Variables in Workflow

These can be configured in the workflow file (`.github/workflows/deploy-staging.yml`):

### DRY_RUN
**Type**: Boolean  
**Default**: `false`  
**Description**: When set to `true`, deployment simulates without making changes. Useful for testing.

**How to enable**:
Edit `.github/workflows/deploy-staging.yml`:
```yaml
env:
  DRY_RUN: true  # Set to true for dry-run mode
```

---

## How to Add Secrets to GitHub

### Via Web UI (Easiest)

1. Go to your GitHub repository
2. Click **Settings**
3. In left sidebar, click **Secrets and variables → Actions**
4. Click **New repository secret**
5. Enter Name (e.g., `STAGING_HOST`)
6. Enter Value (e.g., `staging.example.com`)
7. Click **Add secret**
8. Repeat for each secret

### Via GitHub CLI

```bash
# Install GitHub CLI: https://cli.github.com

# Add STAGING_HOST
gh secret set STAGING_HOST -b "staging.example.com"

# Add STAGING_PATH
gh secret set STAGING_PATH -b "/var/www/app"

# Add STAGING_SSH_KEY (from file)
gh secret set STAGING_SSH_KEY < staging_deploy_key

# List all secrets
gh secret list
```

---

## Security Checklist

- [ ] SSH key is dedicated for staging deployment (not personal key)
- [ ] Private key has no passphrase
- [ ] Public key is in `~/.ssh/authorized_keys` on staging server
- [ ] SSH key file has `chmod 600` permissions
- [ ] GitHub secrets are set to private (not exposed in logs)
- [ ] No credentials are hardcoded in `.yml` files
- [ ] Only necessary personnel have access to modify secrets
- [ ] Staging server IP/hostname is not public
- [ ] Cloudflare credentials (if used) are read-only for cache purge only

---

## Rotating Secrets

### Rotate SSH Key (Recommended: Every 3-6 months)

1. Generate new key:
   ```bash
   ssh-keygen -t rsa -b 4096 -f staging_deploy_key_new -C "staging-deployment-new"
   ```

2. Add new public key to staging:
   ```bash
   cat staging_deploy_key_new.pub >> ~/.ssh/authorized_keys
   ```

3. Update GitHub secret:
   ```bash
   gh secret set STAGING_SSH_KEY < staging_deploy_key_new
   ```

4. Test deployment with new key

5. Remove old public key from staging:
   ```bash
   # Edit ~/.ssh/authorized_keys and remove old key
   nano ~/.ssh/authorized_keys
   ```

6. Delete old private key file securely:
   ```bash
   shred -vfz staging_deploy_key  # Linux
   # or
   rm -P staging_deploy_key  # macOS
   ```

### Rotate Cloudflare Token (Recommended: Every 3-6 months)

1. Create new token in Cloudflare dashboard
2. Update `CLOUDFLARE_API_TOKEN` secret in GitHub
3. Revoke old token in Cloudflare dashboard

---

## Testing Secrets

### Test SSH Connectivity

```bash
export STAGING_HOST="$(gh secret get STAGING_HOST)"
export STAGING_SSH_KEY="$(gh secret get STAGING_SSH_KEY)"

# Create temporary key file
echo "$STAGING_SSH_KEY" > /tmp/test_key
chmod 600 /tmp/test_key

# Test SSH connection
ssh -i /tmp/test_key -o StrictHostKeyChecking=no $STAGING_HOST "echo 'SSH works!'"

# Cleanup
rm /tmp/test_key
```

### Run Workflow with Secrets

GitHub automatically loads secrets into workflow. To test locally, use:

```bash
# Set environment variables (for testing only)
export STAGING_HOST="staging.example.com"
export STAGING_PATH="/var/www/app"
export STAGING_SSH_KEY="$(cat staging_deploy_key)"
export DRY_RUN=true

# Run deployment script
bash scripts/deploy-staging.sh
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| "Error: STAGING_HOST not set" | Add `STAGING_HOST` secret to GitHub |
| "Permission denied (publickey)" | Verify public key in `~/.ssh/authorized_keys` on staging |
| "Connection refused" | Check `STAGING_HOST` is reachable, verify firewall rules |
| "Rsync: command not found" | Install rsync on staging: `apt-get install rsync` |
| "Secrets not appearing in logs" | They shouldn't - GitHub masks secrets automatically |

---

## Reference

- [GitHub Encrypted Secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets)
- [GitHub CLI Secret Management](https://cli.github.com/manual/gh_secret)
- [SSH Key Generation](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent)
- [Cloudflare API Documentation](https://developers.cloudflare.com/api/)
