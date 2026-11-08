// Implementasi MOCK dari kontrak API (bentuk data identik dengan service di src/api/services).
// Dipakai saat VITE_USE_MOCK=true. Menggantinya dengan backend asli cukup dengan VITE_USE_MOCK=false.
import { ApiError } from '../api/ApiError'
import { tokenStorage } from '../api/tokenStorage'
import { GRANTABLE, PERMISSION_DEFAULTS } from './seed'
import { LABELS, label, load, save, wait, menuIsOrderable, createOrderIn, verifyIn, startIn, completeIn, permissionsOf, publicUser } from './db'

export { resetMock } from './db'

const SESSION = 'exito.mock.user'
const currentUser = () => {
  const id = Number(sessionStorage.getItem(SESSION))
  const s = load()
  const u = s.users.find((x) => x.id === id && x.is_active)
  if (!u) throw new ApiError({ status: 401, code: 'UNAUTHENTICATED', message: 'Silakan login terlebih dahulu.' })
  return u
}
const notFound = (what = 'Data') => new ApiError({ status: 404, code: 'NOT_FOUND', message: `${what} tidak ditemukan.` })
const forbid = () => new ApiError({ status: 403, code: 'FORBIDDEN', message: 'Anda tidak memiliki izin untuk aksi ini.' })
const can = (u, perm) => permissionsOf(u).includes(perm)
const need = (u, perm) => { if (!can(u, perm)) throw forbid() }
const sameDay = (iso, d = new Date()) => new Date(iso).toDateString() === d.toDateString()
const pct = (v, total) => (total ? Math.round((v / total) * 1000) / 10 : 0)

// ---------- Serializer ----------
const stockLevel = (i) => {
  if (i.stock <= 0) return 'habis'
  const r = i.stock / (i.min_stock || 1)
  return r <= 0.5 ? 'kritis' : r <= 1 ? 'menipis' : 'aman'
}
const ingredientOut = (i) => ({ id: i.id, name: i.name, category: i.category, unit: i.unit, stock: i.stock, min_stock: i.min_stock, stock_percent: Math.round((i.stock / (i.min_stock || 1)) * 100), level: label(stockLevel(i), { aman: 'Aman', menipis: 'Menipis', kritis: 'Kritis', habis: 'Habis' }), is_active: i.is_active })
const userName = (s, id) => s.users.find((u) => u.id === id)?.name ?? null
const tableOf = (s, o) => s.tables.find((t) => t.id === o.table_id)
const paymentOut = (s, p) => p && ({ id: p.id, method: label(p.method, { tunai: 'Tunai', qris: 'QRIS' }), status: label(p.status, LABELS.payment), amount: p.amount, amount_received: p.amount_received, change: p.amount_received != null ? p.amount_received - p.amount : null, paid_at: p.paid_at, verified_by: userName(s, p.verified_by), transaction_number: p.transaction_number })
const orderOut = (s, o) => {
  const p = s.payments.filter((x) => x.order_id === o.id).slice(-1)[0]
  return {
    id: o.id, public_token: o.public_token, order_number: o.order_number, display_number: o.display_number,
    table: { id: tableOf(s, o).id, number: tableOf(s, o).table_number }, status: label(o.status, LABELS.order),
    ordered_at: o.ordered_at, paid_at: o.paid_at, completed_at: o.completed_at, total: o.total,
    items: s.details.filter((d) => d.order_id === o.id).map((d) => ({ id: d.id, menu_id: d.menu_id, name: d.name, qty: d.qty, unit_price: d.unit_price, subtotal: d.subtotal, station: d.station })),
    payment: paymentOut(s, p),
    tickets: s.tickets.filter((t) => t.order_id === o.id).map((t) => ({ id: t.id, station: t.station, status: label(t.status, LABELS.ticket), started_at: t.started_at, ready_at: t.ready_at })),
  }
}
const ticketOut = (s, t) => {
  const o = s.orders.find((x) => x.id === t.order_id)
  return { id: t.id, order_id: o.id, order_number: o.order_number, display_number: o.display_number, table_number: tableOf(s, o).table_number, station: t.station, status: label(t.status, LABELS.ticket), ordered_at: o.ordered_at, started_at: t.started_at, ready_at: t.ready_at, handled_by: userName(s, t.handled_by), order_status: label(o.status, LABELS.order), items: s.details.filter((d) => d.order_id === o.id && d.station === t.station).map((d) => ({ name: d.name, qty: d.qty })) }
}
const maxPortions = (s, m) => (m.recipe.length ? Math.min(...m.recipe.map(([id, q]) => Math.floor(s.ingredients.find((i) => i.id === id).stock / q))) : null)
const menuAdmin = (s, m) => {
  const cat = s.categories.find((c) => c.id === m.category_id); const o = menuIsOrderable(s, m)
  return { id: m.id, sku: m.sku, name: m.name, price: m.price, cost_price: m.cost_price, category: { id: cat.id, name: cat.name }, display_group: cat.display_group, station: m.station, is_available: m.is_available, is_orderable: o.ok, unavailable_reason: o.reason, has_recipe: m.recipe.length > 0, max_portions: maxPortions(s, m), image_url: m.image ? '/coffee.png' : null }
}
const paidOrders = (s) => s.orders.filter((o) => ['dibayar', 'diproses', 'selesai'].includes(o.status))

