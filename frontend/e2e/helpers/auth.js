import { expect } from '@playwright/test';
import { ROUTES } from '../fixtures/test-data';

export async function loginAs(page, user) {
  await page.goto(ROUTES.LOGIN);
  await expect(page.getByTestId('login-form')).toBeVisible({ timeout: 10000 });
  await page.getByTestId('login-input').fill(user.username);
  await page.getByTestId('login-password').fill(user.password);

  // Register before clicking so a fast response cannot beat the listener.
  const loginPromise = page.waitForResponse(
    (response) => response.url().includes('/api/v1/auth/login') && response.request().method() === 'POST',
    { timeout: 30000 }
  );
  await page.getByTestId('login-submit').click();
  const loginRes = await loginPromise;
  expect(loginRes.status()).toBe(200);
  const payload = await loginRes.json();
  const authenticatedUser = payload.data?.user;
  const role = authenticatedUser?.role?.code || authenticatedUser?.role;
  expect(role).toBe(user.role);
  await expect.poll(() => page.evaluate(() => Boolean(sessionStorage.getItem('exito.token')))).toBe(true);
  return authenticatedUser;
}

export async function logout(page) {
  const logoutBtn = page.getByTestId('staff-logout-btn');
  if (await logoutBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
    await logoutBtn.click();
    await page.waitForURL((url) => url.pathname === '/', { timeout: 10000 });
  }
}
