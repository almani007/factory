# Zoventra FactoryOS — Development Backlog

**Version 1.0** · 10 November 2026

Priority: **P0** = Foundation/release blocker · **P1** = Required for initial operational release · **P2** = Next release · **P3** = Advanced

---

## Epic 1 — Project Foundation (P0)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-001 | Create Laravel project and repository | P0 |
| FOS-002 | Configure PHP 8.4 and Composer dependencies | P0 |
| FOS-003 | Configure React, TypeScript and Inertia.js | P0 |
| FOS-004 | Configure Tailwind CSS + shadcn/ui | P0 |
| FOS-005 | Configure PostgreSQL connection | P0 |
| FOS-006 | Configure Redis and queues | P0 |
| FOS-007 | Create Docker development environment | P0 |
| FOS-008 | Configure GitHub Actions CI pipeline | P0 |
| FOS-009 | Establish coding conventions (PSR-12, TS strict) | P0 |
| FOS-010 | Establish module architecture and directory structure | P0 |
| FOS-011 | Configure Pest testing framework | P0 |
| FOS-012 | Configure logging (Sentry) and exception handling | P0 |

---

## Epic 2 — Tenancy & Company Management (P0)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-013 | Create tenant management (CRUD) | P0 |
| FOS-014 | Create company registration and onboarding | P0 |
| FOS-015 | Implement tenant context middleware | P0 |
| FOS-016 | Implement tenant-aware database scopes | P0 |
| FOS-017 | Create company settings (currency, tax, timezone) | P0 |
| FOS-018 | Create factory management | P0 |
| FOS-019 | Create department management | P0 |
| FOS-020 | Create work centre management | P0 |
| FOS-021 | Create warehouse management | P0 |
| FOS-022 | Implement user-factory access matrix | P0 |
| FOS-023 | Implement tenant isolation tests (cross-tenant denial) | P0 |
| FOS-024 | Implement tenant-aware storage paths and cache keys | P0 |

---

## Epic 3 — Identity & Permissions (P0)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-025 | Implement authentication (login/logout) | P0 |
| FOS-026 | Implement password reset | P0 |
| FOS-027 | Implement user invitations | P0 |
| FOS-028 | Implement role management | P0 |
| FOS-029 | Implement permission management | P0 |
| FOS-030 | Implement factory-level permissions | P0 |
| FOS-031 | Implement user activity logs | P0 |
| FOS-032 | Implement privileged-user MFA | P1 |

---

## Epic 4 — Configuration & Product Master (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-033 | Create item categories | P1 |
| FOS-034 | Create units of measurement | P1 |
| FOS-035 | Implement unit conversions | P1 |
| FOS-036 | Create item master (SKU, name, description, etc.) | P1 |
| FOS-037 | Implement product variants | P1 |
| FOS-038 | Implement barcode management | P1 |
| FOS-039 | Implement batch and serial tracking | P1 |
| FOS-040 | Create industry templates | P1 |
| FOS-041 | Create configurable production operations | P1 |
| FOS-042 | Create configurable document sequences | P1 |

---

## Epic 5 — Inventory Management (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-043 | Create stock movement ledger (immutable) | P1 |
| FOS-044 | Create stock movement posting service (atomic) | P1 |
| FOS-045 | Create stock balance projection | P1 |
| FOS-046 | Implement stock reservations | P1 |
| FOS-047 | Implement goods receipt | P1 |
| FOS-048 | Implement goods issue | P1 |
| FOS-049 | Implement stock transfers | P1 |
| FOS-050 | Implement stock adjustments | P1 |
| FOS-051 | Implement stock counts | P1 |
| FOS-052 | Implement stock valuation (FIFO/WA) | P1 |
| FOS-053 | Implement low-stock alerts | P1 |
| FOS-054 | Implement inventory reports | P1 |
| FOS-055 | Implement stock reversal workflow | P1 |

---

## Epic 6 — Suppliers & Purchasing (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-056 | Create supplier management | P1 |
| FOS-057 | Create purchase requisitions | P1 |
| FOS-058 | Implement approval workflow | P1 |
| FOS-059 | Create supplier quotations | P1 |
| FOS-060 | Create purchase orders | P1 |
| FOS-061 | Implement partial goods receipt | P1 |
| FOS-062 | Create GRN (Goods Receipt Note) | P1 |
| FOS-063 | Implement purchase returns | P1 |
| FOS-064 | Implement supplier invoice recording | P1 |
| FOS-065 | Implement supplier performance reports | P2 |

