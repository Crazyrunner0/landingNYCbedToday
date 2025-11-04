# Local Development Setup

Complete guide to setting up the WordPress development environment locally.

## Prerequisites

### Required Tools

- **Docker** (20.10+) - Container runtime
- **Docker Compose** (1.29+) - Container orchestration
- **Make** (optional but recommended) - Task runner
- **PHP 8.0+** (optional, for local linting)
- **Composer** (optional, for local dependency management)
- **Node.js 18+** (optional, for local asset building)

### System Requirements

- **Disk Space**: 2GB+ for Docker images and vendor directories
- **RAM**: 2GB+ recommended for Docker services
- **Ports**: 8080 (nginx), 3306 (database), 6379 (Redis) must be available

## Quick Start (Recommended)

The fastest way to get started is with one command:

```bash
# Clone the repository
git clone <repository-url>
cd <repository-name>

# Bootstrap everything in one go
make bootstrap
```

This will:
1. Create `.env` from `.env.example`
2. Install PHP dependencies via Composer
3. Start all Docker services
4. Run health checks
5. Display next steps

Then open http://localhost:8080 and complete the WordPress installation wizard.

**Estimated time**: 2-3 minutes

## Step-by-Step Setup

### Step 1: Clone Repository

```bash
git clone <repository-url>
cd <repository-name>
```

### Step 2: Create Environment File

```bash
cp .env.example .env
```

This creates a `.env` file with default values:
- Database: `wordpress` / `wordpress`
- WordPress URL: `http://localhost:8080`
- Environment: `development`

### Step 3: Generate Security Keys (Optional but Recommended)

```bash
./scripts/generate-salts.sh
```

This adds security keys to `.env`. These are required for production but optional for local development.

### Step 4: Install Dependencies

```bash
make install
```

This runs:
```bash
docker compose run --rm php composer install
```

**What happens**:
- Downloads WordPress core (via Composer)
- Installs plugins and themes
- Installs PHP libraries
- Creates necessary directories

**Time**: 1-2 minutes on first run

### Step 5: Start Services

```bash
make up
```

Starts Docker containers:
- **nginx** (web server) on port 8080
- **php-fpm** (application server)
- **db** (MariaDB database)
- **redis** (cache server)

**Verify with**:
```bash
docker compose ps
```

All containers should show "Up" status.

### Step 6: Verify Health Check

```bash
make healthcheck
```

This checks:
- ✓ All Docker containers are running
- ✓ WordPress core files installed
- ✓ Database is accessible
- ✓ WordPress is responsive
- ✓ Redis cache is available

### Step 7: Access WordPress

Open http://localhost:8080 in your browser.

You'll see the WordPress installation wizard. Complete the setup:
1. Select language
2. Enter database credentials (from `.env`):
   - Database name: `wordpress`
   - User: `wordpress`
   - Password: `wordpress`
   - Host: `db`
3. Create admin account
4. Click "Install WordPress"

**Done!** WordPress is now running.

## Post-Installation Setup

### Seed Pages (Optional)

Create demo pages and navigation menus:

```bash
make seed-pages
```

This creates:
- Home page
- Checkout page
- Privacy policy page
- Terms & conditions page
- Navigation menus

### Enable WooCommerce (Optional)

For e-commerce functionality:

1. Complete WordPress installation
2. Visit Settings → WooCommerce to configure store
3. Add products and configure Stripe keys (see WooCommerce docs)

### Configure Local Analytics (Optional)

Add to `.env.local`:

```bash
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
META_PIXEL_ID=xxxxxxxxxx
```

## Makefile Commands

Quick reference for common tasks:

### Setup & Management

```bash
make bootstrap      # Complete one-command setup
make install        # Install dependencies
make up             # Start all services
make down           # Stop services
make restart        # Restart services
make clean          # Remove containers, volumes, vendor
```

### WordPress Operations

```bash
make wp CMD='plugin list'                    # List plugins
make wp CMD='plugin activate plugin-name'    # Activate plugin
make wp CMD='cache flush'                    # Flush cache
make wp CMD='user create username email...'  # Create user
```

### Development

```bash
make shell          # Open PHP container shell
make logs           # Show container logs
make composer CMD='update'  # Run Composer
make healthcheck    # Verify setup
```

### Preview

```bash
make preview.up     # Start Cloudflare tunnel preview
make preview.down   # Stop Cloudflare tunnel
make preview.status # Check tunnel status
```

## Environment Variables

Key variables in `.env`:

### Database

```bash
DB_NAME=wordpress          # Database name
DB_USER=wordpress          # Database user
DB_PASSWORD=wordpress      # Database password
DB_HOST=db                 # Database host (internal Docker DNS)
```

### WordPress

