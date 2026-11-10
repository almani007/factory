# ============================================================================
# Brixel FactoryOS - Makefile
# ============================================================================

.PHONY: help install dev up down build migrate seed fresh test lint format shell-app shell-db ci status logs

help: ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install PHP and JS dependencies locally
	composer install --no-interaction --prefer-dist --optimize-autoloader
	npm install

dev: ## Start development environment (Docker)
	docker compose up -d --build

up: ## Start all services
	docker compose up -d

down: ## Stop all services
	docker compose down

build: ## Rebuild all containers
	docker compose up --build -d

restart: down up ## Restart all services

logs: ## Tail live logs
	docker compose logs -f

status: ## Show container status
	docker compose ps

migrate: ## Run database migrations
	docker compose exec app php artisan migrate --force

seed: ## Run database seeders
	docker compose exec app php artisan db:seed --force

fresh: ## Fresh migrate and seed
	docker compose exec app php artisan migrate:fresh --seed --force

test: ## Run test suite
	docker compose exec app vendor/bin/pest

lint: ## Run ESLint
	docker compose exec node npx eslint resources/js

format: ## Format code with Prettier
	docker compose exec node npx prettier --write "resources/js/**/*.{js,ts,tsx,jsx}"

shell-app: ## Open shell in app container
	docker compose exec app bash

shell-db: ## Open PostgreSQL shell
	docker compose exec postgres psql -U factory -d factory

key:generate: ## Generate Laravel application key
	docker compose exec app php artisan key:generate

cache:clear: ## Clear Laravel caches
	docker compose exec app php artisan optimize:clear

storage:link: ## Create storage symlink
	docker compose exec app php artisan storage:link

ci: ## Run full CI pipeline
	composer install --no-interaction --prefer-dist
	npm ci
	vendor/bin/pest
	npx eslint resources/js

setup: ## Run full server setup (first time on server)
	chmod +x scripts/setup.sh
	./scripts/setup.sh