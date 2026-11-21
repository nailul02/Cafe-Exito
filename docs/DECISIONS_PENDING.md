# Keputusan: CONFIRMED / ASSUMPTION / PENDING

## CONFIRMED (disepakati tim; jangan diubah tanpa persetujuan)
| ID | Keputusan |
|---|---|
| C1 | Frontend dan backend terpisah; backend Laravel **REST API-only**, JSON, prefix `/api/v1`; tanpa Blade |
| C2 | Database **PostgreSQL**; backend **Sanctum** |
| C3 | Frontend: React + Vite + Tailwind + React Router + Axios |
| C4 | Role staf: Owner, Manager, Kasir, `barista_kitchen`; akun produksi wajib menyimpan station `barista` atau `kitchen`. Hak antrean tetap terpisah berdasarkan station; Owner/Manager hanya memantau kedua antrean sesuai permission |
| C5 | **Keputusan historis, digantikan keputusan final 13 November 2026:** pembelian bahan baku pernah diwajibkan di MVP. Inventory, stok operasional, supplier, purchasing/penerimaan, dan laporan penggunaan stok kini di luar scope aktif; keputusan final di bawah berlaku. |
| C6 | Pembayaran: **tunai** dan **QRIS statis + verifikasi manual**; tanpa payment gateway |
| C7 | Metodologi Waterfall; pengembangan lokal di Windows; hosting gratis dibahas paling akhir |
| C8 | Aturan bisnis, validasi, otorisasi, transaksi seluruhnya di backend |
| C9 | Absensi staf wajib mengambil foto dan koordinat GPS untuk check-in serta check-out; timestamp dan metadata disimpan dan diakses sesuai permission |
| C10 | **Keputusan historis, tidak aktif pada scope saat ini:** aturan konsumsi/pemulihan stok pernah ditetapkan, tetapi stok tidak diubah pada operasional aktif. Lihat keputusan final 13 November 2026 di bawah. |
| C11 | **Keputusan historis, tidak aktif:** dahulu tiket menunggu verifikasi pembayaran dan pemrosesan stok; aturan POS final memperbolehkan pesanan tunai masuk produksi saat payment masih pending, dan stok tidak aktif. |
| C12 | POS kasir dan takeaway termasuk lingkup yang telah disetujui; metode pembayaran tetap tunai atau QRIS statis yang diverifikasi manual |
| C13 | Bukti QRIS menerima JPEG/PNG/WEBP valid maksimal 3 MB; rekomendasi 2 MB dari Figma tidak berlaku untuk upload bukti QRIS |

## ASSUMPTION (dibuat sementara agar pengembangan jalan; mudah diubah)
| ID | Asumsi | Di mana diatur |
|---|---|---|
| A1 | Bearer token Sanctum, masa berlaku 12 jam, tanpa mode cookie | `config/sanctum.php`; `frontend/src/api/tokenStorage.js` (sessionStorage) |
| A2 | Login dengan email **atau** username (K-T1) | `AuthController::login` |
| A3 | Uang = bilangan bulat rupiah; jumlah bahan = string desimal 3 digit | Resource, `Rp` |
| A4 | Tiket per stasiun; stok dikurangi saat tiket **dimulai**; urutan antrean FIFO menurut waktu pesan | **Digantikan C10/C11** |
| A5 | **Historis, tidak aktif:** stok kurang saat mulai pernah dirancang ditolak (`INSUFFICIENT_STOCK`); stok tidak menjadi bagian operasi aktif. | `StockService` |
| A6 | **Historis, digantikan:** ketersediaan menu dahulu dihitung dari saklar manual dan stok; scope aktif hanya memakai saklar ketersediaan manual. | `MenuAvailabilityService` |
| A7 | **Historis, tidak aktif:** ambang level stok tidak digunakan dalam operasi aktif. | `StockLevel`, `config/cafe.php` |
| A8 | `X-Table-Token` opsional; pelanggan boleh mengetik nomor meja (menampung kedua kemungkinan K-T2) | `ValidateTableToken` |
| A9 | Kasir boleh mengoreksi metode bayar saat verifikasi (tercatat audit); uang diterima dan alasan tolak **opsional** (Figma tidak memuat kolomnya) | `PaymentService` |
| A10 | Pembatalan Admin: pembayaran â†’ `refunded`; stok dikembalikan hanya bila `restore_stock=true` (bawaan false) | `OrderService::cancelByAdmin` |
| A11 | Laporan: hanya pembayaran `verified`; periode bergulir (hari ini / 7 hari / 30 hari); zona Asia/Jakarta | `ReportService` |
| A12 | Penamaan tabel Inggris (`orders`, `order_details`, â€¦); tabel tambahan: `menu_categories`, `order_tickets`, `stock_movements`, `user_permissions`, `app_settings`, `audit_logs` | `DATABASE_SCHEMA.md` |
| A13 | Kolom tambahan dari Figma: `users.email`, `payments.transaction_number`, `menus.sku`, `menus.cost_price` (opsional, belum dipakai laporan), `ingredients.category` | migrasi |
| A14 | Izin khusus per akun hanya untuk daftar `grantable`; Admin tidak dapat mengubah akun Owner | `config/permissions.php`, `UserPolicy` |
| A15 | Owner membaca fungsi operasional, dan secara khusus mengelola akun staf non-Owner serta izin yang dapat diberikan; Manager menjalankan tiket hanya melalui izin khusus | `config/permissions.php`, `UserPolicy` |
| A16 | Layar tanpa Figma (Kitchen, pengguna, meja/QR, supplier/pembelian, penerimaan) dibuat dengan gaya desain terdekat dan diberi penanda `ASUMSI` di UI | `DevNote` (`VITE_SHOW_DEV_NOTES`) |
| A17 | Laporan mingguan + kustom rentang disediakan (dikonfirmasi oleh desain) | `ReportService` |

