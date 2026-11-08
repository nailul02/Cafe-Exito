import { ApiError } from '../api/ApiError'
import { USERS, CATEGORIES, INGREDIENTS, MENUS, SUPPLIERS, TABLES, PERMISSION_DEFAULTS } from './seed'

const KEY = 'exito.mockdb.v1'
export const wait = (ms = 140) => new Promise((r) => setTimeout(r, ms + Math.random() * 100))
const now = () => new Date().toISOString()
const minutesAgo = (m) => new Date(Date.now() - m * 60000).toISOString()
export const label = (code, map) => ({ code, label: map[code] || code })
export const LABELS = {
  order: { menunggu_pembayaran: 'Menunggu Pembayaran', dibayar: 'Dibayar', diproses: 'Diproses', selesai: 'Selesai', dibatalkan: 'Dibatalkan' },
  ticket: { antre: 'Antre', diproses: 'Diproses', selesai: 'Selesai', dibatalkan: 'Dibatalkan' },
  payment: { pending: 'Menunggu', verified: 'Terverifikasi', rejected: 'Ditolak', cancelled: 'Dibatalkan', refunded: 'Dikembalikan' },
  purchase: { draft: 'Draft', dipesan: 'Dipesan', diterima_sebagian: 'Diterima Sebagian', diterima_lengkap: 'Diterima Lengkap', dibatalkan: 'Dibatalkan' },
  movement: { penerimaan_pembelian: 'Penerimaan Pembelian', pemakaian_pesanan: 'Pemakaian Pesanan', koreksi: 'Koreksi', pembalikan_pemakaian: 'Pembalikan Pemakaian' },
  role: { owner: 'Owner', admin: 'Manager', kasir: 'Kasir', barista: 'Barista', kitchen: 'Kitchen' },
}

// PRNG deterministik untuk riwayat penjualan contoh.
function rng(seed) { let s = seed; return () => ((s = (s * 1664525 + 1013904223) % 4294967296) / 4294967296) }

export function buildSeed() {
  const s = {
    seq: { order: 0, payment: 0, ticket: 0, movement: 0, detail: 0, purchase: 0, receipt: 0, supplier: 3, user: 5, table: 12, ingredient: 12, menu: 13 },
    users: USERS.map((u) => ({ ...u, password: 'password', extra_permissions: [] })),
    categories: CATEGORIES, tables: TABLES.map((t) => ({ ...t })),
    ingredients: INGREDIENTS.map((i) => ({ ...i, is_active: true })),
    menus: MENUS.map((m) => ({ ...m, station: CATEGORIES.find((c) => c.id === m.category_id).station, cost_price: null })),
    suppliers: SUPPLIERS.map((x) => ({ ...x })),
    orders: [], payments: [], tickets: [], details: [], movements: [], purchases: [], receipts: [], audit: [],
    settings: { cafe_name: 'Cafe Exito', cafe_address: 'Koto Tangah, Padang', qris_image_url: null, receipt_footer: 'Terima kasih sudah berkunjung' },
  }
  // Riwayat 7 hari terakhir (pesanan selesai & terverifikasi) agar laporan tidak kosong.
  const rand = rng(42)
  for (let d = 6; d >= 1; d--) {
    const n = 8 + Math.floor(rand() * 8)
    for (let k = 0; k < n; k++) {
      const hour = [8, 9, 10, 12, 13, 14, 15, 16, 19, 20][Math.floor(rand() * 10)]
      const t = new Date(); t.setDate(t.getDate() - d); t.setHours(hour, Math.floor(rand() * 59), 0, 0)
      const items = [[1 + Math.floor(rand() * 8), 1 + Math.floor(rand() * 2)]]
      if (rand() > 0.55) items.push([9 + Math.floor(rand() * 4), 1])
      createOrderIn(s, { table: 1 + Math.floor(rand() * 12), items, method: rand() > 0.35 ? 'qris' : 'tunai', at: t.toISOString(), history: true, cashier: 3 })
    }
  }
  // Pesanan hari ini dengan beragam status.
  const mk = (table, items, method, minutes) => createOrderIn(s, { table, items, method, at: minutesAgo(minutes), cashier: 3 })
  const a = mk(4, [[1, 1]], 'tunai', 3) // menunggu bayar
  const b = mk(5, [[1, 2], [2, 1]], 'qris', 9); verifyIn(s, b.payment.id, 3, {}) // antre barista
  const c = mk(2, [[3, 1], [7, 2]], 'tunai', 16); verifyIn(s, c.payment.id, 3, {}); startIn(s, tk(s, c.order.id, 'barista').id, 4) // diproses
  const d2 = mk(7, [[4, 1], [9, 1]], 'qris', 22); verifyIn(s, d2.payment.id, 3, {}) // campuran
  startIn(s, tk(s, d2.order.id, 'kitchen').id, 5)
  const e = mk(9, [[12, 1], [8, 1]], 'tunai', 40); verifyIn(s, e.payment.id, 3, {})
  startIn(s, tk(s, e.order.id, 'kitchen').id, 5); completeIn(s, tk(s, e.order.id, 'kitchen').id, 5)
  startIn(s, tk(s, e.order.id, 'barista').id, 4); completeIn(s, tk(s, e.order.id, 'barista').id, 4) // selesai
  void a
  s.purchases.push(
    { id: 1, purchase_number: 'PO-20261028-001', supplier_id: 1, status: 'dipesan', ordered_at: minutesAgo(3000), note: null, details: [{ id: 1, ingredient_id: 1, qty_ordered: 10000, qty_received: 0, unit_price: 190 }] },
  )
  s.seq.purchase = 1; s.seq.receipt = 0
  return s
}
const tk = (s, orderId, station) => s.tickets.find((t) => t.order_id === orderId && t.station === station)

