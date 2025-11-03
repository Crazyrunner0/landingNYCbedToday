# WordPress Development Stack

A modern WordPress development environment using Bedrock, Docker Compose, and PHP-FPM.

> 🛒 **WooCommerce E-Commerce Ready!** This stack includes a complete WooCommerce setup with Stripe payments, one-page checkout, and analytics tracking. See [README_WOOCOMMERCE.md](README_WOOCOMMERCE.md) for e-commerce documentation and [STRIPE_TEST_INTEGRATION.md](STRIPE_TEST_INTEGRATION.md) for Stripe test mode setup & testing.

## Stack

- **WordPress**: Bedrock-based setup with Composer dependency management
- **WooCommerce**: E-commerce platform with Stripe payment gateway
- **Web Server**: Nginx (Alpine)
- **PHP**: PHP 8.2-FPM (Alpine)
- **Database**: MariaDB (Latest)
- **Cache**: Redis (Alpine)

## Features

- Modern WordPress stack structure (Bedrock)
- Environment-based configuration with `.env` files
- Composer dependency management
- Docker containerization (nginx, php-fpm, MariaDB, Redis)
- Makefile for common development tasks
- Security best practices built-in
- **WooCommerce with Stripe, Apple Pay, Google Pay**
- **GA4 and Meta Pixel analytics tracking**
- **One-page checkout with reduced fields**
- Code quality tools: PHP CodeSniffer (WordPress Coding Standards), Prettier for formatting
- Automated CI/CD pipeline with GitHub Actions

## Development & Contributing

This project follows a **linear PR workflow** with automated code quality checks. For detailed contribution guidelines, see [CONTRIBUTING.md](CONTRIBUTING.md).

### Local Development Tooling

#### Required Tools
- PHP 8.0+ (with extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, json, opcache, bcmath)
- Composer (for PHP dependency management)
- Node.js 18+ (for formatting and development tools)

#### Setting Up Local Development

1. **Install PHP dependencies**:
   ```bash
   composer install
   ```

2. **Install Node dependencies**:
   ```bash
   npm install
   ```

3. **Verify your setup** by running the quality checks locally before pushing:
   ```bash
   # PHP code standards check
   composer test

   # Format check (JS, CSS, JSON, Markdown)
   npm run format:check

   # Auto-fix formatting issues
   npm run format
   ```

### Code Standards & Formatting

- **PHP**: [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) enforced via PHPCS
- **JavaScript, CSS, JSON, Markdown**: Prettier formatting
- **EditorConfig**: Use `.editorconfig` for editor-consistent indentation and line endings

### Pre-Push Checklist

Before pushing your changes:
1. Ensure `composer test` passes
2. Ensure `npm run format:check` passes
3. All changes follow project code standards
4. Branch naming follows the convention (e.g., `feature/`, `fix/`, `chore/`)

## Prerequisites

- Docker (20.10+)
- Docker Compose (1.29+)
- Make (optional, but recommended)

## Quick Start

### Fastest Setup with Bootstrap (Recommended)

The simplest way to get started is with a single command that handles everything:

```bash
# Clone the repository
git clone <repository-url>
cd <repository-name>

# Bootstrap everything in one go
make bootstrap
```

This will:
1. Create `.env` from `.env.example`
2. Install all PHP dependencies via Composer
3. Start all Docker services (nginx, php-fpm, MariaDB, Redis)
4. Run health checks to verify everything works
5. Display next steps

Then simply:
1. Open http://localhost:8080 in your browser
2. Complete the WordPress installation wizard
3. Create your admin account

**For detailed bootstrap information, see [BOOTSTRAP_GUIDE.md](BOOTSTRAP_GUIDE.md).**

### Manual Step-by-Step Setup

If you prefer more control, follow these steps:

1. **Clone the repository**:
    ```bash
    git clone <repository-url>
    cd <repository-name>
    ```

2. **Install dependencies and setup environment**:
    ```bash
    make install
    ```

    This will:
    - Create `.env` from `.env.example`
    - Install Composer dependencies
    - Download WordPress core

