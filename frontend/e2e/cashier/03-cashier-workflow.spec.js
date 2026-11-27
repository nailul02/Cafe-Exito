import { test, expect } from '@playwright/test';
import { ROUTES, TEST_USERS } from '../fixtures/test-data';
import { loginAs, logout } from '../helpers/auth';

test.describe('Cashier Operational Workflow (KS-01..KS-04)', () => {
  async function createPosOrder(page, paymentMethod) {
    const posBtn = page.getByTestId('new-pos-order-btn');
    await expect(posBtn).toBeVisible({ timeout: 15000 });

    await posBtn.click();
    await expect(page.getByRole('heading', { name: 'Buat Pesanan Baru (POS Kasir)' })).toBeVisible({ timeout: 15000 });

    await page.getByRole('button', { name: /Takeaway/ }).click();
    const customerName = `E2E ${paymentMethod.toUpperCase()} ${Date.now()}`;
    await page.getByLabel('Nama Pelanggan').fill(customerName);

    // Wait for the menu item to be rendered and click it
    const menuItem = page.getByText('Es Kopi Susu E2E', { exact: true });
    await expect(menuItem).toBeVisible({ timeout: 15000 });
    await menuItem.click();
    await expect(page.getByText('Item Pesanan (1)')).toBeVisible({ timeout: 10000 });

    if (paymentMethod === 'qris') {
      await page.getByRole('button', { name: /QRIS Statis/ }).click();
    }

    const orderSubmitBtn = page.getByRole('button', { name: 'Buat Pesanan' });
    await expect(orderSubmitBtn).toBeEnabled({ timeout: 10000 });

    // Explicitly measure and await the POST POS order response
    const startTime = Date.now();
    const orderPromise = page.waitForResponse(
      (response) => response.url().includes('/api/v1/cashier/pos/orders') && response.request().method() === 'POST',
      { timeout: 35000 }
    );

    await orderSubmitBtn.click();
    const orderResponse = await orderPromise;
    const postDurationMs = Date.now() - startTime;
    console.log(`[POS ${paymentMethod.toUpperCase()}] POST duration: ${postDurationMs}ms`);

    expect(orderResponse.status()).toBe(201);
    const resBody = await orderResponse.json();
    expect(resBody.success).toBe(true);
    const orderData = resBody.data;
    expect(orderData.id).toBeTruthy();
    expect(orderData.payment).toBeTruthy();
    expect(orderData.payment.method.code).toBe(paymentMethod === 'qris' ? 'qris' : 'tunai');
    expect(orderData.payment.status.code).toBe('pending');
    expect(orderData.payment.paid_at).toBeNull();

    if (paymentMethod === 'qris') {
      // Business rule: QRIS orders MUST NOT generate production tickets prior to verification
      expect(orderData.tickets || []).toHaveLength(0);
    } else {
      // Cash POS orders initiate tickets while payment remains pending
      expect((orderData.tickets || []).length).toBeGreaterThanOrEqual(1);
    }

    await expect(page.getByRole('heading', { name: 'Buat Pesanan Baru (POS Kasir)' })).toBeHidden({ timeout: 15000 });
    await expect(page.getByText(customerName)).toBeVisible({ timeout: 15000 });
  }

  test.beforeEach(async ({ page }) => {
    await loginAs(page, TEST_USERS.CASHIER);
    await page.waitForURL(new RegExp(ROUTES.CASHIER), { timeout: 15000 });
    await expect(page.getByTestId('new-pos-order-btn')).toBeVisible({ timeout: 15000 });
  });

  test.afterEach(async ({ page }) => {
    const modalClose = page.getByRole('button', { name: 'Tutup' });
    if (await modalClose.isVisible({ timeout: 2000 }).catch(() => false)) {
      await modalClose.click();
    }
    await logout(page);
  });

  test('KS-01: Cashier dashboard loads active order queue', async ({ page }) => {
    await expect(page.getByTestId('new-pos-order-btn')).toBeVisible({ timeout: 10000 });
  });

  test('KS-02: Cashier can open POS order creation modal', async ({ page }) => {
    const posBtn = page.getByTestId('new-pos-order-btn');
    await expect(posBtn).toBeVisible({ timeout: 10000 });
    await posBtn.click();
    await expect(page.getByRole('heading', { name: 'Buat Pesanan Baru (POS Kasir)' })).toBeVisible({ timeout: 15000 });
    await page.getByRole('button', { name: 'Tutup' }).click();
    await expect(page.getByRole('heading', { name: 'Buat Pesanan Baru (POS Kasir)' })).toBeHidden({ timeout: 15000 });
  });

  test('KS-03: POS cash order is created and remains pending payment', async ({ page }) => {
    test.setTimeout(60000);
    await createPosOrder(page, 'cash');
  });

  test('KS-04: POS QRIS order is created with manual verification required', async ({ page }) => {
    test.setTimeout(60000);
    await createPosOrder(page, 'qris');
  });
});
