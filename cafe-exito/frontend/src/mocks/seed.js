// Data contoh realistis untuk mode mock. Bukan data Cafe Exito yang sebenarnya.
// Semua kata sandi akun mock: "password".
export const PERMISSION_DEFAULTS = {
  owner: ['dashboard.owner', 'orders.view', 'transactions.view', 'queue.view_all', 'menus.view', 'ingredients.view', 'purchases.view', 'stock.view', 'reports.view'],
  admin: ['dashboard.manager', 'orders.view', 'orders.cancel', 'transactions.view', 'queue.view_all', 'menus.view', 'menus.manage', 'ingredients.view', 'ingredients.manage', 'recipes.manage', 'suppliers.manage', 'purchases.view', 'purchases.manage', 'purchases.receive', 'stock.view', 'stock.adjust', 'tables.manage', 'users.manage', 'permissions.grant', 'reports.view'],
  kasir: ['dashboard.cashier', 'payments.verify', 'transactions.view', 'menus.view'],
  barista: ['dashboard.station', 'queue.barista'],
  kitchen: ['dashboard.station', 'queue.kitchen'],
}
export const GRANTABLE = ['queue.barista', 'queue.kitchen', 'purchases.receive', 'ingredients.view', 'stock.view', 'menus.view']

export const USERS = [
  { id: 1, name: 'Budi Santoso', username: 'owner', email: 'owner@cafeexito.test', role: 'owner', is_active: true },
  { id: 2, name: 'Rian Baskoro', username: 'manager', email: 'manager@cafeexito.test', role: 'admin', is_active: true },
  { id: 3, name: 'Ucup', username: 'ucup', email: 'ucup@cafeexito.test', role: 'kasir', is_active: true },
  { id: 4, name: 'Nana', username: 'nana', email: 'nana@cafeexito.test', role: 'barista', is_active: true },
  { id: 5, name: 'Dewi Anggraini', username: 'dewi', email: 'dewi@cafeexito.test', role: 'kitchen', is_active: true },
]

export const CATEGORIES = [
  { id: 1, name: 'Signature Espresso', display_group: 'minuman', station: 'barista' },
  { id: 2, name: 'Manual Brew', display_group: 'minuman', station: 'barista' },
  { id: 3, name: 'Non-Coffee & Tea', display_group: 'minuman', station: 'barista' },
  { id: 4, name: 'Pastry & Bakery', display_group: 'cemilan', station: 'kitchen' },
  { id: 5, name: 'Makanan Berat', display_group: 'makanan', station: 'kitchen' },
]

// unit: g | ml | pcs
export const INGREDIENTS = [
  { id: 1, name: 'Biji Kopi House Blend', category: 'Biji Kopi & Bubuk', unit: 'g', stock: 1800, min_stock: 5000 },
  { id: 2, name: 'Fresh Milk Greenfields', category: 'Susu & Dairy', unit: 'ml', stock: 4000, min_stock: 12000 },
  { id: 3, name: 'Oat Milk Barista', category: 'Susu & Dairy', unit: 'ml', stock: 14000, min_stock: 8000 },
  { id: 4, name: 'Sirup Karamel', category: 'Sirup & Topping', unit: 'ml', stock: 0, min_stock: 2000 },
  { id: 5, name: 'Sirup Vanilla', category: 'Sirup & Topping', unit: 'ml', stock: 2200, min_stock: 3000 },
  { id: 6, name: 'Matcha Powder', category: 'Biji Kopi & Bubuk', unit: 'g', stock: 600, min_stock: 300 },
  { id: 7, name: 'Butter Croissant Frozen', category: 'Bahan Makanan', unit: 'pcs', stock: 18, min_stock: 10 },
  { id: 8, name: 'Nasi Putih', category: 'Bahan Makanan', unit: 'g', stock: 9000, min_stock: 4000 },
  { id: 9, name: 'Telur', category: 'Bahan Makanan', unit: 'pcs', stock: 60, min_stock: 30 },
  { id: 10, name: 'Paper Cup 12oz + Lid', category: 'Packaging & Cups', unit: 'pcs', stock: 120, min_stock: 150 },
  { id: 11, name: 'Teh Hitam', category: 'Biji Kopi & Bubuk', unit: 'g', stock: 800, min_stock: 300 },
  { id: 12, name: 'Gula Pasir', category: 'Bahan Makanan', unit: 'g', stock: 5000, min_stock: 2000 },
]

export const MENUS = [
  { id: 1, sku: 'COF-001', name: 'Es Kopi Susu', price: 18000, category_id: 1, is_available: true, image: true, recipe: [[1, 18], [2, 120], [12, 15]] },
  { id: 2, sku: 'COF-002', name: 'Cappuccino', price: 22000, category_id: 1, is_available: true, image: true, recipe: [[1, 18], [2, 150]] },
  { id: 3, sku: 'COF-003', name: 'Americano', price: 18000, category_id: 1, is_available: true, image: true, recipe: [[1, 18]] },
  { id: 4, sku: 'COF-004', name: 'Caramel Macchiato', price: 28000, category_id: 1, is_available: true, image: true, recipe: [[1, 18], [2, 150], [4, 20]] },
  { id: 5, sku: 'COF-005', name: 'Oat Latte', price: 27000, category_id: 1, is_available: true, image: true, recipe: [[1, 18], [3, 150]] },
  { id: 6, sku: 'MBR-001', name: 'V60 Ethiopia Guji', price: 30000, category_id: 2, is_available: true, image: true, recipe: [[1, 15]] },
  { id: 7, sku: 'NCO-001', name: 'Matcha Latte', price: 25000, category_id: 3, is_available: true, image: false, recipe: [[6, 10], [2, 180]] },
  { id: 8, sku: 'NCO-002', name: 'Es Teh Manis', price: 8000, category_id: 3, is_available: true, image: false, recipe: [[11, 5], [12, 20]] },
  { id: 9, sku: 'PST-001', name: 'Butter Croissant', price: 22000, category_id: 4, is_available: true, image: false, recipe: [[7, 1]] },
  { id: 10, sku: 'PST-002', name: 'Double Choco Muffin', price: 24000, category_id: 4, is_available: true, image: false, recipe: [] },
  { id: 11, sku: 'SNK-001', name: 'Kentang Goreng', price: 18000, category_id: 4, is_available: true, image: false, recipe: [] },
  { id: 12, sku: 'FOD-001', name: 'Nasi Goreng Exito', price: 30000, category_id: 5, is_available: true, image: false, recipe: [[8, 200], [9, 1]] },
  { id: 13, sku: 'FOD-002', name: 'Mie Goreng', price: 28000, category_id: 5, is_available: true, image: false, recipe: [] },
]

export const SUPPLIERS = [
  { id: 1, name: 'PT Sukses Berkah Kopi', phone: '0751-555-0101', address: 'Padang', is_active: true },
  { id: 2, name: 'PT Greenfields Dairy', phone: '0751-555-0102', address: 'Padang', is_active: true },
  { id: 3, name: 'Toko Bahan Roti Sentosa', phone: '0751-555-0103', address: 'Padang', is_active: true },
]

export const TABLES = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1, table_number: String(i + 1), qr_token: `demo-table-${String(i + 1).padStart(2, '0')}`, status: 'active',
}))
