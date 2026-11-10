# Zoventra FactoryOS — Module Boundaries & Dependency Rules

**Version 1.0** · 10 November 2026

---

## 1. Core Architectural Rule

**Each module owns its business logic, policies, services, events, jobs, requests, and tests.**

Modules must NOT directly manipulate another module's internal tables.

Cross-module communication happens through:
1. Explicit application services (synchronous, transactional).
2. Domain events (asynchronous, non-critical follow-up).
3. Transactional outbox pattern (reliable event delivery).

---

## 2. Module List & Ownership

| Module | Domain | Key Tables Owned | Priority |
|---|---|---|---|
| Core | Platform bootstrap, health | system_settings, health_checks | P0 |
| Tenancy | Multi-tenant isolation | tenants, companies, company_settings | P0 |
| Identity | Auth, users, roles, permissions | users, tenant_users, roles, permissions, model_has_* | P0 |
| Organisation | Factory structure | factories, departments, shifts, work_centres, working_calendars, warehouse_locations | P1 |
| Configuration | Industry templates, settings | item_categories, units, unit_conversions, number_sequences, industry_templates, custom_fields | P1 |
| Products | Item master | items, item_variants, item_attributes, product_barcodes, product_documents | P1 |
| Inventory | Stock ledger | warehouses, warehouse_bins, stock_batches, stock_serial_numbers, stock_movements, stock_movement_lines, stock_balances, stock_reservations, stock_adjustments, stock_counts, stock_transfers, stock_valuation_layers | P1 |
| Purchasing | Procurement | suppliers, supplier_contacts, purchase_requisitions, purchase_orders, goods_receipts, supplier_invoices, purchase_returns | P1 |
| Manufacturing | BOM & routing | bom_headers, bom_versions, bom_components, bom_operations, routings, routing_versions, routing_operations, operation_parameters | P1 |
| Production | Planning & execution | production_plans, production_orders, work_orders, material_issues, material_returns, production_entries, production_scrap, production_downtime, finished_goods_receipts | P1 |
| Quality | Inspection | inspection_templates, quality_inspections, quality_defects, quality_nonconformances, quality_approvals | P1 |
| Costing | Manufacturing cost | standard_costs, production_costs, labour_costs, machine_costs, overhead_costs, cost_allocations, production_cost_variances | P1 |
| Sales | Customer orders | customers, sales_quotations, sales_orders, delivery_orders | P2 |
| Dispatch | Logistics | dispatches, delivery_notes | P2 |
| Accounting | Double-entry ledger | accounting_books, financial_years, accounting_periods, chart_of_accounts, journal_entries, journal_lines, customer_invoices, supplier_bills, customer_payments, supplier_payments, bank_accounts | P1 |
| Maintenance | Machines | machines, maintenance_schedules, maintenance_orders, machine_breakdowns | P2 |
| HR | Employees | employees, attendance_records, labour_entries | P3 |
| Reporting | Reports & dashboards | (read models / reporting queries only) | P1 |
| Notifications | Alerts | notifications, notification_templates, approval_workflows, approval_requests, approval_actions | P1 |
| Integrations | External connections | integration_connections, integration_logs, webhook_endpoints, api_tokens | P2 |

---

## 3. Module Interaction Rules

1. **A module may expose services and contracts to other modules.**
2. **A module must not write directly into another module's tables.**
3. **Cross-module transactions must use explicit application services.**
4. **Events should be used for non-critical asynchronous follow-up work.**
5. **Critical stock and accounting postings must remain transactionally safe.**
6. **Reporting must use approved read models or reporting queries.**
7. **Do not let frontend controllers contain business calculations.**
8. **Each module exposes a public Service layer as its contract.**

---

## 4. Standard Module Structure

```
ModuleName/
├── Database/
│   ├── Migrations/
│   └── Seeders/
├── Models/
├── Actions/           # Individual business operations
├── Services/          # Cohesive domain functionality (public contract)
├── DTOs/              # Data transfer objects for module contracts
├── Enums/
├── Events/            # Domain events published by this module
├── Listeners/         # Event listeners (own module + cross-module)
├── Jobs/              # Queue jobs
├── Policies/          # Authorisation policies
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Routes/
│   ├── web.php
│   └── api.php
└── Tests/
```

---

## 5. Key Event Flows

### GoodsReceiptPosted
```
GoodsReceiptPosted
       |
       +----> Inventory creates stock movement (stock_movements)
       |
       +----> Accounting posts inventory journal (journal_entries)
       |
       +----> Notification service sends update
       |
       +----> Reporting refreshes relevant metrics
```

### ProductionOrderCompleted
```
ProductionOrderCompleted
       |
       +----> Inventory creates finished goods stock movement
       |
       +----> Costing calculates actual work-order cost
       |
       +----> Accounting posts WIP → Finished Goods journal
       |
       +----> Quality checks if inspection required
       |
       +----> Notification service sends completion alert
```

### JournalPosted
```
JournalPosted
       |
       +----> Accounting updates account balances
       |
       +----> Reporting refreshes financial metrics
       |
       +----> Notification service (if alert configured)
```

---

## 6. Transactional Outbox Pattern

For reliable cross-module processing:

1. A committed business transaction and its pending events are recorded together.
2. An outbox table stores pending events with the business transaction in the same DB transaction.
3. A relay process publishes outbox events to the queue/message broker.
4. Queue failures cannot silently lose important financial or inventory events.

**Outbox table columns:**
- id, aggregate_type, aggregate_id, event_type, payload, occurred_at, processed_at, attempts

---

## 7. Dependency Direction (Acyclic)

```
Platform Core  ◄── Tenancy  ◄── Identity
                                    │
                                    ▼
                              Organisation ── Configuration
                                    │              │
                                    ▼              ▼
                              Products ◄── Inventory
                                    │              │
                                    ▼              ▼
                              Purchasing ◄── Manufacturing
                                    │              │
                                    ▼              ▼
                              Production ── Quality ── Costing
                                    │
                                    ▼
                              Sales ── Dispatch ── Accounting
                                    │
                                    ▼
                              Maintenance ── HR ── Reporting ── Notifications ── Integrations
```

**Rule:** Lower-level modules (Tenancy, Identity, Organisation, Configuration, Products, Inventory) are depended upon by higher-level modules. Higher-level modules never export write access to lower-level module tables.

---

## 8. Module Service Contract Example (Inventory Module)

```php
namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\DTOs\StockMovementDTO;
use App\Modules\Inventory\Enums\MovementType;

interface StockMovementService
{
    public function postMovement(StockMovementDTO $dto): StockMovement;
    public function reserveStock(string $itemId, int $warehouseId, int $quantity): StockReservation;
    public function releaseReservation(string $reservationId): void;
    public function getAvailableQuantity(string $itemId, int $warehouseId): int;
}
```

Other modules depend on this interface, not on `StockMovement` model directly.