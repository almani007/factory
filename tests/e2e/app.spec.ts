/**
 * Brixel FactoryOS — E2E Tests
 * End-to-end tests using Playwright
 */

import { test, expect } from '@playwright/test';

test.describe('Dashboard', () => {
  test('should load dashboard page', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveTitle(/Dashboard/);
    await expect(page.locator('h1')).toContainText('Dashboard');
  });

  test('should show KPI cards', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page.locator('text=Production Today')).toBeVisible();
    await expect(page.locator('text=Total Orders')).toBeVisible();
  });
});

test.describe('Inventory', () => {
  test('should load inventory page', async ({ page }) => {
    await page.goto('/inventory');
    await expect(page).toHaveTitle(/Inventory/);
    await expect(page.locator('text=Stock Movements')).toBeVisible();
  });

  test('should navigate to create stock transfer', async ({ page }) => {
    await page.goto('/inventory/transfers');
    await expect(page).toHaveTitle(/Stock Transfers/);
    await page.click('text=New Transfer');
    await expect(page).toHaveTitle(/Create Stock Transfer/);
  });
});

test.describe('Sales', () => {
  test('should load sales page', async ({ page }) => {
    await page.goto('/sales');
    await expect(page).toHaveTitle(/Sales/);
  });

  test('should load sales quotes page', async ({ page }) => {
    await page.goto('/sales/quotes');
    await expect(page).toHaveTitle(/Quotes/);
  });
});

test.describe('Purchasing', () => {
  test('should load purchasing page', async ({ page }) => {
    await page.goto('/purchasing');
    await expect(page).toHaveTitle(/Purchasing/);
  });

  test('should load vendor scorecards page', async ({ page }) => {
    await page.goto('/purchasing/vendor-scorecards');
    await expect(page).toHaveTitle(/Vendor Scorecards/);
  });
});

test.describe('HR', () => {
  test('should load HR page', async ({ page }) => {
    await page.goto('/hr');
    await expect(page).toHaveTitle(/HR/);
  });

  test('should load payroll page', async ({ page }) => {
    await page.goto('/hr/payroll');
    await expect(page).toHaveTitle(/Payroll/);
  });
});

test.describe('Quality', () => {
  test('should load quality page', async ({ page }) => {
    await page.goto('/quality');
    await expect(page).toHaveTitle(/Quality/);
  });

  test('should load SPC page', async ({ page }) => {
    await page.goto('/quality/spc');
    await expect(page).toHaveTitle(/SPC Control Charts/);
  });
});

test.describe('Maintenance', () => {
  test('should load maintenance page', async ({ page }) => {
    await page.goto('/maintenance');
    await expect(page).toHaveTitle(/Maintenance/);
  });

  test('should load preventive maintenance page', async ({ page }) => {
    await page.goto('/maintenance/preventive');
    await expect(page).toHaveTitle(/Preventive Maintenance/);
  });
});

test.describe('EHS', () => {
  test('should load EHS dashboard', async ({ page }) => {
    await page.goto('/ehs');
    await expect(page).toHaveTitle(/EHS/);
  });
});

test.describe('Energy', () => {
  test('should load energy dashboard', async ({ page }) => {
    await page.goto('/energy');
    await expect(page).toHaveTitle(/Energy/);
  });
});

test.describe('Sustainability', () => {
  test('should load sustainability dashboard', async ({ page }) => {
    await page.goto('/sustainability');
    await expect(page).toHaveTitle(/Sustainability/);
  });
});

test.describe('Analytics', () => {
  test('should load analytics page', async ({ page }) => {
    await page.goto('/analytics');
    await expect(page).toHaveTitle(/Analytics/);
  });

  test('should load advanced analytics page', async ({ page }) => {
    await page.goto('/analytics/advanced');
    await expect(page).toHaveTitle(/Advanced Analytics/);
  });
});

test.describe('Customer Portal', () => {
  test('should load customer dashboard', async ({ page }) => {
    await page.goto('/customer/dashboard');
    await expect(page).toHaveTitle(/Customer Portal/);
  });
});

test.describe('Supplier Portal', () => {
  test('should load supplier dashboard', async ({ page }) => {
    await page.goto('/supplier/dashboard');
    await expect(page).toHaveTitle(/Supplier Portal/);
  });
});

test.describe('Reports', () => {
  test('should load reports page', async ({ page }) => {
    await page.goto('/reports');
    await expect(page).toHaveTitle(/Reports/);
  });

  test('should load analytics page', async ({ page }) => {
    await page.goto('/analytics');
    await expect(page).toHaveTitle(/Analytics/);
  });
});

test.describe('Accounting', () => {
  test('should load accounting page', async ({ page }) => {
    await page.goto('/accounting');
    await expect(page).toHaveTitle(/Accounting/);
  });

  test('should load chart of accounts page', async ({ page }) => {
    await page.goto('/accounting/chart-of-accounts');
    await expect(page).toHaveTitle(/Chart of Accounts/);
  });

  test('should load trial balance page', async ({ page }) => {
    await page.goto('/accounting/trial-balance');
    await expect(page).toHaveTitle(/Trial Balance/);
  });
});

test.describe('RBAC', () => {
  test('should load roles page', async ({ page }) => {
    await page.goto('/rbac/roles');
    await expect(page).toHaveTitle(/Roles/);
  });

  test('should load users page', async ({ page }) => {
    await page.goto('/rbac/users');
    await expect(page).toHaveTitle(/Users/);
  });
});