## PENDING (menunggu keputusan pemilik proyek)
**Bisnis**
| ID | Pertanyaan | Rekomendasi |
|---|---|---|
| K-B1 | Jadwal shift, absensi, cuti, tukar shift | Terjawab: absensi langsung dengan kamera, GPS, dan riwayat masuk MVP (C9); kebijakan retensi foto/lokasi perlu ditetapkan; cuti/tukar shift belum disetujui |
| K-B2 | POS kasir (kasir membuat pesanan) dan Takeaway | **Terjawab: disetujui (C12)** |
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

**Deployment**
| ID | Pertanyaan |
|---|---|
| K-D1 | Hosting gratis frontend (statis) dan backend + PostgreSQL: riset free-tier terkini saat tahap akhir |
| K-D2 | Penyimpanan foto menu/QRIS di hosting gratis (disk sementara) |
| K-D3 | Printer termal (Epson TM-T82X di Figma) dari perangkat kasir, di luar hosting |

## Catatan implementasi keputusan — 12 November 2026

Keputusan C4 dan C9–C12 diterapkan pada skema, role/station, API, dan frontend. Database utama kini memiliki event absensi dengan metadata GPS/foto, role produksi `barista_kitchen`, serta pembayaran manual tunai/QRIS. Migrasi dan batas verifikasi terkini dijelaskan pada `database/FINAL_DATABASE_AND_SYSTEM_READINESS_REPORT.md`; keputusan CONFIRMED tidak berubah.

## Catatan implementasi - 13 November 2026

Owner mengelola akun staf non-Owner; akun Owner dan akun sendiri terlindungi dari edit/nonaktif/reset. `attendance_events.accuracy_meters` nullable dan migration tambahannya telah diterapkan. Lihat `implementation/ATTENDANCE_AND_EMPLOYEE_ACCOUNT_IMPLEMENTATION_REPORT.md`.
## Keputusan final klien - 13 November 2026

- Owner dan Manager memantau absensi operasional; laporan lengkap/keterlambatan dan ekspor hanya Owner; koreksi hanya Owner. Manager tidak menerima foto/koordinat event staf lain.
- Closing harian ditolak selama transaksi tunai belum lunas.
- Lokasi cafe: Plus Code 59C3+6MW, Jl. Raya Balai Gadang, Balai Gadang, Kec. Koto Tangah, Padang. Koordinat numerik pusat harus diverifikasi sebelum geofence 30 meter diaktifkan.


## Keputusan final pemilik proyek - 13 November 2026 (menggantikan keputusan lama terkait stok; bagian pembatalan diperbarui AUD-01)

- Inventori, stok bahan baku, supplier, pembelian/penerimaan, dan laporan penggunaan stok dihapus dari kebutuhan operasional. Endpoint untuk fitur tersebut tidak boleh ditambahkan; kode lama yang belum dihapus bukan API aktif.
- POS Kasir dan QR meja tetap dipertahankan. Pembayaran QRIS harus diverifikasi manual oleh Kasir sebelum diproses. Pesanan tunai boleh diproses saat payment masih pending dan harus tetap dianggap belum lunas sampai pembayaran dicatat.
- Catatan pada tanggal ini bahwa pembatalan hanya Owner dan alur refund masih gap telah digantikan oleh keputusan AUD-01 tanggal 20 November 2026 di bawah. Jangan gunakan kalimat historis ini sebagai aturan operasional terkini.
- Keputusan ini menggantikan C5 dan seluruh catatan operasional stok/pembelian sebelumnya, termasuk asumsi pengurangan/pemulihan stok pada C10/A10. C10 tetap tercatat sebagai riwayat keputusan, tetapi tidak mengaktifkan stok, route, atau perubahan stok di operasional saat ini. Tidak ada perubahan skema/database yang dilakukan sebagai bagian dari dokumentasi ini.

## Keputusan final tambahan — pembatalan item/refund AUD-01 (20 November 2026; berlaku sebagai aturan terkini)

