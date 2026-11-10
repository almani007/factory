# Zoventra FactoryOS — Final Software Requirements Specification (SRS)

**Version 1.0** · 10 November 2026  
**Project:** factory (Zoventra FactoryOS)  
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

**Architectural consequences:**
1. Supporting both cloud SaaS and on-premises deployment requires deployment flexibility from the beginning.
2. Building full accounting requires a proper double-entry financial engine, not simply invoices, expenses and payment records.

---

## 2. Product Definition

### 2.1 Product Objectives

FactoryOS must provide an integrated system to:

- Manage multiple companies, factories and warehouses.
- Configure different manufacturing industries.
- Manage products, raw materials, semi-finished and finished goods.
- Manage procurement and suppliers.
- Define configurable Bills of Materials and manufacturing processes.
- Plan and execute production.
- Record material consumption, labour, machine time and wastage.
- Manage quality inspections.
- Calculate actual manufacturing costs.
- Manage customers, orders and dispatch.
- Maintain complete double-entry accounting.
- Produce financial and operational reports.
- Support cloud, dedicated cloud and on-premises installations.

### 2.2 Product Positioning

FactoryOS should not compete only as inventory software.

**Core value proposition:**

> Know what you purchased, what you manufactured, what it actually cost, what you sold and how much profit you made.

### 2.3 Product Principles

1. Industry-independent core.
2. Configurable manufacturing workflows.
3. Financially and operationally traceable transactions.
4. Multi-company and multi-factory support.
5. Cloud and on-premises compatibility.
6. Mobile-friendly production operations.
7. Extensible integrations.
8. No unnecessary microservices.
9. Complete auditability.
10. Consistent business rules across deployment models.

---

## 3. Architecture Decisions

| ID | Decision | Final Approach |
|---|---|---|
| ADR-001 | Backend | Laravel modular monolith |
| ADR-002 | Frontend | React + TypeScript + Inertia |
| ADR-003 | Database | PostgreSQL |
| ADR-004 | Cache/queues | Redis |
| ADR-005 | SaaS tenancy | Shared database with tenant isolation |
| ADR-006 | Enterprise isolation | Optional dedicated database |
| ADR-007 | On-premises | Same application, self-hosted deployment |
| ADR-008 | Accounting | Double-entry ledger |
| ADR-009 | Inventory | Immutable movement ledger |
| ADR-010 | Manufacturing | Configurable operations and routing |
| ADR-011 | API | Versioned REST API |
| ADR-012 | AI | Separate Python services when needed |
| ADR-013 | Industrial integration | Go services when needed |
| ADR-014 | Deployment | Docker-based |
| ADR-015 | Architecture | Modular monolith |

---

## 4. Tenancy Architecture

### 4.1 Tenant Hierarchy

```
FactoryOS Platform
│
├── Tenant / Company A
│   ├── Factory Lahore
│   │   ├── Departments
│   │   ├── Work Centres
│   │   └── Warehouses
│   └── Factory Karachi
│
├── Tenant / Company B
│   └── Factory Islamabad
│
└── Tenant / Company C
    └── Factory Faisalabad
```

**Required entities:** tenants, companies, company_settings, factories, departments, work_centres, warehouses, warehouse_locations, users, tenant_users, user_factory_access, roles, permissions.

A tenant represents the security and subscription boundary. A company can contain one or multiple factories.

### 4.2 Tenant Isolation Rules

Every tenant-owned business record must carry `tenant_id`. Factory-specific records should additionally carry `factory_id`.

Requirements:
- Global tenant context middleware.
- Tenant-aware model scopes.
- Tenant-aware policies.
- Tenant-aware jobs and events.
- Tenant-aware file storage paths.
- Tenant-aware cache keys.
- Tenant-aware exports.
- Tenant-aware audit logs.
- Cross-tenant access tests.

Use PostgreSQL row-level security as an additional safeguard where practical, but do not rely on it as a replacement for application-level authorisation.

---

## 5. Configurable Manufacturing Architecture

### 5.1 Manufacturing Concepts