export function load() {
  try { const raw = localStorage.getItem(KEY); if (raw) return JSON.parse(raw) } catch { /* abaikan */ }
  const s = buildSeed(); save(s); return s
}
export const save = (s) => localStorage.setItem(KEY, JSON.stringify(s))
export const resetMock = () => { localStorage.removeItem(KEY); save(buildSeed()) }

const pad = (n, l = 3) => String(n).padStart(l, '0')
const ymd = (iso) => iso.slice(0, 10).replaceAll('-', '')

export function menuIsOrderable(s, m) {
  if (!m.is_available) return { ok: false, reason: 'Sedang tidak tersedia' }
  for (const [ingId, q] of m.recipe) {
    const ing = s.ingredients.find((i) => i.id === ingId)
    if (!ing || ing.stock < q) return { ok: false, reason: 'Bahan habis' }
  }
  return { ok: true, reason: null }
}

export function createOrderIn(s, { table, items, method, at = now(), history = false, cashier = null }) {
  const tbl = s.tables.find((t) => String(t.table_number) === String(table) && t.status === 'active')
  if (!tbl) throw new ApiError({ status: 422, code: 'TABLE_INVALID', message: 'Nomor meja tidak ditemukan.' })
  s.seq.order += 1
  const orderId = s.seq.order
  const orderNo = `EX-${ymd(at)}-${pad(orderId, 4)}`
  let total = 0
  const lines = items.map(([menuId, qty]) => {
    const m = s.menus.find((x) => x.id === menuId)
    if (!m) throw new ApiError({ status: 422, code: 'MENU_UNAVAILABLE', message: 'Menu tidak ditemukan.' })
    s.seq.detail += 1
    const subtotal = m.price * qty; total += subtotal
    return { id: s.seq.detail, order_id: orderId, menu_id: m.id, name: m.name, qty, unit_price: m.price, subtotal, station: m.station }
  })
  s.details.push(...lines)
  s.seq.payment += 1
  const payment = { id: s.seq.payment, order_id: orderId, method, status: 'pending', amount: total, amount_received: null, paid_at: null, verified_by: null, transaction_number: null, reject_reason: null }
  s.payments.push(payment)
  const order = { id: orderId, public_token: `tok${Date.now().toString(36)}${orderId}${Math.random().toString(36).slice(2, 8)}`, order_number: orderNo, display_number: `#${pad(orderId % 1000)}`, table_id: tbl.id, status: 'menunggu_pembayaran', ordered_at: at, paid_at: null, completed_at: null, total, cancel_reason: null }
  s.orders.push(order)
  if (history) {
    verifyIn(s, payment.id, cashier, {}, at)
    s.tickets.filter((t) => t.order_id === orderId).forEach((t) => { t.status = 'selesai'; t.started_at = at; t.ready_at = new Date(new Date(at).getTime() + (3 + (orderId % 4)) * 60000).toISOString() })
    order.status = 'selesai'; order.completed_at = new Date(new Date(at).getTime() + 6 * 60000).toISOString()
  }
  return { order, payment }
}

