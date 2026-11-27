import { test, expect } from '@playwright/test';
import { ROUTES, TEST_USERS } from '../fixtures/test-data';
import { loginAs, logout } from '../helpers/auth';

const API_BASE_URL = process.env.E2E_API_BASE_URL || 'http://localhost:8000/api/v1';

function bearer(token) {
  return { Authorization: `Bearer ${token}` };
}

async function responseData(response, expectedStatus) {
  expect(response.status()).toBe(expectedStatus);
  const body = await response.json();
  if (expectedStatus < 400) expect(body.success).toBe(true);
  return body.data;
}

async function apiLogin(page, user) {
  const response = await page.request.post(`${API_BASE_URL}/auth/login`, {
    data: { login: user.username, password: user.password },
  });
  const data = await responseData(response, 200);
  const role = data.user?.role?.code || data.user?.role;
  expect(role).toBe(user.role);
  return data.token;
}

async function apiLogout(page, token) {
  if (!token) return;
  await page.request.post(`${API_BASE_URL}/auth/logout`, { headers: bearer(token) }).catch(() => {});
}

async function createPendingRefund(page, cashierToken, ordinal) {
  const menuResponse = await page.request.get(`${API_BASE_URL}/customer/menus`);
  const menus = await responseData(menuResponse, 200);
  const menu = menus.find((row) => row.is_orderable);
  expect(menu, 'an orderable E2E menu is required').toBeTruthy();

  const customerName = `Manager boundary ${ordinal} ${Date.now()}`;
  let sessionResponse = await page.request.post(`${API_BASE_URL}/customer/sessions/open`, {
    data: { qr_token: 'demo-table-01' },
  });
  let session = await responseData(sessionResponse, 200);
  if (session.requires_name) {
    sessionResponse = await page.request.post(`${API_BASE_URL}/customer/sessions/open`, {
      data: { qr_token: 'demo-table-01', customer_name: customerName },
    });
    session = await responseData(sessionResponse, 200);
  }
  expect(session.session_token).toBeTruthy();
  const customerHeaders = { 'X-Table-Session': session.session_token };

  const orderResponse = await page.request.post(`${API_BASE_URL}/customer/sessions/orders`, {
    headers: customerHeaders,
    data: {
      payment_method: 'tunai',
      client_request_id: globalThis.crypto.randomUUID(),
      items: [{ menu_id: menu.id, qty: 1 }],
    },
  });
  const customerOrder = await responseData(orderResponse, 201);

  // Customer responses intentionally omit internal order/payment IDs. The authorized cashier
  // view supplies them for the next business step in this isolated test database.
  const ordersResponse = await page.request.get(
    `${API_BASE_URL}/cashier/orders?search=${encodeURIComponent(customerOrder.order_number)}`,
    { headers: bearer(cashierToken) },
  );
  const orders = await responseData(ordersResponse, 200);
  const order = orders.find((row) => row.order_number === customerOrder.order_number);
  expect(order, 'cashier must be able to find the new customer order').toBeTruthy();

  const verifyResponse = await page.request.post(`${API_BASE_URL}/cashier/payments/${order.payment.id}/verify`, {
    headers: bearer(cashierToken),
    data: { amount_received: order.total },
  });
  await responseData(verifyResponse, 200);

  const cancelResponse = await page.request.post(
    `${API_BASE_URL}/customer/orders/${customerOrder.public_token}/cancel`,
    {
      headers: customerHeaders,
      data: { detail_id: customerOrder.items[0].id, qty: 1, reason: `Manager boundary test ${ordinal}` },
    },
  );
  await responseData(cancelResponse, 200);

  const refundsResponse = await page.request.get(`${API_BASE_URL}/refunds?per_page=100`, {
    headers: bearer(cashierToken),
  });
  const pageData = await responseData(refundsResponse, 200);
  const rows = Array.isArray(pageData) ? pageData : (pageData.data || []);
  const refund = rows.find((row) => row.order?.order_number === customerOrder.order_number);
  expect(refund?.status).toBe('requested');
  return refund;
}

