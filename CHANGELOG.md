# Brixel FactoryOS — CHANGELOG

## [1.0.0] — 2026-11-10

### Added
- Initial project scaffold for Brixel FactoryOS
- Final Software Requirements Specification (docs/srs/SRS.md)
- Complete Database ERD and schema catalogue (database/migrations/)
- Module boundaries and dependency rules (src/ DDD structure)
- Accounting & inventory transaction specification
- Versioned REST API specification (routes/api.php)
- Development backlog with stories across epics
- Sprint plan with delivery sequence
- Docker development environment (docker-compose.yml, Dockerfile)
- Nginx configuration
- Environment template (.env.example)
- Composer and package.json dependency manifests
- Git repository initialised
- Core business logic services (inventory ledger, double-entry accounting,
  production lifecycle, BOM versioning, costing engine, quality release gate,
  tenant onboarding)
- Frontend feature components (dashboard, inventory, purchasing, production,
  quality, costing, sales, dispatch, accounting)

### Technology Stack
- Laravel 13, PHP 8.5+
- React 19 + TypeScript 5.7 + Vite 6
- Tailwind CSS v4 + shadcn/ui
- PostgreSQL 18, Redis 8
- Pest testing, Laravel Telescope
- Docker + Nginx deployment

### Locked Decisions
- Industry: Generic, configurable manufacturing
- Market: Pakistan and international
- Manufacturing: Make-to-order and make-to-stock
- Deployment: Cloud SaaS and on-premises
- Accounting: Full double-entry accounting
- Factory size: 5–200 employees

### Stack Upgrade (2026-11-10)
- PHP: 8.4 → 8.5
- Laravel: 11 → 13
- React: 18 → 19
- TypeScript: 5.0 → 5.7
- Vite: 5 → 6
- Tailwind CSS: 3.4 → 4.0
- PostgreSQL: 16 → 18
- Redis: 7 → 8
- Node.js: 20 → 22
- brick/math: 0.10 → 0.11