import { ApiError } from '../api/ApiError'
import { USERS, CATEGORIES, INGREDIENTS, MENUS, SUPPLIERS, TABLES, PERMISSION_DEFAULTS } from './seed'

const KEY = 'exito.mockdb.v4'
export const wait = (ms = 140) => new Promise((r) => setTimeout(r, ms + Math.random() * 100))
export const cafeDate = (value = new Date()) => {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit',
  }).formatToParts(value)
  const fields = Object.fromEntries(parts.map(({ type, value: part }) => [type, part]))
  return `${fields.year}-${fields.month}-${fields.day}`
}
const now = () => new Date().toISOString()
const minutesAgo = (m) => new Date(Date.now() - m * 60000).toISOString()
export const label = (code, map) => ({ code, label: map[code] || code })
export const LABELS = {
  order: { menunggu_pembayaran: 'Menunggu Pembayaran', dibayar: 'Dibayar', diproses: 'Diproses', selesai: 'Selesai', dibatalkan: 'Dibatalkan' },
  ticket: { antre: 'Antre', diproses: 'Diproses', selesai: 'Selesai', dibatalkan: 'Dibatalkan' },
  payment: { pending: 'Menunggu', verified: 'Terverifikasi', rejected: 'Ditolak', cancelled: 'Dibatalkan', refunded: 'Dikembalikan' },
  purchase: { draft: 'Draft', dipesan: 'Dipesan', diterima_sebagian: 'Diterima Sebagian', diterima_lengkap: 'Diterima Lengkap', dibatalkan: 'Dibatalkan' },
  movement: { penerimaan_pembelian: 'Penerimaan Pembelian', pemakaian_pesanan: 'Pemakaian Pesanan', koreksi: 'Koreksi', pembalikan_pemakaian: 'Pembalikan Pemakaian' },
  role: { owner: 'Owner', manager: 'Manager', admin: 'Manager', kasir: 'Kasir', barista_kitchen: 'Barista & Kitchen', barista: 'Barista', kitchen: 'Kitchen' },
  service_type: { dine_in: 'Dine In', takeaway: 'Takeaway' },
  order_source: { qr_customer: 'QR Meja', pos_cashier: 'POS Kasir' },
  attendance_type: { check_in: 'Check In', check_out: 'Check Out' },
}

// PRNG deterministik untuk riwayat penjualan contoh.
function rng(seed) { let s = seed; return () => ((s = (s * 1664525 + 1013904223) % 4294967296) / 4294967296) }

