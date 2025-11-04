# Documentation Index

Complete documentation for the WordPress development stack and deployment pipeline.

## Quick Start

**New to the project?** Start here:

1. **[Setup Guide](setup-local.md)** - Get your development environment running
2. **[Architecture Guide](architecture.md)** - Understand the project structure
3. **[Deployment Guide](deployment.md)** - Deploy to staging/production

## Documentation by Topic

### Development

- **[Architecture Guide](architecture.md)** - Project structure, Bedrock setup, Docker services, configuration hierarchy
- **[Setup Guide](setup-local.md)** - Local environment setup, Make commands, troubleshooting
- **[Design System](design-system.md)** - Color palette, typography, spacing, components, accessibility

### Features

- **[Custom Blocks](blocks.md)** - Gutenberg blocks for landing pages (Hero, Value Stack, CTA, etc.)
- **[Logistics System](logistics.md)** - Same-day delivery, ZIP validation, time slots, WooCommerce integration
- **[SEO & Analytics](seo-analytics.md)** - RankMath setup, JSON-LD schemas, GA4, Meta Pixel tracking

### Operations & Deployment

- **[Deployment Guide](deployment.md)** - Staging deployment (automated), production deployment, CI/CD workflow
- **[Production Launch Guide](production-launch.md)** - Complete production go-live checklist (DNS, SSL, Stripe, monitoring)
- **[Production Rollout Checklist](production-rollout-checklist.md)** - Step-by-step launch day procedures and validation
- **[Backup & Recovery Guide](backup-recovery.md)** - Database/uploads backups, automated scripts, restore procedures
- **[Multibrand Rollout Guide](multibrand.md)** - Setting up new brands, DNS/Cloudflare/Stripe per-brand, content templates, parametrized CI/CD
- **[Operations Runbook](ops-runbook.md)** - Daily operations, maintenance tasks, monitoring, troubleshooting

## Directory Structure

```
docs/
├── README.md                          # This file (index/TOC)
├── architecture.md                    # Bedrock structure, paths, environments
├── setup-local.md                     # Docker/Make targets, .env configuration
├── deployment.md                      # Staging/prod, GitHub Actions, rsync/ssh
├── production-launch.md               # Complete production go-live guide
├── production-rollout-checklist.md    # Launch day procedures and validation
├── backup-recovery.md                 # Database/uploads backups and restore procedures
├── multibrand.md                      # Multibrand setup, cloning guide, per-brand config
├── design-system.md                   # Theme tokens, components, accessibility
├── blocks.md                          # Custom Gutenberg blocks usage
├── logistics.md                       # ZIP/slots/cut-off admin and UX
├── seo-analytics.md                   # RankMath, JSON-LD, GA4/Pixel
└── ops-runbook.md                     # Cache, common commands, troubleshooting
```

## Common Tasks

### I want to...

**Set up local development**
→ [Setup Guide](setup-local.md)

**Understand the project structure**
→ [Architecture Guide](architecture.md)

**Deploy to staging**
→ [Deployment Guide](deployment.md)

**Manage deliveries/slots**
→ [Logistics System](logistics.md)

**Configure SEO or analytics**
→ [SEO & Analytics](seo-analytics.md)

**Customize the design/styling**
→ [Design System](design-system.md)

**Add custom Gutenberg blocks**
→ [Custom Blocks](blocks.md)

**Run common operations**
→ [Operations Runbook](ops-runbook.md)

**Deploy to production**
→ [Production Launch Guide](production-launch.md)

**Execute production launch day**
→ [Production Rollout Checklist](production-rollout-checklist.md)

**Backup or recover the database/uploads**
→ [Backup & Recovery Guide](backup-recovery.md)

**Set up a new sibling brand (multibrand rollout)**
→ [Multibrand Rollout Guide](multibrand.md)

## Core Concepts

### Bedrock

