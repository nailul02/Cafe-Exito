// Navigasi resmi per role Cafe Éxito; absensi MVP hanya mencatat check-in/out dasar.
const baristaKitchenNav = [
  { to: '/production', label: 'Antrean Produksi', end: true },
  { to: '/attendance', label: 'Absensi' },
  { to: '/production/profile', label: 'Profil' },
]

const managerNav = [
  { to: '/manager', label: 'Dashboard', end: true },
  { to: '/manager/production', label: 'Antrean Produksi' },
  { to: '/manager/menus', label: 'Kelola Menu' },
  { to: '/manager/users', label: 'Meja & QR' },
  { to: '/manager/reports', label: 'Kelola Laporan' },
  { to: '/manager/attendance', label: 'Rekap Absensi' },
  { to: '/manager/refund-conflicts', label: 'Konflik Refund' },
  { to: '/attendance', label: 'Absensi Saya' },
  { to: '/manager/profile', label: 'Profil' },
]

export const NAV = {
  kasir: [
    { to: '/cashier', label: 'Dashboard', end: true },
    { to: '/cashier/orders', label: 'Pesanan' },
    { to: '/cashier/refunds', label: 'Refund' },
    { to: '/attendance', label: 'Absensi' },
    { to: '/cashier/profile', label: 'Profil' },
  ],
  barista_kitchen: baristaKitchenNav,
  barista: baristaKitchenNav, // backward-compatibility
  kitchen: baristaKitchenNav, // backward-compatibility
  manager: managerNav,
  admin: managerNav, // backward-compatibility
  owner: [
    { to: '/owner', label: 'Dashboard', end: true },
    { to: '/owner/menus', label: 'Kelola Menu' },
    { to: '/owner/production', label: 'Monitoring Produksi' },
    { to: '/owner/orders/cancellations', label: 'Pembatalan Item' },
    { to: '/owner/refunds', label: 'Persetujuan Refund' },
    { to: '/owner/reports', label: 'Laporan Penjualan' },
    { to: '/owner/attendance', label: 'Laporan Absensi' },
    { to: '/owner/users', label: 'Akun Karyawan' },
    { to: '/owner/profile', label: 'Profil Akun' },
  ],
}