```bash
WP_ENV=development         # development, staging, or production
WP_HOME=http://localhost:8080        # Site home URL
WP_SITEURL=${WP_HOME}/wp            # WordPress installation URL
WP_DEBUG=true                        # Enable debug mode
```

### Cache

```bash
REDIS_HOST=redis           # Redis host
REDIS_PORT=6379            # Redis port
```

### Payment & Analytics (Optional)

```bash
STRIPE_PUBLISHABLE_KEY=pk_test_xxxxx
STRIPE_SECRET_KEY=sk_test_xxxxx
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
META_PIXEL_ID=xxxxxxxxxx
```

### Local Overrides

For development, you can override variables in `.env.local` without committing:

```bash
# Create .env.local
cp .env.local.example .env.local

# Then edit .env.local to override any variables
WP_DEBUG=true
```

## Troubleshooting

### Docker Services Won't Start

**Error**: `Error response from daemon: driver failed programming external connectivity...`

**Solution**: Port 8080 is already in use. Either:
- Stop the conflicting service: `lsof -i :8080 | kill -9 <PID>`
- Or edit `docker-compose.yml` to use a different port (e.g., 8081)

### Database Connection Failed

**Error**: `Error: Can't connect to database server`

**Solution**:
```bash
# Check database is running
docker compose logs db

# Wait a bit longer for DB to initialize (first run)
sleep 10

# Try health check again
make healthcheck
```

### Permission Denied on web/app/uploads

**Error**: `Permission denied` when uploading media

**Solution**:
```bash
docker compose exec php sh -c "chmod -R 755 web/app/uploads"
```

### WordPress Installation Stuck

**Error**: Installation wizard won't proceed

**Solution**:
1. Check PHP logs: `make logs`
2. Verify database credentials match `.env`
3. Clear browser cache and try again
4. Or restart services: `make restart && make healthcheck`

### Redis Connection Issues

**Error**: Redis errors in logs

**Solution**:
```bash
# Check Redis is running
docker compose ps redis

# Test Redis connection
docker compose exec redis redis-cli ping
# Should respond: PONG
```

### Composer Dependency Conflicts

**Error**: Composer install fails

**Solution**:
```bash
# Remove vendor directory and try again
rm -rf vendor/
make install

# Or clear composer cache
docker compose run --rm php composer clearcache
make install
```

## Useful Docker Commands

### Access PHP Container

```bash
make shell
# Inside container, you can run WP-CLI, PHP, etc.
wp plugin list
php -v
exit
```

### View Logs

```bash
# All services
make logs

# Specific service
docker compose logs php
docker compose logs db
docker compose logs nginx

# Tail logs (follow)
docker compose logs -f php
```

### Inspect Database

```bash
# Interactive MySQL/MariaDB client
docker compose exec db mysql -u wordpress -pwordpress wordpress

# Inside MySQL:
# SELECT * FROM wp_users;
# SELECT * FROM wp_options WHERE option_name='siteurl';
# exit;
```

### Check Running Containers

```bash
docker compose ps
```

Shows status, ports, and resource usage of all containers.

## Code Quality Checks

Before committing code, run:

### PHP Code Standards

```bash
composer test
```

Runs PHPCS with WordPress Coding Standards.

### Code Formatting

```bash
npm run format:check      # Check for formatting issues
npm run format            # Auto-fix formatting issues
```

Checks and formats: JavaScript, CSS, JSON, PHP, Markdown

## Performance Tips

1. **First Run**: Initial `make bootstrap` can take 2-3 minutes. Subsequent runs are faster.

2. **Composer Caching**: Install dependencies once, then changes rarely require re-install.

3. **Database**: First-time database initialization (mariadb) can take 30-60 seconds.

4. **Redis**: Once warmed up, Redis queries are very fast. Performance improves with traffic.

5. **Asset Building**: JS/CSS builds are fast (~5-10 seconds) with Webpack.

## Clean Up

To remove all Docker containers and volumes (resets everything):

```bash
make clean
```

This removes:
- All containers
- Docker volumes (database, Redis data)
- Vendor directory (Composer packages)
- Node modules

Then run `make bootstrap` again to start fresh.

**Warning**: This deletes the database. Only use if you want a clean slate.

## Next Steps

1. **Install Development Tools** (optional): `npm install`
2. **Seed Demo Pages**: `make seed-pages`
3. **Enable WooCommerce**: Configure products and Stripe keys
4. **Customize Theme**: Edit files in `web/app/themes/blocksy-child/`
5. **Deploy to Staging**: See [deployment.md](deployment.md)

## Need Help?

- Run `make healthcheck` to diagnose issues
- Check logs: `make logs`
- Docker documentation: https://docs.docker.com/
- WordPress documentation: https://wordpress.org/support/
- Bedrock documentation: https://roots.io/bedrock/docs/
