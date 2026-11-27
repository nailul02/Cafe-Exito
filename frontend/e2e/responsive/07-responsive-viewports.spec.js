import { test, expect } from '@playwright/test';
import { ROUTES, TEST_USERS } from '../fixtures/test-data';
import { loginAs, logout } from '../helpers/auth';

const VIEWPORTS = [
  { width: 360, height: 800 },
  { width: 390, height: 844 },
  { width: 768, height: 1024 },
  { width: 1024, height: 768 },
  { width: 1366, height: 768 },
  { width: 1920, height: 1080 },
];

async function checkViewportMatrix(page) {
  for (const viewport of VIEWPORTS) {
    await page.setViewportSize(viewport);
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(viewport.width);
    await expect(page.locator('body')).toBeVisible();
  }
}

test.describe('Responsive smoke across required viewports (dashboard/catalog surfaces)', () => {
  test('Customer catalog fits six target viewports', async ({ page }) => {
    await page.goto(ROUTES.HOME);
    await expect(page.getByTestId('menu-tab-minuman')).toBeVisible();
    await checkViewportMatrix(page);
  });

  for (const [label, user, route, anchor] of [
    ['Kasir', TEST_USERS.CASHIER, ROUTES.CASHIER, 'new-pos-order-btn'],
    ['Barista', TEST_USERS.BARISTA, ROUTES.STATION, 'staff-logout-btn'],
    ['Kitchen', TEST_USERS.KITCHEN, ROUTES.STATION, 'staff-logout-btn'],
    ['Owner', TEST_USERS.OWNER, ROUTES.OWNER, 'owner-dashboard-container'],
    ['Manager', TEST_USERS.MANAGER, ROUTES.MANAGER, 'manager-dashboard-container'],
  ]) {
    test(`${label} dashboard fits six target viewports`, async ({ page }) => {
      await loginAs(page, user);
      await page.waitForURL(new RegExp(route));
      await expect(page.getByTestId(anchor)).toBeVisible({ timeout: 15000 });
      await checkViewportMatrix(page);
      await logout(page);
    });
  }
});
