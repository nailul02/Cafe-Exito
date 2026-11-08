# Keputusan: CONFIRMED / ASSUMPTION / PENDING

## CONFIRMED (disepakati tim; jangan diubah tanpa persetujuan)
| ID | Keputusan |
|---|---|
| C1 | Frontend dan backend terpisah; backend Laravel **REST API-only**, JSON, prefix `/api/v1`; tanpa Blade |
| C2 | Database **PostgreSQL**; backend **Sanctum** |
| C3 | Frontend: React + Vite + Tailwind + React Router + Axios |
| C4 | Enam role: Owner, Admin/Manager, Kasir, Barista, Kitchen, Pelanggan; **Barista dan Kitchen terpisah** |
| C5 | Pembelian bahan baku dari supplier **wajib** di MVP (supplier, pembelian, detail, penerimaan, riwayat pembelian, riwayat pergerakan stok); pembelian ≠ penerimaan; stok bertambah hanya setelah penerimaan dikonfirmasi |
| C6 | Pembayaran: **tunai** dan **QRIS statis + verifikasi manual**; tanpa payment gateway |
| C7 | Metodologi Waterfall; pengembangan lokal di Windows; hosting gratis dibahas paling akhir |
| C8 | Aturan bisnis, validasi, otorisasi, transaksi seluruhnya di backend |

## ASSUMPTION (dibuat sementara agar pengembangan jalan; mudah diubah)
| ID | Asumsi | Di mana diatur |
|---|---|---|
| A1 | Bearer token Sanctum, masa berlaku 12 jam, tanpa mode cookie | `config/sanctum.php`; `frontend/src/api/tokenStorage.js` (sessionStorage) |
| A2 | Login dengan email **atau** username (K-T1) | `AuthController::login` |
| A3 | Uang = bilangan bulat rupiah; jumlah bahan = string desimal 3 digit | Resource, `Rp` |
| A4 | Tiket per stasiun; stok dikurangi saat tiket **dimulai**; urutan antrean FIFO menurut waktu pesan | `TicketService` |
| A5 | Stok kurang saat mulai → ditolak (`INSUFFICIENT_STOCK`), tiket tetap antre | `StockService` |
| A6 | Ketersediaan menu dihitung dinamis (saklar manual + stok bahan resep), tidak disimpan | `MenuAvailabilityService` |
| A7 | Level stok: habis = 0; kritis ≤ 50% minimum; menipis ≤ 100%; aman > 100% | `StockLevel`, `config/cafe.php` |
| A8 | `X-Table-Token` opsional; pelanggan boleh mengetik nomor meja (menampung kedua kemungkinan K-T2) | `ValidateTableToken` |
| A9 | Kasir boleh mengoreksi metode bayar saat verifikasi (tercatat audit); uang diterima dan alasan tolak **opsional** (Figma tidak memuat kolomnya) | `PaymentService` |
| A10 | Pembatalan Admin: pembayaran → `refunded`; stok dikembalikan hanya bila `restore_stock=true` (bawaan false) | `OrderService::cancelByAdmin` |
| A11 | Laporan: hanya pembayaran `verified`; periode bergulir (hari ini / 7 hari / 30 hari); zona Asia/Jakarta | `ReportService` |
| A12 | Penamaan tabel Inggris (`orders`, `order_details`, …); tabel tambahan: `menu_categories`, `order_tickets`, `stock_movements`, `user_permissions`, `app_settings`, `audit_logs` | `DATABASE_SCHEMA.md` |
| A13 | Kolom tambahan dari Figma: `users.email`, `payments.transaction_number`, `menus.sku`, `menus.cost_price` (opsional, belum dipakai laporan), `ingredients.category` | migrasi |
| A14 | Izin khusus per akun hanya untuk daftar `grantable`; Admin tidak dapat mengubah akun Owner | `config/permissions.php`, `UserPolicy` |
| A15 | Owner hanya membaca; Manager belum boleh menjalankan tiket (hanya memantau) | `config/permissions.php` |
| A16 | Layar tanpa Figma (Kitchen, pengguna, meja/QR, supplier/pembelian, penerimaan) dibuat dengan gaya desain terdekat dan diberi penanda `ASUMSI` di UI | `DevNote` (`VITE_SHOW_DEV_NOTES`) |
| A17 | Laporan mingguan + kustom rentang disediakan (dikonfirmasi oleh desain) | `ReportService` |

