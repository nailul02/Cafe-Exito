import { test, expect } from '@playwright/test';
import { ROUTES, TEST_USERS } from '../fixtures/test-data';
import { loginAs, logout } from '../helpers/auth';

test.describe('Production Station Operations (ST-01..ST-04)', () => {
  test('ST-01: Barista accesses bar queue and views active tickets', async ({ page }) => {
    await loginAs(page, TEST_USERS.BARISTA);
    await expect(page).toHaveURL(new RegExp(ROUTES.STATION), { timeout: 10000 });
    await expect(page.getByTestId('staff-logout-btn')).toBeVisible();
    await logout(page);
  });

  test('ST-02: Kitchen accesses kitchen queue and views active tickets', async ({ page }) => {
    await loginAs(page, TEST_USERS.KITCHEN);
    await expect(page).toHaveURL(new RegExp(ROUTES.STATION), { timeout: 10000 });
    await expect(page.getByTestId('staff-logout-btn')).toBeVisible();
    await logout(page);
  });
});