The system must support: Product, Product variant, BOM, BOM version, Routing, Routing operation, Work centre, Machine, Production plan, Production order, Work order, Material issue, Production entry, Quality inspection, Finished goods receipt.

### 5.2 Configurable Manufacturing Operations

A factory must be able to define its own operations. Examples:

| Industry | Example operations |
|---|---|
| Garments | Cutting → Stitching → Finishing |
| Furniture | Cutting → Assembly → Polishing |
| Packaging | Printing → Cutting → Folding |
| Metal | Cutting → Welding → Grinding |
| Plastic | Mixing → Moulding → Packing |
| Food | Preparation → Processing → Packing |

The system must not hardcode these operations into the application.

**Required configuration entities:** operation_types, routing_templates, routings, routing_versions, routing_operations, work_centres, operation_parameters, operation_quality_requirements.

A production order must preserve the exact routing version used when it was approved.

### 5.3 Manufacturing Models

Support both make-to-stock and make-to-order. Both models share the same production engine.

---

## 6. Functional Module Specifications

| Module | Responsibilities | Priority |
|---|---|---|
| Platform Core | SaaS administration, licensing, plans | P1 |
| Tenancy | Companies, tenants and access isolation | P1 |
| Organisation | Factories, departments and shifts | P1 |
| Identity | Users, roles and permissions | P1 |
| Configuration | Industry templates and business settings | P1 |
| Products | Items, variants, units and categories | P1 |
| Inventory | Warehouses, stock ledger and reservations | P1 |
| Purchasing | Suppliers, requisitions and POs | P1 |
| Manufacturing | BOM, routing and production orders | P1 |
| Production Execution | Material issues, output and wastage | P1 |
| Quality | Inspections, defects and release | P1 |
| Costing | Standard and actual manufacturing cost | P1 |
| Sales | Customers, quotations and sales orders | P2 |
| Dispatch | Deliveries and shipments | P2 |
| Accounting | Double-entry financial ledger | P1 |
| Maintenance | Machines and preventive maintenance | P2 |
| HR | Employees, attendance and labour | P3 |
| Reporting | Operational and financial reports | P1 |
| Notifications | Alerts and approval messages | P1 |
| Integration | External systems and API connections | P2 |
| AI | Forecasting and intelligent analytics | P3 |
| IoT | Machine data and industrial integration | P3 |

P1 = essential for first operational release. Accounting is P1 because it is an explicit product requirement.

---

## 7. Cross-Module Business Rules

| ID | Business Rule |
|---|---|
| BR-001 | All operational records belong to a company/tenant. |
| BR-002 | Factory-level records must respect factory access permissions. |
| BR-003 | Posted inventory movements cannot be silently edited or deleted. |
| BR-004 | Stock cannot become negative unless explicitly allowed by policy. |
| BR-005 | Production cannot consume more material than is available or authorised. |
| BR-006 | A production order must retain its BOM version. |
| BR-007 | Finished goods must follow quality-release rules. |
| BR-008 | Every adjustment requires a reason and authorised user. |
| BR-009 | Cancelled documents must retain their audit history. |
| BR-010 | Financial and inventory transactions must be atomic. |
| BR-011 | Historical costs must not change when current product prices change. |
| BR-012 | Unit conversions must be controlled and traceable. |
| BR-013 | Period closing must protect historical financial records. |
| BR-014 | Approval and posting permissions must be separate. |
| BR-015 | Reversals must use traceable compensating transactions. |

---

## 8. Non-Functional Requirements

| ID | Requirement | Target |
|---|---|---|
| NFR-001 | Responsive interface | Desktop, tablet and mobile |
| NFR-002 | Standard page performance | Under 2 seconds under normal load |
| NFR-003 | Data isolation | No cross-tenant data access |
| NFR-004 | Security | HTTPS, secure sessions, CSRF protection |
| NFR-005 | Password security | Secure password hashing and policies |
| NFR-006 | Auditability | Trace critical user actions |
| NFR-007 | Backup | Automated database and file backups |
| NFR-008 | Recovery | Documented restore and disaster recovery |
| NFR-009 | Scalability | Horizontal application and worker scaling |
| NFR-010 | Availability | Initial target 99.5% monthly |
| NFR-011 | Accessibility | Keyboard-friendly, readable interface |
| NFR-012 | Testing | Automated critical business workflow tests |
| NFR-013 | Data export | CSV and Excel for permitted records |
| NFR-014 | Observability | Logs, metrics and error monitoring |
| NFR-015 | Maintainability | Documented module boundaries and APIs |