3. **Generate security salts** (optional but recommended):

    You can either:
    - Run the automated script: `./scripts/generate-salts.sh`
    - Or manually visit https://roots.io/salts.html and update the security keys in your `.env` file

4. **Start the services**:
    ```bash
    make up
    ```

5. **Verify setup with health check**:
    ```bash
    make healthcheck
    ```

6. **Access WordPress**:

    Open your browser and navigate to http://localhost:8080

    Complete the WordPress installation wizard to create your admin account.

7. **WooCommerce Setup** (Optional):

    For e-commerce functionality, see [QUICKSTART_WOOCOMMERCE.md](QUICKSTART_WOOCOMMERCE.md) for quick setup or [README_WOOCOMMERCE.md](README_WOOCOMMERCE.md) for full documentation.

## WooCommerce E-Commerce

This stack includes a complete WooCommerce setup. Features:

- ✅ **Stripe Payments** (test mode with Apple Pay/Google Pay)
- ✅ **One-Page Checkout** with reduced fields
- ✅ **Auto-Seeded Products** (4 mattresses + 3 add-ons)
- ✅ **Analytics Tracking** (GA4 + Meta Pixel)
- ✅ **Sticky Mobile CTA**

**Quick Start:** See [QUICKSTART_WOOCOMMERCE.md](QUICKSTART_WOOCOMMERCE.md)

**Documentation:**
- [README_WOOCOMMERCE.md](README_WOOCOMMERCE.md) - Complete e-commerce guide
- [WOOCOMMERCE_SETUP.md](WOOCOMMERCE_SETUP.md) - Detailed setup instructions
- [TESTING_CHECKLIST.md](TESTING_CHECKLIST.md) - Testing procedures

## Core Pages Scaffolding

The stack includes a deterministic pages seeding system that creates a complete site structure with placeholder content:

### Features

- ✅ **Four Core Pages** (Home, Checkout, Privacy, Terms)
- ✅ **Gutenberg Block Content** - Fully customizable via block editor
- ✅ **Navigation Menus** - Primary header menu and footer menu
- ✅ **Idempotent** - Safe to run multiple times without duplication
- ✅ **Theme Integration** - Uses Blocksy child theme styling

### Quick Start

After WordPress installation is complete:

```bash
# Create pages and menus
make seed-pages
```

This creates:
- Home page as front page (/)
- Checkout page (/checkout/)
- Privacy policy (/privacy-policy/)
- Terms & conditions (/terms/)
- Primary navigation menu (header)
- Footer navigation menu

**For detailed documentation:** See [SEED_PAGES_GUIDE.md](SEED_PAGES_GUIDE.md)

## Makefile Commands

The project includes a Makefile with several useful commands:

```bash
make bootstrap   # Complete setup: install deps, start services, verify with health check
make install     # Install dependencies and setup environment
make build       # Build Docker images
make up          # Start all services
make down        # Stop all services
make restart     # Restart all services
make logs        # Show container logs
make shell       # Open shell in PHP container
make composer    # Run composer commands (e.g., make composer CMD='update')
make wp          # Run WP-CLI commands (e.g., make wp CMD='plugin list')
make healthcheck # Run WordPress stack health check
make clean       # Clean up containers, volumes, and vendor directory
```

### Bootstrap Command

The `make bootstrap` command is the fastest way to set up everything:

```bash
make bootstrap
```

This single command performs:
1. Installs all PHP dependencies via Composer
2. Starts all Docker services (nginx, php-fpm, MariaDB, Redis)
3. Waits for services to stabilize
4. Runs comprehensive health checks
5. Displays completion status and next steps

## Manual Setup (without Make)

If you prefer not to use Make, you can run the commands manually:

1. **Create environment file**:
   ```bash
   cp .env.example .env
   ```

2. **Install dependencies**:
   ```bash
   docker compose run --rm php composer install
   ```

3. **Start services**:
   ```bash
   docker compose up -d
   ```

## Directory Structure

