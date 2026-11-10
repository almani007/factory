# Brixel FactoryOS — "factory"

Configurable Manufacturing ERP Platform — Version 1.0

## Overview

Brixel FactoryOS is a multi-tenant manufacturing management and ERP platform for small and medium factories (5–200 employees). It connects the entire manufacturing lifecycle:

01 Sales Order → 02 Production Planning → 03 Purchasing → 04 Raw Material Inventory → 05 Production → 06 Quality Control → 07 Finished Goods → 08 Dispatch & Accounts

One platform to answer: **What did we purchase, what did we manufacture, what did it actually cost, what did we sell, and how much profit did we make?**

## Approved Product Direction (Locked Decisions)

| Decision | Selection |
|---|---|
| Industry | Generic, configurable manufacturing |
| Target market | Pakistan and international |
| Manufacturing model | Make-to-order and make-to-stock |
| Deployment | Cloud SaaS and on-premises |
| Accounting | Full double-entry accounting |
| Factory size | 5–200 employees initially |

## Technology Stack

| Component | Technology |
|---|---|
| Backend | Laravel |
| PHP | PHP 8.4+ |
| Frontend | React + TypeScript + Vite |
| UI | Tailwind CSS + shadcn/ui |
| Database | PostgreSQL |
| Cache | Redis |
| Queue | Laravel Queues + Redis |
| Authentication | Laravel + Sanctum |
| Deployment | Docker + Nginx |

## Architecture

**Modular monolith with Domain-Driven Design (DDD)** — one Laravel application with bounded context modules in `src/`. Each module follows DDD layers:

```
src/{Module}/
├── Application/        # Use cases, services, DTOs, contracts
├── Domain/             # Entities, enums, exceptions, value objects
├── Infrastructure/     # Providers, persistence (models), middleware
│   ├── Provider/
│   └── Persistence/
└── Presentation/       # HTTP controllers, requests, resources
```

Modules: Tenancy, Identity, Organisation, Configuration, Products, Inventory, Purchasing, Manufacturing, Production, Quality, Costing, Sales, Dispatch, Accounting, Maintenance, HR, Reporting, Notifications, Integrations, Shared.

## Key Design Decisions

- **Tenant isolation** — Every tenant record carries `tenant_id`; factory records carry `factory_id`
- **Immutable inventory ledger** — `stock_movements` is the authoritative stock history
- **Double-entry accounting** — `journal_entries` / `journal_lines` must balance (Σ Debits = Σ Credits)
- **BOM versioning** — Production orders retain the exact BOM/routing version at approval
- **Atomic transactions** — Stock and accounting postings are atomic
- **Configurable manufacturing** — Operations, routings, and work centres are data-driven
- **Money/Quantity value objects** — Decimal precision, no floating-point for money

## Directory Structure

```
factory/
├── app/                    # Laravel core (Providers, Http, Console)
├── bootstrap/              # Laravel bootstrap
├── config/                 # Configuration files
├── database/               # Migrations (19 files), seeders, factories
├── docker/                 # Docker (frontend, nginx, php, postgres)
├── docs/                   # Documentation (srs)
├── public/                 # Public entry point
├── resources/              # CSS, JS, views
│   ├── js/
│   │   ├── app/            # App entry and layout
│   │   ├── features/       # Feature-based page components
│   │   │   ├── dashboard, inventory, purchasing, production,
│   │   │   │   quality, costing, sales, dispatch, accounting
│   │   │   └── shared/     # Shared pages registry
│   │   ├── modules/        # Shared modules (Link, pages registry)
│   │   └── ...
│   └── ...
├── routes/                 # Web and API routes
├── src/                    # DDD domain modules (19 modules)
├── tests/                  # Feature and Unit tests
├── artisan                 # Laravel CLI
├── composer.json           # PHP dependencies
├── docker-compose.yml      # Docker services
├── package.json            # JS dependencies
└── vite.config.js          # Vite configuration
```

## Getting Started

### Prerequisites
- PHP 8.4+
- Composer
- Node.js + npm
- PostgreSQL
- Redis
- Docker & Docker Compose (recommended)

### Development setup

```bash
# Copy environment
cp .env.example .env

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start development servers
npm run dev
php artisan serve

# Or use Docker
docker compose up -d
```

### Docker

```bash
# Development
docker compose up -d
```

## Testing

```bash
# Pest (PHP)
vendor/bin/pest

# Frontend
npm run test
```

## Documentation

- `docs/srs/SRS.md` — Software Requirements Specification

## Licensing

See `LICENSE` and `SECURITY.md`.