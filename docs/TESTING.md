# Brixel FactoryOS Testing Guide

## Overview

The FactoryOS test suite includes:

- **Backend Tests** (PHPUnit/Pest) - Tests all web controllers and API routes
- **Frontend Tests** (Vitest/React Testing Library) - Tests React components
- **E2E Tests** (Playwright) - End-to-end browser tests

## Setup

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
```

### 2. Run Tests

#### Run All Tests
```bash
# Backend tests
php artisan test

# Frontend tests
npm test

# All tests
composer test && npm test
```

#### Run Specific Test Suites

```bash
# Run only accounting tests
php artisan test tests/Feature/AccountingControllerTest.php

# Run only React component tests
npm test -- components.spec.tsx

# Run only E2E tests
npx playwright test
```

#### Run Tests in Watch Mode (Development)

```bash
# Backend
php artisan test --watch

# Frontend
npm run test:watch
```

## Test Structure

```
tests/
├── Feature/              # Backend feature tests
│   ├── AccountingControllerTest.php
│   ├── ProductionControllerTest.php
│   ├── InventoryControllerTest.php
│   ├── SalesControllerTest.php
│   ├── PurchasingControllerTest.php
│   ├── HRControllerTest.php
│   ├── QualityControllerTest.php
│   ├── MaintenanceControllerTest.php
│   ├── EHSTest.php
│   ├── EnergyTest.php
│   ├── SustainabilityTest.php
│   ├── ReportingTest.php
│   ├── WorkflowTest.php
    ├── IntegrationsTest.php
│   ├── ComplianceTest.php
│   ├── RBACControllerTest.php
│   ├── CustomerPortalTest.php
│   └── SupplierPortalTest.php
├── features/             # Frontend component tests
│   └── components.spec.tsx
├── e2e/                  # End-to-end tests
│   └── app.spec.ts
├── Helpers.php           # Test helpers
├── Macros.php            # Custom test macros
└── TestCase.php          # Base test case
```

### Test Coverage

| Module              | Backend Tests | Frontend Tests | E2E Tests |
|---------------------|---------------|----------------|-----------|
| Dashboard           | ✅             | ✅              | ✅         |
| Inventory           | ✅             | ✅              | ✅         |
| Production          | ✅             | ✅              | ✅         |
| Purchasing          | ✅             | ✅              | ✅         |
| Sales               | ✅             | ✅              | ✅         |
| HR                  | ✅             | ✅              | ✅         |
| Quality             | ✅             | ✅              | ✅         |
| Maintenance         | ✅             | ✅              | ✅         |
| EHS                 | ✅             | ✅              | ✅         |
| Energy              | ✅             | ✅              | ✅         |
| Sustainability      | ✅             | ✅              | ✅         |
| Reporting           | ✅             | ✅              | ✅         |
| Workflow            | ✅             | ✅              | ✅         |
| Integrations        | ✅             | ✅              | ✅         |
| Compliance          | ✅             | ✅              | ✅         |
| RBAC                | ✅             | ✅              | ✅         |
| Customer Portal     | ✅             | ✅              | ✅         |
| Supplier Portal     | ✅             | ✅              | ✅         |

## Writing Tests

### Backend Tests (PHPUnit/Pest)

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** @test */
    public function user_can_access_page()
    {
        $response = $this->get('/accounting');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Accounting/Accounting')
        );
    }
}
```

### Frontend Tests (Vitest/React Testing Library)

```typescript
import { render, screen } from '@testing-library/react';
import { DataTable } from '@/components/DataTable';

describe('DataTable', () => {
    it('renders table with data', () => {
        const columns = [
            { key: 'name', label: 'Name' },
            { key: 'status', label: 'Status' },
        ];
        const data = [
            { id: 1, name: 'Item 1', status: 'active' },
        ];

        const { getByText } = render(
            <DataTable title="Test" columns={columns} data={data} />
        );

        expect(getByText('Test')).toBeInTheDocument();
        expect(getByText('Item 1')).toBeInTheDocument();
    });
});
```

### E2E Tests (Playwright)

```typescript
import { test, expect } from '@playwright/test';

test('should load dashboard page', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveTitle(/Dashboard/);
    await expect(page.locator('h1')).toContainText('Dashboard');
});
```

### Running Tests in Docker

```bash
# Start all services
docker compose -f docker-compose.testing.yml up -d

# Run backend tests in Docker
docker exec factoryos-web php artisan test

# Run frontend tests in Docker
docker exec factoryos-web npm test
```

## Continuous Integration

Add this to your `.github/workflows/test.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:18
        ports:
          - 5432:5432
      redis:
        image: redis:8-alpine
        ports:
          - 6379:6379
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: 20
      - name: Install PHP dependencies
        run: composer install
      - name: Install Node dependencies
        run: npm install
      - name: Run PHP tests
        run: php artisan test
      - name: Run frontend tests
        run: npm test
```