export function buildSeed() {
  const s = {
    seq: { order: 0, payment: 0, ticket: 0, movement: 0, detail: 0, purchase: 0, receipt: 0, supplier: 3, user: 5, table: 12, ingredient: 12, menu: 13, attendance: 0 },
    users: USERS.map((u) => ({ ...u, password: 'password', extra_permissions: [] })),
    categories: CATEGORIES, tables: TABLES.map((t) => ({ ...t })),
    ingredients: INGREDIENTS.map((i) => ({ ...i, is_active: true })),
    menus: MENUS.map((m) => ({ ...m, station: CATEGORIES.find((c) => c.id === m.category_id).station, cost_price: null })),
    suppliers: SUPPLIERS.map((x) => ({ ...x })),
    orders: [], payments: [], tickets: [], details: [], movements: [], purchases: [], receipts: [], audit: [],
    attendances: [],
    settings: { cafe_name: 'Cafe Exito', cafe_address: 'Jl. Balai Gadang, Koto Tangah, Padang', qris_image_url: '/coffee.png', receipt_footer: 'Terima kasih atas kunjungan Anda di Cafe Éxito!' },
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
  const b = mk(5, [[1, 2], [2, 1]], 'qris', 9); verifyIn(s, b.payment.id, 3, {}, undefined, false) // antre barista
  const c = mk(2, [[3, 1], [7, 2]], 'tunai', 16); verifyIn(s, c.payment.id, 3, {}, undefined, false); startIn(s, tk(s, c.order.id, 'barista').id, 4) // diproses
  const d2 = mk(7, [[4, 1], [9, 1]], 'qris', 22); verifyIn(s, d2.payment.id, 3, {}, undefined, false) // campuran
  startIn(s, tk(s, d2.order.id, 'kitchen').id, 5)
  const e = mk(9, [[12, 1], [8, 1]], 'tunai', 40); verifyIn(s, e.payment.id, 3, {}, undefined, false)
  startIn(s, tk(s, e.order.id, 'kitchen').id, 5); completeIn(s, tk(s, e.order.id, 'kitchen').id, 5)
  startIn(s, tk(s, e.order.id, 'barista').id, 4); completeIn(s, tk(s, e.order.id, 'barista').id, 4) // selesai
  void a

  // Pesanan Takeaway via POS kasir contoh
  const posTakeaway = createOrderIn(s, {
    service_type: 'takeaway',
    customer_name: 'Kak Siska (Bungkus)',
    order_source: 'pos_cashier',
    items: [[1, 2], [9, 2]],
    method: 'tunai',
    at: minutesAgo(12),
    cashier: 3,
  })
  verifyIn(s, posTakeaway.payment.id, 3, { amount_received: 100000 }, minutesAgo(12), false)

  // Seed absensi dasar staf (tanpa menyimpan foto atau koordinat).
  const todayMorning = new Date(); todayMorning.setHours(7, 52, 0, 0)
  const seedAttend = (userId, checkInAt) => {
    s.seq.attendance += 1
    s.attendances.push({
      id: s.seq.attendance,
      user_id: userId,
      attendance_date: cafeDate(new Date(checkInAt)),
      status: 'present',
      check_in_at: checkInAt,
      check_out_at: null,
    })
  }
  seedAttend(3, todayMorning.toISOString())
  const nanaMorning = new Date(); nanaMorning.setHours(7, 56, 0, 0)
  seedAttend(4, nanaMorning.toISOString())
  const mgrMorning = new Date(); mgrMorning.setHours(7, 45, 0, 0)
  seedAttend(2, mgrMorning.toISOString())

  s.purchases.push(
    { id: 1, purchase_number: 'PO-20261028-001', supplier_id: 1, status: 'dipesan', ordered_at: minutesAgo(3000), note: null, details: [{ id: 1, ingredient_id: 1, qty_ordered: 10000, qty_received: 0, unit_price: 190 }] },
  )
  s.seq.purchase = 1; s.seq.receipt = 0
  return s
}
const tk = (s, orderId, station) => s.tickets.find((t) => t.order_id === orderId && t.station === station)

export function load() {
  try {
    // Bersihkan cache lama jika ada
    localStorage.removeItem('exito.mockdb.v3')
    localStorage.removeItem('exito.mockdb.v2')
    localStorage.removeItem('exito.mockdb.v1')
    const raw = localStorage.getItem(KEY)
    if (raw) {
      const s = JSON.parse(raw)
      const oldAttendance = (s.attendances || []).some((row) => row.type || row.timestamp)
      let legacyRoles = false
      if (oldAttendance) {
        const daily = new Map()
        for (const event of s.attendances || []) {
          const timestamp = event.timestamp || event.check_in_at || event.check_out_at
          if (!timestamp) continue
          const attendanceDate = cafeDate(new Date(timestamp))
          const key = `${event.user_id}:${attendanceDate}`
          const record = daily.get(key) || {
            id: event.id,
            user_id: event.user_id,
            attendance_date: attendanceDate,
            status: 'present',
            check_in_at: null,
            check_out_at: null,
            events: [],
          }
          const eventType = event.event_type || event.type || (event.check_out_at ? 'check_out' : 'check_in')
          if (eventType === 'check_in' || event.check_in_at) record.check_in_at = timestamp
          if (eventType === 'check_out' || event.check_out_at) record.check_out_at = timestamp
          record.events.push({ ...event, event_type: eventType, recorded_at: event.recorded_at || timestamp })
          daily.set(key, record)
        }
        s.attendances = [...daily.values()]
        s.seq.attendance = Math.max(0, ...s.attendances.map((row) => row.id || 0))
      }
      if (s.users) {
        s.users = s.users.map((u) => {
          if (u.role === 'admin' || u.role === 'barista' || u.role === 'kitchen' || (u.role === 'barista_kitchen' && !u.station)) legacyRoles = true
          return {
            ...u,
            role: u.role === 'admin' ? 'manager' : (u.role === 'barista' || u.role === 'kitchen' ? 'barista_kitchen' : u.role),
            station: u.station || (u.role === 'barista' ? 'barista' : u.role === 'kitchen' ? 'kitchen' : u.username === 'nana' ? 'barista' : u.username === 'dewi' ? 'kitchen' : null),
          }
        })
      }
      if (oldAttendance || legacyRoles) save(s)
      return s
    }
  } catch { /* abaikan */ }
  const s = buildSeed(); save(s); return s
}
export const save = (s) => localStorage.setItem(KEY, JSON.stringify(s))
export const resetMock = () => {
  localStorage.removeItem(KEY)
  localStorage.removeItem('exito.mockdb.v3')
  save(buildSeed())
}

const pad = (n, l = 3) => String(n).padStart(l, '0')
const ymd = (iso) => iso.slice(0, 10).replaceAll('-', '')

export function menuIsOrderable(s, m) {
  if (!m.is_available) return { ok: false, reason: 'Sedang tidak tersedia' }
  return { ok: true, reason: null }
}

function createTicketsIn(s, orderId, at) {
  const stations = new Set(s.details.filter((detail) => detail.order_id === orderId).map((detail) => detail.station))
  for (const station of stations) {
    if (s.tickets.some((ticket) => ticket.order_id === orderId && ticket.station === station)) continue
    s.seq.ticket += 1
    s.tickets.push({ id: s.seq.ticket, order_id: orderId, station, status: 'antre', started_at: null, ready_at: null, handled_by: null, updated_at: at })
  }
}

export function createOrderIn(s, { table, items, method, at = now(), history = false, cashier = null, service_type = 'dine_in', order_source = 'qr_customer', customer_name = null }) {
  let tableId = null
  if (service_type === 'dine_in') {
    const tbl = s.tables.find((t) => String(t.table_number) === String(table) && t.status === 'active')
    if (!tbl) throw new ApiError({ status: 422, code: 'TABLE_INVALID', message: 'Nomor meja tidak ditemukan untuk pesanan Dine In.' })
    tableId = tbl.id
  } else {
    // takeaway: nomor meja tidak dipaksakan
    if (table) {
      const tbl = s.tables.find((t) => String(t.table_number) === String(table))
      tableId = tbl ? tbl.id : null
    }
  }
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
  const order = {
    id: orderId,
    public_token: `tok${Date.now().toString(36)}${orderId}${Math.random().toString(36).slice(2, 8)}`,
    order_number: orderNo,
    display_number: `#${pad(orderId % 1000)}`,
    table_id: tableId,
    service_type,
    order_source,
    customer_name: customer_name || (service_type === 'takeaway' ? 'Pelanggan Takeaway' : null),
    status: 'menunggu_pembayaran',
    ordered_at: at,
    paid_at: null,
    completed_at: null,
    total,
    cancel_reason: null,
  }
  s.orders.push(order)
  if (method === 'tunai') createTicketsIn(s, orderId, at)
  if (history) {
    verifyIn(s, payment.id, cashier, {}, at, false)
    s.tickets.filter((t) => t.order_id === orderId).forEach((t) => { t.status = 'selesai'; t.started_at = at; t.ready_at = new Date(new Date(at).getTime() + (3 + (orderId % 4)) * 60000).toISOString() })
    order.status = 'selesai'; order.completed_at = new Date(new Date(at).getTime() + 6 * 60000).toISOString()
  }
  return { order, payment }
}

export function recordAttendanceIn(s, { userId, type, latitude, longitude, accuracy_meters, device_info, photo_name }) {
  const u = s.users.find((x) => x.id === Number(userId))
  if (!u) throw new ApiError({ status: 404, code: 'NOT_FOUND', message: 'Pengguna tidak ditemukan.' })
  if (!['check_in', 'check_out'].includes(type)) {
    throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Tipe absensi tidak valid.' })
  }
  if (!s.attendances) s.attendances = []
  const today = cafeDate()
  let record = s.attendances.find((row) => row.user_id === u.id && row.attendance_date === today)

  if (type === 'check_in') {
    if (record?.check_in_at) {
      throw new ApiError({ status: 409, code: 'INVALID_STATE_TRANSITION', message: 'Anda sudah melakukan check-in hari ini.' })
    }
    if (!record) {
      s.seq.attendance = (s.seq.attendance || 0) + 1
      record = { id: s.seq.attendance, user_id: u.id, attendance_date: today, status: 'present', check_in_at: null, check_out_at: null, events: [] }
      s.attendances.push(record)
    }
    record.check_in_at = now()
  } else {
    if (!record?.check_in_at) {
      throw new ApiError({ status: 409, code: 'INVALID_STATE_TRANSITION', message: 'Check-in harus dicatat sebelum check-out.' })
    }
    if (record.check_out_at) {
      throw new ApiError({ status: 409, code: 'INVALID_STATE_TRANSITION', message: 'Anda sudah melakukan check-out hari ini.' })
    }
    record.check_out_at = now()
  }

  record.events = record.events || []
  record.events.push({
    id: `${record.id}-${type}`,
    event_type: type,
    recorded_at: type === 'check_in' ? record.check_in_at : record.check_out_at,
    latitude: Number(latitude),
    longitude: Number(longitude),
      accuracy_meters: accuracy_meters == null ? null : Number(accuracy_meters),
    device_info: device_info || null,
    photo_name: photo_name || null,
    photo_url: null,
  })

  return { ...record }
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
  if (p.proof) p.proof.status = 'verified'
  p.transaction_number = `TRX-${ymd(at)}-${pad(p.id)}`
  order.status = 'dibayar'; order.paid_at = at
  createTicketsIn(s, order.id, at)
  return p
}

export function startIn(s, ticketId, userId) {
  const t = s.tickets.find((x) => x.id === ticketId)
  if (!t) throw new ApiError({ status: 404, code: 'NOT_FOUND', message: 'Tiket tidak ditemukan.' })
  if (t.status !== 'antre') throw new ApiError({ status: 409, code: 'INVALID_STATE_TRANSITION', message: 'Tiket tidak bisa dimulai dari status ini.' })
  t.status = 'diproses'; t.started_at = now(); t.handled_by = userId; t.updated_at = now()
  return t
}

export function completeIn(s, ticketId, userId) {
  const t = s.tickets.find((x) => x.id === ticketId)
  if (!t) throw new ApiError({ status: 404, code: 'NOT_FOUND', message: 'Tiket tidak ditemukan.' })
  if (t.status !== 'diproses') throw new ApiError({ status: 409, code: 'INVALID_STATE_TRANSITION', message: 'Tiket belum diproses.' })
  t.status = 'selesai'; t.ready_at = now(); t.handled_by = t.handled_by || userId; t.updated_at = now()
  return t
}

export const permissionsOf = (u) => {
  const normRole = u.role === 'admin' ? 'manager' : u.role
  const permissions = [...new Set([...(PERMISSION_DEFAULTS[normRole] || PERMISSION_DEFAULTS[u.role] || []), ...(u.extra_permissions || [])])]
  return normRole === 'barista_kitchen' ? permissions.filter((p) => !p.startsWith('queue.') || p === `queue.${u.station}`) : permissions
}
export const publicUser = (u) => {
  const normRole = u.role === 'admin' ? 'manager' : u.role
  return { id: u.id, name: u.name, username: u.username, email: u.email, role: label(normRole, LABELS.role), station: u.station || null, is_active: u.is_active, permissions: permissionsOf({ ...u, role: normRole }) }
}