---

## Epic 7 — BOM & Manufacturing Configuration (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-066 | Create BOM master | P1 |
| FOS-067 | Implement BOM versions | P1 |
| FOS-068 | Implement BOM components | P1 |
| FOS-069 | Implement alternative materials | P1 |
| FOS-070 | Implement BOM costing | P1 |
| FOS-071 | Create routing master | P1 |
| FOS-072 | Implement routing versions | P1 |
| FOS-073 | Implement routing operations | P1 |
| FOS-074 | Implement work centre capacity | P1 |
| FOS-075 | Implement industry templates for BOM and routing | P1 |

---

## Epic 8 — Production Planning & Execution (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-076 | Create production plans | P1 |
| FOS-077 | Implement make-to-stock planning | P1 |
| FOS-078 | Implement make-to-order planning | P1 |
| FOS-079 | Create production orders | P1 |
| FOS-080 | Implement material availability checks | P1 |
| FOS-081 | Implement material reservations | P1 |
| FOS-082 | Create work orders | P1 |
| FOS-083 | Implement operation scheduling | P1 |
| FOS-084 | Implement material issue to production | P1 |
| FOS-085 | Implement material returns | P1 |
| FOS-086 | Implement production output entry | P1 |
| FOS-087 | Implement scrap and wastage | P1 |
| FOS-088 | Implement rework | P1 |
| FOS-089 | Implement downtime recording | P1 |
| FOS-090 | Implement production completion | P1 |
| FOS-091 | Implement finished goods receipt | P1 |

---

## Epic 9 — Quality Management (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-092 | Create inspection templates | P1 |
| FOS-093 | Implement incoming inspections | P1 |
| FOS-094 | Implement in-process inspections | P1 |
| FOS-095 | Implement final inspections | P1 |
| FOS-096 | Implement defect classification | P1 |
| FOS-097 | Implement quality approvals | P1 |
| FOS-098 | Implement quality rejection and rework | P1 |
| FOS-099 | Implement quality reports | P1 |

---

## Epic 10 — Costing Engine (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-100 | Implement standard costing | P1 |
| FOS-101 | Implement actual material costing | P1 |
| FOS-102 | Implement labour costing | P1 |
| FOS-103 | Implement machine costing | P1 |
| FOS-104 | Implement overhead allocation | P1 |
| FOS-105 | Implement work-order costing | P1 |
| FOS-106 | Implement wastage costing | P1 |
| FOS-107 | Implement production variance analysis | P1 |
| FOS-108 | Implement unit cost analysis | P1 |
| FOS-109 | Implement costing reports | P1 |

---

## Epic 11 — Accounting & Finance (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-110 | Create accounting books (multi-book) | P1 |
| FOS-111 | Implement financial years and periods | P1 |
| FOS-112 | Create chart of accounts | P1 |
| FOS-113 | Create journal entry engine | P1 |
| FOS-114 | Implement balanced journal validation | P1 |
| FOS-115 | Implement journal posting and reversal | P1 |
| FOS-116 | Implement accounts receivable | P1 |
| FOS-117 | Implement accounts payable | P1 |
| FOS-118 | Implement customer invoices | P1 |
| FOS-119 | Implement supplier bills | P1 |
| FOS-120 | Implement customer receipts | P1 |
| FOS-121 | Implement supplier payments | P1 |
| FOS-122 | Implement payment allocations | P1 |
| FOS-123 | Implement cash and bank accounts | P1 |
| FOS-124 | Implement bank reconciliation | P1 |
| FOS-125 | Implement tax configuration (jurisdiction adapters) | P1 |
| FOS-126 | Implement accounting dimensions | P1 |
| FOS-127 | Implement fixed assets | P2 |
| FOS-128 | Implement depreciation | P2 |
| FOS-129 | Implement trial balance | P1 |
| FOS-130 | Implement profit and loss | P1 |
| FOS-131 | Implement balance sheet | P1 |
| FOS-132 | Implement cash flow report | P1 |
| FOS-133 | Implement financial period closing | P1 |

---

## Epic 12 — Sales & Dispatch (P2)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-134 | Customer management | P2 |
| FOS-135 | Sales quotations | P2 |
| FOS-136 | Sales orders | P2 |
| FOS-137 | Customer-specific production orders | P2 |
| FOS-138 | Stock reservation for sales | P2 |
| FOS-139 | Dispatch management | P2 |
| FOS-140 | Delivery notes | P2 |
| FOS-141 | Customer returns | P2 |

---