Keputusan final terbaru mengizinkan Customer dan Owner membatalkan item/kuantitas yang belum mulai diproses; pembatalan berbayar otomatis membuat refund request untuk keputusan Owner. Refund yang disetujui dieksekusi Cashier dengan cash atau bank transfer. Payment verified tidak mencegah pembatalan; refund bukan otomatis. Item mulai diproses ketika Barista/Kitchen memulai baris item; seluruh kuantitas tersisa pada baris itu dianggap mulai. Customer wajib memberi alasan. Satu request berstatus requested per payment digabung; request yang sudah diputuskan/selesai tidak digabung. Manager hanya mencatat pengecualian konflik, Owner memverifikasi catatan Manager. Notifikasi in-app dan tenggat keputusan 24 jam/pelaksanaan 48 jam.

Keputusan tersebut menggantikan catatan final 13 November yang membatasi refund hanya untuk order yang dibatalkan Owner. Ketentuan AUD-01 lainnya tetap berlaku. Konflik kepentingan dicatat sebagai `conflict_of_interest` dan ditangani di luar alur keputusan normal.

QR Self-Order QRIS memerlukan bukti unggahan pelanggan yang diperiksa Kasir; Kasir tidak dapat memverifikasi atau menolak sebelum bukti tersedia, dan metode QRIS tidak dapat diubah menjadi tunai saat verifikasi. QRIS POS dikonfirmasi langsung oleh Kasir tanpa upload pelanggan. Upload tidak melunasi payment. Tidak ada OCR, gateway pembayaran, webhook, atau verifikasi otomatis. Batas final upload bukti QRIS adalah 3 MB; rekomendasi 2 MB dari Figma tidak diterapkan pada alur ini.

## Klarifikasi POS dan scope aktif dari rekonsiliasi publish (20 November 2026)

- POS tunai dan QRIS dimulai dengan payment `pending`. Order tunai dapat selesai saat payment masih pending; penyelesaian order tidak melunasi payment. Kasir tetap mencatat pembayaran kemudian tanpa mengubah status order yang sudah selesai.
- Inventory/purchasing tidak diaktifkan oleh fitur refund, pembatalan, maupun POS. Supplier, pembelian/penerimaan, mutasi stok, dan laporan penggunaan stok tetap di luar scope operasional aktif.
- Pembatalan ber-idempotency key yang sama dan payload sama tidak menghitung ulang kuantitas atau nominal; payload berbeda ditolak.

## Keputusan Persetujuan Lingkungan Pengujian (Testing Approval) — 21 November 2026

User telah memberikan persetujuan eksplisit untuk dua gerbang pengujian (Gate A & Gate B):
1. **APPROVE FEATURE (Persetujuan Eksekusi Feature Test):**
   - Feature Test diizinkan untuk dijalankan secara eksklusif terhadap database pengujian `cafe_exito_test`.
   - **Batas Keselamatan Mutlak:** Persetujuan ini TIDAK membebaskan kewajiban preflight keselamatan database. Karena seluruh 11 class Feature Test menggunakan `RefreshDatabase` (yang memicu `migrate:fresh`) dan `phpunit.xml` menggunakan `force="false"`, eksekusi hanya boleh dilakukan setelah target database dipastikan secara eksplisit (child process dengan `DB_DATABASE=cafe_exito_test`) dan terisolasi dari database aktif `cafe_exito`.
   - Database aktif `cafe_exito` dilarang keras terkena migrasi, reset, seeder, atau operasi pengujian.
   - Status: Persetujuan diberikan, namun pengujian **BELUM DIJALANKAN**.

2. **APPROVE PLAYWRIGHT (Persetujuan Instalasi Playwright & Browser):**
   - Instalasi dependency `@playwright/test@1.65.1` dan browser binary Chromium diperbolehkan.
   - Status: Persetujuan diberikan, namun instalasi dan browser testing **BELUM DIJALANKAN**.

3. **Status Task 5 dan Penundaan Task 6:**
   - Kedua persetujuan di atas tidak mengubah status penyelesaian pengujian. Task 5 Integration Testing tetap berstatus **BLOCKED** sampai seluruh bukti pengujian aktual tersedia dan diverifikasi.
   - Task 6 System Testing **TETAP DITUNDA** dan tidak boleh dimulai sebelum Task 5 diselesaikan dan diverifikasi.
   - Seluruh keputusan bisnis, teknis, keamanan, dan deployment yang berstatus PENDING di atas (K-B1 s/d K-D3) **tetap berstatus PENDING** dan tidak terpengaruh oleh persetujuan pengujian ini.

## Audit status update — 21 November 2026

The testing approval statuses above are historical and superseded as follows: guarded Feature suite execution completed successfully (75/75, 632 assertions) on `cafe_exito_test`; active `cafe_exito` remained unchanged. `@playwright/test@1.65.1` was installed. Bundled Chromium download timed out, but a minimal `/login` browser smoke passed with installed Google Chrome. Task 5 remains BLOCKED for full browser workflows, responsive/accessibility evidence, and targeted Pint and working-tree whitespace checks pass; the cached index still needs user review/update for documentation whitespace. Task 6 remains deferred.