## PENDING (menunggu keputusan pemilik proyek)
**Bisnis**
| ID | Pertanyaan | Rekomendasi |
|---|---|---|
| K-B1 | Jadwal shift, absensi, cuti, tukar shift (muncul di 9 layar Figma) | Di luar MVP; menu disembunyikan (sudah demikian) |
| K-B2 | POS kasir (kasir membuat pesanan) dan Takeaway | Tunda; bila disetujui tambah `orders.order_type/source` |
| K-B3 | HPP, pengeluaran, laba bersih, margin | Hanya omzet; HPP manual opsional |
| K-B4 | Pajak PB1 10% (harga inklusif? tampil di struk?) | Putuskan sebelum laporan final |
| K-B5 | Opsi tambahan (modifier) dan promo | Di luar MVP |
| K-B6 | Produk retail (Whole Bean, Merch) | Di luar MVP atau menu tanpa resep |
| K-B7 | Multi-outlet | Satu outlet |
| K-B8 | Hapus EDC/Transfer/QRIS dinamis dari desain Owner | Ya |
| K-B9 | Tutup kasir harian, kas fisik, void | Tunda |
| K-B10 | Stok masuk tanpa PO (formulir Figma menambah stok langsung) | Wajib lewat PO/penerimaan (diimplementasikan) |
| K-B11 | Owner boleh membuat PO? Manager boleh menjalankan tiket? | Owner baca saja; Manager lewat izin khusus |
| K-B12 | Status "Siap Antar" dan siapa yang mengantar | Tambah status `siap` bila ada runner |
| K-B13 | Persetujuan laporan, rekap gaji/lembur | Keluar dari MVP |

**Teknis**
| ID | Pertanyaan | Rekomendasi |
|---|---|---|
| K-T1 | Login email atau username | Kolom "login" menerima keduanya (diimplementasikan) |
| K-T2 | QR per meja (token) vs satu QR umum + nomor manual | QR per meja |
| K-T3 | Kategori menu terkelola (tabel) | Tabel `menu_categories` (diimplementasikan) |
| K-T4 | Ketersediaan menu dinamis vs disimpan | Dinamis (diimplementasikan) |
| K-T5 | Ambang level stok | Lihat A7 |
| K-T7 | Ekspor PDF/Excel/cetak | Frontend cetak; backend CSV (saat ini CSV dibuat di frontend) |
| K-T8 | Tabel `app_settings` (QRIS statis, identitas kafe) | Diimplementasikan; UI unggah belum |
| K-T10 | EXP/batch/foto faktur pada penerimaan | Tunda |

**Keamanan**
| ID | Pertanyaan |
|---|---|
| K-S1 | Kebijakan data selfie/GPS/biometrik (hanya relevan bila absensi dibangun) |
| K-S2 | Sesi layar dapur: refresh token atau masa berlaku lebih panjang; penyimpanan token final |
| K-S3 | Batas unggah foto: 2 MB JPG/PNG/WebP (sesuai Figma) |

**Deployment**
| ID | Pertanyaan |
|---|---|
| K-D1 | Hosting gratis frontend (statis) dan backend + PostgreSQL: riset free-tier terkini saat tahap akhir |
| K-D2 | Penyimpanan foto menu/QRIS di hosting gratis (disk sementara) |
| K-D3 | Printer termal (Epson TM-T82X di Figma) dari perangkat kasir, di luar hosting |
