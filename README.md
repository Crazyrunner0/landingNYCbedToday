# WordPress Development Stack

A modern WordPress development environment using Bedrock, Docker Compose, and PHP-FPM.

## Stack

- **WordPress**: Bedrock-based setup with Composer dependency management
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

## Prerequisites

- Docker (20.10+)
- Docker Compose (1.29+)
- Make (optional, but recommended)

## Quick Start

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

3. **Generate security salts**:
   
   You can either:
   - Run the automated script: `./scripts/generate-salts.sh`
   - Or manually visit https://roots.io/salts.html and update the security keys in your `.env` file

4. **Start the services**:
   ```bash
   make up
   ```

5. **Access WordPress**:
   
   Open your browser and navigate to http://localhost:8080
   
   Complete the WordPress installation wizard to create your admin account.

## Makefile Commands

The project includes a Makefile with several useful commands:

```bash
make install    # Install dependencies and setup environment
make build      # Build Docker images
make up         # Start all services
make down       # Stop all services
make restart    # Restart all services
make logs       # Show container logs
make shell      # Open shell in PHP container
make composer   # Run composer commands (e.g., make composer CMD='update')
make wp         # Run WP-CLI commands (e.g., make wp CMD='plugin list')
make clean      # Clean up containers, volumes, and vendor directory
```

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
# List plugins
make wp CMD='plugin list'

# Activate a plugin
make wp CMD='plugin activate plugin-name'

# Update WordPress
make wp CMD='core update'

# Create a new user
make wp CMD='user create username email@example.com --role=administrator'
```

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
