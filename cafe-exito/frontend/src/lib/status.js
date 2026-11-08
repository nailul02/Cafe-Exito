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
  owner: 'Owner', admin: 'Manager', kasir: 'Kasir', barista: 'Barista', kitchen: 'Kitchen',
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
  owner: '/owner', admin: '/manager', kasir: '/cashier', barista: '/barista', kitchen: '/kitchen',
}
