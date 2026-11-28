// Implementasi MOCK dari kontrak API (bentuk data identik dengan service di src/api/services).
// Dipakai saat VITE_USE_MOCK=true. Menggantinya dengan backend asli cukup dengan VITE_USE_MOCK=false.
import { ApiError } from '../api/ApiError'
import { tokenStorage } from '../api/tokenStorage'
import { GRANTABLE, PERMISSION_DEFAULTS } from './seed'
import { LABELS, label, load, save, wait, menuIsOrderable, createOrderIn, verifyIn, startIn, completeIn, permissionsOf, publicUser, recordAttendanceIn, jakartaDate } from './db'

export { resetMock } from './db'

const SESSION = 'exito.mock.user'
const mockPaymentProofFiles = new Map()
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
const retiredOperationalFeature = (name) => new ApiError({ status: 404, code: 'NOT_FOUND', message: `Fitur ${name} tidak aktif dalam scope operasional.` })
const sameDay = (iso, d = new Date()) => iso != null && jakartaDate(iso) === jakartaDate(d)
const pct = (v, total) => (total ? Math.round((v / total) * 1000) / 10 : 0)

// ---------- Serializer ----------
const userName = (s, id) => s.users.find((u) => u.id === id)?.name ?? null
const tableOf = (s, o) => (o.table_id ? s.tables.find((t) => t.id === o.table_id) : null)
const paymentOut = (s, p) => p && ({ id: p.id, method: label(p.method, { tunai: 'Tunai', qris: 'QRIS' }), status: label(p.status, LABELS.payment), amount: p.amount, amount_received: p.amount_received, change: p.amount_received != null ? p.amount_received - p.amount : null, paid_at: p.paid_at, verified_by: userName(s, p.verified_by), transaction_number: p.transaction_number, proof: p.proof ? { ...p.proof, preview_available: mockPaymentProofFiles.has(p.id) } : null })
const orderOut = (s, o) => {
  const p = s.payments.filter((x) => x.order_id === o.id).slice(-1)[0]
  const tbl = tableOf(s, o)
  return {
    id: o.id, public_token: o.public_token, order_number: o.order_number, display_number: o.display_number,
    table: tbl ? { id: tbl.id, number: tbl.table_number } : null,
    service_type: o.service_type || 'dine_in',
    order_source: o.order_source || 'qr_customer',
    customer_name: o.customer_name || (o.service_type === 'takeaway' ? 'Pelanggan Takeaway' : null),
    status: label(o.status, LABELS.order),
    ordered_at: o.ordered_at, paid_at: o.paid_at, completed_at: o.completed_at, total: o.total,
    items: s.details.filter((d) => d.order_id === o.id).map((d) => ({ id: d.id, menu_id: d.menu_id, name: d.name, qty: d.qty, qty_cancelled: d.qty_cancelled || 0, qty_remaining: d.qty - (d.qty_cancelled || 0), started_at: d.started_at || null, ready_at: d.ready_at || null, cancellations: d.cancellations || [], unit_price: d.unit_price, subtotal: d.subtotal, station: d.station })),
    payment: paymentOut(s, p),
    tickets: s.tickets.filter((t) => t.order_id === o.id).map((t) => ({ id: t.id, station: t.station, status: label(t.status, LABELS.ticket), started_at: t.started_at, ready_at: t.ready_at })),
  }
}
const sessionOut = (s, session) => {
  const table = s.tables.find((row) => row.id === session.table_id)
  const orders = s.orders.filter((order) => order.table_session_id === session.id).sort((a, b) => a.id - b.id).map((order) => {
    const output = orderOut(s, order)
    const payments = s.payments.filter((payment) => payment.order_id === order.id)
    const paid = payments.filter((payment) => ['verified', 'refunded'].includes(payment.status)).reduce((sum, payment) => sum + payment.amount, 0)
    return { public_token: order.public_token, order_number: order.order_number, display_number: order.display_number, status: order.status, total: order.total, paid, balance: Math.max(0, order.total - paid), items: output.items, payments: payments.map((payment) => ({ method: payment.method, status: payment.status, amount: payment.amount, proof_status: payment.proof?.status || null, paid_at: payment.paid_at })) }
  })
  const total = orders.reduce((sum, order) => sum + order.total, 0)
  const paid = orders.reduce((sum, order) => sum + order.paid, 0)
  return { table: { number: table?.table_number }, customer_name: session.customer_name, status: session.status, opened_at: session.opened_at, closed_at: session.closed_at, total, paid, balance: Math.max(0, total - paid), orders }
}

