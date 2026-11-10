# Zoventra FactoryOS — CHANGELOG

## [1.0.0] — 2026-11-10

### Added
- Initial project scaffold for Zoventra FactoryOS
- Final Software Requirements Specification (FINAL_SRS.md)
- Complete Database ERD and schema catalogue (DATABASE_ERD.md)
- Module boundaries and dependency rules (MODULE_BOUNDARIES.md)
- Accounting & inventory transaction specification (ACCOUNTING_INVENTORY_TRANSACTION_SPEC.md)
- Versioned REST API specification (API_SPEC.md)
- Development backlog with 180 stories across 16 epics (DEVELOPMENT_BACKLOG.md)
- Sprint plan with 16 two-week sprints (SPRINT_PLAN.md)
- Docker development environment (docker-compose.yml, Dockerfile)
- Nginx configuration
- Environment template (.env.example)
- Composer and package.json dependency manifests
- Git repository initialised

### Technology Stack
- Laravel 13, PHP 8.4
- React + TypeScript + Inertia.js
- Tailwind CSS + shadcn/ui
- PostgreSQL 16, Redis
- Pest testing, Sentry + Telescope
- Docker + Nginx deployment

### Locked Decisions
- Industry: Generic, configurable manufacturing
- Market: Pakistan and international
- Manufacturing: Make-to-order and make-to-stock
- Deployment: Cloud SaaS and on-premises
- Accounting: Full double-entry accounting
- Factory size: 5–200 employees