```
.
├── config/                 # WordPress configuration files
│   ├── application.php     # Main application config
│   └── environments/       # Environment-specific configs
├── docker/                 # Docker configuration files
│   ├── nginx/             # Nginx configuration
│   └── php/               # PHP-FPM configuration
├── web/                    # Public web root
│   ├── app/               # WordPress content directory
│   │   ├── mu-plugins/    # Must-use plugins
│   │   ├── plugins/       # Plugins
│   │   ├── themes/        # Themes
│   │   └── uploads/       # Media uploads
│   ├── wp/                # WordPress core (managed by Composer)
│   ├── index.php          # WordPress entry point
│   └── wp-config.php      # WordPress config loader
├── .env.example           # Example environment variables
├── composer.json          # PHP dependencies
├── docker-compose.yml     # Docker services configuration
└── Makefile              # Development commands
```

## Environment Variables

Key environment variables (defined in `.env`):

- `DB_NAME`: Database name
- `DB_USER`: Database user
- `DB_PASSWORD`: Database password
- `DB_HOST`: Database host (default: `db`)
- `WP_ENV`: Environment (`development`, `staging`, `production`)
- `WP_HOME`: WordPress home URL
- `WP_SITEURL`: WordPress installation URL
- `REDIS_HOST`: Redis host (default: `redis`)
- `REDIS_PORT`: Redis port (default: `6379`)

## Accessing Services

- **WordPress**: http://localhost:8080
- **Database**: localhost:3306
  - User: `wordpress` (or value from `.env`)
  - Password: `wordpress` (or value from `.env`)
  - Database: `wordpress` (or value from `.env`)
- **Redis**: localhost:6379

## Development

### Installing Plugins

Install WordPress plugins using Composer:

```bash
make composer CMD='require wpackagist-plugin/plugin-name'
```

### Installing Themes

Install WordPress themes using Composer:

```bash
make composer CMD='require wpackagist-theme/theme-name'
```

### Using WP-CLI

You can use WP-CLI for various WordPress operations:

```bash
# Check WordPress version
make wp CMD='core version'

# Check if WordPress is installed
make wp CMD='core is-installed'

# List plugins
make wp CMD='plugin list'

# Activate a plugin
make wp CMD='plugin activate plugin-name'

# Update WordPress
make wp CMD='core update'

# Create a new user
make wp CMD='user create username email@example.com --role=administrator'
```

### Health Checks

To verify that your WordPress stack is running correctly, use the health check script:

```bash
# Run health check
make healthcheck

# Or with verbose output for detailed diagnostics
./scripts/healthcheck.sh --verbose
```

The health check verifies:
- ✓ All Docker containers are running
- ✓ WordPress core files are installed  
- ✓ Configuration files exist
- ✓ Database connectivity works
- ✓ WordPress is installed and accessible
- ✓ HTTP endpoint is reachable
- ✓ Redis cache is available

### Debugging

Development mode is enabled by default when `WP_ENV=development`. This enables:

- Error display
- Query logging
- Debug logging to `web/app/debug.log`
- Script debugging

Check logs:

```bash
make logs
```

## Production Deployment

Before deploying to production:

1. Set `WP_ENV=production` in your `.env` file
2. Generate unique security salts at https://roots.io/salts.html
3. Use strong, unique passwords for database credentials
4. Disable file modifications (`DISALLOW_FILE_MODS=true` - already set by default)
5. Enable HTTPS and update `WP_HOME` and `WP_SITEURL` accordingly
6. Configure proper backups for database and uploads directory

## Troubleshooting

### Permission Issues

If you encounter permission issues with uploads or cache:

```bash
docker compose exec php sh
chmod -R 755 web/app/uploads
```

### Database Connection Issues

Ensure the database service is running:

```bash
docker compose ps
docker compose logs db
```

### Clear Cache

To clear Redis cache:

```bash
docker compose exec redis redis-cli FLUSHALL
```

## Security

- File editing and modifications are disabled in production by default
- WordPress debug mode is disabled in production
- Sensitive files are protected via nginx configuration
- Environment variables are used for sensitive data
- Security headers are configured in nginx

## License

[Your License Here]

## Contributing

[Your Contributing Guidelines Here]
