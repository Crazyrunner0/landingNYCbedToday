.PHONY: help install bootstrap build up down restart logs shell composer wp clean healthcheck seed-pages seed-brand-template seed-brand-from-template preview.up preview.down preview.status

# Default target
help:
    @echo "Available commands:"
    @echo "  make bootstrap   - Complete setup: install deps, start services, setup WordPress"
    @echo "  make install     - Install dependencies and setup environment"
    @echo "  make build       - Build Docker images"
    @echo "  make up          - Start all services"
    @echo "  make down        - Stop all services"
    @echo "  make restart     - Restart all services"
    @echo "  make logs        - Show container logs"
    @echo "  make shell       - Open shell in PHP container"
    @echo "  make composer    - Run composer commands (e.g., make composer CMD='update')"
    @echo "  make wp          - Run WP-CLI commands (e.g., make wp CMD='plugin list')"
    @echo "  make seed-pages  - Create core pages with placeholder content and navigation menus"
    @echo "  make healthcheck          - Run WordPress stack health check"
    @echo "  make clean                - Clean up containers, volumes, and vendor directory"
    @echo ""
    @echo "Multibrand commands:"
    @echo "  make seed-brand-template      - Export current brand template"
    @echo "  make seed-brand-from-template - Seed pages from brand template"
    @echo ""
    @echo "Preview commands (Cloudflare Tunnel):"
    @echo "  make preview.up    - Start Cloudflare Tunnel for live preview"
    @echo "  make preview.down  - Stop Cloudflare Tunnel"
    @echo "  make preview.status - Check Cloudflare Tunnel status"

# Install dependencies
install:
    @if [ ! -f .env ]; then cp .env.example .env; echo ".env file created from .env.example"; fi
    docker compose run --rm php composer install
    @echo "Installation complete! Run 'make up' to start the services."

# Bootstrap: Complete setup from fresh clone
bootstrap:
    @echo "🚀 Starting WordPress stack bootstrap..."
    @echo ""
    @echo "Step 1: Installing dependencies..."
    @$(MAKE) install
    @echo ""
    @echo "Step 2: Starting Docker services..."
    @$(MAKE) up
    @echo ""
    @echo "Waiting for services to be ready..."
    @sleep 5
    @echo ""
    @echo "Step 3: Running health checks..."
    @./scripts/healthcheck.sh
    @echo ""
    @echo "✅ Bootstrap complete!"
    @echo ""
    @echo "Next steps:"
    @echo "1. Open http://localhost:8080 in your browser"
    @echo "2. Complete the WordPress installation wizard"
    @echo "3. Create your admin account"
    @echo ""
    @echo "Useful commands:"
    @echo "  • View logs:  make logs"
    @echo "  • WP-CLI:     make wp CMD='plugin list'"
    @echo "  • Shell:      make shell"

# Build Docker images
build:
    docker compose build

# Start services
up:
    docker compose up -d
    @echo "Services started! WordPress is available at http://localhost:8080"

# Stop services
down:
    docker compose down

# Restart services
restart:
    docker compose restart

# Show logs
logs:
    docker compose logs -f

# Open shell in PHP container
shell:
    docker compose exec php sh

# Run composer commands
composer:
    docker compose run --rm php composer $(CMD)

# Run WP-CLI commands
wp:
    docker compose exec php wp $(CMD) --allow-root

# Run health check
healthcheck:
    @./scripts/healthcheck.sh $(ARGS)

# Seed core pages with placeholder content
seed-pages:
    @echo "🌱 Seeding core pages..."
    docker compose exec php wp --allow-root eval-file scripts/seed-pages.php

# Export brand template
seed-brand-template:
    @echo "📤 Exporting brand template..."
    docker compose exec php wp --allow-root eval-file scripts/seed-brand-template.php

# Seed pages from brand template
seed-brand-from-template:
    @echo "🌱 Seeding brand from template..."
    docker compose exec php wp --allow-root eval-file scripts/seed-brand-from-template.php

# Clean up everything
clean:
    docker compose down -v
    rm -rf vendor web/wp web/app/plugins/* web/app/mu-plugins/* web/app/themes/*
    @echo "Cleanup complete!"

# Cloudflare Tunnel Preview Commands
preview.up:
    @./scripts/preview-tunnel.sh up

preview.down:
    @./scripts/preview-tunnel.sh down

preview.status:
    @./scripts/preview-tunnel.sh status