---

## 9. MVP Scope

| Module | MVP |
|---|---|
| SaaS tenancy | Required |
| Company/factory setup | Required |
| Users and permissions | Required |
| Products and units | Required |
| Inventory | Required |
| Suppliers and purchasing | Required |
| BOM | Required |
| Production planning | Required |
| Production execution | Required |
| Quality control | Basic version |
| Finished goods | Required |
| Production costing | Required |
| Reports | Basic version |
| Maintenance | Later |
| Full accounting | P1 (required) |
| HR and payroll | Later |
| AI/ML | Later |
| IoT | Later |

The MVP delivers a complete operational loop: purchase material, store it, manufacture a product, inspect it, calculate its cost and receive finished goods.

---

## 10. Release Acceptance Criteria

| Acceptance Test | Expected Outcome |
|---|---|
| Tenant isolation | No cross-company data access |
| Stock receipt | Stock and ledger update atomically |
| Stock issue | Cannot exceed available quantity without authorised override |
| BOM versioning | Existing production orders retain their original BOM |
| Production | Material, output and wastage are correctly recorded |
| Quality | Unapproved finished goods are not released |
| Costing | Planned and actual costs reconcile |
| Accounting | Every posted journal balances |
| Accounting integration | Stock and finance transactions cannot silently diverge |
| Period closing | Closed periods are protected |
| On-premises | Core operations work without central SaaS connectivity |
| Cloud | Multiple tenants can operate safely |
| Backup | Database and files can be restored successfully |

---

## 11. Recommended Delivery Sequence

| Stage | Focus | Exit Condition |
|---|---|---|
| 1 | Architecture and foundation | Tenancy and deployment work |
| 2 | Organisation and product master | Factory and item setup works |
| 3 | Inventory and purchasing | Complete stock procurement cycle |
| 4 | BOM and routing | Configurable manufacturing definition |
| 5 | Production | Complete work-order lifecycle |
| 6 | Quality and costing | Actual production cost verified |
| 7 | Accounting | Complete double-entry cycle |
| 8 | Sales and dispatch | Order-to-delivery workflow |
| 9 | Reports and integrations | Management visibility |
| 10 | Pilot and production hardening | Customer acceptance |

**Critical recommendation:** Build and test the inventory ledger and accounting journal engine before allowing production transactions to post.

---

## 12. Final Engineering Baseline

- Product: Configurable Manufacturing ERP
- Backend: Laravel modular monolith
- Frontend: React + TypeScript + Inertia
- Database: PostgreSQL
- Deployment: Docker, cloud SaaS and self-hosted
- Tenant model: Shared database initially, dedicated database option for enterprise
- Manufacturing: Configurable BOM, routing and work orders
- Inventory: Immutable stock ledger with controlled balance projections
- Accounting: Double-entry financial ledger
- Extensibility: REST APIs, events, webhooks and integration adapters
- Future languages: Python for AI; Go for industrial edge and high-throughput services.

---

## 13. Recommended Next Implementation Deliverables

1. Complete database ERD: Every table, field, data type, index, foreign key and relationship.
2. Accounting and inventory transaction specification: Exact posting rules, costing and reversal behaviour.
3. Module-by-module technical design: Actions, services, events, APIs and ownership boundaries.
4. Docker repository: Development, staging, cloud production and on-premises configurations.
5. UI/UX wireframes: Every major screen, navigation flow and form.
6. Sprint-ready tickets: Dependencies, acceptance criteria and testing requirements.

**Start with the database ERD and accounting/inventory design.**