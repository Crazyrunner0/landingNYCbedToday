# Architecture Guide

## Bedrock Project Structure

This project is built on [Roots Bedrock](https://roots.io/bedrock/), a modern WordPress boilerplate that uses Composer for dependency management and environment-based configuration.

### Directory Structure

```
.
├── config/                    # WordPress configuration files
│   ├── application.php       # Main application configuration
│   └── environments/         # Environment-specific configurations
│       ├── development.php   # Development environment
│       ├── staging.php       # Staging environment
│       └── production.php    # Production environment
│
├── docker/                    # Docker configuration
│   ├── nginx/               # Nginx web server config
│   │   └── default.conf     # Nginx configuration
│   └── php/                 # PHP-FPM configuration
│       ├── Dockerfile      # PHP image build
│       └── php.ini          # PHP settings
│
├── web/                      # Public web root
│   ├── app/                 # WordPress content directory
│   │   ├── mu-plugins/      # Must-use plugins (auto-loaded)
│   │   │   ├── woocommerce-sameday-logistics.php
│   │   │   ├── analytics-integration.php
│   │   │   ├── cache-headers.php
│   │   │   ├── redis-cache.php
│   │   │   └── rankmath-setup.php
│   │   │
│   │   ├── plugins/         # Regular plugins
│   │   │   └── nycbedtoday-logistics/
│   │   │       ├── nycbedtoday-logistics.php
│   │   │       ├── includes/
│   │   │       └── assets/
│   │   │
│   │   ├── themes/          # WordPress themes
│   │   │   ├── blocksy/     # Parent Blocksy theme
│   │   │   └── blocksy-child/   # Custom child theme
│   │   │       ├── assets/
│   │   │       ├── blocks/
│   │   │       └── templates/
│   │   │
│   │   └── uploads/         # User media uploads
│   │
│   ├── wp/                  # WordPress core (managed by Composer)
│   ├── index.php            # WordPress entry point
│   ├── wp-config.php        # WordPress config loader
│   └── wp-cli-config.php    # WP-CLI configuration
│
├── scripts/                  # Development and deployment scripts
│   ├── bootstrap.sh         # Bootstrap script
│   ├── healthcheck.sh       # Health check script
│   ├── deploy-staging.sh    # Staging deployment script
│   ├── generate-salts.sh    # Generate security salts
│   └── preview-tunnel.sh    # Cloudflare tunnel script
│
├── vendor/                  # Composer dependencies (generated)
├── node_modules/           # NPM dependencies (generated)
│
├── .github/workflows/       # GitHub Actions CI/CD
│   ├── ci.yml              # Main CI pipeline
│   └── deploy-staging.yml  # Staging deployment workflow
│
├── docker-compose.yml       # Docker container configuration
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies
├── Makefile                # Development commands
└── README.md              # Main documentation
```

## Environment Configuration

The project supports multiple environments with different configurations:

### Environment Files

- `.env.example` - Template for environment variables (committed)
- `.env.local.example` - Template for local overrides
- `.env` - Local environment (created from `.env.example`, not committed)
- `.env.local` - Local overrides (not committed)

### Environment Variables

**Database**:
- `DB_NAME` - Database name
- `DB_USER` - Database user
- `DB_PASSWORD` - Database password
- `DB_HOST` - Database host (default: `db`)

**WordPress**:
- `WP_ENV` - Environment: `development`, `staging`, or `production`
- `WP_HOME` - WordPress home URL (e.g., `http://localhost:8080`)
- `WP_SITEURL` - WordPress installation URL (e.g., `http://localhost:8080/wp`)
- `WP_DEBUG` - Debug mode (development only)

**Cache & Performance**:
- `REDIS_HOST` - Redis host (default: `redis`)
- `REDIS_PORT` - Redis port (default: `6379`)

**Payment & Analytics**:
- `STRIPE_PUBLISHABLE_KEY` - Stripe public key
- `STRIPE_SECRET_KEY` - Stripe secret key
- `GA4_MEASUREMENT_ID` - Google Analytics 4 ID
- `META_PIXEL_ID` - Meta Pixel ID

**Security**:
- `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `LOGGED_IN_SALT`, etc. - WordPress security salts
- Generate with: `./scripts/generate-salts.sh`

### Configuration Hierarchy

WordPress configuration is loaded in this order:
1. `config/application.php` - Base configuration
2. `config/environments/{WP_ENV}.php` - Environment-specific overrides
3. `.env` - Environment variables

This allows different behaviors per environment:

**Development** (`WP_ENV=development`):
- Debug mode enabled
- Error display turned on
- Query logging enabled
- DISALLOW_INDEXING disabled

**Staging** (`WP_ENV=staging`):
- Debug mode disabled
- Improved performance
- DISALLOW_INDEXING enabled (prevents search indexing)
- Cache headers configured (5 min)

**Production** (`WP_ENV=production`):
- All debugging disabled
- File modifications disabled
- Strong cache headers (1 hour)
- Performance optimized

## Docker Architecture

The project uses Docker Compose to orchestrate multiple services:

### Services

**nginx** (web server):
- Alpine Linux base
- HTTP/HTTPS serving
- SSL certificate support
- Port: 8080 (local), 80/443 (production)

**php-fpm** (application server):
- PHP 8.2 with FPM
- Extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, json, opcache, bcmath
- Composer auto-installation support

**db** (database):
- MariaDB (MySQL-compatible)
- Auto-initialization with SQL scripts
- Persistent volume for data

**redis** (cache):
- In-memory data store
- Object cache for WordPress
- Session cache
- Port: 6379

### Container Communication

Services communicate via internal Docker network:
- nginx accesses PHP via `php:9000`
- PHP accesses database via `db:3306`
- PHP accesses Redis via `redis:6379`

### Volumes

- `web/` - Mounted into PHP and nginx containers
- Database data - Named volume `wordpress_db_data`
- Redis data - Named volume `wordpress_redis_data`

## Core Plugins & Features

### Must-Use Plugins (`web/app/mu-plugins/`)

These plugins are automatically loaded on every request.

**rankmath-setup.php**:
- RankMath SEO configuration
- JSON-LD structured data
- XML sitemap generation
- robots.txt management

**woocommerce-sameday-logistics.php**:
- Same-day delivery slot integration
- Checkout field integration
- Order metadata storage
- Email customization

**analytics-integration.php**:
- Google Analytics 4 event tracking
- Meta Pixel event tracking
- Automatic ecommerce event capture
- Debug mode support

**cache-headers.php**:
- HTTP cache header management
- Environment-aware cache durations
- Security headers (HSTS, X-Frame-Options, etc.)

**redis-cache.php**:
- Redis object cache configuration
- WordPress cache backend

### Plugin: nycbedtoday-logistics

Custom plugin for same-day delivery logistics:

- **Admin Interface**: Manage delivery slots and ZIP whitelist
- **Slot Generation**: Automatic 2-hour slot generation
- **Capacity Management**: Configure slot capacity
- **ZIP Whitelist**: Restrict delivery to specific ZIP codes
- **Blackout Dates**: Set dates when delivery is unavailable
- **Public Block**: Display slot selection on frontend
- **Shortcode**: `[nycbedtoday_logistics_slots]` for custom placement

### Theme: blocksy-child

Custom child theme extending Blocksy:

- **Custom Blocks**: Gutenberg blocks for landing page
- **Performance**: Critical CSS, font preloading, image optimization
- **Design System**: Tailwind CSS tokens and components
- **Accessibility**: WCAG AA contrast compliance
- **Mobile-first**: Responsive design

## Code Quality & CI/CD

### Local Development Tools

- **Composer** - PHP dependency management
- **PHPCS** - PHP Code Standards enforcement (WordPress Coding Standards)
- **Prettier** - Code formatting (JS, CSS, JSON, Markdown)
- **EditorConfig** - Editor consistency

### GitHub Actions Workflows

**ci.yml** - Main CI pipeline on every push/PR:
1. **Validate** - Composer validation and security audit
2. **Code Quality** - PHP CodeSniffer checks
3. **Format Check** - Prettier formatting validation
4. **Docker** - Docker image build and compose validation

**deploy-staging.yml** - Automated staging deployment:
1. Runs all CI checks
2. Builds frontend assets
3. Deploys to staging server via rsync/SSH

## Security Practices

1. **File Modifications**: Disabled in staging/production (`DISALLOW_FILE_MODS=true`)
2. **Security Keys**: Generated via script, stored in `.env`
3. **Database**: Uses `wp_` prefix with Bedrock's `DB_PREFIX`
4. **SSH Keys**: Dedicated deployment keys, stored in GitHub Secrets
5. **Search Indexing**: Disabled in staging/preview environments
6. **HTTPS**: Required in production

## Performance Optimization

1. **Redis Object Cache**: Caches WordPress database queries and transients
2. **Cache Headers**: HTTP caching configured per environment
3. **Critical CSS**: Inline critical above-the-fold CSS
4. **Image Optimization**: Lazy loading, format selection, sizing
5. **DNS Prefetch**: External resource preloading

## Related Documentation

- [Setup Guide](setup-local.md) - Local development setup
- [Deployment Guide](deployment.md) - Staging and production deployment
- [Design System](design-system.md) - Theme and component documentation
- [Custom Blocks](blocks.md) - Gutenberg block usage
- [Logistics Plugin](logistics.md) - Delivery slot management
- [SEO & Analytics](seo-analytics.md) - RankMath and analytics setup