export function verifyIn(s, paymentId, userId, payload = {}, at = now()) {
  const p = s.payments.find((x) => x.id === paymentId)
  if (!p) throw new ApiError({ status: 404, code: 'NOT_FOUND', message: 'Pembayaran tidak ditemukan.' })
  if (p.status !== 'pending') throw new ApiError({ status: 409, code: 'PAYMENT_ALREADY_PROCESSED', message: 'Pembayaran sudah diproses.' })
  const order = s.orders.find((o) => o.id === p.order_id)
  if (payload.method) p.method = payload.method
  if (p.method === 'tunai' && payload.amount_received != null && Number(payload.amount_received) < p.amount) {
    throw new ApiError({ status: 422, code: 'AMOUNT_TOO_LOW', message: 'Uang diterima kurang dari total tagihan.' })
  }
  p.amount_received = p.method === 'tunai' ? Number(payload.amount_received ?? p.amount) : null
  p.status = 'verified'; p.paid_at = at; p.verified_by = userId
  p.transaction_number = `TRX-${ymd(at)}-${pad(p.id)}`
  order.status = 'dibayar'; order.paid_at = at
  for (const station of ['barista', 'kitchen']) {
    const lines = s.details.filter((d) => d.order_id === order.id && d.station === station)
    if (!lines.length) continue
    s.seq.ticket += 1
    s.tickets.push({ id: s.seq.ticket, order_id: order.id, station, status: 'antre', started_at: null, ready_at: null, handled_by: null, updated_at: at })
  }
  return p
}

export function startIn(s, ticketId, userId) {
  const t = s.tickets.find((x) => x.id === ticketId)
  if (!t) throw new ApiError({ status: 404, code: 'NOT_FOUND', message: 'Tiket tidak ditemukan.' })
  if (t.status !== 'antre') throw new ApiError({ status: 409, code: 'INVALID_STATE_TRANSITION', message: 'Tiket tidak bisa dimulai dari status ini.' })
  const need = {}
  for (const d of s.details.filter((x) => x.order_id === t.order_id && x.station === t.station)) {
    const m = s.menus.find((x) => x.id === d.menu_id)
    for (const [ingId, q] of m.recipe) need[ingId] = (need[ingId] || 0) + q * d.qty
  }
  const short = Object.entries(need).filter(([id, q]) => s.ingredients.find((i) => i.id === Number(id)).stock < q)
  if (short.length) {
    throw new ApiError({ status: 409, code: 'INSUFFICIENT_STOCK', message: 'Stok bahan tidak cukup untuk memulai tiket ini.', details: { ingredients: short.map(([id]) => s.ingredients.find((i) => i.id === Number(id)).name) } })
  }
  for (const [id, q] of Object.entries(need)) {
    const ing = s.ingredients.find((i) => i.id === Number(id)); ing.stock -= q
    s.seq.movement += 1
    s.movements.push({ id: s.seq.movement, ingredient_id: ing.id, type: 'pemakaian_pesanan', quantity_change: -q, stock_after: ing.stock, user_id: userId, reference: `Tiket #${t.id}`, note: null, created_at: now() })
  }
  t.status = 'diproses'; t.started_at = now(); t.handled_by = userId; t.updated_at = now()
  const order = s.orders.find((o) => o.id === t.order_id)
  if (order.status === 'dibayar') order.status = 'diproses'
  return t
}

export function completeIn(s, ticketId, userId) {
  const t = s.tickets.find((x) => x.id === ticketId)
  if (!t) throw new ApiError({ status: 404, code: 'NOT_FOUND', message: 'Tiket tidak ditemukan.' })
  if (t.status !== 'diproses') throw new ApiError({ status: 409, code: 'INVALID_STATE_TRANSITION', message: 'Tiket belum diproses.' })
  t.status = 'selesai'; t.ready_at = now(); t.handled_by = t.handled_by || userId; t.updated_at = now()
  const order = s.orders.find((o) => o.id === t.order_id)
  const open = s.tickets.filter((x) => x.order_id === order.id && !['selesai', 'dibatalkan'].includes(x.status))
  if (!open.length) { order.status = 'selesai'; order.completed_at = now() }
  return t
}

export const permissionsOf = (u) => [...new Set([...(PERMISSION_DEFAULTS[u.role] || []), ...(u.extra_permissions || [])])]
export const publicUser = (u) => ({ id: u.id, name: u.name, username: u.username, email: u.email, role: label(u.role, LABELS.role), is_active: u.is_active, permissions: permissionsOf(u) })
