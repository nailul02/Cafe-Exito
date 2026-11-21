// Data contoh realistis untuk mode mock. Bukan data Cafe Exito yang sebenarnya.
// Semua kata sandi akun mock: "password".
export const PERMISSION_DEFAULTS = {
  owner: ['dashboard.owner', 'orders.view', 'orders.cancel', 'transactions.view', 'queue.view_all', 'menus.view', 'reports.view', 'attendance.view_all', 'users.manage', 'permissions.grant', 'refund.review', 'refund.audit', 'refund.conflict.document', 'notifications.view'],
  manager: ['dashboard.manager', 'orders.view', 'transactions.view', 'queue.view_all', 'menus.view', 'menus.manage', 'tables.manage', 'reports.view', 'audit.view', 'attendance.view_all', 'attendance.record', 'refund.conflict.document', 'notifications.view'],
  kasir: ['dashboard.cashier', 'payments.verify', 'transactions.view', 'menus.view', 'orders.create_pos', 'attendance.record', 'refund.view', 'refund.execute', 'refund.request', 'notifications.view'],
  barista_kitchen: ['dashboard.station', 'queue.barista', 'queue.kitchen', 'attendance.record', 'notifications.view'],
}
export const GRANTABLE = ['queue.barista', 'queue.kitchen', 'menus.view', 'attendance.record']

export const USERS = [
  { id: 1, name: 'Budi Santoso', username: 'owner', email: 'owner@cafeexito.test', role: 'owner', is_active: true },
  { id: 2, name: 'Rian Baskoro', username: 'manager', email: 'manager@cafeexito.test', role: 'manager', is_active: true },
  { id: 3, name: 'Ucup', username: 'ucup', email: 'ucup@cafeexito.test', role: 'kasir', is_active: true },
  { id: 4, name: 'Nana', username: 'nana', email: 'nana@cafeexito.test', role: 'barista_kitchen', station: 'barista', is_active: true },
  { id: 5, name: 'Dewi Anggraini', username: 'dewi', email: 'dewi@cafeexito.test', role: 'barista_kitchen', station: 'kitchen', is_active: true },
]

export const CATEGORIES = [
  { id: 1, name: 'Signature Espresso', display_group: 'minuman', station: 'barista' },
  { id: 2, name: 'Manual Brew', display_group: 'minuman', station: 'barista' },
  { id: 3, name: 'Non-Coffee & Tea', display_group: 'minuman', station: 'barista' },
  { id: 4, name: 'Pastry & Bakery', display_group: 'cemilan', station: 'kitchen' },
  { id: 5, name: 'Makanan Berat', display_group: 'makanan', station: 'kitchen' },
]

export const MENUS = [
  { id: 1, sku: 'COF-001', name: 'Es Kopi Susu', price: 18000, category_id: 1, is_available: true, image: true },
  { id: 2, sku: 'COF-002', name: 'Cappuccino', price: 22000, category_id: 1, is_available: true, image: true },
  { id: 3, sku: 'COF-003', name: 'Americano', price: 18000, category_id: 1, is_available: true, image: true },
  { id: 4, sku: 'COF-004', name: 'Caramel Macchiato', price: 28000, category_id: 1, is_available: true, image: true },
  { id: 5, sku: 'COF-005', name: 'Oat Latte', price: 27000, category_id: 1, is_available: true, image: true },
  { id: 6, sku: 'MBR-001', name: 'V60 Ethiopia Guji', price: 30000, category_id: 2, is_available: true, image: true },
  { id: 7, sku: 'NCO-001', name: 'Matcha Latte', price: 25000, category_id: 3, is_available: true, image: false },
  { id: 8, sku: 'NCO-002', name: 'Es Teh Manis', price: 8000, category_id: 3, is_available: true, image: false },
  { id: 9, sku: 'PST-001', name: 'Butter Croissant', price: 22000, category_id: 4, is_available: true, image: false },
  { id: 10, sku: 'PST-002', name: 'Double Choco Muffin', price: 24000, category_id: 4, is_available: true, image: false },
  { id: 11, sku: 'SNK-001', name: 'Kentang Goreng', price: 18000, category_id: 4, is_available: true, image: false },
  { id: 12, sku: 'FOD-001', name: 'Nasi Goreng Exito', price: 30000, category_id: 5, is_available: true, image: false },
  { id: 13, sku: 'FOD-002', name: 'Mie Goreng', price: 28000, category_id: 5, is_available: true, image: false },
]

export const TABLES = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1, table_number: String(i + 1), qr_token: `demo-table-${String(i + 1).padStart(2, '0')}`, status: 'active',
}))