// ---------- Auth ----------
export const authApi = {
  async login(login, password) {
    await wait(); const s = load(); const l = String(login).trim().toLowerCase()
    const u = s.users.find((x) => x.username === l || x.email === l)
    if (!u || u.password !== password) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Email/username atau kata sandi salah.' })
    if (!u.is_active) throw new ApiError({ status: 403, code: 'FORBIDDEN', message: 'Akun dinonaktifkan.' })
    sessionStorage.setItem(SESSION, String(u.id)); const token = `mock-${u.id}`; tokenStorage.set(token)
    return { token, expires_at: new Date(Date.now() + 12 * 3600e3).toISOString(), user: publicUser(u) }
  },
  async logout() { await wait(60); sessionStorage.removeItem(SESSION); tokenStorage.clear(); return null },
  async me() { await wait(60); return publicUser(currentUser()) },
  async updateProfile(p) { await wait(); const s = load(); const u = s.users.find((x) => x.id === currentUser().id); Object.assign(u, { name: p.name ?? u.name, email: p.email ?? u.email }); save(s); return publicUser(u) },
  async changePassword(p) { await wait(); const s = load(); const u = s.users.find((x) => x.id === currentUser().id); if (u.password !== p.current_password) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Kata sandi saat ini salah.', errors: { current_password: ['Kata sandi saat ini salah.'] } }); u.password = p.password; save(s); return null },
}

// ---------- Pelanggan ----------
export const customerApi = {
  async table(qrToken) { await wait(); const t = load().tables.find((x) => x.qr_token === qrToken && x.status === 'active'); if (!t) throw new ApiError({ status: 404, code: 'TABLE_INVALID', message: 'QR meja tidak dikenali.' }); return { id: t.id, number: t.table_number } },
  async menus() {
    await wait(); const s = load()
    return s.menus.map((m) => { const c = s.categories.find((x) => x.id === m.category_id); const o = menuIsOrderable(s, m); return { id: m.id, name: m.name, price: m.price, category: { id: c.id, name: c.name }, display_group: c.display_group, image_url: m.image ? '/coffee.png' : null, is_orderable: o.ok, unavailable_reason: o.reason } })
  },
  async validateCart(items) {
    await wait(80); const s = load(); const issues = []; let total = 0
    const lines = items.map(({ menu_id, qty }) => { const m = s.menus.find((x) => x.id === menu_id); const o = m ? menuIsOrderable(s, m) : { ok: false, reason: 'Menu tidak ditemukan' }; if (!o.ok) issues.push({ menu_id, code: 'MENU_UNAVAILABLE', message: `${m?.name ?? 'Menu'} ${o.reason.toLowerCase()}` }); const subtotal = (m?.price ?? 0) * qty; total += subtotal; return { menu_id, name: m?.name, qty, unit_price: m?.price ?? 0, subtotal } })
    return { items: lines, total, issues }
  },
  async createOrder({ table_number, payment_method, items, table_token }) {
    await wait(); const s = load()
    if (table_token) { const t = s.tables.find((x) => x.qr_token === table_token); if (!t || String(t.table_number) !== String(table_number)) throw new ApiError({ status: 422, code: 'TABLE_INVALID', message: 'Nomor meja tidak sesuai dengan QR.' }) }
    for (const { menu_id } of items) { const m = s.menus.find((x) => x.id === menu_id); const o = m && menuIsOrderable(s, m); if (!o?.ok) throw new ApiError({ status: 409, code: 'MENU_UNAVAILABLE', message: `${m?.name ?? 'Menu'} sedang tidak tersedia.` }) }
    const { order } = createOrderIn(s, { table: table_number, items: items.map((i) => [i.menu_id, i.qty]), method: payment_method })
    save(s); return orderOut(s, order)
  },
  async order(publicToken) { await wait(60); const s = load(); const o = s.orders.find((x) => x.public_token === publicToken); if (!o) throw notFound('Pesanan'); return orderOut(s, o) },
  async cancelOrder(publicToken) {
    await wait(); const s = load(); const o = s.orders.find((x) => x.public_token === publicToken); if (!o) throw notFound('Pesanan')
    if (o.status !== 'menunggu_pembayaran') throw new ApiError({ status: 409, code: 'ORDER_NOT_CANCELLABLE', message: 'Pesanan sudah dibayar dan tidak bisa dibatalkan.' })
    o.status = 'dibatalkan'; s.payments.filter((p) => p.order_id === o.id).forEach((p) => { p.status = 'cancelled' }); save(s); return orderOut(s, o)
  },
  async paymentInfo() { await wait(60); const s = load(); return { cafe_name: s.settings.cafe_name, qris_image_url: s.settings.qris_image_url, instructions: { qris: 'Scan QRIS di kasir atau bayar lalu tunjukkan bukti ke kasir. Pesanan diproses setelah kasir memverifikasi.', tunai: 'Bayar tunai di kasir. Pesanan diproses setelah kasir memverifikasi.' } } },
}

// ---------- Kasir ----------
export const cashierApi = {
  async dashboard() { await wait(); need(currentUser(), 'dashboard.cashier'); const s = load(); const today = s.orders.filter((o) => sameDay(o.ordered_at)); return { new_orders: today.filter((o) => ['menunggu_pembayaran', 'dibayar'].includes(o.status)).length, awaiting_payment: s.orders.filter((o) => o.status === 'menunggu_pembayaran').length, orders_today: today.length, revenue_today: s.payments.filter((p) => p.status === 'verified' && sameDay(p.paid_at)).reduce((a, p) => a + p.amount, 0) } },
  async orders(params = {}) {
    await wait(); need(currentUser(), 'payments.verify'); const s = load(); const st = params['filter[status]']; const q = (params.search || '').toLowerCase()
    return s.orders.filter((o) => sameDay(o.ordered_at) && (!st || st === 'semua' || o.status === st) && (!q || o.order_number.toLowerCase().includes(q) || o.display_number.includes(q))).sort((a, b) => b.id - a.id).map((o) => orderOut(s, o))
  },
  async order(id) { await wait(60); const s = load(); const o = s.orders.find((x) => x.id === Number(id)); if (!o) throw notFound('Pesanan'); return orderOut(s, o) },
  async verify(paymentId, payload = {}) { await wait(); const u = currentUser(); need(u, 'payments.verify'); const s = load(); const p = verifyIn(s, Number(paymentId), u.id, payload); save(s); return orderOut(s, s.orders.find((o) => o.id === p.order_id)) },
  async reject(paymentId, payload = {}) {
    await wait(); need(currentUser(), 'payments.verify'); const s = load(); const p = s.payments.find((x) => x.id === Number(paymentId)); if (!p) throw notFound('Pembayaran')
    if (p.status !== 'pending') throw new ApiError({ status: 409, code: 'PAYMENT_ALREADY_PROCESSED', message: 'Pembayaran sudah diproses.' })
    p.status = 'rejected'; p.reject_reason = payload.reason || 'Ditolak kasir'; const o = s.orders.find((x) => x.id === p.order_id); o.status = 'dibatalkan'; o.cancel_reason = p.reject_reason; save(s); return orderOut(s, o)
  },
  async receipt(orderId) {
    await wait(); const s = load(); const o = s.orders.find((x) => x.id === Number(orderId)); if (!o) throw notFound('Pesanan'); const p = s.payments.filter((x) => x.order_id === o.id).slice(-1)[0]
    return { cafe: { name: s.settings.cafe_name, address: s.settings.cafe_address, footer: s.settings.receipt_footer }, transaction_number: p.transaction_number, order: orderOut(s, o), cashier: userName(s, p.verified_by) }
  },
}

// ---------- Stasiun (Barista / Kitchen) ----------
const guardStation = (u, station) => {
  if (!['barista', 'kitchen'].includes(station)) throw notFound('Stasiun')
  if (!can(u, `queue.${station}`) && !can(u, 'queue.view_all')) throw notFound('Tiket')
}
const canAct = (u, station) => can(u, `queue.${station}`)
export const stationApi = {
  async dashboard(station) { await wait(); const u = currentUser(); guardStation(u, station); const s = load(); const t = s.tickets.filter((x) => x.station === station); return { queued: t.filter((x) => x.status === 'antre').length, in_progress: t.filter((x) => x.status === 'diproses').length, completed_today: t.filter((x) => x.status === 'selesai' && sameDay(x.ready_at || s.orders.find((o) => o.id === x.order_id).ordered_at)).length } },
  async tickets(station, params = {}) {
    await wait(); const u = currentUser(); guardStation(u, station); const s = load(); const st = params['filter[status]']
    return s.tickets.filter((t) => t.station === station && (!st || st === 'semua' || t.status === st) && sameDay(s.orders.find((o) => o.id === t.order_id).ordered_at)).sort((a, b) => new Date(s.orders.find((o) => o.id === a.order_id).ordered_at) - new Date(s.orders.find((o) => o.id === b.order_id).ordered_at)).map((t) => ticketOut(s, t))
  },
  async ticket(station, id) { await wait(60); const u = currentUser(); guardStation(u, station); const s = load(); const t = s.tickets.find((x) => x.id === Number(id) && x.station === station); if (!t) throw notFound('Tiket'); return ticketOut(s, t) },
  async start(station, id) { await wait(); const u = currentUser(); guardStation(u, station); if (!canAct(u, station)) throw forbid(); const s = load(); const t = s.tickets.find((x) => x.id === Number(id) && x.station === station); if (!t) throw notFound('Tiket'); startIn(s, t.id, u.id); save(s); return ticketOut(s, t) },
  async complete(station, id) { await wait(); const u = currentUser(); guardStation(u, station); if (!canAct(u, station)) throw forbid(); const s = load(); const t = s.tickets.find((x) => x.id === Number(id) && x.station === station); if (!t) throw notFound('Tiket'); completeIn(s, t.id, u.id); save(s); return ticketOut(s, t) },
}

// ---------- Menu ----------
export const menusApi = {
  async list(params = {}) { await wait(); need(currentUser(), 'menus.view'); const s = load(); const q = (params.search || '').toLowerCase(); return s.menus.filter((m) => !q || m.name.toLowerCase().includes(q) || m.sku.toLowerCase().includes(q)).map((m) => menuAdmin(s, m)) },
  async show(id) { await wait(60); const s = load(); const m = s.menus.find((x) => x.id === Number(id)); if (!m) throw notFound('Menu'); return menuAdmin(s, m) },
  async create(p) { await wait(); need(currentUser(), 'menus.manage'); const s = load(); const cat = s.categories.find((c) => c.id === Number(p.category_id)); s.seq.menu += 1; const m = { id: s.seq.menu, sku: p.sku || `NEW-${s.seq.menu}`, name: p.name, price: Number(p.price), category_id: cat.id, is_available: true, image: false, recipe: [], station: p.station || cat.station, cost_price: p.cost_price ?? null }; s.menus.push(m); save(s); return menuAdmin(s, m) },
  async update(id, p) { await wait(); need(currentUser(), 'menus.manage'); const s = load(); const m = s.menus.find((x) => x.id === Number(id)); if (!m) throw notFound('Menu'); Object.assign(m, { name: p.name ?? m.name, sku: p.sku ?? m.sku, price: p.price != null ? Number(p.price) : m.price, category_id: p.category_id ? Number(p.category_id) : m.category_id, station: p.station ?? m.station, cost_price: p.cost_price ?? m.cost_price }); save(s); return menuAdmin(s, m) },
  async remove(id) { await wait(); need(currentUser(), 'menus.manage'); const s = load(); s.menus = s.menus.filter((x) => x.id !== Number(id)); save(s); return null },
  async setAvailability(id, v) { await wait(80); need(currentUser(), 'menus.manage'); const s = load(); const m = s.menus.find((x) => x.id === Number(id)); if (!m) throw notFound('Menu'); m.is_available = !!v; save(s); return menuAdmin(s, m) },
  async recipe(id) { await wait(60); const s = load(); const m = s.menus.find((x) => x.id === Number(id)); if (!m) throw notFound('Menu'); return m.recipe.map(([ingId, q]) => { const i = s.ingredients.find((x) => x.id === ingId); return { ingredient_id: i.id, name: i.name, unit: i.unit, quantity: q, level: ingredientOut(i).level } }) },
  async saveRecipe(id, items) { await wait(); need(currentUser(), 'recipes.manage'); const s = load(); const m = s.menus.find((x) => x.id === Number(id)); m.recipe = items.map((i) => [Number(i.ingredient_id), Number(i.quantity)]); save(s); return menuAdmin(s, m) },
  async categories() { await wait(60); return load().categories },
}

// ---------- Inventori ----------
export const inventoryApi = {
  async ingredients(params = {}) { await wait(); need(currentUser(), 'ingredients.view'); const s = load(); const q = (params.search || '').toLowerCase(); return s.ingredients.filter((i) => i.is_active && (!params.category || i.category === params.category) && (!q || i.name.toLowerCase().includes(q)) && (!params.status || (params.status === 'low' ? ['menipis', 'kritis', 'habis'].includes(stockLevel(i)) : stockLevel(i) === params.status))).map(ingredientOut) },
  async createIngredient(p) { await wait(); need(currentUser(), 'ingredients.manage'); const s = load(); s.seq.ingredient += 1; const i = { id: s.seq.ingredient, name: p.name, category: p.category || 'Bahan Makanan', unit: p.unit, stock: Number(p.stock || 0), min_stock: Number(p.min_stock || 0), is_active: true }; s.ingredients.push(i); save(s); return ingredientOut(i) },
  async updateIngredient(id, p) { await wait(); need(currentUser(), 'ingredients.manage'); const s = load(); const i = s.ingredients.find((x) => x.id === Number(id)); Object.assign(i, { name: p.name ?? i.name, category: p.category ?? i.category, unit: p.unit ?? i.unit, min_stock: p.min_stock != null ? Number(p.min_stock) : i.min_stock }); save(s); return ingredientOut(i) },
  async removeIngredient(id) { await wait(); need(currentUser(), 'ingredients.manage'); const s = load(); const i = s.ingredients.find((x) => x.id === Number(id)); if (i) i.is_active = false; save(s); return null },
  async summary() {
    await wait(); need(currentUser(), 'ingredients.view'); const s = load(); const act = s.ingredients.filter((i) => i.is_active); const incoming = {}
    s.movements.filter((m) => m.type === 'penerimaan_pembelian' && sameDay(m.created_at)).forEach((m) => { const u = s.ingredients.find((i) => i.id === m.ingredient_id).unit; incoming[u] = (incoming[u] || 0) + m.quantity_change })
    return { total_items: act.length, critical_count: act.filter((i) => ['kritis', 'habis'].includes(stockLevel(i))).length, low_count: act.filter((i) => stockLevel(i) !== 'aman').length, incoming_today: Object.entries(incoming).map(([unit, total]) => ({ unit, total })), categories: [...new Set(act.map((i) => i.category))].map((c) => ({ name: c, count: act.filter((i) => i.category === c).length })) }
  },
  async movements(params = {}) {
    await wait(); need(currentUser(), 'stock.view'); const s = load()
    return s.movements.filter((m) => (!params.type || m.type === params.type) && (!params.ingredient_id || m.ingredient_id === Number(params.ingredient_id))).sort((a, b) => b.id - a.id).slice(0, 60).map((m) => { const i = s.ingredients.find((x) => x.id === m.ingredient_id); return { id: m.id, ingredient: { id: i.id, name: i.name, unit: i.unit }, type: label(m.type, LABELS.movement), quantity_change: m.quantity_change, stock_after: m.stock_after, user: userName(s, m.user_id), reference: m.reference, note: m.note, created_at: m.created_at } })
  },
  async adjust({ ingredient_id, quantity_change, note }) {
    await wait(); const u = currentUser(); need(u, 'stock.adjust'); const s = load(); const i = s.ingredients.find((x) => x.id === Number(ingredient_id)); if (!i) throw notFound('Bahan')
    if (!note) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Catatan wajib diisi.', errors: { note: ['Catatan wajib diisi.'] } })
    if (i.stock + Number(quantity_change) < 0) throw new ApiError({ status: 409, code: 'INSUFFICIENT_STOCK', message: 'Hasil koreksi membuat stok negatif.' })
    i.stock += Number(quantity_change); s.seq.movement += 1; s.movements.push({ id: s.seq.movement, ingredient_id: i.id, type: 'koreksi', quantity_change: Number(quantity_change), stock_after: i.stock, user_id: u.id, reference: null, note, created_at: new Date().toISOString() }); save(s); return ingredientOut(i)
  },
}

// ---------- Pembelian ----------
const purchaseOut = (s, p) => ({ id: p.id, purchase_number: p.purchase_number, supplier: { id: p.supplier_id, name: s.suppliers.find((x) => x.id === p.supplier_id)?.name }, status: label(p.status, LABELS.purchase), ordered_at: p.ordered_at, note: p.note, total: p.details.reduce((a, d) => a + d.qty_ordered * d.unit_price, 0), details: p.details.map((d) => { const i = s.ingredients.find((x) => x.id === d.ingredient_id); return { id: d.id, ingredient: { id: i.id, name: i.name, unit: i.unit }, qty_ordered: d.qty_ordered, qty_received: d.qty_received, qty_outstanding: d.qty_ordered - d.qty_received, unit_price: d.unit_price } }), receipts: s.receipts.filter((r) => r.purchase_id === p.id).map((r) => ({ id: r.id, receipt_number: r.receipt_number, status: label(r.status, { draft: 'Draft', dikonfirmasi: 'Dikonfirmasi' }), received_at: r.received_at, details: r.details })) })
export const purchasingApi = {
  async suppliers() { await wait(); need(currentUser(), 'purchases.view'); return load().suppliers.filter((x) => x.is_active) },
  async createSupplier(p) { await wait(); need(currentUser(), 'suppliers.manage'); const s = load(); s.seq.supplier += 1; const x = { id: s.seq.supplier, name: p.name, phone: p.phone || null, address: p.address || null, is_active: true }; s.suppliers.push(x); save(s); return x },
  async updateSupplier(id, p) { await wait(); need(currentUser(), 'suppliers.manage'); const s = load(); const x = s.suppliers.find((y) => y.id === Number(id)); Object.assign(x, p); save(s); return x },
  async removeSupplier(id) { await wait(); need(currentUser(), 'suppliers.manage'); const s = load(); const x = s.suppliers.find((y) => y.id === Number(id)); if (x) x.is_active = false; save(s); return null },
  async purchases() { await wait(); need(currentUser(), 'purchases.view'); const s = load(); return s.purchases.slice().reverse().map((p) => purchaseOut(s, p)) },
  async purchase(id) { await wait(60); const s = load(); const p = s.purchases.find((x) => x.id === Number(id)); if (!p) throw notFound('Pembelian'); return purchaseOut(s, p) },
  async createPurchase(p) {
    await wait(); need(currentUser(), 'purchases.manage'); const s = load(); s.seq.purchase += 1; let dseq = s.purchases.reduce((a, x) => a + x.details.length, 0)
    const rec = { id: s.seq.purchase, purchase_number: `PO-${new Date().toISOString().slice(0, 10).replaceAll('-', '')}-${String(s.seq.purchase).padStart(3, '0')}`, supplier_id: Number(p.supplier_id), status: 'draft', ordered_at: null, note: p.note || null, details: p.details.map((d) => ({ id: ++dseq, ingredient_id: Number(d.ingredient_id), qty_ordered: Number(d.qty_ordered), qty_received: 0, unit_price: Number(d.unit_price || 0) })) }
    s.purchases.push(rec); save(s); return purchaseOut(s, rec)
  },
  async updatePurchase() { throw new ApiError({ status: 501, code: 'NOT_IMPLEMENTED', message: 'Ubah draft pembelian belum tersedia di mock.' }) },
  async placePurchase(id) { await wait(); need(currentUser(), 'purchases.manage'); const s = load(); const p = s.purchases.find((x) => x.id === Number(id)); if (p.status !== 'draft') throw new ApiError({ status: 409, code: 'INVALID_STATE_TRANSITION', message: 'Hanya draft yang bisa dipesan.' }); p.status = 'dipesan'; p.ordered_at = new Date().toISOString(); save(s); return purchaseOut(s, p) },
  async cancelPurchase(id) { await wait(); need(currentUser(), 'purchases.manage'); const s = load(); const p = s.purchases.find((x) => x.id === Number(id)); if (s.receipts.some((r) => r.purchase_id === p.id && r.status === 'dikonfirmasi')) throw new ApiError({ status: 409, code: 'PURCHASE_NOT_EDITABLE', message: 'Sudah ada penerimaan yang dikonfirmasi.' }); p.status = 'dibatalkan'; save(s); return purchaseOut(s, p) },
  async createReceipt(purchaseId, payload) {
    await wait(); need(currentUser(), 'purchases.receive'); const s = load(); const p = s.purchases.find((x) => x.id === Number(purchaseId))
    if (!['dipesan', 'diterima_sebagian'].includes(p.status)) throw new ApiError({ status: 409, code: 'PURCHASE_NOT_EDITABLE', message: 'Pembelian belum dipesan atau sudah selesai.' })
    for (const d of payload.details) { const pd = p.details.find((x) => x.id === Number(d.purchase_detail_id)); if (Number(d.qty_received) > pd.qty_ordered - pd.qty_received) throw new ApiError({ status: 409, code: 'OVER_RECEIPT', message: 'Jumlah diterima melebihi sisa pesanan.' }) }
    s.seq.receipt += 1; const r = { id: s.seq.receipt, receipt_number: `GR-${new Date().toISOString().slice(0, 10).replaceAll('-', '')}-${String(s.seq.receipt).padStart(3, '0')}`, purchase_id: p.id, status: 'draft', received_at: new Date().toISOString().slice(0, 10), note: payload.note || null, details: payload.details.map((d) => ({ purchase_detail_id: Number(d.purchase_detail_id), qty_received: Number(d.qty_received) })) }
    s.receipts.push(r); save(s); return purchaseOut(s, p)
  },
  async confirmReceipt(receiptId) {
    await wait(); const u = currentUser(); need(u, 'purchases.receive'); const s = load(); const r = s.receipts.find((x) => x.id === Number(receiptId)); if (!r) throw notFound('Penerimaan')
    if (r.status === 'dikonfirmasi') throw new ApiError({ status: 409, code: 'RECEIPT_ALREADY_CONFIRMED', message: 'Penerimaan sudah dikonfirmasi.' })
    const p = s.purchases.find((x) => x.id === r.purchase_id)
    for (const d of r.details) { const pd = p.details.find((x) => x.id === d.purchase_detail_id); if (d.qty_received > pd.qty_ordered - pd.qty_received) throw new ApiError({ status: 409, code: 'OVER_RECEIPT', message: 'Jumlah diterima melebihi sisa pesanan.' }) }
    for (const d of r.details) {
      const pd = p.details.find((x) => x.id === d.purchase_detail_id); const i = s.ingredients.find((x) => x.id === pd.ingredient_id)
      i.stock += d.qty_received; pd.qty_received += d.qty_received; s.seq.movement += 1
      s.movements.push({ id: s.seq.movement, ingredient_id: i.id, type: 'penerimaan_pembelian', quantity_change: d.qty_received, stock_after: i.stock, user_id: u.id, reference: `${p.purchase_number} / ${r.receipt_number}`, note: null, created_at: new Date().toISOString() })
    }
    r.status = 'dikonfirmasi'; p.status = p.details.every((d) => d.qty_received >= d.qty_ordered) ? 'diterima_lengkap' : 'diterima_sebagian'; save(s); return purchaseOut(s, p)
  },
}

// ---------- Pengguna & meja ----------
export const usersApi = {
  async list() { await wait(); need(currentUser(), 'users.manage'); return load().users.map(publicUser) },
  async create(p) { await wait(); need(currentUser(), 'users.manage'); const s = load(); if (p.role === 'owner') throw forbid(); if (s.users.some((u) => u.username === p.username || u.email === p.email)) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Username atau email sudah dipakai.', errors: { username: ['Sudah dipakai.'] } }); s.seq.user += 1; const u = { id: s.seq.user, name: p.name, username: p.username.toLowerCase(), email: p.email, role: p.role, is_active: true, password: p.password, extra_permissions: [] }; s.users.push(u); save(s); return publicUser(u) },
  async update(id, p) { await wait(); need(currentUser(), 'users.manage'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); if (u.role === 'owner') throw forbid(); Object.assign(u, { name: p.name ?? u.name, email: p.email ?? u.email, role: p.role ?? u.role }); save(s); return publicUser(u) },
  async setStatus(id, v) { await wait(80); need(currentUser(), 'users.manage'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); if (u.role === 'owner') throw forbid(); u.is_active = !!v; save(s); return publicUser(u) },
  async permissions(id) { await wait(60); need(currentUser(), 'permissions.grant'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); return { defaults: PERMISSION_DEFAULTS[u.role], granted: u.extra_permissions, grantable: GRANTABLE } },
  async savePermissions(id, perms) { await wait(); need(currentUser(), 'permissions.grant'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); if (perms.some((p) => !GRANTABLE.includes(p))) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Ada izin yang tidak dapat diberikan.' }); u.extra_permissions = perms; save(s); return publicUser(u) },
  async tables() { await wait(60); need(currentUser(), 'tables.manage'); return load().tables.map((t) => ({ ...t, qr_url: `${window.location.origin}/order?t=${t.qr_token}` })) },
  async createTable(p) { await wait(); need(currentUser(), 'tables.manage'); const s = load(); s.seq.table += 1; const t = { id: s.seq.table, table_number: String(p.table_number), qr_token: `demo-table-${Math.random().toString(36).slice(2, 8)}`, status: 'active' }; s.tables.push(t); save(s); return { ...t, qr_url: `${window.location.origin}/order?t=${t.qr_token}` } },
  async regenerateQr(id) { await wait(); need(currentUser(), 'tables.manage'); const s = load(); const t = s.tables.find((x) => x.id === Number(id)); t.qr_token = `demo-table-${Math.random().toString(36).slice(2, 8)}`; save(s); return { ...t, qr_url: `${window.location.origin}/order?t=${t.qr_token}` } },
}

// ---------- Laporan & dashboard ----------
const periodRange = (period = 'daily', from, to) => {
  const end = to ? new Date(to) : new Date(); const start = from ? new Date(from) : new Date()
  if (!from) { start.setHours(0, 0, 0, 0); if (period === 'weekly') start.setDate(start.getDate() - 6); if (period === 'monthly') start.setDate(start.getDate() - 29) }
  end.setHours(23, 59, 59, 999); return [start, end]
}
function salesReport(s, params = {}) {
  const [start, end] = periodRange(params.period, params.from, params.to)
  const orders = paidOrders(s).filter((o) => { const t = new Date(o.paid_at); return t >= start && t <= end })
  const revenue = orders.reduce((a, o) => a + o.total, 0)
  const hourly = (params.granularity || (params.period === 'daily' || !params.period ? 'hour' : 'day')) === 'hour'
  const buckets = {}
  orders.forEach((o) => { const d = new Date(o.paid_at); const k = hourly ? String(d.getHours()).padStart(2, '0') + ':00' : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }); buckets[k] = buckets[k] || { label: k, amount: 0, orders: 0 }; buckets[k].amount += o.total; buckets[k].orders += 1 })
  const series = hourly ? Array.from({ length: 15 }, (_, i) => { const k = String(i + 8).padStart(2, '0') + ':00'; return buckets[k] || { label: k, amount: 0, orders: 0 } }) : Object.values(buckets)
  const lines = s.details.filter((d) => orders.some((o) => o.id === d.order_id))
  const byMenu = {}; const byCat = {}
  lines.forEach((d) => { byMenu[d.name] = byMenu[d.name] || { name: d.name, qty: 0, amount: 0 }; byMenu[d.name].qty += d.qty; byMenu[d.name].amount += d.subtotal; const m = s.menus.find((x) => x.id === d.menu_id); const c = s.categories.find((x) => x.id === m?.category_id)?.name || 'Lainnya'; byCat[c] = byCat[c] || { category: c, qty: 0, amount: 0 }; byCat[c].qty += d.qty; byCat[c].amount += d.subtotal })
  const pays = s.payments.filter((p) => p.status === 'verified' && orders.some((o) => o.id === p.order_id)); const byMethod = ['qris', 'tunai'].map((m) => { const x = pays.filter((p) => p.method === m); const amount = x.reduce((a, p) => a + p.amount, 0); return { method: m, orders: x.length, amount, percent: pct(amount, revenue) } })
  const cashiers = {}; pays.forEach((p) => { const n = userName(s, p.verified_by) || '-'; cashiers[n] = cashiers[n] || { cashier: n, orders: 0, amount: 0 }; cashiers[n].orders += 1; cashiers[n].amount += p.amount })
  const done = s.tickets.filter((t) => t.ready_at && orders.some((o) => o.id === t.order_id)); const avg = done.length ? Math.round(done.reduce((a, t) => a + (new Date(t.ready_at) - new Date(s.orders.find((o) => o.id === t.order_id).ordered_at)) / 1000, 0) / done.length) : null
  return { period: params.period || 'daily', from: start.toISOString(), to: end.toISOString(), totals: { orders: orders.length, revenue, average_order: orders.length ? Math.round(revenue / orders.length) : 0, items: lines.reduce((a, d) => a + d.qty, 0) }, by_method: byMethod, by_category: Object.values(byCat).sort((a, b) => b.amount - a.amount).map((c) => ({ ...c, percent: pct(c.amount, revenue) })), series, top_menus: Object.values(byMenu).sort((a, b) => b.qty - a.qty).slice(0, 5), cashier_recap: Object.values(cashiers), avg_service_seconds: avg }
}
export const reportsApi = {
  async sales(params) { await wait(); need(currentUser(), 'reports.view'); return salesReport(load(), params) },
  async ownerDashboard() {
    await wait(); need(currentUser(), 'dashboard.owner'); const s = load(); const today = salesReport(s, { period: 'daily' })
    const y = new Date(); y.setDate(y.getDate() - 1); const yest = salesReport(s, { period: 'daily', from: y.toISOString().slice(0, 10), to: y.toISOString().slice(0, 10) })
    return { today: { revenue: today.totals.revenue, orders: today.totals.orders, average_order: today.totals.average_order, vs_yesterday_percent: yest.totals.revenue ? Math.round(((today.totals.revenue - yest.totals.revenue) / yest.totals.revenue) * 1000) / 10 : null }, series: today.series, top_menus: today.top_menus, by_method: today.by_method, low_stock: s.ingredients.filter((i) => i.is_active && stockLevel(i) !== 'aman').map(ingredientOut), critical_count: s.ingredients.filter((i) => ['kritis', 'habis'].includes(stockLevel(i))).length, pending_purchases: s.purchases.filter((p) => ['dipesan', 'diterima_sebagian'].includes(p.status)).length }
  },
  async managerDashboard() {
    await wait(); need(currentUser(), 'dashboard.manager'); const s = load(); const today = salesReport(s, { period: 'daily' }); const active = s.orders.filter((o) => sameDay(o.ordered_at) && ['dibayar', 'diproses'].includes(o.status))
    return { revenue_today: today.totals.revenue, active_orders: { total: active.length, queued: s.tickets.filter((t) => t.status === 'antre').length, in_progress: s.tickets.filter((t) => t.status === 'diproses').length }, orders: s.orders.filter((o) => sameDay(o.ordered_at)).sort((a, b) => b.id - a.id).slice(0, 8).map((o) => orderOut(s, o)), low_stock: s.ingredients.filter((i) => i.is_active && stockLevel(i) !== 'aman').map(ingredientOut), unavailable_menus: s.menus.filter((m) => !menuIsOrderable(s, m).ok).map((m) => ({ id: m.id, name: m.name, reason: menuIsOrderable(s, m).reason })), pending_receipts: s.purchases.filter((p) => ['dipesan', 'diterima_sebagian'].includes(p.status)).length }
  },
  async stockUsage() { await wait(); need(currentUser(), 'reports.view'); const s = load(); const map = {}; s.movements.filter((m) => m.type === 'pemakaian_pesanan').forEach((m) => { const i = s.ingredients.find((x) => x.id === m.ingredient_id); map[i.id] = map[i.id] || { ingredient: i.name, unit: i.unit, used: 0 }; map[i.id].used += Math.abs(m.quantity_change) }); return Object.values(map).sort((a, b) => b.used - a.used) },
  async transactions(params = {}) { await wait(); need(currentUser(), 'transactions.view'); const s = load(); const q = (params.search || '').toLowerCase(); return s.payments.filter((p) => p.status === 'verified' && (!params.method || p.method === params.method)).map((p) => ({ order: orderOut(s, s.orders.find((o) => o.id === p.order_id)), payment: paymentOut(s, p) })).filter((x) => !q || x.payment.transaction_number.toLowerCase().includes(q) || x.order.items.some((i) => i.name.toLowerCase().includes(q))).sort((a, b) => new Date(b.payment.paid_at) - new Date(a.payment.paid_at)).slice(0, 25) },
  async orders(params = {}) { await wait(); need(currentUser(), 'orders.view'); const s = load(); const st = params['filter[status]']; return s.orders.filter((o) => sameDay(o.ordered_at) && (!st || o.status === st)).sort((a, b) => b.id - a.id).map((o) => orderOut(s, o)) },
  async auditLogs() { await wait(); return load().audit },
  async alerts() { await wait(60); const s = load(); return { low_stock_count: s.ingredients.filter((i) => i.is_active && stockLevel(i) !== 'aman').length, awaiting_payment: s.orders.filter((o) => o.status === 'menunggu_pembayaran').length, new_tickets: s.tickets.filter((t) => t.status === 'antre').length } },
}