async function auditActions(page, token, refundId) {
  const response = await page.request.get(
    `${API_BASE_URL}/audit-logs?subject_type=refund_request&subject_id=${refundId}&per_page=100`,
    { headers: bearer(token) },
  );
  const rows = await responseData(response, 200);
  return rows.map((row) => ({ id: row.id, action: row.action }));
}

test.describe('Manager Operational Scope & Policy Boundaries (MG-01..MG-03)', () => {
  test.beforeEach(async ({ page }, testInfo) => {
    const dashboardResponse = testInfo.title.includes('MG-01: Manager dashboard')
      ? page.waitForResponse(
        (response) => response.url().includes('/api/v1/dashboard/manager') && response.request().method() === 'GET',
        { timeout: 25000 },
      )
      : null;

    const authenticatedUser = await loginAs(page, TEST_USERS.MANAGER);
    expect(authenticatedUser.role?.code || authenticatedUser.role).toBe('manager');
    await page.waitForURL((url) => url.pathname === ROUTES.MANAGER);

    if (dashboardResponse) {
      const response = await dashboardResponse;
      expect(response.status()).toBe(200);
      const body = await response.json();
      expect(body.success).toBe(true);
      expect(body.data).toEqual(expect.objectContaining({
        revenue_today: expect.any(Number),
        active_orders: expect.objectContaining({ total: expect.any(Number) }),
        orders: expect.any(Array),
      }));
    }
  });

  test.afterEach(async ({ page }) => {
    await logout(page);
  });

  test('MG-01: Manager dashboard renders store overview', async ({ page }) => {
    await expect(page.getByTestId('manager-dashboard-container')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Dashboard', exact: true })).toBeVisible();
    await expect(page.getByText('Penjualan hari ini', { exact: true })).toBeVisible();
    await expect(page.getByText('Pesanan aktif', { exact: true })).toBeVisible();
    await expect(page.getByText('Pantau pesanan masuk', { exact: true })).toBeVisible();
  });

  test('MG-02: Manager can view menu catalog management', async ({ page }) => {
    const menusResponse = page.waitForResponse(
      (response) => response.url().includes('/api/v1/admin/menus') && response.request().method() === 'GET',
      { timeout: 20000 },
    );
    await page.goto(ROUTES.MANAGER_MENUS);
    await expect(page).toHaveURL((url) => url.pathname === ROUTES.MANAGER_MENUS);
    expect((await menusResponse).status()).toBe(200);
    await expect(page.getByText('Kelola Data & Ketersediaan Menu')).toBeVisible({ timeout: 15000 });
  });

  test('MG-03: Manager is denied refund data/actions and Owner retains approve/reject authority', async ({ page, browser }) => {
    test.setTimeout(150000);
    const managerToken = await page.evaluate(() => sessionStorage.getItem('exito.token'));
    expect(managerToken).toBeTruthy();
    const cashierToken = await apiLogin(page, TEST_USERS.CASHIER);

    let refunds;
    let ownerPage;
    try {
      refunds = [
        await createPendingRefund(page, cashierToken, 'approve'),
        await createPendingRefund(page, cashierToken, 'reject'),
      ];

      // A manager session must not enter the Owner-only refund review route.
      await page.evaluate((path) => {
        window.history.pushState({}, '', path);
        window.dispatchEvent(new PopStateEvent('popstate'));
      }, ROUTES.OWNER_REFUNDS);
      await page.waitForURL((url) => url.pathname === ROUTES.MANAGER, { timeout: 25000 });

      await page.evaluate(() => {
        window.history.pushState({}, '', '/manager/refund-conflicts');
        window.dispatchEvent(new PopStateEvent('popstate'));
      });
      await expect(page).toHaveURL((url) => url.pathname === '/manager/refund-conflicts');
      await expect(page.getByRole('heading', { name: 'Dokumentasi Konflik Kepentingan' })).toBeVisible();

      const protectedList = await page.request.get(`${API_BASE_URL}/refunds`, {
        headers: bearer(managerToken),
      });
      expect(protectedList.status()).toBe(403);

      const auditBefore = new Map();
      for (const refund of refunds) auditBefore.set(refund.id, await auditActions(page, managerToken, refund.id));

      const approvalAttempt = await page.request.post(`${API_BASE_URL}/refunds/${refunds[0].id}/decision`, {
        headers: bearer(managerToken),
        data: { decision: 'approved' },
      });
      expect(approvalAttempt.status()).toBe(403);

      const rejectionAttempt = await page.request.post(`${API_BASE_URL}/refunds/${refunds[1].id}/decision`, {
        headers: bearer(managerToken),
        data: { decision: 'rejected', reason: 'Manager must not decide an Owner refund' },
      });
      expect(rejectionAttempt.status()).toBe(403);

      const executionAttempt = await page.request.post(`${API_BASE_URL}/refunds/${refunds[0].id}/execute`, {
        headers: bearer(managerToken),
        data: { method: 'cash', confirm_cash: true },
      });
      expect(executionAttempt.status()).toBe(403);

      const ownerApiToken = await apiLogin(page, TEST_USERS.OWNER);
      try {
        const ownerListResponse = await page.request.get(`${API_BASE_URL}/refunds?per_page=100`, {
          headers: bearer(ownerApiToken),
        });
        const ownerList = await responseData(ownerListResponse, 200);
        const ownerRows = Array.isArray(ownerList) ? ownerList : (ownerList.data || []);
        for (const refund of refunds) {
          expect(ownerRows.find((row) => row.id === refund.id)?.status).toBe('requested');
          expect(await auditActions(page, managerToken, refund.id)).toEqual(auditBefore.get(refund.id));
        }
      } finally {
        await apiLogout(page, ownerApiToken);
      }

      ownerPage = await browser.newPage();
      const owner = await loginAs(ownerPage, TEST_USERS.OWNER);
      expect(owner.role?.code || owner.role).toBe('owner');
      await ownerPage.waitForURL((url) => url.pathname === ROUTES.OWNER);
      const ownerRefundListResponse = ownerPage.waitForResponse(
        (response) => response.url().includes('/api/v1/refunds') && response.request().method() === 'GET',
        { timeout: 30000 },
      );
      await ownerPage.getByRole('link', { name: 'Persetujuan Refund' }).click();
      await ownerPage.waitForURL((url) => url.pathname === ROUTES.OWNER_REFUNDS);
      expect((await ownerRefundListResponse).status()).toBe(200);
      await expect(ownerPage.getByRole('heading', { name: 'Persetujuan Refund' })).toBeVisible();
      for (const refund of refunds) {
        await expect(ownerPage.getByTestId(`refund-approve-btn-${refund.id}`)).toBeVisible();
        await expect(ownerPage.getByTestId(`refund-reject-btn-${refund.id}`)).toBeVisible();
      }

      const approveResponse = ownerPage.waitForResponse(
        (response) => response.url().endsWith(`/refunds/${refunds[0].id}/decision`) && response.request().method() === 'POST',
      );
      await ownerPage.getByTestId(`refund-approve-btn-${refunds[0].id}`).click();
      const approvedResponse = await approveResponse;
      expect(approvedResponse.status()).toBe(200);
      expect((await approvedResponse.json()).data.status).toBe('approved');

      ownerPage.once('dialog', (dialog) => dialog.accept('Ditolak oleh Owner pada pengujian E2E'));
      const rejectResponse = ownerPage.waitForResponse(
        (response) => response.url().endsWith(`/refunds/${refunds[1].id}/decision`) && response.request().method() === 'POST',
      );
      await ownerPage.getByTestId(`refund-reject-btn-${refunds[1].id}`).click();
      const rejectedResponse = await rejectResponse;
      expect(rejectedResponse.status()).toBe(200);
      expect((await rejectedResponse.json()).data.status).toBe('rejected');

      const ownerToken = await ownerPage.evaluate(() => sessionStorage.getItem('exito.token'));
      const approvedAudit = await auditActions(ownerPage, ownerToken, refunds[0].id);
      const rejectedAudit = await auditActions(ownerPage, ownerToken, refunds[1].id);
      expect(approvedAudit.map((row) => row.action)).toContain('refund.approved');
      expect(rejectedAudit.map((row) => row.action)).toContain('refund.rejected');
    } finally {
      await apiLogout(page, cashierToken);
      if (ownerPage) await ownerPage.close();
    }
  });
});
