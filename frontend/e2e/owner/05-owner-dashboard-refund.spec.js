import { test, expect } from '@playwright/test';
import { ROUTES, TEST_USERS } from '../fixtures/test-data';
import { loginAs, logout } from '../helpers/auth';

test.describe('Owner Dashboard & Refund Authority (OW-01..OW-04)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, TEST_USERS.OWNER);
    await page.waitForURL(new RegExp(ROUTES.OWNER));
  });

  test.afterEach(async ({ page }) => {
    await logout(page);
  });

  test('OW-01: Owner dashboard renders financial indicators', async ({ page }) => {
    await expect(page.getByTestId('owner-dashboard-container')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Penjualan hari ini', { exact: true })).toBeVisible();
  });

  test('OW-02: Owner can access refunds management page', async ({ page }) => {
    await page.goto(ROUTES.OWNER_REFUNDS);
    await expect(page).toHaveURL(new RegExp(ROUTES.OWNER_REFUNDS));
    await expect(page.getByRole('heading', { name: 'Persetujuan Refund' })).toBeVisible({ timeout: 10000 });
  });
});
