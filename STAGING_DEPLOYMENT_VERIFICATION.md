# Staging Deployment Verification Checklist

Use this checklist to verify the staging deployment pipeline is working correctly.

## Pre-Deployment Setup

- [ ] SSH key pair generated (`ssh-keygen -t rsa -b 4096`)
- [ ] Private key content copied to GitHub secret `STAGING_SSH_KEY`
- [ ] Public key added to staging server `~/.ssh/authorized_keys`
- [ ] `STAGING_HOST` secret configured (e.g., `staging.example.com`)
- [ ] `STAGING_PATH` secret configured (e.g., `/var/www/app`)
- [ ] Staging server directory created and writable
- [ ] Staging server has rsync installed: `which rsync`
- [ ] Staging server has SSH access working: `ssh -i key user@host ls`

## Workflow File Verification

- [ ] `.github/workflows/deploy-staging.yml` exists
- [ ] Workflow name is "Deploy Staging"
- [ ] Trigger configured for `main` branch
- [ ] Manual trigger `workflow_dispatch` enabled
- [ ] All four jobs present: validate, code-quality, format-check, deploy
- [ ] Deploy job depends on other jobs: `needs: [validate, code-quality, format-check]`
- [ ] Deploy job has condition: `if: github.ref == 'refs/heads/main'`

## Deployment Script Verification

- [ ] `scripts/deploy-staging.sh` exists and is executable: `ls -l scripts/deploy-staging.sh`
- [ ] Script starts with `#!/bin/bash`
- [ ] Script checks environment variables
- [ ] Script includes color-coded logging
- [ ] Script supports DRY_RUN mode
- [ ] Script validates required variables
- [ ] Script builds npm/composer dependencies
- [ ] Script executes rsync command
- [ ] Script cleans up development artifacts

## Documentation Verification

- [ ] `STAGING_DEPLOYMENT_GUIDE.md` exists (comprehensive guide)
- [ ] `STAGING_DEPLOYMENT_QUICK_START.md` exists (5-minute setup)
- [ ] `STAGING_SECRETS_TEMPLATE.md` exists (secret configuration)
- [ ] `.github/workflows/README_STAGING_DEPLOY.md` exists (workflow reference)
- [ ] Documentation includes troubleshooting section
- [ ] Documentation explains .gitignore exclusions
- [ ] Documentation covers dry-run testing

## GitHub Secrets Configuration

- [ ] Navigate to Settings → Secrets and variables → Actions
- [ ] `STAGING_HOST` secret exists
- [ ] `STAGING_PATH` secret exists
- [ ] `STAGING_SSH_KEY` secret exists
- [ ] All three secrets have values (not empty)
- [ ] Secret values are not visible in public logs

## First Deployment Test

### Dry Run Test

- [ ] Push a test commit to `main` branch
- [ ] GitHub Actions workflow starts automatically
- [ ] Workflow shows in "Actions" tab
- [ ] validate job passes ✅
- [ ] code-quality job passes ✅
- [ ] format-check job passes ✅
- [ ] deploy job starts and completes
- [ ] Logs show "DRY RUN MODE ENABLED" or "staging credentials not configured"
- [ ] No actual changes made to staging server

### Actual Deployment Test (After Dry Run)

- [ ] SSH to staging server: `ssh staging.example.com`
- [ ] Check deployment directory: `ls -la /var/www/app`
- [ ] Verify it's a git repository: `cd /var/www/app && git log`
- [ ] Check for expected files: `ls web/app/themes/blocksy-child/`
- [ ] Verify build artifacts exist: `ls web/app/themes/blocksy-child/build/` (if applicable)
- [ ] Check file ownership and permissions
- [ ] Verify no user uploads were synced: `! [ -d web/app/uploads/* ]`

## Build Process Verification

- [ ] npm dependencies installed: Check for `node_modules/`
- [ ] Frontend assets built: Check for webpack output
- [ ] PHP dependencies compiled: Check for `vendor/` directory
- [ ] No development files: `!grep -r "DEBUG=true"`

## Exclusion Verification (What Shouldn't Be There)

SSH to staging and verify these are NOT present:

- [ ] No `.git` directory (hidden history)
- [ ] No `.env` file (no exposed credentials)
- [ ] No `node_modules` directory (development only)
- [ ] No `docker-compose.yml` (development only)
- [ ] No user uploads from `/uploads/` (handled separately)
- [ ] No database files
- [ ] No `.idea` or `.vscode` directories
- [ ] No `*.log` files (except WordPress logs)

## CI Pipeline Integration

- [ ] Composer validation runs before deployment
- [ ] Security audit runs: `composer audit`
- [ ] Unit tests run: `composer test`
- [ ] PHP CodeSniffer checks: `npm run format:check`
- [ ] Any failures block deployment
- [ ] All jobs must pass before deploy job runs

