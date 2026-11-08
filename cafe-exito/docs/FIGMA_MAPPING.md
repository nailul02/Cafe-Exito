# Pemetaan Figma → Halaman → API → Tabel

Sumber: ekspor ZIP `exito_cafe` (59 PNG; tanpa prototype). 28 layar logis. Hasil analisis penuh ada di percakapan perencanaan; ringkasan implementasi:

| Kode | Layar Figma | Halaman frontend | API utama | Tabel | Status |
|---|---|---|---|---|---|
| P-01 | QR "Scan untuk memesan" | `/order` `QrEntryPage` | `GET /customer/tables/{token}` | tables | Dibuat; K-T2 pending |
| P-02 | Isi nomor meja | `/order/table` | validasi saat checkout | tables | Dibuat (+tombol "Mulai memesan": asumsi) |
| P-03 | Katalog + tab | `/order/menu` | `GET /customer/menus` | menus, menu_categories, recipes | Dibuat |
| P-04 | Keranjang | `/order/cart` | `POST /customer/cart/validate` | — | Dibuat (+ubah jumlah: asumsi) |
| P-05 | Metode bayar | `/order/payment` | `POST /customer/orders` | orders, order_details, payments | Dibuat |
| P-06 | Pesanan diterima | `/order/done` | respons checkout | orders | Dibuat (+petunjuk bayar: asumsi) |
| P-07 | Status pesanan | `/order/status/:token` | `GET /customer/orders/{token}` | orders, order_tickets | Dibuat (+menunggu bayar/batal: asumsi) |
| S-01 | Login | `/login` | `POST /auth/login` | users | Dibuat |
| KS-01 | Dashboard Kasir | `/cashier` | `/cashier/dashboard`, `/cashier/orders`, `payments/{id}/verify|reject` | orders, payments | Dibuat |
| KS-02 | Pesanan (tabel) | `/cashier/orders` | `GET /cashier/orders` | orders | Dibuat |
| KS-03 | Detail transaksi + struk | `/cashier/orders/:id` | `GET /cashier/transactions/{id}/receipt` | payments, orders | Dibuat |
| KS-05/BR-04 | Profil | `*/profile` | `GET/PUT /auth/me` | users | Dibuat |
| BR-01 | Dashboard Barista | `/barista` | `GET /station/barista/dashboard` | order_tickets | Dibuat |
| BR-02 | Antrean minuman | `/barista/queue` | `GET /station/barista/tickets`, `start`, `complete` | order_tickets, stock_movements | Dibuat (tombol aksi: asumsi) |
| — | Kitchen | `/kitchen`, `/kitchen/queue` | `/station/kitchen/*` | order_tickets | **Tanpa desain**; ikut pola Barista |
| MG-01 | Dashboard Manager | `/manager` | `GET /dashboard/manager` | beragam | Dibuat (tanpa kehadiran/jadwal) |
| MG-02 | Kelola Menu | `/manager/menus` | `/admin/menus*`, recipe | menus, recipes | Dibuat (tanpa foto/PB1/modifier/promo) |
| MG-03 | Kelola Stok | `/manager/inventory` | `/inventory/*`, penerimaan PO | ingredients, stock_movements, goods_receipts | Dibuat (stok masuk hanya lewat PO) |
| MG-06 / OW-02 | Laporan | `/manager/reports`, `/owner/reports` | `GET /reports/sales`, `/transactions` | payments, orders | Dibuat (tanpa HPP/laba/EDC) |
| OW-01 | Dashboard Owner | `/owner` | `GET /dashboard/owner` | beragam | Dibuat (tanpa laba/kehadiran/outlet) |
| — | Pengguna, izin, meja/QR | `/manager/users` | `/admin/users*`, `/admin/tables*` | users, user_permissions, tables | **Tanpa desain** |
| — | Supplier, pembelian | `/manager/purchasing` | `/purchasing/*` | suppliers, purchases, … | **Tanpa desain** |
| MG-04/05, BR-03, KS-04, OW-03/04/05 | Jadwal shift, absensi, tukar shift | — | — | — | **Tidak dibuat** (K-B1 pending) |

Navigasi yang dapat dibuktikan dari desain: urutan 7 layar pelanggan; sidebar per role (Dashboard, Pesanan, Profil; Manager: Kelola Menu/Stok/Jadwal/Absensi/Laporan; Owner: Dashboard, Data Karyawan & Shift, Laporan, Stok Bahan, Profil Akun). Interaksi klik antar layar tidak dapat dibuktikan dari PNG (perlu klarifikasi).