This project is built on [Roots Bedrock](https://roots.io/bedrock/), a modern WordPress boilerplate featuring:

- **Composer** for dependency management
- **Environment-based configuration** (.env variables)
- **Improved directory structure** (web root separation)
- **Security best practices** (sensitive files outside web root)

See [Architecture Guide](architecture.md) for details.

### Docker Compose

Services run in containers:
- **nginx** - Web server
- **php-fpm** - Application server
- **db** - MariaDB database
- **redis** - Cache server

Services communicate via Docker network. See [Setup Guide](setup-local.md) for more.

### Git Workflow

- **Branch**: Create feature branch
- **Develop**: Make changes and test locally
- **Push**: Push to GitHub
- **CI**: Automated tests run via GitHub Actions
- **Staging**: Auto-deploy to staging (if main branch)
- **Production**: Manual deployment via rsync/SSH

See [Deployment Guide](deployment.md) for details.

### Make Commands

Quick access to common tasks:

```bash
make bootstrap          # One-command setup
make up                # Start services
make shell             # Access PHP container
make wp CMD='...'      # Run WP-CLI commands
make healthcheck       # Verify everything works
```

See [Setup Guide](setup-local.md) for complete list.

## Key Features

✅ **Local Development** - Docker Compose with all services
✅ **Modern WordPress** - Bedrock structure with Composer
✅ **Responsive Design** - Mobile-first Blocksy theme
✅ **Custom Blocks** - 6 Gutenberg blocks for landing pages
✅ **E-Commerce** - WooCommerce with Stripe payments
✅ **Same-Day Delivery** - Custom logistics plugin
✅ **Analytics** - GA4 + Meta Pixel tracking
✅ **SEO** - RankMath with structured data
✅ **Automated Deployment** - GitHub Actions CI/CD
✅ **Performance** - Redis caching, optimized assets
✅ **Multibrand Support** - Configuration-based brand switching in <1 hour

## Technology Stack

- **WordPress** - CMS platform
- **Bedrock** - WordPress boilerplate
- **PHP 8.2** - Application language
- **MariaDB** - Database
- **Redis** - Object cache
- **Docker Compose** - Containerization
- **Nginx** - Web server
- **Node.js** - Asset building
- **GitHub Actions** - CI/CD pipeline

## External Resources

- [WordPress Documentation](https://wordpress.org/support/)
- [Bedrock Documentation](https://roots.io/bedrock/docs/)
- [Docker Documentation](https://docs.docker.com/)
- [WooCommerce Documentation](https://woocommerce.com/documentation/)
- [RankMath Documentation](https://rankmath.com/kb/)

## Getting Help

### Troubleshooting

Check the relevant guide for troubleshooting sections:
- Setup issues → [Setup Guide](setup-local.md)
- Deployment issues → [Deployment Guide](deployment.md)
- Performance issues → [Operations Runbook](ops-runbook.md)

### Common Issues

**Docker containers won't start**
→ See Setup Guide → Troubleshooting → Docker Services Won't Start

**WordPress installation stuck**
→ See Setup Guide → Troubleshooting → WordPress Installation Stuck

**Site showing 500 error**
→ See Operations Runbook → Troubleshooting → Site Down

**Analytics not tracking**
→ See SEO & Analytics → Troubleshooting → GA4 Not Tracking

### Emergency

For critical issues:
1. Check [Operations Runbook](ops-runbook.md) → Emergency Procedures
2. Run: `make healthcheck --verbose`
3. Check logs: `make logs`
4. See Troubleshooting section in relevant guide

## Documentation Updates

Last updated: 2024

**Adding new documentation?**
- Keep individual guides focused (1 topic per file)
- Link to related documentation at the end
- Include troubleshooting section
- Add to this index

## Related Projects

- [NYCBedToday - E-Commerce Site](../README.md)
- [nycbedtoday-logistics Plugin](../web/app/plugins/nycbedtoday-logistics/)
- [blocksy-child Theme](../web/app/themes/blocksy-child/)

---

**Questions or feedback?** Check the [Setup Guide](setup-local.md) for support resources.
