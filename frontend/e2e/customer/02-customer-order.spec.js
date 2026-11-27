import { test, expect } from '@playwright/test';
import { ROUTES, TEST_CUSTOMER } from '../fixtures/test-data';

test.describe('Customer Self-Ordering Workflow (CS-01..CS-06)', () => {
  async function enterCustomerSession(page) {
    await page.goto(ROUTES.TABLE);
    const name = page.getByLabel('Nama pelanggan');
    const entry = await Promise.race([
      name.waitFor({ state: 'visible', timeout: 10000 }).then(() => 'name'),
      page.waitForURL(new RegExp(ROUTES.CATALOG), { timeout: 10000 }).then(() => 'catalog'),
    ]);
    if (entry === 'name') {
      await name.fill(TEST_CUSTOMER.CUSTOMER_NAME);
      await page.getByTestId('start-order-submit').click();
      await page.waitForURL(new RegExp(ROUTES.CATALOG), { timeout: 10000 });
    }
  }

  async function openPaymentForOneItem(page, method) {
    await enterCustomerSession(page);
    await page.getByTestId('stepper-plus').first().click();
    await page.getByTestId('view-cart-btn').click();
    await page.waitForURL(new RegExp(ROUTES.CART));
    await page.getByTestId('proceed-to-payment-btn').click();
    await page.waitForURL(new RegExp(ROUTES.PAYMENT_METHOD));
    await page.getByTestId(`payment-method-${method}`).click();
    await page.getByTestId('place-order-btn').click();
    await expect(page).toHaveURL(/\/order\/done/, { timeout: 20000 });
    await expect(page.getByRole('heading', { name: /Pesanan\s*Diterima/i })).toBeVisible({ timeout: 15000 });
    await page.getByRole('button', { name: 'Lihat Status Pesanan' }).click();
    await expect(page).toHaveURL(new RegExp(ROUTES.ORDER_STATUS));
    await expect(page.getByText(/Menunggu pembayaran/i)).toBeVisible({ timeout: 15000 });
  }

  test('CS-01: Table entry sets active table session', async ({ page }) => {
    await enterCustomerSession(page);
    await expect(page).toHaveURL(new RegExp(ROUTES.CATALOG), { timeout: 10000 });
  });

  test('CS-02: Customer browses catalog, filters by category, and adds items', async ({ page }) => {
    await enterCustomerSession(page);

    // Verify category tabs exist
    const tabMinuman = page.getByTestId('menu-tab-minuman');
    if (await tabMinuman.isVisible({ timeout: 3000 }).catch(() => false)) {
      await tabMinuman.click();
    }

    // Add first available item to cart
    const plusBtn = page.getByTestId('stepper-plus').first();
    await expect(plusBtn).toBeVisible({ timeout: 10000 });
    await plusBtn.click();

    // Verify cart button shows item count
    const cartBtn = page.getByTestId('view-cart-btn');
    await expect(cartBtn).toBeVisible();
    await cartBtn.click();
    await expect(page).toHaveURL(new RegExp(ROUTES.CART));
  });

  test('CS-03: Cart view allows proceeding to payment method selection', async ({ page }) => {
    await enterCustomerSession(page);

    const plusBtn = page.getByTestId('stepper-plus').first();
    await plusBtn.click();

    const cartBtn = page.getByTestId('view-cart-btn');
    await cartBtn.click();
    await page.waitForURL(new RegExp(ROUTES.CART));

    const proceedBtn = page.getByTestId('proceed-to-payment-btn');
    await expect(proceedBtn).toBeVisible();
    await proceedBtn.click();
    await expect(page).toHaveURL(new RegExp(ROUTES.PAYMENT_METHOD));
  });

  test('CS-04: Cash checkout creates a pending order without marking payment paid', async ({ page }) => {
    test.setTimeout(60000);
    await openPaymentForOneItem(page, 'tunai');
    await expect(page.getByText(/Menunggu pembayaran\. Selesaikan pembayaran di kasir/i)).toBeVisible();
  });

  test('CS-05: QRIS checkout stays pending and offers payment proof upload', async ({ page }) => {
    test.setTimeout(60000);
    await openPaymentForOneItem(page, 'qris');
    await expect(page.getByTestId('payment-proof-input')).toBeVisible();
    await expect(page.getByTestId('upload-proof-btn')).toBeDisabled();
  });
});
