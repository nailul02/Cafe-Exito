// Kosakata status per layar Figma dipetakan dari satu kode API (pemetaan status: ASUMSI).
export const ORDER_STATUS = {
  menunggu_pembayaran: { label: 'Menunggu Bayar', tone: 'warn' },
  dibayar: { label: 'Sudah Bayar', tone: 'info' },
  diproses: { label: 'Diproses', tone: 'info' },
  selesai: { label: 'Selesai', tone: 'ok' },
  dibatalkan: { label: 'Dibatalkan', tone: 'bad' },
}
export const TICKET_STATUS = {
  antre: { label: 'Menunggu', tone: 'bad' },
  diproses: { label: 'Diproses', tone: 'warn' },
  selesai: { label: 'Selesai', tone: 'ok' },
  dibatalkan: { label: 'Dibatalkan', tone: 'bad' },
}
export const PAYMENT_STATUS = {
  pending: { label: 'Menunggu', tone: 'warn' },
  verified: { label: 'Terverifikasi', tone: 'ok' },
  rejected: { label: 'Ditolak', tone: 'bad' },
  cancelled: { label: 'Dibatalkan', tone: 'bad' },
  refunded: { label: 'Dikembalikan', tone: 'bad' },
}
export const PURCHASE_STATUS = {
  draft: { label: 'Draft', tone: 'neutral' },
  dipesan: { label: 'Dipesan', tone: 'info' },
  diterima_sebagian: { label: 'Diterima Sebagian', tone: 'warn' },
  diterima_lengkap: { label: 'Diterima Lengkap', tone: 'ok' },
  dibatalkan: { label: 'Dibatalkan', tone: 'bad' },
}
export const STOCK_LEVEL = {
  aman: { label: 'Aman', tone: 'ok' },
  menipis: { label: 'Menipis', tone: 'warn' },
  kritis: { label: 'Kritis', tone: 'bad' },
  habis: { label: 'Habis', tone: 'bad' },
}
export const ROLE_LABEL = {
  owner: 'Owner',
  manager: 'Manager',
  admin: 'Manager', // backward-compatibility
  kasir: 'Kasir',
  barista_kitchen: 'Barista & Kitchen', // backward-compatibility
  barista: 'Barista',
  kitchen: 'Kitchen',
}
export const SERVICE_TYPE = {
  dine_in: { label: 'Dine In' },
  takeaway: { label: 'Takeaway' },
}
// Tiga langkah di Figma P-07. Status "menunggu pembayaran" ditangani terpisah di halaman (ASUMSI, tidak ada di Figma).
export const customerSteps = (status) => {
  const idx = { menunggu_pembayaran: -1, dibayar: 0, diproses: 1, selesai: 2, dibatalkan: -2 }[status] ?? -1
  return [
    { label: 'Pesanan Diterima', done: idx >= 0, current: idx === 0 },
    { label: 'Sedang Diproses', done: idx >= 1, current: idx === 1 },
    { label: 'Selesai', done: idx >= 2, current: idx === 2 },
  ]
}
export const HOME_BY_ROLE = {
  owner: '/owner',
  manager: '/manager',
  admin: '/manager', // backward-compatibility
  kasir: '/cashier',
  barista_kitchen: '/production',
  barista: '/production', // backward-compatibility
  kitchen: '/production', // backward-compatibility
}

export const getHomeByRole = (roleCode, station = null) => {
  if (!roleCode) return '/order'
  const normalized = roleCode === 'admin' ? 'manager' : (['barista', 'kitchen'].includes(roleCode) ? 'barista_kitchen' : roleCode)
  if (normalized === 'barista_kitchen') return station === 'kitchen' ? '/production?station=kitchen' : '/production?station=barista'
  return HOME_BY_ROLE[normalized] || HOME_BY_ROLE[roleCode] || '/login'
}