## Error Handling Verification

### SSH Key Issues

- [ ] If SSH key invalid: deployment fails with clear error
- [ ] If SSH key missing: workflow handles gracefully
- [ ] Known hosts file updated automatically

### Network Issues

- [ ] If staging server unreachable: rsync fails clearly
- [ ] If staging path doesn't exist: rsync fails clearly
- [ ] If permissions denied: error message indicates permission issue

### Build Issues

- [ ] If npm build fails: deployment blocked
- [ ] If composer install fails: deployment blocked
- [ ] If linting fails: deployment blocked

## Cleanup Verification

After deployment (successful or failed):

- [ ] SSH key file removed: `! [ -f ~/.ssh/staging_key ]`
- [ ] node_modules cleaned up on runner
- [ ] build/ and dist/ directories cleaned
- [ ] vendor/ directory cleaned
- [ ] No credentials left in runner logs

## Rollback Verification

- [ ] Git history available on staging: `git log`
- [ ] Can revert with: `git revert <hash> && git push`
- [ ] Revert automatically triggers new deployment
- [ ] Previous version restored successfully

## Monitoring & Logging

- [ ] Workflow logs preserved in GitHub
- [ ] Logs show build time and file count
- [ ] Logs show rsync statistics
- [ ] Logs show deployment completion time
- [ ] Color coding helps identify issues
- [ ] No sensitive data in logs (secrets masked)

## Performance Verification

- [ ] Workflow completes in reasonable time (< 15 min)
- [ ] npm cache working: "(cached)" appears in logs
- [ ] Composer cache working: "(from cache)" appears
- [ ] No unnecessary rebuilds: reused cached layers

## Security Verification

- [ ] SSH private key never appears in logs
- [ ] Environment variables masked in output
- [ ] Secrets properly configured as GitHub Secrets
- [ ] Deploy only on main branch
- [ ] Manual trigger available only to maintainers
- [ ] SSH key has restricted permissions: 600

## Optional: Cloudflare Integration

- [ ] Cloudflare secrets configured (if using)
- [ ] Cache purge API token valid
- [ ] Zone ID correct
- [ ] Cache purge step uncommented (if enabled)
- [ ] Cloudflare cache cleared after successful deployment

## Documentation Review

- [ ] README clearly explains setup process
- [ ] Quick start guide covers 5 essential steps
- [ ] Secrets template documents each required secret
- [ ] Workflow README explains each job
- [ ] Troubleshooting covers common issues
- [ ] Security section explains best practices
- [ ] Examples provided for common tasks

## Live Testing

- [ ] Make a small code change
- [ ] Push to `main` branch
- [ ] Verify deployment happens automatically
- [ ] Check code appears on staging within minutes
- [ ] Verify no manual intervention needed
- [ ] Test with multiple commits
- [ ] Monitor logs for patterns and performance

## User Acceptance

- [ ] Team members can view workflow status
- [ ] Developers understand how to trigger deployments
- [ ] Developers understand what gets deployed
- [ ] Team knows what to do if deployment fails
- [ ] On-call person knows rollback procedure
- [ ] Documentation is discoverable and clear

## Success Criteria Met

✅ **Deployment runs automatically on main branch push**
- Workflow trigger configured
- All CI checks pass first
- Deploy job conditional on main branch

✅ **Pipeline builds assets and syncs code**
- npm build step included
- composer install --no-dev included
- rsync syncs to staging server
- Proper exclusions respected

✅ **Fails gracefully without credentials**
- Dry-run mode available
- Workflow completes successfully even without secrets
- Clear messaging when credentials missing
- No test failures due to missing deployment info

✅ **Documentation comprehensive**
- Setup instructions complete
- Secret configuration documented
- Troubleshooting guide included
- Examples provided

✅ **Security best practices**
- SSH keys secure
- Secrets not exposed
- Only production code deployed
- Audit trail available

## Final Sign-Off

| Item | Verified | Approver | Date |
|------|----------|----------|------|
| Workflow functions correctly | ☐ | | |
| Documentation complete | ☐ | | |
| Security review passed | ☐ | | |
| Team trained | ☐ | | |
| Ready for production | ☐ | | |

---

**Date Checklist Started**: ___________  
**Date Checklist Completed**: ___________  
**Verified By**: ___________  
**Approved By**: ___________  

---

## Notes

Use this section to document any issues found and how they were resolved:

```
Issue 1: [Description]
Resolution: [How it was fixed]
Date: [When fixed]

Issue 2: [Description]
Resolution: [How it was fixed]
Date: [When fixed]
```
