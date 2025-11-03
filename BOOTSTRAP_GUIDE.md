# WordPress Stack Bootstrap Guide

This guide documents the complete WordPress stack bootstrap process, which automates environment setup, dependency installation, Docker container startup, and health verification.

## What is Bootstrap?

The bootstrap process (`make bootstrap`) is a single command that:

1. **Installs Dependencies**: Runs `composer install` to download WordPress core, plugins, themes, and PHP libraries
2. **Starts Services**: Brings up all Docker containers (nginx, php-fpm, MariaDB, Redis)
3. **Waits for Stability**: Pauses briefly to allow services to fully initialize
4. **Runs Health Checks**: Verifies all containers are running and WordPress is accessible
5. **Provides Next Steps**: Shows instructions for completing WordPress setup

## Quick Start

For new developers or fresh clones, just run:

```bash
make bootstrap
```

## Step-by-Step Breakdown

### Step 1: Install Dependencies
```bash
make install
```

This creates `.env` from `.env.example` and runs `composer install`.

### Step 2: Start Docker Services
```bash
make up
```

Brings up all containers (nginx, php-fpm, MariaDB, Redis).

### Step 3: Health Check
```bash
make healthcheck
```

Verifies all containers are running and WordPress is accessible.

## Post-Bootstrap Setup

After bootstrap, complete WordPress installation:

1. Open http://localhost:8080 in your browser
2. Select language and proceed with setup
3. Enter database credentials (all from `.env`)
4. Create admin account

## Manual Alternative

For step-by-step control:

```bash
cp .env.example .env
docker compose run --rm php composer install
docker compose up -d
sleep 5
./scripts/generate-salts.sh
make healthcheck
```

## Environment Variables

Key variables are in `.env` (created from `.env.example`):

- `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`: Database config
- `WP_ENV`, `WP_HOME`, `WP_SITEURL`: WordPress URLs
- `AUTH_KEY`, `SECURE_AUTH_KEY`, etc.: Security keys (generate with `./scripts/generate-salts.sh`)
- `STRIPE_PUBLISHABLE_KEY`, `STRIPE_SECRET_KEY`: WooCommerce Stripe keys
- `GA4_MEASUREMENT_ID`, `META_PIXEL_ID`: Analytics keys

## Troubleshooting

### Services Won't Start
```bash
docker compose ps          # Check status
docker compose logs php    # View logs
make clean                 # Full cleanup
make bootstrap            # Retry
```

### Permission Issues
```bash
make shell
chmod -R 755 web/app/uploads
exit
```

### Health Check Fails
```bash
./scripts/healthcheck.sh --verbose    # Verbose output
docker compose logs                    # View all logs
```

### Port Already in Use
Find and stop the conflicting service using port 8080.

## Useful Commands After Bootstrap

```bash
make logs                             # View all logs
make shell                            # Access PHP container
make wp CMD='core version'            # Check WordPress version
make wp CMD='plugin list'             # List plugins
make healthcheck                      # Verify health
make down                             # Stop services
make clean                            # Full cleanup
```

## Documentation References

- [README.md](README.md) - Main documentation
- [QUICKSTART.md](QUICKSTART.md) - Quick setup guide
- [Makefile](Makefile) - Available commands
- [BOOTSTRAP_GUIDE.md](BOOTSTRAP_GUIDE.md) - This file
