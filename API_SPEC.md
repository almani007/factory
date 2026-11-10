# Zoventra FactoryOS — API Specification

**Version 1.0** · 10 November 2026  
**Base URL:** `/api/v1`  
**Auth:** Laravel Sanctum (stateful) + Personal Access Tokens (SPA/mobile)

---

## 1. Design Principles

- Versioned REST API: `/api/v1/{resource}`
- Consistent JSON response format
- Pagination on all list endpoints
- Filtering, sorting, search support
- Standard validation error format
- Rate limiting per tenant/user
- Idempotency keys for critical POST/PUT/PATCH/DELETE
- Tenant context enforced on every request
- Permission enforcement via Spatie policies
- OpenAPI documentation
- Webhooks for integrations

---

## 2. Response Format

### Success (single)
```json
{
  "status": "success",
  "data": { ... },
  "meta": { ... }
}
```

### Success (list)
```json
{
  "status": "success",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

### Error
```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "errors": {
      "email": ["The email field is required."]
    }
  }
}
```

---

## 3. Endpoint Groups

### Authentication
```
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/refresh
POST   /api/v1/auth/password/email
POST   /api/v1/auth/password/reset
GET    /api/v1/auth/user
```

### Tenants & Companies
```
GET    /api/v1/tenants
POST   /api/v1/tenants
GET    /api/v1/tenants/{id}
PUT    /api/v1/tenants/{id}
GET    /api/v1/companies
POST   /api/v1/companies
GET    /api/v1/companies/{id}
PUT    /api/v1/companies/{id}
GET    /api/v1/companies/{id}/factories
```

### Factories & Organisation
```
GET    /api/v1/factories
POST   /api/v1/factories
GET    /api/v1/factories/{id}
PUT    /api/v1/factories/{id}
GET    /api/v1/factories/{id}/departments
GET    /api/v1/factories/{id}/work-centres
GET    /api/v1/factories/{id}/warehouses
GET    /api/v1/departments
GET    /api/v1/work-centres
GET    /api/v1/warehouses
```

### Users & Permissions
```
GET    /api/v1/users
POST   /api/v1/users
GET    /api/v1/users/{id}
PUT    /api/v1/users/{id}
DELETE /api/v1/users/{id}
GET    /api/v1/roles
POST   /api/v1/roles
GET    /api/v1/permissions
GET    /api/v1/user-factory-access
POST   /api/v1/user-factory-access
```

### Products
```
GET    /api/v1/products
POST   /api/v1/products
GET    /api/v1/products/{id}
PUT    /api/v1/products/{id}
DELETE /api/v1/products/{id}
GET    /api/v1/product-categories
GET    /api/v1/units
GET    /api/v1/product-variants
GET    /api/v1/product-barcodes
```

### Inventory
```
GET    /api/v1/inventory/movements
POST   /api/v1/inventory/movements
GET    /api/v1/inventory/movements/{id}
GET    /api/v1/inventory/balances
GET    /api/v1/inventory/reservations
POST   /api/v1/inventory/reservations
GET    /api/v1/inventory/adjustments
POST   /api/v1/inventory/adjustments
GET    /api/v1/inventory/transfers
POST   /api/v1/inventory/transfers
GET    /api/v1/inventory/counts
POST   /api/v1/inventory/counts
GET    /api/v1/inventory/valuation
```

### Purchasing
```
GET    /api/v1/suppliers
POST   /api/v1/suppliers
GET    /api/v1/suppliers/{id}
GET    /api/v1/purchase-requisitions
POST   /api/v1/purchase-requisitions
POST   /api/v1/purchase-requisitions/{id}/approve
GET    /api/v1/purchase-orders
POST   /api/v1/purchase-orders
GET    /api/v1/purchase-orders/{id}
POST   /api/v1/purchase-orders/{id}/receive
GET    /api/v1/goods-receipts
POST   /api/v1/goods-receipts
GET    /api/v1/supplier-invoices
POST   /api/v1/supplier-invoices
```

### BOM & Manufacturing
```
GET    /api/v1/boms
POST   /api/v1/boms
GET    /api/v1/boms/{id}/versions
POST   /api/v1/boms/{id}/versions
GET    /api/v1/bom-versions/{id}/components
GET    /api/v1/routings
POST   /api/v1/routings
GET    /api/v1/routings/{id}/versions
GET    /api/v1/routing-versions/{id}/operations
```

### Production
```
GET    /api/v1/production-plans
POST   /api/v1/production-plans
GET    /api/v1/production-plans/{id}
POST   /api/v1/production-plans/{id}/approve
GET    /api/v1/production-orders
POST   /api/v1/production-orders
GET    /api/v1/production-orders/{id}
POST   /api/v1/production-orders/{id}/start
POST   /api/v1/production-orders/{id}/pause
POST   /api/v1/production-orders/{id}/resume
POST   /api/v1/production-orders/{id}/complete
GET    /api/v1/work-orders
POST   /api/v1/work-orders
GET    /api/v1/material-issues
POST   /api/v1/material-issues
GET    /api/v1/production-entries
POST   /api/v1/production-entries
GET    /api/v1/production-scrap
POST   /api/v1/production-scrap
```

### Quality
```
GET    /api/v1/quality/inspection-templates
POST   /api/v1/quality/inspection-templates
GET    /api/v1/quality/inspections
POST   /api/v1/quality/inspections
POST   /api/v1/quality/inspections/{id}/approve
POST   /api/v1/quality/inspections/{id}/reject
GET    /api/v1/quality/defects
GET    /api/v1/quality/nonconformances
```

### Costing
```
GET    /api/v1/costing/standard-costs
POST   /api/v1/costing/standard-costs
GET    /api/v1/costing/production-costs
GET    /api/v1/costing/production-orders/{id}/cost
GET    /api/v1/costing/variances
GET    /api/v1/costing/unit-cost-analysis
```

### Accounting
```
GET    /api/v1/accounting/chart-of-accounts
POST   /api/v1/accounting/chart-of-accounts
GET    /api/v1/accounting/journal-entries
POST   /api/v1/accounting/journal-entries
POST   /api/v1/accounting/journal-entries/{id}/post
POST   /api/v1/accounting/journal-entries/{id}/reverse
GET    /api/v1/accounting/customer-invoices
POST   /api/v1/accounting/customer-invoices
GET    /api/v1/accounting/supplier-bills
POST   /api/v1/accounting/supplier-bills
GET    /api/v1/accounting/customer-payments
POST   /api/v1/accounting/customer-payments
GET    /api/v1/accounting/supplier-payments
POST   /api/v1/accounting/supplier-payments
GET    /api/v1/accounting/trial-balance
GET    /api/v1/accounting/profit-loss
GET    /api/v1/accounting/balance-sheet
GET    /api/v1/accounting/cash-flow
```

### Sales & Dispatch
```
GET    /api/v1/customers
POST   /api/v1/customers
GET    /api/v1/sales-orders
POST   /api/v1/sales-orders
GET    /api/v1/dispatches
POST   /api/v1/dispatches
GET    /api/v1/delivery-notes
POST   /api/v1/delivery-notes
```

### Reporting
```
GET    /api/v1/reports/dashboard
GET    /api/v1/reports/inventory/stock-balance
GET    /api/v1/reports/inventory/movement
GET    /api/v1/reports/production/daily-output
GET    /api/v1/reports/production/order-status
GET    /api/v1/reports/costing/variance
GET    /api/v1/reports/accounting/trial-balance
GET    /api/v1/reports/export/{reportId}  // CSV/Excel
```

### System
```
GET    /api/v1/system/health
GET    /api/v1/system/licence
GET    /api/v1/audit-logs
GET    /api/v1/notifications
POST   /api/v1/notifications/{id}/read
```

---

## 4. Common Query Parameters

| Parameter | Description |
|---|---|
| `page` | Page number |
| `per_page` | Items per page (max 100) |
| `sort` | Field to sort by |
| `dir` | Sort direction (asc/desc) |
| `search` | Full-text search |
| `filter[key]` | Filter by field |
| `from_date` | Date range from |
| `to_date` | Date range to |
| `tenant_id` | Tenant filter (super admin only) |
| `factory_id` | Factory filter |
| `include` | Eager-load relationships |

---

## 5. Idempotency

Critical posting endpoints accept an `Idempotency-Key` header. Reusing a key returns the cached response without re-executing.

Applies to:
- POST /api/v1/inventory/movements
- POST /api/v1/accounting/journal-entries
- POST /api/v1/production-entries
- POST /api/v1/material-issues

---

## 6. Rate Limiting

| Tier | Limit |
|---|---|
| Guest | 60 req/min |
| Authenticated | 600 req/min |
| API token | 1200 req/min |

Limits applied per tenant + user combination.

---

## 7. OpenAPI

Full OpenAPI 3.0 specification available at:
```
GET /api/documentation/openapi.json
GET /api/documentation/swagger
```

---

## 8. Webhooks

Events published:
- `stock.movement.posted`
- `journal.entry.posted`
- `production.order.completed`
- `purchase.order.approved`
- `quality.inspection.failed`
- `low.stock.alert`
- `subscription.expiring`

Webhook endpoints configured per tenant with signature verification.