export const ordersApi = {
  async list() { await wait(); need(currentUser(),'orders.view'); const s=load(); return s.orders.slice().sort((a,b)=>b.id-a.id).map(o=>orderOut(s,o)) },
  async cancelItem(orderId,payload) { await wait(); const u=currentUser(); need(u,'orders.cancel'); const s=load(); const o=s.orders.find(x=>x.id===Number(orderId)); const d=s.details.find(x=>x.id===Number(payload.detail_id)&&x.order_id===o?.id); if(!d)throw notFound('Item'); if(d.started_at)throw new ApiError({status:409,code:'ORDER_NOT_CANCELLABLE',message:'Item sudah mulai diproses.'}); const qty=Number(payload.qty); const rem=d.qty-(d.qty_cancelled||0); if(!String(payload.reason||'').trim()||qty<1||qty>rem)throw new ApiError({status:422,code:'VALIDATION_FAILED',message:'Alasan atau kuantitas tidak valid.'}); d.qty_cancelled=(d.qty_cancelled||0)+qty; d.cancellations||=[]; d.cancellations.push({qty,reason:payload.reason,source:'owner_cancellation'}); const p=s.payments.find(x=>x.order_id===o.id); if(p?.status==='pending'){p.amount-=d.unit_price*qty;o.total-=d.unit_price*qty} if(p?.status==='verified'){s.refundRequests||=[];s.refundRequests.push({id:Date.now(),order_id:o.id,payment_id:p.id,status:'requested',amount:d.unit_price*qty,source:'owner_cancellation',reason:payload.reason})} if(s.details.filter(x=>x.order_id===o.id).every(x=>(x.qty_cancelled||0)>=x.qty))o.status='dibatalkan'; save(s); return orderOut(s,o) },
}
const ticketOut = (s, t) => {
  const o = s.orders.find((x) => x.id === t.order_id)
  const tbl = tableOf(s, o)
  return {
    id: t.id, order_id: o.id, order_number: o.order_number, display_number: o.display_number,
    table_number: tbl ? tbl.table_number : null,
    service_type: o.service_type || 'dine_in',
    customer_name: o.customer_name || null,
    station: t.station, status: label(t.status, LABELS.ticket),
    ordered_at: o.ordered_at, started_at: t.started_at, ready_at: t.ready_at,
    handled_by: userName(s, t.handled_by), order_status: label(o.status, LABELS.order),
    items: s.details.filter((d) => d.order_id === o.id && d.station === t.station && d.qty>(d.qty_cancelled||0)).map((d) => ({ order_detail_id:d.id,name: d.name, qty: d.qty-(d.qty_cancelled||0), qty_cancelled:d.qty_cancelled||0, started_at:d.started_at||null, ready_at:d.ready_at||null })),
  }
}
const menuAdmin = (s, m) => {
  const cat = s.categories.find((c) => c.id === m.category_id); const o = menuIsOrderable(s, m)
  return { id: m.id, sku: m.sku, name: m.name, price: m.price, cost_price: m.cost_price, category: { id: cat.id, name: cat.name }, display_group: cat.display_group, station: m.station, is_available: m.is_available, is_orderable: o.ok, unavailable_reason: o.reason, image_url: m.image ? '/coffee.png' : null }
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
  async table(identifier) { await wait(); const t = load().tables.find((x) => x.qr_token === identifier && x.status === 'active' && /^(?:[1-9]|1[0-5])$/.test(String(x.table_number))); if (!t) throw new ApiError({ status: 422, code: 'TABLE_INVALID', message: 'QR meja tidak dikenali atau tidak tersedia.' }); return { number: t.table_number } },
  async openSession(qrToken, customerName) {
    await wait(); const s = load()
    const table = s.tables.find((row) => row.qr_token === qrToken && row.status === 'active' && /^(?:[1-9]|1[0-5])$/.test(String(row.table_number)))
    if (!table) throw new ApiError({ status: 422, code: 'TABLE_INVALID', message: 'QR meja tidak dikenali atau tidak tersedia.' })
    let session = s.tableSessions.find((row) => row.table_id === table.id && row.status === 'active')
    if (!session && !String(customerName || '').trim()) return { requires_name: true, table: { number: table.table_number }, session: null, session_token: null }
    if (!session) {
      s.seq.tableSession = (s.seq.tableSession || 0) + 1
      session = { id: s.seq.tableSession, table_id: table.id, customer_name: String(customerName).trim(), status: 'active', opened_at: new Date().toISOString(), closed_at: null }
      s.tableSessions.push(session)
    }
    const token = `mock-session-${session.id}-${Math.random().toString(36).slice(2)}`
    s.tableSessionTokens.push({ session_id: session.id, token, revoked: false })
    save(s); tokenStorage.setTableSession(token)
    return { requires_name: false, table: { number: table.table_number }, session: sessionOut(s, session), session_token: token }
  },
  async currentSession() { await wait(40); const s = load(); const token = tokenStorage.getTableSession(); const credential = s.tableSessionTokens.find((row) => row.token === token && !row.revoked); const session = s.tableSessions.find((row) => row.id === credential?.session_id && row.status === 'active'); if (!session) throw new ApiError({ status: 401, code: 'TABLE_SESSION_INVALID', message: 'Sesi meja tidak aktif. Silakan scan ulang QR meja.' }); return sessionOut(s, session) },
  async menus() {
    await wait(); const s = load()
    return s.menus.map((m) => { const c = s.categories.find((x) => x.id === m.category_id); const o = menuIsOrderable(s, m); return { id: m.id, name: m.name, price: m.price, category: { id: c.id, name: c.name }, display_group: c.display_group, image_url: m.image ? '/coffee.png' : null, is_available: m.is_available, is_orderable: o.ok, unavailable_reason: o.reason } })
  },
  async validateCart(items) {
    await wait(80); const s = load(); const issues = []; let total = 0
    const lines = items.map(({ menu_id, qty }) => { const m = s.menus.find((x) => x.id === menu_id); const o = m ? menuIsOrderable(s, m) : { ok: false, reason: 'Menu tidak ditemukan' }; if (!o.ok) issues.push({ menu_id, code: 'MENU_UNAVAILABLE', message: `${m?.name ?? 'Menu'} ${o.reason.toLowerCase()}` }); const subtotal = (m?.price ?? 0) * qty; total += subtotal; return { menu_id, name: m?.name, qty, unit_price: m?.price ?? 0, subtotal } })
    return { items: lines, total, issues }
  },
  async createOrder({ payment_method, items, client_request_id }) {
    await wait(); const s = load()
    const token = tokenStorage.getTableSession(); const credential = s.tableSessionTokens.find((row) => row.token === token && !row.revoked); const session = s.tableSessions.find((row) => row.id === credential?.session_id && row.status === 'active')
    if (!session) throw new ApiError({ status: 401, code: 'TABLE_SESSION_INVALID', message: 'Scan ulang QR meja untuk melanjutkan sesi.' })
    const signature = JSON.stringify({ items: items.slice().sort((a, b) => a.menu_id - b.menu_id), payment_method })
    const prior = s.orderRequests.find((row) => row.session_id === session.id && row.id === client_request_id)
    if (prior) { if (prior.signature !== signature) throw new ApiError({ status: 409, code: 'IDEMPOTENCY_KEY_REUSED', message: 'ID checkout sudah digunakan untuk isi keranjang berbeda.' }); const old = s.orders.find((row) => row.id === prior.order_id); return orderOut(s, old) }
    for (const { menu_id } of items) { const m = s.menus.find((x) => x.id === menu_id); const o = m && menuIsOrderable(s, m); if (!o?.ok) throw new ApiError({ status: 409, code: 'MENU_UNAVAILABLE', message: `${m?.name ?? 'Menu'} sedang tidak tersedia.` }) }
    const { order } = createOrderIn(s, { table: s.tables.find((row) => row.id === session.table_id).table_number, items: items.map((i) => [i.menu_id, i.qty]), method: payment_method, customer_name: session.customer_name, table_session_id: session.id, client_request_id })
    if (client_request_id) s.orderRequests.push({ session_id: session.id, id: client_request_id, order_id: order.id, signature })
    save(s); return orderOut(s, order)
  },
  async order(publicToken) { await wait(60); const s = load(); const o = s.orders.find((x) => x.public_token === publicToken); if (!o) throw notFound('Pesanan'); const token = tokenStorage.getTableSession(); const credential = s.tableSessionTokens.find((row) => row.token === token && !row.revoked); if (!credential || credential.session_id !== o.table_session_id) throw new ApiError({ status: 404, code: 'NOT_FOUND', message: 'Pesanan tidak ditemukan.' }); return orderOut(s, o) },
  async cancelOrder(publicToken, payload = {}) {
    await wait(); const s = load(); const o = s.orders.find((x) => x.public_token === publicToken); const credential = s.tableSessionTokens.find((row) => row.token === tokenStorage.getTableSession() && !row.revoked); if (!o || credential?.session_id !== o.table_session_id) throw notFound('Pesanan')
    const d = s.details.find((x) => x.id === Number(payload.detail_id) && x.order_id === o.id); if (!d) throw notFound('Item pesanan')
    if (!String(payload.reason || '').trim()) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Alasan pembatalan wajib diisi.' })
    const qty = Number(payload.qty); const remaining = d.qty - (d.qty_cancelled || 0)
    if (d.started_at) throw new ApiError({ status: 409, code: 'ORDER_NOT_CANCELLABLE', message: 'Item sudah mulai diproses.' })
    if (!Number.isInteger(qty) || qty < 1 || qty > remaining) throw new ApiError({ status: 422, code: 'INVALID_CANCELLATION_QUANTITY', message: 'Kuantitas pembatalan tidak valid.' })
    d.qty_cancelled = (d.qty_cancelled || 0) + qty; d.cancellations ||= []; d.cancellations.push({ qty, reason: payload.reason.trim(), source: 'customer_cancellation', cancelled_at: new Date().toISOString() })
    const p = s.payments.find((x) => x.order_id === o.id)
    if (p?.status === 'pending') { p.amount -= d.unit_price * qty; o.total -= d.unit_price * qty }
    if (p?.status === 'verified') { s.refundRequests ||= []; s.refundRequests.push({ id: Date.now(), order_id: o.id, payment_id: p.id, status: 'requested', source: 'customer_cancellation', amount: d.unit_price * qty, reason: payload.reason.trim() }) }
    if (s.details.filter((x) => x.order_id === o.id).every((x) => x.qty_cancelled >= x.qty)) { o.status = 'dibatalkan'; o.cancel_reason = payload.reason.trim(); if (p?.status === 'pending') p.status = 'cancelled'; s.tickets.filter((t) => t.order_id === o.id && t.status === 'antre').forEach((t) => { t.status = 'dibatalkan' }) }
    save(s); return orderOut(s, o)
  },
  async uploadPaymentProof(publicToken, file) {
    await wait(); const s = load(); const o = s.orders.find((x) => x.public_token === publicToken); const credential = s.tableSessionTokens.find((row) => row.token === tokenStorage.getTableSession() && !row.revoked); if (!o || credential?.session_id !== o.table_session_id) throw notFound('Pesanan')
    if (o.order_source !== 'qr_customer' || o.status !== 'menunggu_pembayaran') throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Bukti hanya dapat diunggah untuk QR Self-Order yang menunggu pembayaran.' })
    if (!file || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 3 * 1024 * 1024) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Pilih gambar JPEG, PNG, atau WEBP maksimal 3 MB.' })
    const payments = s.payments.filter((x) => x.order_id === o.id); let p = payments.at(-1)
    if (p?.status === 'rejected' && p.method === 'qris') {
      s.seq.payment += 1
      p = { id: s.seq.payment, order_id: o.id, method: 'qris', status: 'pending', amount: p.amount, amount_received: null, paid_at: null, verified_by: null, transaction_number: null, reject_reason: null, proof: null }
      s.payments.push(p)
    }
    if (!p || p.method !== 'qris' || p.status !== 'pending' || p.proof?.status === 'pending') throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Pembayaran belum siap menerima bukti baru.' })
    p.proof = { status: 'pending', reject_reason: null, uploaded_at: new Date().toISOString(), preview_available: false }
    mockPaymentProofFiles.set(p.id, file)
    p.proof.preview_available = true
    save(s); return { status: p.proof.status, uploaded_at: p.proof.uploaded_at, mime_type: file.type, size_bytes: file.size }
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
  async paymentProof(paymentId) { await wait(); need(currentUser(), 'payments.verify'); const file = mockPaymentProofFiles.get(Number(paymentId)); if (!file) throw notFound('Bukti pembayaran'); return file },
  async verify(paymentId, payload = {}) { await wait(); const u = currentUser(); need(u, 'payments.verify'); const s = load(); const p = verifyIn(s, Number(paymentId), u.id, payload); save(s); return orderOut(s, s.orders.find((o) => o.id === p.order_id)) },
  async reject(paymentId, payload = {}) {
    await wait(); need(currentUser(), 'payments.verify'); const s = load(); const p = s.payments.find((x) => x.id === Number(paymentId)); if (!p) throw notFound('Pembayaran')
    if (p.status !== 'pending') throw new ApiError({ status: 409, code: 'PAYMENT_ALREADY_PROCESSED', message: 'Pembayaran sudah diproses.' })
    if (!String(payload.reason || '').trim()) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Alasan penolakan wajib diisi.' })
    const o = s.orders.find((x) => x.id === p.order_id)
    if (p.method === 'tunai') throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Pembayaran tunai tetap menunggu pelunasan.' })
    if (o.order_source === 'qr_customer') {
      if (p.method !== 'qris' || !p.proof || p.proof.status !== 'pending') throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Tidak ada bukti pelanggan yang menunggu verifikasi.' })
      p.proof = { ...p.proof, status: 'rejected', reject_reason: payload.reason.trim() }
    }
    p.status = 'rejected'; p.reject_reason = payload.reason.trim()
    if (o.order_source !== 'qr_customer') { o.status = 'dibatalkan'; o.cancel_reason = p.reject_reason }
    save(s); return orderOut(s, o)
  },
  async receipt(orderId) {
    await wait(); const s = load(); const o = s.orders.find((x) => x.id === Number(orderId)); if (!o) throw notFound('Pesanan'); const p = s.payments.filter((x) => x.order_id === o.id).slice(-1)[0]
    return { cafe: { name: s.settings.cafe_name, address: s.settings.cafe_address, footer: s.settings.receipt_footer }, transaction_number: p.transaction_number, order: orderOut(s, o), cashier: userName(s, p.verified_by) }
  },
  async createPosOrder(payload) {
    await wait(); const u = currentUser(); need(u, 'orders.create_pos'); const s = load()
    const { service_type = 'dine_in', table_id, customer_name, payment_method = 'tunai', amount_received, items } = payload
    if (amount_received != null) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Uang diterima dicatat setelah pesanan dibuat.' })
    if (!items || !items.length) {
      throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Item pesanan tidak boleh kosong.' })
    }
    const selectedTable = service_type === 'dine_in' ? s.tables.find((t) => t.id === Number(table_id) && t.status === 'active' && Number(t.table_number) >= 1 && Number(t.table_number) <= 15) : null
    if (service_type === 'dine_in' && !selectedTable) throw new ApiError({ status: 422, code: 'TABLE_INVALID', message: 'Pilih meja aktif dari daftar Meja 1–15.' })
    if (service_type === 'takeaway' && table_id != null) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Pesanan takeaway tidak boleh memiliki meja.' })
    const { order, payment } = createOrderIn(s, {
      table: selectedTable?.table_number ?? null,
      service_type,
      order_source: 'pos_cashier',
      customer_name: customer_name || (service_type === 'takeaway' ? 'Pelanggan Takeaway' : `Meja ${selectedTable.table_number}`),
      items: items.map((i) => [i.menu_id, i.qty]),
      method: payment_method,
      cashier: u.id,
    })
    save(s)
    return orderOut(s, s.orders.find((o) => o.id === order.id))
  },
  async tables() {
    await wait(40)
    return load().tables.filter((t) => t.status === 'active' && /^(?:[1-9]|1[0-5])$/.test(String(t.table_number))).sort((a, b) => Number(a.table_number) - Number(b.table_number))
  },
  async sessions() { await wait(40); need(currentUser(), 'payments.verify'); const s = load(); return s.tableSessions.filter((session) => session.status === 'active').map((session) => ({ id: session.id, ...sessionOut(s, session) })) },
  async closeSession(id) {
    await wait(); need(currentUser(), 'payments.verify'); const s = load(); const session = s.tableSessions.find((row) => row.id === Number(id) && row.status === 'active'); if (!session) throw notFound('Sesi meja')
    const snapshot = sessionOut(s, session)
    if (snapshot.orders.some((order) => !['selesai', 'dibatalkan'].includes(order.status))) throw new ApiError({ status: 409, code: 'SESSION_ORDERS_INCOMPLETE', message: 'Semua pesanan harus selesai sebelum sesi ditutup.' })
    if (snapshot.orders.some((order) => order.payments.some((payment) => payment.status === 'pending'))) throw new ApiError({ status: 409, code: 'SESSION_PAYMENTS_PENDING', message: 'Selesaikan verifikasi pembayaran sebelum sesi ditutup.' })
    if (snapshot.balance > 0) throw new ApiError({ status: 409, code: 'SESSION_BALANCE_DUE', message: 'Sisa tagihan sesi harus lunas.' })
    session.status = 'closed'; session.closed_at = new Date().toISOString(); for (const credential of s.tableSessionTokens.filter((row) => row.session_id === session.id)) credential.revoked = true
    save(s); return { id: session.id, ...sessionOut(s, session) }
  },
}

export const refundsApi = {
  async list(){await wait();const u=currentUser();if(!can(u,'refund.view')&&!can(u,'refund.review'))throw forbid();const s=load();return s.refundRequests||[]},
  async request(payload){await wait();const u=currentUser();need(u,'refund.request');const s=load();const p=s.payments.find(x=>x.id===Number(payload.payment_id));if(!p||p.status!=='verified')throw new ApiError({status:409,code:'INVALID_REFUND_STATE',message:'Pembayaran harus verified.'});s.refundRequests||=[];const r={id:Date.now(),payment_id:p.id,order_id:p.order_id,amount:Number(payload.amount),reason:payload.reason,status:'requested',source:'manual',requested_by:u.id};s.refundRequests.push(r);save(s);return r},
  async decide(id,decision,reason){await wait();const u=currentUser();need(u,'refund.review');const s=load();const r=(s.refundRequests||[]).find(x=>x.id===Number(id));if(!r)throw notFound('Refund');if(r.status!=='requested')throw new ApiError({status:409,code:'INVALID_REFUND_STATE',message:'Pengajuan bukan menunggu keputusan.'});r.status=decision;r.decided_at=new Date().toISOString();r.decision_reason=reason;save(s);return r},
  async withdraw(id,reason){await wait();const u=currentUser();need(u,'refund.view');const s=load();const r=(s.refundRequests||[]).find(x=>x.id===Number(id));if(!r||r.status!=='requested')throw new ApiError({status:409,code:'INVALID_REFUND_STATE',message:'Tidak dapat ditarik.'});r.status='withdrawn';r.withdraw_reason=reason;save(s);return r},
  async revoke(id,reason){await wait();const u=currentUser();need(u,'refund.review');const s=load();const r=(s.refundRequests||[]).find(x=>x.id===Number(id));if(!r||r.status!=='approved')throw new ApiError({status:409,code:'INVALID_REFUND_STATE',message:'Tidak dapat dicabut.'});r.status='approval_revoked';r.approval_revoke_reason=reason;save(s);return r},
  async execute(id,payload){await wait();const u=currentUser();need(u,'refund.execute');const s=load();const r=(s.refundRequests||[]).find(x=>x.id===Number(id));if(!r||r.status!=='approved')throw new ApiError({status:409,code:'INVALID_REFUND_STATE',message:'Belum disetujui.'});r.status='completed';r.completed_at=new Date().toISOString();r.executed_by=u.id;r.method=payload.get('method');save(s);return r},
  async notifications(){await wait();return []}, async readNotification(){return null},
  async conflictNote(id,data){await wait();const u=currentUser();need(u,'refund.conflict.document');const s=load();const r=(s.refundRequests||[]).find(x=>x.id===Number(id));if(!r||r.status!=='conflict_of_interest')throw new ApiError({status:409,code:'INVALID_REFUND_STATE',message:'Bukan konflik kepentingan.'});r.conflict_note=data;r.verified=u.role==='owner';save(s);return r},
  async verifyConflict(id){await wait();need(currentUser(),'refund.review');return {id:Number(id),verified:true}},
}

// ---------- Stasiun (Barista / Kitchen) ----------
const guardStation = (u, station) => {
  if (station !== 'all' && !['barista', 'kitchen'].includes(station)) throw notFound('Stasiun')
  if (!can(u, `queue.${station}`) && !can(u, 'queue.view_all') && !can(u, 'queue.barista') && !can(u, 'queue.kitchen')) throw notFound('Tiket')
}
const canAct = (u, station) => can(u, `queue.${station}`) || can(u, 'queue.barista') || can(u, 'queue.kitchen')
export const stationApi = {
  async dashboard(station) { await wait(); const u = currentUser(); guardStation(u, station); const s = load(); const t = s.tickets.filter((x) => x.station === station); return { queued: t.filter((x) => x.status === 'antre').length, in_progress: t.filter((x) => x.status === 'diproses').length, completed_today: t.filter((x) => x.status === 'selesai' && sameDay(x.ready_at || s.orders.find((o) => o.id === x.order_id).ordered_at)).length } },
  async tickets(station, params = {}) {
    await wait(); const u = currentUser(); guardStation(u, station); const s = load(); const st = params['filter[status]']
    return s.tickets.filter((t) => (station === 'all' || t.station === station) && (!st || st === 'semua' || t.status === st) && sameDay(s.orders.find((o) => o.id === t.order_id).ordered_at)).sort((a, b) => new Date(s.orders.find((o) => o.id === a.order_id).ordered_at) - new Date(s.orders.find((o) => o.id === b.order_id).ordered_at)).map((t) => ticketOut(s, t))
  },
  async combinedTickets(params = {}) {
    await wait(); const u = currentUser(); const s = load()
    const st = params['filter[status]']
    const stn = params.station
    return s.tickets
      .filter((t) => (!stn || stn === 'all' || t.station === stn) &&
                     (!st || st === 'semua' || t.status === st) &&
                     sameDay(s.orders.find((o) => o.id === t.order_id).ordered_at))
      .sort((a, b) => new Date(s.orders.find((o) => o.id === a.order_id).ordered_at) - new Date(s.orders.find((o) => o.id === b.order_id).ordered_at))
      .map((t) => ticketOut(s, t))
  },
  async combinedDashboard() {
    await wait(); const s = load()
    const drinkTickets = s.tickets.filter((x) => x.station === 'barista')
    const foodTickets = s.tickets.filter((x) => x.station === 'kitchen')
    return {
      drink: {
        queued: drinkTickets.filter((x) => x.status === 'antre').length,
        in_progress: drinkTickets.filter((x) => x.status === 'diproses').length,
        completed_today: drinkTickets.filter((x) => x.status === 'selesai' && sameDay(x.ready_at || s.orders.find((o) => o.id === x.order_id).ordered_at)).length,
      },
      food: {
        queued: foodTickets.filter((x) => x.status === 'antre').length,
        in_progress: foodTickets.filter((x) => x.status === 'diproses').length,
        completed_today: foodTickets.filter((x) => x.status === 'selesai' && sameDay(x.ready_at || s.orders.find((o) => o.id === x.order_id).ordered_at)).length,
      },
      total_queued: s.tickets.filter((x) => x.status === 'antre').length,
      total_in_progress: s.tickets.filter((x) => x.status === 'diproses').length,
      total_completed: s.tickets.filter((x) => x.status === 'selesai' && sameDay(x.ready_at || s.orders.find((o) => o.id === x.order_id).ordered_at)).length,
    }
  },
  async ticket(station, id) { await wait(60); const u = currentUser(); guardStation(u, station); const s = load(); const t = s.tickets.find((x) => x.id === Number(id) && (station === 'all' || x.station === station)); if (!t) throw notFound('Tiket'); return ticketOut(s, t) },
  async start(station, id) { await wait(); const u = currentUser(); const s = load(); const t = s.tickets.find((x) => x.id === Number(id)); if (!t) throw notFound('Tiket'); if (!canAct(u, t.station)) throw forbid(); startIn(s, t.id, u.id); save(s); return ticketOut(s, t) },
  async startItem(station, ticketId, detailId) { await wait(); const u = currentUser(); const s = load(); const t = s.tickets.find((x) => x.id === Number(ticketId)); const d = s.details.find((x) => x.id === Number(detailId)); if (!t || !d || d.order_id !== t.order_id || d.station !== t.station) throw notFound('Item tiket'); if (!canAct(u,t.station)) throw forbid(); d.started_at = new Date().toISOString(); if (t.status === 'antre') { t.status = 'diproses'; t.started_at = d.started_at; t.handled_by = u.id } const o=s.orders.find(x=>x.id===t.order_id); if(o.status==='dibayar')o.status='diproses'; save(s); return ticketOut(s,t) },
  async complete(station, id) { await wait(); const u = currentUser(); const s = load(); const t = s.tickets.find((x) => x.id === Number(id)); if (!t) throw notFound('Tiket'); if (!canAct(u, t.station)) throw forbid(); completeIn(s, t.id, u.id); save(s); return ticketOut(s, t) },
}

// ---------- Menu ----------
export const menusApi = {
  async list(params = {}) { await wait(); const u = currentUser(); need(u, 'menus.view'); const s = load(); const q = (params.search || '').toLowerCase(); return s.menus.filter((m) => { const c=s.categories.find((x)=>x.id===m.category_id); const stationRole=u.role==='barista_kitchen'||u.role==='barista'||u.role==='kitchen'; return (!stationRole||c.station===u.station) && (!q || m.name.toLowerCase().includes(q) || m.sku.toLowerCase().includes(q)) }).map((m) => menuAdmin(s, m)) },
  async show(id) { await wait(60); const s = load(); const m = s.menus.find((x) => x.id === Number(id)); if (!m) throw notFound('Menu'); return menuAdmin(s, m) },
  async create(p) { await wait(); need(currentUser(), 'menus.manage'); const s = load(); const cat = s.categories.find((c) => c.id === Number(p.category_id)); s.seq.menu += 1; const m = { id: s.seq.menu, sku: p.sku || `NEW-${s.seq.menu}`, name: p.name, price: Number(p.price), category_id: cat.id, is_available: true, image: false, station: p.station || cat.station, cost_price: p.cost_price ?? null }; s.menus.push(m); save(s); return menuAdmin(s, m) },
  async update(id, p) { await wait(); need(currentUser(), 'menus.manage'); const s = load(); const m = s.menus.find((x) => x.id === Number(id)); if (!m) throw notFound('Menu'); Object.assign(m, { name: p.name ?? m.name, sku: p.sku ?? m.sku, price: p.price != null ? Number(p.price) : m.price, category_id: p.category_id ? Number(p.category_id) : m.category_id, station: p.station ?? m.station, cost_price: p.cost_price ?? m.cost_price }); save(s); return menuAdmin(s, m) },
  async remove(id) { await wait(); need(currentUser(), 'menus.manage'); const s = load(); s.menus = s.menus.filter((x) => x.id !== Number(id)); save(s); return null },
  async setAvailability(id, v) { await wait(80); const u=currentUser(); need(u, 'menus.availability'); const s = load(); const m = s.menus.find((x) => x.id === Number(id)); if (!m) throw notFound('Menu'); const c=s.categories.find((x)=>x.id===m.category_id); if (u.role!=='owner' && (!['barista_kitchen','barista','kitchen'].includes(u.role)||u.station!==c.station)) throw forbid(); m.is_available = !!v; save(s); return menuAdmin(s, m) },
  async recipe() { throw retiredOperationalFeature('resep') },
  async saveRecipe() { throw retiredOperationalFeature('resep') },
  async categories() { await wait(60); return load().categories },
}

// ---------- Inventori ----------
export const inventoryApi = {
  async ingredients() { await wait(); throw retiredOperationalFeature('inventori') },
  async createIngredient() { await wait(); throw retiredOperationalFeature('inventori') },
  async updateIngredient() { await wait(); throw retiredOperationalFeature('inventori') },
  async removeIngredient() { await wait(); throw retiredOperationalFeature('inventori') },
  async summary() { await wait(); throw retiredOperationalFeature('inventori') },
  async movements() { await wait(); throw retiredOperationalFeature('inventori') },
  async adjust() { await wait(); throw retiredOperationalFeature('inventori') },
}

// ---------- Pembelian ----------
export const purchasingApi = {
  async suppliers() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async createSupplier() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async updateSupplier() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async removeSupplier() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async purchases() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async purchase() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async createPurchase() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async updatePurchase() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async placePurchase() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async cancelPurchase() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async createReceipt() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
  async confirmReceipt() { await wait(); throw retiredOperationalFeature('supplier dan pembelian') },
}

// ---------- Pengguna & meja ----------
export const usersApi = {
  async list() { await wait(); need(currentUser(), 'users.manage'); return load().users.map(publicUser) },
  async create(p) {
    await wait(); need(currentUser(), 'users.manage'); const s = load()
    const username = String(p.username || '').trim().toLowerCase(); const email = String(p.email || '').trim().toLowerCase()
    const errors = {}
    if (!['manager', 'kasir', 'barista_kitchen'].includes(p.role)) errors.role = ['Role tidak diizinkan.']
    if (s.users.some((u) => u.username.toLowerCase() === username)) errors.username = ['Username sudah dipakai.']
    if (s.users.some((u) => u.email.toLowerCase() === email)) errors.email = ['Email sudah dipakai.']
    if ((p.role === 'barista_kitchen' && !['barista', 'kitchen'].includes(p.station)) || (p.role !== 'barista_kitchen' && p.station)) errors.station = ['Station tidak sesuai dengan role.']
    if (String(p.password || '').length < 8) errors.password = ['Kata sandi minimal 8 karakter.']
    if (Object.keys(errors).length) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Data akun tidak valid.', errors })
    s.seq.user += 1
    const u = { id: s.seq.user, name: p.name, username, email, role: p.role, station: p.station || null, is_active: true, password: p.password, extra_permissions: [] }
    s.users.push(u); save(s); return publicUser(u)
  },
  async update(id, p) {
    await wait(); need(currentUser(), 'users.manage'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); if (!u || u.role === 'owner') throw forbid()
    const username = String(p.username ?? u.username).trim().toLowerCase(); const email = String(p.email ?? u.email).trim().toLowerCase(); const role = p.role ?? u.role; const station = p.station ?? (role === u.role ? u.station : null)
    const errors = {}
    if (s.users.some((x) => x.id !== u.id && x.username.toLowerCase() === username)) errors.username = ['Username sudah dipakai.']
    if (s.users.some((x) => x.id !== u.id && x.email.toLowerCase() === email)) errors.email = ['Email sudah dipakai.']
    if (!['manager', 'kasir', 'barista_kitchen'].includes(role)) errors.role = ['Role tidak diizinkan.']
    if ((role === 'barista_kitchen' && !['barista', 'kitchen'].includes(station)) || (role !== 'barista_kitchen' && station)) errors.station = ['Station tidak sesuai dengan role.']
    if (Object.keys(errors).length) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Data akun tidak valid.', errors })
    Object.assign(u, { name: p.name ?? u.name, username, email, role, station: station || null }); save(s); return publicUser(u)
  },
  async setStatus(id, v) { await wait(80); need(currentUser(), 'users.manage'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); if (!u || u.role === 'owner') throw forbid(); u.is_active = !!v; save(s); return publicUser(u) },
  async resetPassword(id, password) { await wait(); need(currentUser(), 'users.manage'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); if (!u || u.role === 'owner') throw forbid(); if (String(password).length < 8) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Kata sandi minimal 8 karakter.' }); u.password = password; save(s); return null },
  async permissions(id) { await wait(60); need(currentUser(), 'permissions.grant'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); return { defaults: PERMISSION_DEFAULTS[u.role], granted: u.extra_permissions, grantable: GRANTABLE } },
  async savePermissions(id, perms) { await wait(); need(currentUser(), 'permissions.grant'); const s = load(); const u = s.users.find((x) => x.id === Number(id)); if (perms.some((p) => !GRANTABLE.includes(p))) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Ada izin yang tidak dapat diberikan.' }); u.extra_permissions = perms; save(s); return publicUser(u) },
  async tables() { await wait(60); need(currentUser(), 'tables.manage'); return load().tables.filter((t) => /^(?:[1-9]|1[0-5])$/.test(String(t.table_number))).sort((a, b) => Number(a.table_number) - Number(b.table_number)).map((t) => ({ ...t, qr_url: `${window.location.origin}/order?t=${t.qr_token}` })) },
  async createTable(p) { await wait(); need(currentUser(), 'tables.manage'); const n = String(p.table_number); const s = load(); if (!/^(?:[1-9]|1[0-5])$/.test(n) || s.tables.some((t) => t.table_number === n)) throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Nomor meja harus unik dan berada pada rentang 1–15.' }); s.seq.table = Math.max(s.seq.table, ...s.tables.map((t) => Number(t.id))); const t = { id: ++s.seq.table, table_number: n, qr_token: `demo-table-${Math.random().toString(36).slice(2, 8)}`, status: 'active' }; s.tables.push(t); save(s); return { ...t, qr_url: `${window.location.origin}/order?t=${t.qr_token}` } },
  async regenerateQr(id) { await wait(); need(currentUser(), 'tables.manage'); const s = load(); const t = s.tables.find((x) => x.id === Number(id)); t.qr_token = `demo-table-${Math.random().toString(36).slice(2, 8)}`; save(s); return { ...t, qr_url: `${window.location.origin}/order?t=${t.qr_token}` } },
}

// ---------- Laporan & dashboard ----------
const periodRange = (period = 'daily', from, to) => {
  const today = jakartaDate(new Date())
  const daysBack = period === 'weekly' ? 6 : period === 'monthly' ? 29 : 0
  const startDate = from || (() => { const date = new Date(`${today}T12:00:00+07:00`); date.setUTCDate(date.getUTCDate() - daysBack); return jakartaDate(date) })()
  const endDate = to || today
  return [new Date(`${startDate}T00:00:00+07:00`), new Date(`${endDate}T23:59:59.999+07:00`)]
}
function salesReport(s, params = {}) {
  const [start, end] = periodRange(params.period, params.from, params.to)
  const orders = paidOrders(s).filter((o) => { const t = new Date(o.paid_at); return t >= start && t <= end })
  const revenue = orders.reduce((a, o) => a + o.total, 0)
  const hourly = (params.granularity || (params.period === 'daily' || !params.period ? 'hour' : 'day')) === 'hour'
  const buckets = {}
  orders.forEach((o) => { const d = new Date(o.paid_at); const k = hourly ? `${new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jakarta', hour: '2-digit', hourCycle: 'h23' }).format(d)}:00` : d.toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short' }); buckets[k] = buckets[k] || { label: k, amount: 0, orders: 0 }; buckets[k].amount += o.total; buckets[k].orders += 1 })
  const series = hourly ? Array.from({ length: 15 }, (_, i) => { const k = String(i + 8).padStart(2, '0') + ':00'; return buckets[k] || { label: k, amount: 0, orders: 0 } }) : Object.values(buckets)
  const lines = s.details.filter((d) => orders.some((o) => o.id === d.order_id))
  const byMenu = {}; const byCat = {}
  lines.forEach((d) => { byMenu[d.name] = byMenu[d.name] || { name: d.name, qty: 0, amount: 0 }; byMenu[d.name].qty += d.qty; byMenu[d.name].amount += d.subtotal; const m = s.menus.find((x) => x.id === d.menu_id); const c = s.categories.find((x) => x.id === m?.category_id)?.name || 'Lainnya'; byCat[c] = byCat[c] || { category: c, qty: 0, amount: 0 }; byCat[c].qty += d.qty; byCat[c].amount += d.subtotal })
  const pays = s.payments.filter((p) => p.status === 'verified' && orders.some((o) => o.id === p.order_id)); const byMethod = ['qris', 'tunai'].map((m) => { const x = pays.filter((p) => p.method === m); const amount = x.reduce((a, p) => a + p.amount, 0); return { method: m, orders: x.length, amount, percent: pct(amount, revenue) } })
  const cashiers = {}; pays.forEach((p) => { const n = userName(s, p.verified_by) || '-'; cashiers[n] = cashiers[n] || { cashier: n, orders: 0, amount: 0 }; cashiers[n].orders += 1; cashiers[n].amount += p.amount })
  const done = s.tickets.filter((t) => t.ready_at && orders.some((o) => o.id === t.order_id)); const avg = done.length ? Math.round(done.reduce((a, t) => a + (new Date(t.ready_at) - new Date(s.orders.find((o) => o.id === t.order_id).ordered_at)) / 1000, 0) / done.length) : null
  const inRange = (value) => value && new Date(value) >= start && new Date(value) <= end
  const refunds = s.refundRequests || []
  const refundsApproved = refunds.filter((r) => ['approved', 'completed'].includes(r.status) && inRange(r.decided_at)).reduce((sum, r) => sum + Number(r.amount || 0), 0)
  const refundsCompleted = refunds.filter((r) => r.status === 'completed' && inRange(r.completed_at)).reduce((sum, r) => sum + Number(r.amount || 0), 0)
  return { period: params.period || 'daily', from: start.toISOString(), to: end.toISOString(), totals: { orders: orders.length, revenue, average_order: orders.length ? Math.round(revenue / orders.length) : 0, items: lines.reduce((a, d) => a + d.qty, 0), refunds_approved: refundsApproved, refunds_completed: refundsCompleted }, by_method: byMethod, by_category: Object.values(byCat).sort((a, b) => b.amount - a.amount).map((c) => ({ ...c, percent: pct(c.amount, revenue) })), series, top_menus: Object.values(byMenu).sort((a, b) => b.qty - a.qty).slice(0, 5), cashier_recap: Object.values(cashiers), avg_service_seconds: avg }
}
export const reportsApi = {
  async sales(params) { await wait(); need(currentUser(), 'reports.view'); return salesReport(load(), params) },
  async ownerDashboard() {
    await wait(); need(currentUser(), 'dashboard.owner'); const s = load(); const today = salesReport(s, { period: 'daily' })
    const y = new Date(`${jakartaDate(new Date())}T12:00:00+07:00`); y.setUTCDate(y.getUTCDate() - 1); const yesterday = jakartaDate(y); const yest = salesReport(s, { period: 'daily', from: yesterday, to: yesterday })
    return { today: { revenue: today.totals.revenue, orders: today.totals.orders, average_order: today.totals.average_order, vs_yesterday_percent: yest.totals.revenue ? Math.round(((today.totals.revenue - yest.totals.revenue) / yest.totals.revenue) * 1000) / 10 : null }, series: today.series, top_menus: today.top_menus, by_method: today.by_method }
  },
  async managerDashboard() {
    await wait(); need(currentUser(), 'dashboard.manager'); const s = load(); const today = salesReport(s, { period: 'daily' }); const active = s.orders.filter((o) => sameDay(o.ordered_at) && ['dibayar', 'diproses'].includes(o.status))
    return { revenue_today: today.totals.revenue, active_orders: { total: active.length, queued: s.tickets.filter((t) => t.status === 'antre').length, in_progress: s.tickets.filter((t) => t.status === 'diproses').length }, orders: s.orders.filter((o) => sameDay(o.ordered_at)).sort((a, b) => b.id - a.id).slice(0, 8).map((o) => orderOut(s, o)), unavailable_menus: s.menus.filter((m) => !menuIsOrderable(s, m).ok).map((m) => ({ id: m.id, name: m.name, reason: menuIsOrderable(s, m).reason })) }
  },
  async stockUsage() { throw retiredOperationalFeature('laporan stok') },
  async transactions(params = {}) { await wait(); need(currentUser(), 'transactions.view'); const s = load(); const q = (params.search || '').toLowerCase(); return s.payments.filter((p) => p.status === 'verified' && (!params.method || p.method === params.method)).map((p) => ({ order: orderOut(s, s.orders.find((o) => o.id === p.order_id)), payment: paymentOut(s, p) })).filter((x) => !q || x.payment.transaction_number.toLowerCase().includes(q) || x.order.items.some((i) => i.name.toLowerCase().includes(q))).sort((a, b) => new Date(b.payment.paid_at) - new Date(a.payment.paid_at)).slice(0, 25) },
  async orders(params = {}) { await wait(); need(currentUser(), 'orders.view'); const s = load(); const st = params['filter[status]']; return s.orders.filter((o) => sameDay(o.ordered_at) && (!st || o.status === st)).sort((a, b) => b.id - a.id).map((o) => orderOut(s, o)) },
  async auditLogs() { await wait(); return load().audit },
  async alerts() { await wait(60); const s = load(); return { awaiting_payment: s.orders.filter((o) => o.status === 'menunggu_pembayaran').length, new_tickets: s.tickets.filter((t) => t.status === 'antre').length } },
}

const mockAttendancePayload = (payload, userId, type) => {
  const get = (key) => payload?.get?.(key)
  const photo = get('photo')
  const rawLatitude = get('latitude')
  const rawLongitude = get('longitude')
  const rawAccuracy = get('accuracy_meters')
  const latitude = Number(rawLatitude)
  const longitude = Number(rawLongitude)
  const accuracy = rawAccuracy == null || rawAccuracy === '' ? null : Number(rawAccuracy)
  if (!photo || rawLatitude == null || rawLatitude === '' || rawLongitude == null || rawLongitude === '' || !Number.isFinite(latitude) || latitude < -90 || latitude > 90 || !Number.isFinite(longitude) || longitude < -180 || longitude > 180 || !Number.isFinite(accuracy) || accuracy < 0 || accuracy > 100000) {
    throw new ApiError({ status: 422, code: 'VALIDATION_FAILED', message: 'Foto, koordinat GPS, dan akurasi lokasi yang valid wajib diisi.' })
  }
  return { userId, type, latitude, longitude, accuracy_meters: accuracy, device_info: get('device_info'), photo_name: photo.name }
}

// ---------- Absensi demo: metadata foto/GPS mengikuti kontrak API. ----------
export const attendanceApi = {
  async todayStatus() {
    await wait(60); const u = currentUser(); const s = load()
    const today = jakartaDate(new Date())
    const record = (s.attendances || []).find((a) => a.user_id === u.id && a.attendance_date === today)
    return {
      attendance_date: today,
      checked_in: Boolean(record?.check_in_at),
      checked_out: Boolean(record?.check_out_at),
      check_in_at: record?.check_in_at || null,
      check_out_at: record?.check_out_at || null,
    }
  },
  async checkIn(payload) {
    await wait(); const u = currentUser(); need(u, 'attendance.record'); const s = load()
    const record = recordAttendanceIn(s, mockAttendancePayload(payload, u.id, 'check_in'))
    save(s)
    return record
  },
  async checkOut(payload) {
    await wait(); const u = currentUser(); need(u, 'attendance.record'); const s = load()
    const record = recordAttendanceIn(s, mockAttendancePayload(payload, u.id, 'check_out'))
    save(s)
    return record
  },
  async myHistory() {
    await wait(60); const u = currentUser(); const s = load()
    return (s.attendances || [])
      .filter((a) => a.user_id === u.id)
      .sort((a, b) => b.attendance_date.localeCompare(a.attendance_date))
      .slice(0, 30)
  },
  async list(params = {}) {
    await wait(); const u = currentUser(); need(u, 'attendance.view_all'); const s = load()
    return (s.attendances || [])
      .filter((a) => {
        if (params.from && a.attendance_date < params.from) return false
        if (params.to && a.attendance_date > params.to) return false
        return true
      })
      .map((att) => {
        const user = s.users.find((x) => x.id === att.user_id)
        return {
          ...att,
          user: user ? {
            id: user.id,
            name: user.name,
            username: user.username,
            role: { code: user.role, label: LABELS.role[user.role] || user.role },
          } : null,
        }
      })
      .sort((a, b) => b.attendance_date.localeCompare(a.attendance_date))
      .slice(0, Number(params.per_page || 30))
  },
}
