// Navigasi per role. Jadwal shift/absensi sengaja tidak ada: keputusan K-B1 masih PENDING (di luar MVP).
export const NAV = {
  kasir: [
    { to: '/cashier', label: 'Dashboard', end: true },
    { to: '/cashier/orders', label: 'Pesanan' },
    { to: '/cashier/profile', label: 'Profil' },
  ],
  barista: [
    { to: '/barista', label: 'Dashboard', end: true },
    { to: '/barista/queue', label: 'Pesanan' },
    { to: '/barista/profile', label: 'Profil' },
  ],
  kitchen: [
    { to: '/kitchen', label: 'Dashboard', end: true },
    { to: '/kitchen/queue', label: 'Pesanan' },
    { to: '/kitchen/profile', label: 'Profil' },
  ],
  admin: [
    { to: '/manager', label: 'Dashboard', end: true },
    { to: '/manager/menus', label: 'Kelola Menu' },
    { to: '/manager/inventory', label: 'Kelola Stok' },
    { to: '/manager/purchasing', label: 'Pembelian' },
    { to: '/manager/users', label: 'Pengguna & Meja' },
    { to: '/manager/reports', label: 'Kelola Laporan' },
    { to: '/manager/profile', label: 'Profil' },
  ],
  owner: [
    { to: '/owner', label: 'Dashboard', end: true },
    { to: '/owner/reports', label: 'Laporan Penjualan' },
    { to: '/owner/profile', label: 'Profil Akun' },
  ],
}
