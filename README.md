# WordPress Development Stack

A modern WordPress development environment using Bedrock, Docker Compose, and PHP-FPM with WooCommerce e-commerce, analytics tracking, and SEO optimization.

## Quick Links

📚 **Complete Documentation**: See [`docs/README.md`](docs/README.md) for full documentation index.

### Getting Started

- **[Setup Guide](docs/setup-local.md)** - Local development setup
- **[Architecture Guide](docs/architecture.md)** - Project structure and configuration
- **[Deployment Guide](docs/deployment.md)** - Staging and production deployment

### Features

- **[Design System](docs/design-system.md)** - Color palette, components, accessibility
- **[Custom Blocks](docs/blocks.md)** - Gutenberg blocks for landing pages
- **[Logistics System](docs/logistics.md)** - Same-day delivery management
- **[SEO & Analytics](docs/seo-analytics.md)** - RankMath, GA4, Meta Pixel
- **[Operations Runbook](docs/ops-runbook.md)** - Common operational tasks

## Quick Start

### One-Command Setup (Recommended)

```bash
# Clone repository
git clone <repository-url>
cd <repository-name>

# Bootstrap everything
make bootstrap
```

This will:
1. Create `.env` from `.env.example`
2. Install PHP and Node dependencies
3. Start all Docker services
4. Run health checks
5. Display next steps

Then open http://localhost:8080 and complete WordPress installation.

### Manual Setup

```bash
# Create environment file
cp .env.example .env

# Install dependencies
make install

# Start services
make up

# Run health check
make healthcheck
```

## Stack

- **WordPress**: Bedrock-based setup with Composer dependency management
- **WooCommerce**: E-commerce platform with Stripe payment gateway
- **Nginx**: Web server (Alpine Linux)
- **PHP 8.2**: PHP-FPM application server (Alpine Linux)
- **MariaDB**: SQL database
- **Redis**: Object cache server

## Key Features

✅ Modern WordPress stack (Bedrock)  
✅ Environment-based configuration  
✅ Docker containerization (nginx, PHP-FPM, MariaDB, Redis)  
✅ WooCommerce with Stripe & Apple Pay/Google Pay  
✅ GA4 and Meta Pixel analytics tracking  
✅ RankMath SEO with JSON-LD structured data  
✅ Same-day delivery logistics system  
✅ 6 custom Gutenberg blocks  
✅ Responsive design (Blocksy theme)  
✅ Automated CI/CD pipeline  

## Make Commands

```bash
# Setup
make bootstrap      # Complete one-command setup
make install        # Install dependencies
make build          # Build Docker images
make up             # Start all services
make down           # Stop all services

# Development
make shell          # Open shell in PHP container
make logs           # View container logs
make healthcheck    # Verify setup
make wp CMD='...'   # Run WP-CLI commands

# Cleanup
make clean          # Remove containers and volumes
```

For complete list: See [Setup Guide](docs/setup-local.md)

## WooCommerce & E-Commerce

This stack includes a complete WooCommerce setup:
- ✅ Stripe Payments (test mode with Apple Pay/Google Pay)
- ✅ One-Page Checkout with reduced fields
- ✅ Same-Day Delivery Integration
- ✅ Order Slot Reservations
- ✅ Analytics Tracking (GA4 + Meta Pixel)

See [Logistics System](docs/logistics.md) for delivery management and [SEO & Analytics](docs/seo-analytics.md) for tracking setup.

## Development

### Code Quality

Before pushing code:

```bash
# PHP standards
composer test

# Format check (JS, CSS, JSON, Markdown)
npm run format:check

# Auto-fix formatting
npm run format
```

### Local Development Tools

- **PHP 8.0+** (for local linting)
- **Composer** (for local dependency management)
- **Node.js 18+** (for asset building)
- **Docker & Docker Compose** (for containerization)

See [Setup Guide](docs/setup-local.md) for detailed setup instructions.

## Deployment

### Staging (Automated)

Code automatically deploys to staging when pushed to `main`:

```bash
git add .
git commit -m "feature: description"
git push origin main

# View deployment: GitHub → Actions → Deploy Staging
```

### Production (Manual)

Manual deployment to production via SSH/rsync:

```bash
# See Deployment Guide for complete setup
export PROD_HOST="production.example.com"
export PROD_PATH="/var/www/app"
export PROD_SSH_KEY="$(cat ~/.ssh/prod_deploy_key)"

bash scripts/deploy-staging.sh  # Reusable for all environments
```

For complete deployment guide: See [Deployment Guide](docs/deployment.md)

## Project Structure

```
.
├── config/              # WordPress configuration
├── docker/              # Docker configuration
├── web/                 # Public web root
│   ├── app/            # WordPress content directory
│   │   ├── plugins/    # Plugins (including nycbedtoday-logistics)
│   │   ├── themes/     # Themes (blocksy-child child theme)
│   │   └── uploads/    # Media files
│   └── wp/             # WordPress core (via Composer)
├── scripts/            # Development and deployment scripts
├── docs/               # Complete documentation
├── composer.json       # PHP dependencies
├── package.json        # Node.js dependencies
├── docker-compose.yml  # Docker services
├── Makefile           # Development commands
└── README.md          # This file
```

See [Architecture Guide](docs/architecture.md) for detailed structure.

## Environment Variables

Key variables (in `.env`):

```bash
# Database
DB_NAME=wordpress
DB_USER=wordpress
DB_PASSWORD=wordpress
DB_HOST=db

# WordPress
WP_ENV=development
WP_HOME=http://localhost:8080
WP_SITEURL=${WP_HOME}/wp

# Cache
REDIS_HOST=redis
REDIS_PORT=6379

# Optional: Analytics & Payments
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
META_PIXEL_ID=xxxxxxxxxx
STRIPE_PUBLISHABLE_KEY=pk_test_xxxxx
STRIPE_SECRET_KEY=sk_test_xxxxx
```

See [Architecture Guide](docs/architecture.md) for complete list.

## Troubleshooting

### Common Issues

**Docker services won't start**
- Check port 8080 isn't in use
- See [Setup Guide](docs/setup-local.md) → Troubleshooting

**WordPress installation stuck**
- Check database connection
- Clear browser cache
- See [Setup Guide](docs/setup-local.md) → Troubleshooting

**Build errors**
- Clear vendor directory: `rm -rf vendor/`
- Reinstall: `make install`
- Check PHP version: `php -v`

**Health check fails**
- View logs: `make logs`
- Restart services: `make restart`
- Check container status: `docker compose ps`

For more troubleshooting: See relevant documentation guide (Setup, Deployment, or Operations).

## Support & Resources

- **[Complete Documentation](docs/README.md)** - Full documentation index
- **[Setup Guide](docs/setup-local.md)** - Local development setup
- **[Deployment Guide](docs/deployment.md)** - Staging and production deployment
- **[Operations Runbook](docs/ops-runbook.md)** - Common tasks and troubleshooting
- **[WordPress Docs](https://wordpress.org/support/)** - Official WordPress documentation
- **[Bedrock Docs](https://roots.io/bedrock/docs/)** - Bedrock documentation
- **[Docker Docs](https://docs.docker.com/)** - Docker documentation

## Contributing

This project follows a linear PR workflow with automated code quality checks. See contribution guidelines in the repository.

**Pre-push checklist**:
1. Run `composer test` - PHP standards check
2. Run `npm run format:check` - Formatting check
3. All changes follow project code standards
4. Branch naming follows convention (e.g., `feature/`, `fix/`, `chore/`)

## License

[License details to be added]

---

**Need help?** Check the [documentation index](docs/README.md) or relevant guide for your task.
