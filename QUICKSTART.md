# Quick Start Guide

## Fastest Path: Bootstrap (Recommended)

The fastest way to get started is to use the bootstrap target, which automates the entire setup:

```bash
make bootstrap
```

This single command will:
1. Install all PHP dependencies via Composer
2. Start all Docker services (nginx, php-fpm, MariaDB, Redis)
3. Run health checks to verify everything is working
4. Display next steps and useful commands

After bootstrap completes, follow the on-screen instructions to:
1. Open http://localhost:8080 in your browser
2. Complete the WordPress installation wizard
3. Create your admin account

## Manual Setup Steps (Alternative)

If you prefer to set up manually or need finer control:

```bash
# 1. Install dependencies
make install

# 2. Generate security salts (optional but recommended)
./scripts/generate-salts.sh

# 3. Start services
make up

# 4. Verify setup is working
make healthcheck
```

## Access Points

- **WordPress**: http://localhost:8080
- **Database**: localhost:3306 (user: wordpress, pass: wordpress)
- **Redis**: localhost:6379

## Verifying Your Setup

After starting the services, verify everything is working:

```bash
# Run comprehensive health check
make healthcheck

# Or with verbose output for troubleshooting
./scripts/healthcheck.sh --verbose
```

The health check verifies:
- ✓ All Docker containers are running
- ✓ WordPress core files are installed
- ✓ Database connectivity works
- ✓ WordPress is installed and accessible
- ✓ Admin backend is reachable

## Using WP-CLI for Setup

You can also use WP-CLI commands to automate WordPress setup:

```bash
# Check WordPress version
make wp CMD='core version'

# Check if WordPress is installed
make wp CMD='core is-installed'

# Get WordPress information
make wp CMD='site get'

# Activate plugins
make wp CMD='plugin activate woocommerce'

# List installed plugins
make wp CMD='plugin list'
```

## Common Commands

```bash
# Start services
make up

# Stop services
make down

# Restart services
make restart

# View logs
make logs

# Open PHP container shell
make shell

# Run Composer commands
make composer CMD='require vendor/package'

# Run WP-CLI commands
make wp CMD='plugin list'

# Clean everything
make clean
```

## WordPress Installation

1. Visit http://localhost:8080
2. Select language
3. Create admin account
4. Login and start developing!

## Troubleshooting

### Services won't start
```bash
docker compose down -v
make clean
make install
make up
```

### Permission issues
```bash
docker compose exec php chmod -R 755 web/app/uploads
```

### Database connection error
Check that all services are running:
```bash
docker compose ps
```

## Next Steps

- Install plugins: `make composer CMD='require wpackagist-plugin/plugin-name'`
- Install themes: `make composer CMD='require wpackagist-theme/theme-name'`
- Customize your theme in `web/app/themes/`
- Add custom plugins in `web/app/plugins/`

For more details, see [README.md](README.md)
