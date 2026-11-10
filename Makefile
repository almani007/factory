# Brixel FactoryOS — Makefile

.PHONY: help install dev test lint docker-up docker-down migrate seed fresh lint-fix key:generate cache:clear ci

help: ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install PHP and JS dependencies
	composer install
	npm install

dev: ## Start development environment
	docker compose up -d

docker-down: ## Stop and remove containers
	docker compose down

migrate: ## Run database migrations
	php artisan migrate

seed: ## Run database seeders
	php artisan db:seed

fresh: ## Fresh migrate and seed
	php artisan migrate:fresh --seed

test: ## Run Pest test suite
	vendor/bin/pest

lint: ## Run ESLint
	npx eslint resources/js

lint-fix: ## Auto-fix lint issues
	npx eslint resources/js --fix

format: ## Format code with Prettier
	npx prettier --write "resources/js/**/*.{js,ts,tsx,jsx}"

key:generate: ## Generate Laravel application key
	php artisan key:generate

cache:clear: ## Clear all Laravel caches
	php artisan optimize:clear

ci: ## Run full CI pipeline
	composer install --no-interaction --prefer-dist
	npm ci
	vendor/bin/pest
	npx eslint resources/js