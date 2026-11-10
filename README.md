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
| Backend | Laravel 13 |
| PHP | PHP 8.4 |
| Frontend | React + TypeScript |
| Application integration | Inertia.js |
| UI components | Tailwind CSS + shadcn/ui |
| Database | PostgreSQL |
| Cache | Redis |
| Queue processing | Laravel Queues + Horizon |
| Real-time | Laravel Reverb |
| Authentication | Laravel authentication + Sanctum |
| Permissions | Spatie Laravel Permission (tenant-aware) |
| File storage | S3-compatible or local |
| Testing | Pest |
| Monitoring | Sentry + Laravel Telescope |
| Deployment | Docker + Nginx |
| CI/CD | GitHub Actions |

## Architecture

**Modular monolith** — one Laravel application with clear boundaries between business domains. No separate microservice per module.

```
factoryos/
├── app/
│   ├── Modules/        (Core, Tenancy, Identity, Organisation, Configuration,
│   │   │                 Products, Inventory, Purchasing, Manufacturing, Production,
│   │   │                 Quality, Costing, Sales, Dispatch, Accounting, Maintenance,
│   │   │                 HR, Reporting, Notifications, Integrations)
│   ├── Shared/         (Contracts, Enums, Exceptions, Money, Quantity, Support)
│   └── Providers/
├── resources/js/       (React + TypeScript frontend)
├── routes/             (web.php, api.php)
├── database/           (migrations, seeders, factories)
├── tests/              (Feature, Integration, Unit)
├── docker/             (dev, staging, cloud, on-premises)
├── docs/               (erd, api, wireframes, architecture)
└── deployment/         (cloud, on-premises)
```

## Module Boundaries

Each module owns its business logic, policies, services, events, jobs, requests, and tests. Modules must not directly manipulate another module's internal tables. Cross-module communication happens through explicit application services and events (transactional outbox pattern).

See `docs/MODULE_BOUNDARIES.md` for the complete module interaction rules and dependency map.

## Key Engineering Principles

- **Tenant isolation** — every tenant-owned record carries `tenant_id`; factory-specific records additionally carry `factory_id`. Company A must never access Company B's records.
- **Immutable inventory ledger** — `stock_movements` / `stock_movement_lines` are the authoritative stock history; `stock_balances` is a controlled projection.
- **Double-entry accounting** — `journal_entries` / `journal_lines` must balance (`Σ Debits = Σ Credits`); posted journals cannot be silently changed.
- **BOM versioning** — a production order retains the exact BOM/routing version used at approval.
- **Atomic transactions** — stock postings and journal postings are atomic.
- **Configurable manufacturing** — operations, routings, and work centres are data-driven, not hardcoded.
- **Cloud + on-premises** — same codebase, Docker-based deployment; licensing supports offline operation.

## Documentation

- `FINAL_SRS.md` — Final Software Requirements Specification
- `DATABASE_ERD.md` — Complete database ERD and schema catalogue
- `MODULE_BOUNDARIES.md` — Module boundaries, dependency rules, and event flows
- `DEVELOPMENT_BACKLOG.md` — Epics, stories, priorities (P0/P1/P2/P3)
- `API_SPEC.md` — Versioned REST API specification (/api/v1)
- `ACCOUNTING_INVENTORY_TRANSACTION_SPEC.md` — Exact posting rules, costing and reversal behaviour
- `SPRINT_PLAN.md` — Delivery sequence and exit conditions per stage

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
# Clone the repository
git clone <repo-url> factory
cd factory

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
docker-compose -f docker-compose.yml up -d
```

### Docker

```bash
# Development
docker-compose -f docker-compose.yml up -d

# Cloud production
docker-compose -f docker-compose.prod.yml up -d

# On-premises
docker-compose -f docker-compose.onprem.yml up -d
```

## Testing

```bash
# Pest (PHP)
vendor/bin/pest

# Frontend
npm run test
```

## Coding Conventions

- PSR-12 for PHP
- TypeScript strict mode for frontend
- Actions for individual business operations
- Services for cohesive domain functionality
- DTOs for explicit module contracts
- Pest for PHP testing

## Licensing

See `LICENSE` and `SECURITY.md`.