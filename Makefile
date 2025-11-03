.PHONY: help install up down restart logs shell composer wp clean build

# Default target
help:
	@echo "Available commands:"
	@echo "  make install    - Install dependencies and setup environment"
	@echo "  make build      - Build Docker images"
	@echo "  make up         - Start all services"
	@echo "  make down       - Stop all services"
	@echo "  make restart    - Restart all services"
	@echo "  make logs       - Show container logs"
	@echo "  make shell      - Open shell in PHP container"
	@echo "  make composer   - Run composer commands (e.g., make composer CMD='update')"
	@echo "  make wp         - Run WP-CLI commands (e.g., make wp CMD='plugin list')"
	@echo "  make clean      - Clean up containers, volumes, and vendor directory"

# Install dependencies
install:
	@if [ ! -f .env ]; then cp .env.example .env; echo ".env file created from .env.example"; fi
	docker compose run --rm php composer install
	@echo "Installation complete! Run 'make up' to start the services."

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

# Clean up everything
clean:
	docker compose down -v
	rm -rf vendor web/wp web/app/plugins/* web/app/mu-plugins/* web/app/themes/*
	@echo "Cleanup complete!"
