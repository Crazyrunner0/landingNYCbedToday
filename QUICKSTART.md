# Quick Start Guide

## Initial Setup

```bash
# 1. Install dependencies
make install

# 2. Generate security salts
./scripts/generate-salts.sh

# 3. Start services
make up
```

## Access Points

- **WordPress**: http://localhost:8080
- **Database**: localhost:3306 (user: wordpress, pass: wordpress)
- **Redis**: localhost:6379

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