## Epic 13 — Maintenance & HR (P2/P3)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-142 | Machine registry | P2 |
| FOS-143 | Maintenance schedules | P2 |
| FOS-144 | Breakdown recording | P2 |
| FOS-145 | Maintenance work orders | P2 |
| FOS-146 | Spare parts management | P2 |
| FOS-147 | Employee management | P3 |
| FOS-148 | Attendance and shifts | P3 |
| FOS-149 | Labour hours recording | P2 |
| FOS-150 | Piece-rate production | P3 |

---

## Epic 14 — Reporting & Dashboards (P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-151 | Company dashboard | P1 |
| FOS-152 | Factory dashboard | P1 |
| FOS-153 | Production dashboard | P1 |
| FOS-154 | Inventory dashboard | P1 |
| FOS-155 | Costing dashboard | P1 |
| FOS-156 | Accounting dashboard | P1 |
| FOS-157 | Report filtering (date, factory, tenant) | P1 |
| FOS-158 | Excel and CSV exports | P1 |
| FOS-159 | PDF reports | P1 |
| FOS-160 | Scheduled reporting | P2 |

---

## Epic 15 — SaaS, Deployment & Integrations (P0/P1)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-161 | Subscription plans | P1 |
| FOS-162 | Subscription feature limits | P1 |
| FOS-163 | SaaS administration | P1 |
| FOS-164 | Dedicated tenant database option | P2 |
| FOS-165 | Docker production deployment | P0 |
| FOS-166 | On-premises deployment package | P1 |
| FOS-167 | Licence management (offline-capable) | P1 |
| FOS-168 | Backup and restore procedures | P0 |
| FOS-169 | OpenAPI documentation | P1 |
| FOS-170 | Integration framework | P2 |
| FOS-171 | Webhooks | P2 |
| FOS-172 | Email and WhatsApp integrations | P2 |
| FOS-173 | External accounting integrations (Xero/QuickBooks) | P2 |

---

## Epic 16 — Advanced Capabilities (P3)

| ID | Story / Task | Priority |
|---|---|---|
| FOS-174 | PWA and offline production entry | P3 |
| FOS-175 | AI production forecasting | P3 |
| FOS-176 | AI inventory recommendations | P3 |
| FOS-177 | AI costing analysis | P3 |
| FOS-178 | Machine telemetry integration | P3 |
| FOS-179 | Predictive maintenance | P3 |
| FOS-180 | Industrial edge agent (Go) | P3 |

---

## Recommended Delivery Sequence (by stage)

| Stage | Focus | Stories | Exit Condition |
|---|---|---|---|
| 1 | Architecture & foundation | FOS-001 to FOS-024 | Tenancy and deployment work |
| 2 | Organisation & product master | FOS-025 to FOS-042 | Factory and item setup works |
| 3 | Inventory & purchasing | FOS-043 to FOS-065 | Complete stock procurement cycle |
| 4 | BOM & routing | FOS-066 to FOS-075 | Configurable manufacturing definition |
| 5 | Production | FOS-076 to FOS-091 | Complete work-order lifecycle |
| 6 | Quality & costing | FOS-092 to FOS-109 | Actual production cost verified |
| 7 | Accounting | FOS-110 to FOS-133 | Complete double-entry cycle |
| 8 | Sales & dispatch | FOS-134 to FOS-141 | Order-to-delivery workflow |
| 9 | Reports & integrations | FOS-142 to FOS-173 | Management visibility |
| 10 | Pilot & hardening | All + acceptance tests | Customer acceptance |

**Critical path:** Build and test inventory ledger (Epic 5) and accounting journal engine (Epic 11) before allowing production transactions (Epic 8) to post.

---

## Acceptance Criteria (Initial Release)

| Test | Expected Outcome |
|---|---|
| Tenant isolation | No cross-company data access via API or UI |
| Stock receipt | Stock and ledger update atomically |
| Stock issue | Cannot exceed available quantity without override |
| BOM versioning | Existing production orders retain original BOM |
| Production | Material, output and wastage correctly recorded |
| Quality | Unapproved finished goods not released to inventory |
| Costing | Planned vs actual costs reconcile with variance explanation |
| Accounting | Every posted journal balances (Σ Dr = Σ Cr) |
| Accounting integration | Stock and finance transactions cannot silently diverge |
| Period closing | Closed periods protected from backdated postings |
| On-premises | Core operations work without central SaaS connectivity |
| Cloud | Multiple tenants operate safely in shared database |
| Backup | Database and files can be restored successfully |