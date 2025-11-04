#!/bin/bash

# Staging Deployment Script
# Syncs built application code to staging server via rsync
# Respects .gitignore patterns - only syncs necessary application code

set -euo pipefail

# Configuration from environment variables
STAGING_HOST="${STAGING_HOST:?"Error: STAGING_HOST environment variable not set"}"
STAGING_PATH="${STAGING_PATH:?"Error: STAGING_PATH environment variable not set"}"
STAGING_SSH_KEY="${STAGING_SSH_KEY:?"Error: STAGING_SSH_KEY environment variable not set"}"
DRY_RUN="${DRY_RUN:-false}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $*"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $*"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $*"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $*" >&2
}

# Check if dry-run is enabled
if [ "$DRY_RUN" = "true" ] || [ "$DRY_RUN" = "1" ]; then
    log_warn "DRY RUN MODE ENABLED - No files will be synced"
    RSYNC_DRY_RUN="--dry-run"
else
    RSYNC_DRY_RUN=""
fi

# Setup SSH key
log_info "Setting up SSH key..."
SSH_KEY_FILE="/tmp/staging_deploy_key_$$"
trap "rm -f $SSH_KEY_FILE" EXIT

echo "$STAGING_SSH_KEY" > "$SSH_KEY_FILE"
chmod 600 "$SSH_KEY_FILE"

log_info "Preparing deployment artifacts..."

# Build theme assets
if [ -f "package.json" ]; then
    log_info "Installing Node dependencies..."
    npm install --prefer-offline --no-audit 2>/dev/null || log_warn "npm install encountered issues but continuing..."
    
    log_info "Building frontend assets..."
    npm run build 2>/dev/null || log_error "npm build failed"
fi

# Install production PHP dependencies
if [ -f "composer.json" ]; then
    log_info "Installing production PHP dependencies..."
    composer install --no-dev --prefer-dist --no-progress --no-interaction 2>/dev/null || log_error "composer install failed"
fi

log_info "Syncing code to staging server..."
log_info "Target: ${STAGING_HOST}:${STAGING_PATH}"

# Build rsync command with excludes
RSYNC_CMD=(
    rsync
    -avz
    --delete
    $RSYNC_DRY_RUN
    -e "ssh -i $SSH_KEY_FILE -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null"
    --exclude='.git'
    --exclude='.github'
    --exclude='.gitignore'
    --exclude='.editorconfig'
    --exclude='.prettierrc'
    --exclude='.prettierignore'
    --exclude='.env'
    --exclude='.env.local'
    --exclude='.env.*.local'
    --exclude='Makefile'
    --exclude='docker-compose.yml'
    --exclude='docker-compose.override.yml'
    --exclude='.dockerignore'
    --exclude='docker-compose.*.yml'
    --exclude='Dockerfile'
    --exclude='.DS_Store'
    --exclude='Thumbs.db'
    --exclude='node_modules'
    --exclude='npm-debug.log'
    --exclude='yarn-error.log'
    --exclude='vendor'
    --exclude='composer.lock'
    --exclude='.idea'
    --exclude='.vscode'
    --exclude='*.swp'
    --exclude='*.swo'
    --exclude='*~'
    --exclude='web/app/uploads'
    --exclude='web/app/cache'
    --exclude='storage/cache'
    --exclude='web/wp'
    --exclude='web/.htaccess'
    --exclude='web/app/plugins/*'
    --exclude='!web/app/plugins/.gitkeep'
    --exclude='!web/app/plugins/nycbedtoday-blocks'
    --exclude='!web/app/plugins/nycbedtoday-logistics'
    --exclude='web/app/mu-plugins/*'
    --exclude='!web/app/mu-plugins/tests'
    --exclude='web/app/upgrade'
    --exclude='*.log'
    --exclude='*lighthouse-report*.html'
    --exclude='*performance-report*.json'
    --exclude='*performance-audit*.txt'
    --exclude='build/'
    --exclude='dist/'
    --exclude='web/app/themes/blocksy-child/build/'
    ./
    "${STAGING_HOST}:${STAGING_PATH}/"
)

# Execute rsync
if "${RSYNC_CMD[@]}"; then
    log_success "Deployment rsync completed successfully"
else
    log_error "Deployment rsync failed"
    exit 1
fi

# Cleanup development dependencies after successful sync
if [ "$DRY_RUN" != "true" ]; then
    log_info "Cleaning up development artifacts..."
    rm -rf node_modules/ 2>/dev/null || true
    rm -rf build/ dist/ 2>/dev/null || true
fi

if [ "$DRY_RUN" = "true" ]; then
    log_warn "This was a DRY RUN. No changes were made to the staging server."
    exit 0
fi

log_success "Staging deployment completed successfully!"
log_info "Deployment time: $(date)"

exit 0
