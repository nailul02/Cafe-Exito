export const ROUTES = {
  HOME: '/',
  LOGIN: '/login',
  TABLE: '/order?t=demo-table-01',
  CATALOG: '/order/menu',
  CART: '/order/cart',
  PAYMENT_METHOD: '/order/payment',
  ORDER_STATUS: '/order/status',
  CASHIER: '/cashier',
  STATION: '/production',
  OWNER: '/owner',
  OWNER_REFUNDS: '/owner/refunds',
  MANAGER: '/manager',
  MANAGER_MENUS: '/manager/menus',
};

export const TEST_USERS = {
  // Set this only in the local E2E environment; never commit a staff password.
  ...(process.env.E2E_STAFF_PASSWORD ? {} : (() => { throw new Error('E2E_STAFF_PASSWORD must be set before running E2E tests.') })()),
  CASHIER: {
    username: process.env.E2E_CASHIER_USER || 'e2e_cashier',
    password: process.env.E2E_STAFF_PASSWORD,
    role: 'kasir',
    homePath: '/kasir',
  },
  BARISTA: {
    username: process.env.E2E_BARISTA_USER || 'e2e_barista',
    password: process.env.E2E_STAFF_PASSWORD,
    role: 'barista_kitchen',
    station: 'barista',
    homePath: '/production',
  },
  KITCHEN: {
    username: process.env.E2E_KITCHEN_USER || 'e2e_kitchen',
    password: process.env.E2E_STAFF_PASSWORD,
    role: 'barista_kitchen',
    station: 'kitchen',
    homePath: '/production',
  },
  OWNER: {
    username: process.env.E2E_OWNER_USER || 'e2e_owner',
    password: process.env.E2E_STAFF_PASSWORD,
    role: 'owner',
    homePath: '/owner',
  },
  MANAGER: {
    username: process.env.E2E_MANAGER_USER || 'e2e_manager',
    password: process.env.E2E_STAFF_PASSWORD,
    role: 'manager',
    homePath: '/manager',
  },
};

export const TEST_CUSTOMER = {
  TABLE_NUMBER: '1',
  CUSTOMER_NAME: 'Pelanggan E2E',
};
