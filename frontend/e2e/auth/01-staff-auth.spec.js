import { test, expect } from '@playwright/test';
import { ROUTES, TEST_USERS } from '../fixtures/test-data';
import { loginAs, logout } from '../helpers/auth';

test.describe('Role Authentication & Access Control (AU-01..AU-07)', () => {
  test('AU-01: Kasir login succeeds and navigates to cashier dashboard', async ({ page }) => {
    await loginAs(page, TEST_USERS.CASHIER);
    await expect(page).toHaveURL(new RegExp(ROUTES.CASHIER), { timeout: 10000 });
    await expect(page.getByTestId('staff-logout-btn')).toBeVisible();
    await logout(page);
  });

  test('AU-02: Barista login succeeds and navigates to production station', async ({ page }) => {
    await loginAs(page, TEST_USERS.BARISTA);
    await expect(page).toHaveURL(new RegExp(ROUTES.STATION), { timeout: 10000 });
    await expect(page.getByTestId('staff-logout-btn')).toBeVisible();
    await logout(page);
  });

  test('AU-03: Kitchen login succeeds and navigates to production station', async ({ page }) => {
    await loginAs(page, TEST_USERS.KITCHEN);
    await expect(page).toHaveURL(new RegExp(ROUTES.STATION), { timeout: 10000 });
    await expect(page.getByTestId('staff-logout-btn')).toBeVisible();
    await logout(page);
  });

  test('AU-04: Owner login succeeds and navigates to owner dashboard', async ({ page }) => {
    await loginAs(page, TEST_USERS.OWNER);
    await expect(page).toHaveURL(new RegExp(ROUTES.OWNER), { timeout: 10000 });
    await expect(page.getByTestId('owner-dashboard-container')).toBeVisible({ timeout: 10000 });
    await expect(page.getByTestId('staff-logout-btn')).toBeVisible();
    await logout(page);
  });

  test('AU-05: Manager login succeeds and navigates to manager dashboard', async ({ page }) => {
    await loginAs(page, TEST_USERS.MANAGER);
    await expect(page).toHaveURL(new RegExp(ROUTES.MANAGER), { timeout: 10000 });
    await expect(page.getByTestId('manager-dashboard-container')).toBeVisible({ timeout: 10000 });
    await expect(page.getByTestId('staff-logout-btn')).toBeVisible();
    await logout(page);
  });

  test('AU-06: Invalid credentials display error without session leak', async ({ page }) => {
    await page.goto(ROUTES.LOGIN);
    await page.getByTestId('login-input').fill('invalid_user_xyz');
    await page.getByTestId('login-password').fill('WrongPassword123!');
    await page.getByTestId('login-submit').click();
    await expect(page.getByRole('alert')).toBeVisible({ timeout: 10000 });
    await expect(page).toHaveURL(new RegExp(ROUTES.LOGIN));
  });

  test('AU-07: Unauthenticated access to protected management route redirects to login', async ({ page }) => {
    await page.goto(ROUTES.OWNER);
    await expect(page).toHaveURL(new RegExp(ROUTES.LOGIN), { timeout: 10000 });
  });
});
