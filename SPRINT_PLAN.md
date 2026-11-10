# Zoventra FactoryOS — Sprint Plan & Delivery Sequence

**Version 1.0** · 10 November 2026

---

## Delivery Strategy

The backlog contains ~180 stories. They should **not** be built in numerical order without considering dependencies. The recommended sequence is stage-based:

| Stage | Focus | Key Stories | Exit Condition |
|---|---|---|---|
| **1** | Architecture & foundation | FOS-001 → FOS-024 | Tenancy middleware works, Docker deploy works, cross-tenant isolation verified |
| **2** | Organisation & product master | FOS-025 → FOS-042 | Factory, department, item, unit, variant setup works in UI |
| **3** | Inventory & purchasing | FOS-043 → FOS-065 | Complete cycle: PO → GRN → stock receipt → ledger |
| **4** | BOM & routing | FOS-066 → FOS-075 | Configurable BOM/routing with versions works |
| **5** | Production | FOS-076 → FOS-091 | Complete work-order lifecycle: plan → reserve → issue → output → finish |
| **6** | Quality & costing | FOS-092 → FOS-109 | Inspections pass/fail; actual cost reconciles with variance |
| **7** | Accounting | FOS-110 → FOS-133 | Double-entry cycle: all postings balance, period close works |
| **8** | Sales & dispatch | FOS-134 → FOS-141 | Order → reservation → dispatch → delivery note |
| **9** | Reports & integrations | FOS-142 → FOS-173 | Dashboards, exports, OpenAPI, webhooks |
| **10** | Pilot & hardening | All stories + acceptance tests | Customer pilot accepted, backup/restore tested |

---

## Critical Path

```
Inventory Ledger (Epic 5)
    ↓
Accounting Journal Engine (Epic 11)
    ↓
Production Transactions (Epic 8)
```

**Build and test the inventory ledger and accounting journal engine before allowing production transactions to post.** Production is where inventory and accounting become tightly connected.

---

## Suggested Sprint Cadence (2-week sprints)

| Sprint | Focus | Goal |
|---|---|---|
| Sprint 1 | Foundation | Laravel + React + Inertia scaffold, Docker, CI, tenant middleware |
| Sprint 2 | Tenancy & Identity | Tenants, companies, users, roles, permissions, factory access |
| Sprint 3 | Products & Organisation | Items, variants, units, factories, departments, work centres, warehouses |
| Sprint 4 | Inventory Ledger | Stock movements, balances, reservations, goods receipt/issue (atomic) |
| Sprint 5 | Purchasing | Suppliers, POs, GRN, stock receipt → ledger |
| Sprint 6 | BOM & Routing | BOM headers/versions/components, routings, operations |
| Sprint 7 | Production Planning | Production plans, production orders, material reservation |
| Sprint 8 | Production Execution | Work orders, material issues, output entry, scrap, completion |
| Sprint 9 | Quality | Inspection templates, incoming/in-process/final inspections, approvals |
| Sprint 10 | Costing | Standard costing, actual costing, work-order cost, variance |
| Sprint 11 | Accounting | Chart of accounts, journal engine, balanced posting, AR/AP |
| Sprint 12 | Financial Reporting | Trial balance, P&L, balance sheet, cash flow |
| Sprint 13 | Sales & Dispatch | Customers, sales orders, dispatch, delivery notes |
| Sprint 14 | Dashboards & Reports | Management dashboard, inventory/costing/accounting dashboards, exports |
| Sprint 15 | SaaS & Deployment | Subscriptions, licences, on-premises package, backup/restore |
| Sprint 16 | Hardening & Pilot | Acceptance tests, tenant isolation tests, performance tuning, customer pilot |

---

## Exit Criteria per Stage

### Stage 1 — Foundation
- [ ] Laravel app boots via Docker
- [ ] PostgreSQL + Redis connected
- [ ] Tenant context middleware sets `tenant_id` on every request
- [ ] Cross-tenant API request returns 403
- [ ] CI pipeline runs Pest tests on push

### Stage 2 — Organisation & Product Master
- [ ] CRUD for factories, departments, work centres, warehouses
- [ ] CRUD for items, variants, units, categories
- [ ] Unit conversion works
- [ ] Barcode generation works

### Stage 3 — Inventory & Purchasing
- [ ] Stock movement is atomic (movement + balance updated together)
- [ ] Cannot issue more stock than available (unless override)
- [ ] GRN creates stock movement + ledger entry
- [ ] Stock balance = OnHand − Reserved

### Stage 4 — BOM & Routing
- [ ] Multi-level BOM with versions
- [ ] Effective dates on BOM versions
- [ ] Routing with operations and work centres
- [ ] Production order preserves BOM + routing version at approval

### Stage 5 — Production
- [ ] Production order lifecycle: Draft → Approved → Material Reserved → In Production → Completed → Closed
- [ ] Material issue reduces stock + creates movement
- [ ] Production output creates finished goods
- [ ] Scrap/wastage/rework tracked separately

### Stage 6 — Quality & Costing
- [ ] Inspection templates with pass/fail criteria
- [ ] Finished goods blocked until quality approved
- [ ] Actual cost = material + labour + machine + overhead
- [ ] Variance report explains material/labour/overhead contributions

### Stage 7 — Accounting
- [ ] Every journal balances (Σ Dr = Σ Cr)
- [ ] Posted journals cannot be edited
- [ ] Reversals use compensating transactions
- [ ] Period closing protects history

### Stage 8 — Sales & Dispatch
- [ ] Sales order can reserve available stock
- [ ] Dispatch cannot exceed authorised available stock
- [ ] Delivery note generated on dispatch

### Stage 9 — Reports & Integrations
- [ ] Management dashboard with KPIs
- [ ] Excel/CSV/PDF exports
- [ ] OpenAPI docs published
- [ ] Webhooks fire on key events

### Stage 10 — Pilot & Hardening
- [ ] All acceptance criteria pass
- [ ] Tenant isolation tests pass across all modules
- [ ] Backup and restore tested
- [ ] On-premises offline operation verified

---

## Risk Management

| Risk | Mitigation |
|---|---|
| Building universal ERP before validating common engine | Launch with limited, well-tested manufacturing templates; add industry templates incrementally |
| Inventory and accounting divergence | Atomic cross-module posting; reconciliation report; drift alerts |
| Cross-tenant data access | Tenant-aware scopes + policies + automated isolation tests on every module |
| Poor early database decisions | Complete ERD and spec before migrations; review with stakeholders |
| Scope creep | Fixed MVP scope; P2/P3 items explicitly deferred |

---

## Definition of Done (per story)

- [ ] Code written following module structure standard
- [ ] Pest unit + feature tests written and passing
- [ ] Migration created and tested (up/down)
- [ ] Policy/authorisation implemented
- [ ] API endpoint documented (OpenAPI)
- [ ] Frontend page/component created (Inertia + React + TS)
- [ ] Code reviewed and merged to main
- [ ] No cross-tenant data leak (isolation test)