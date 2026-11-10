# Brixel FactoryOS — Software Requirements Specification

**Version 1.0** · November 2026  
**Project:** factory (Brixel FactoryOS)  
**Status:** Approved — Implementation Baseline

---

## 1. Approved Product Direction

| Decision | Final Selection |
|---|---|
| Industry | Generic, configurable manufacturing |
| Target market | Pakistan and international |
| Manufacturing model | Make-to-order and make-to-stock |
| Deployment | Cloud SaaS and on-premises |
| Accounting | Full double-entry accounting |
| Factory size | 5–200 employees initially |

---

## 2. Product Definition

Brixel FactoryOS is a configurable manufacturing ERP platform for small and medium factories (5–200 employees). It connects the entire manufacturing lifecycle:

**01 Sales Order → 02 Production Planning → 03 Purchasing → 04 Raw Material Inventory → 05 Production → 06 Quality Control → 07 Finished Goods → 08 Dispatch & Accounts**

**Core value proposition:**

> Know what you purchased, what you manufactured, what it actually cost, what you sold and how much profit you made.

---

## 3. Technology Stack

| Component | Technology |
|---|---|
| Backend | Laravel 13 (modular monolith, DDD) |
| PHP | PHP 8.5+ |
| Frontend | React 19 + TypeScript 5.7 + Vite 6 |
| UI | Tailwind CSS v4 + shadcn/ui |
| Database | PostgreSQL 18 |
| Cache | Redis 8 |
| Queue | Laravel Queues + Redis |
| Authentication | Laravel + Sanctum |
| Deployment | Docker + Nginx |

---

## 4. Directory Structure (Pharmacy-style DDD)

```
factory/
├── app/                    # Laravel core (Providers, Http, Models, Console)
├── bootstrap/              # Laravel bootstrap
├── config/                 # Configuration files
├── database/               # Migrations, seeders, factories
├── docker/                 # Docker (frontend, nginx, php, postgres)
├── docs/                   # Documentation (srs, architecture/adrs, api)
├── public/                 # Public entry point
├── resources/              # CSS, JS, views
├── routes/                 # Web and API routes
├── src/                    # DDD domain modules
│   ├── Tenancy/
│   ├── Identity/
│   ├── Organisation/
│   ├── Configuration/
│   ├── Products/
│   ├── Inventory/
│   ├── Purchasing/
│   ├── Manufacturing/
│   ├── Production/
│   ├── Quality/
│   ├── Costing/
│   ├── Sales/
│   ├── Dispatch/
│   ├── Accounting/
│   ├── Maintenance/
│   ├── HR/
│   ├── Reporting/
│   ├── Notifications/
│   ├── Integrations/
│   └── Shared/
├── tests/                  # Feature and Unit tests
├── artisan                 # Laravel CLI
├── composer.json           # PHP dependencies
├── docker-compose.yml      # Docker services
├── package.json            # JS dependencies
└── vite.config.js          # Vite configuration
```

Each module in `src/` follows DDD layers:
- `Application/` — Use cases, services, DTOs, contracts
- `Domain/` — Entities, enums, exceptions, traits
- `Infrastructure/` — Providers, persistence (models), HTTP middleware
- `Presentation/` — HTTP controllers, requests, resources

---

## 5. Key Design Decisions

- **Modular monolith with DDD** — One Laravel application with bounded context modules in `src/`
- **Tenant isolation** — Every tenant record carries `tenant_id`; factory records carry `factory_id`
- **Immutable inventory ledger** — `stock_movements` is the authoritative stock history
- **Double-entry accounting** — `journal_entries` / `journal_lines` must balance (`Σ Debits = Σ Credits`)
- **BOM versioning** — Production orders retain the exact BOM/routing version at approval
- **Atomic transactions** — Stock and accounting postings are atomic
- **Configurable manufacturing** — Operations, routings, and work centres are data-driven
