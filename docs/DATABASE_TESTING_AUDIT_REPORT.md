# LAPORAN AUDIT & PENGUJIAN DATABASE CAFE ÉXITO
**Task 2 — Database Testing & Database Audit**\
**Tanggal Audit:** 21 November 2026\
**Target Database:** `cafe_exito` (PostgreSQL 16.14 pada `127.0.0.1:5432`)\
**Metode Audit:** Read-Only Inspection (Zero Mutation, Non-Destructive)\
**File Output:** `docs/DATABASE_TESTING_AUDIT_REPORT.md`\

---

## A. Ringkasan Eksekutif

| Parameter | Nilai / Kondisi | Keterangan |
|---|---|---|
| **Database Aktif** | `cafe_exito` | Terverifikasi dari `backend/.env` (`DB_CONNECTION=pgsql`, `DB_DATABASE=cafe_exito`) |
| **DBMS & Versi** | PostgreSQL 16.14 | Host `127.0.0.1:5432`, SSL mode `prefer` |
| **Pengguna Database** | `cafe_exito_user` | Credential/password tidak ditampilkan |
| **Schema Aktif** | `public` | Read-only inspection pada schema public |
| **Status Koneksi** | **CONNECTED (HEALTHY)** | 6 open connections, respons query instan (< 10ms) |
| **Jumlah Tabel** | **33 tabel** | 22 tabel operasional, 8 framework/system, 3 tabel legacy |
| **Jumlah Kolom** | **298 kolom** | Tersebar di 33 tabel |
| **Jumlah Primary Key** | **33 PK** | Seluruh tabel memiliki Primary Key |
| **Jumlah Foreign Key** | **38 FK** | Terdefinisi dengan aturan referential integrity |
| **Jumlah Unique Constraints** | **24 constraints** | Menjaga keunikan data identitas/transaksi |
| **Jumlah Check Constraints** | **221 constraints** | Validasi nilai enum, rentang numerik, dan not null |
| **Total Constraints** | **316 constraints** | PK + FK + Unique + Check |
| **Jumlah Index** | **92 indexes** | B-tree indexes untuk PK, FK, kolom pencarian & status |
| **Status Migrasi Laravel** | **29 Ran, 2 Pending** | 2 migrasi kritis fitur baru belum dieksekusi di database aktif |

---

## B. Hasil Audit Struktur Database

Audit terhadap 33 tabel aktual di database `cafe_exito`:

| No | Nama Tabel | Kategori | Jumlah Kolom | Jumlah Baris | PK | FK | Status | Keterangan |
|---|---|---|---|---|---|---|---|---|
| 1 | `app_settings` | Konfigurasi | 5 | 0 | `id` | - | PASS | Key-value store dengan `jsonb` value |
| 2 | `attendance_events` | Operasional | 12 | 2 | `id` | 2 (`attendances`, `users`) | PASS | Tracking check-in/out GPS + foto selfie |
| 3 | `attendances` | Operasional | 9 | 1 | `id` | 1 (`users`) | PASS | Rekap kehadiran harian |
| 4 | `audit_logs` | Security/Audit | 13 | 5 | `id` | 1 (`users`) | PASS | Audit trail event sistem & autentikasi |
| 5 | `cache` | Framework | 3 | 0 | `key` | - | PASS | Cache store Laravel |
| 6 | `cache_locks` | Framework | 3 | 0 | `key` | - | PASS | Concurrency locking Laravel |
| 7 | `daily_counters` | Operasional | 5 | 1 | `id` | - | PASS | Generator nomor urut order harian |
| 8 | `failed_jobs` | Framework | 7 | 0 | `id` | - | PASS | Queue failed jobs |
| 9 | `goods_receipt_details` | Operasional | 6 | 0 | `id` | 2 (`goods_receipts`, `purchase_details`) | PASS | Detail penerimaan barang gudang |
| 10 | `goods_receipts` | Operasional | 11 | 0 | `id` | 2 (`purchases`, `users`) | PASS | Header penerimaan barang gudang |
| 11 | `ingredients` | Operasional | 12 | 1 | `id` | - | PASS | Master bahan baku & stok |
| 12 | `job_batches` | Framework | 10 | 0 | `id` | - | PASS | Batch processing queue |
| 13 | `jobs` | Framework | 7 | 0 | `id` | - | PASS | Queue jobs worker |
| 14 | `legacy_attendance_photos` | Legacy | 8 | 0 | `id` | - | WARNING | Tabel peninggalan skema awal, kosong |
| 15 | `legacy_inventory_logs` | Legacy | 9 | 0 | `id` | - | WARNING | Tabel peninggalan skema awal, kosong |
| 16 | `legacy_transactions` | Legacy | 13 | 0 | `id` | - | WARNING | Tabel peninggalan skema awal, kosong |
| 17 | `menu_categories` | Operasional | 9 | 3 | `id` | - | PASS | Kategori menu (Coffee, Non-Coffee, Makanan) |
| 18 | `menus` | Operasional | 13 | 11 | `id` | 1 (`menu_categories`) | PASS | Master menu & harga |
| 19 | `migrations` | Framework | 3 | 46 | `id` | - | PASS | Catatan migrasi Laravel |
| 20 | `order_details` | Operasional | 13 | 15 | `id` | 2 (`orders`, `menus`) | WARNING | Kolom `started_at` & `ready_at` belum ada di DB |
| 21 | `order_status_histories` | Operasional | 9 | 0 | `id` | 2 (`orders`, `users`) | PASS | Riwayat pergantian status pesanan |
| 22 | `order_tickets` | Operasional | 10 | 0 | `id` | 2 (`orders`, `users`) | PASS | Tiket antrean stasiun Barista/Kitchen |
| 23 | `orders` | Operasional | 23 | 14 | `id` | 2 (`tables`, `users`) | WARNING | Kolom `created_by` belum ada di DB |
| 24 | `payments` | Operasional | 12 | 14 | `id` | 2 (`orders`, `users`) | PASS | Header pembayaran pesanan |
| 25 | `personal_access_tokens` | Framework | 10 | 23 | `id` | - | PASS | Token autentikasi Laravel Sanctum |
| 26 | `purchase_details` | Operasional | 8 | 0 | `id` | 2 (`purchases`, `ingredients`) | PASS | Detail pembelian bahan baku |
| 27 | `purchases` | Operasional | 10 | 0 | `id` | 2 (`suppliers`, `users`) | PASS | Header pesanan pembelian (PO) |
| 28 | `recipes` | Operasional | 6 | 0 | `id` | 2 (`menus`, `ingredients`) | PASS | Komposisi bahan per resep menu |
| 29 | `stock_movements` | Operasional | 10 | 0 | `id` | 2 (`ingredients`, `users`) | PASS | Kartu stok mutasi masuk/keluar |
| 30 | `suppliers` | Operasional | 8 | 1 | `id` | - | PASS | Master pemasok bahan baku |
| 31 | `tables` | Operasional | 7 | 6 | `id` | - | PASS | Master meja & QR token |
| 32 | `user_permissions` | Operasional | 5 | 0 | `id` | 1 (`users`) | PASS | Hak akses kustom pengguna |
| 33 | `users` | Operasional | 11 | 5 | `id` | - | PASS | Master pengguna sistem |

---

## C. Hasil Rekonsiliasi Database dengan Source Code

Berdasarkan perbandingan antara model Eloquent (`backend/app/Models`), migrasi (`backend/database/migrations`), dan database aktual:

| No | Model / Komponen | Database Aktual | Status Migrasi | Temuan Rekonsiliasi |
|---|---|---|---|---|
| 1 | `PaymentProof` | **TIDAK ADA** | Pending (`2026_11_19_000001`) | Tabel `payment_proofs` belum dibuat di DB aktif. Upload bukti QRIS belum memiliki tabel tujuan. |
| 2 | `OrderItemCancellation` | **TIDAK ADA** | Pending (`2026_11_20_000001`) | Tabel `order_item_cancellations` belum dibuat di DB aktif. |
| 3 | `RefundRequest` | **TIDAK ADA** | Pending (`2026_11_20_000001`) | Tabel `refund_requests` belum dibuat di DB aktif. |
| 4 | `RefundRequestItem` | **TIDAK ADA** | Pending (`2026_11_20_000001`) | Tabel `refund_request_items` belum dibuat di DB aktif. |
| 5 | `RefundExecution` | **TIDAK ADA** | Pending (`2026_11_20_000001`) | Tabel `refund_executions` belum dibuat di DB aktif. |
| 6 | `RefundEvent` | **TIDAK ADA** | Pending (`2026_11_20_000001`) | Tabel `refund_events` belum dibuat di DB aktif. |
| 7 | `RefundConflictNote` | **TIDAK ADA** | Pending (`2026_11_20_000001`) | Tabel `refund_conflict_notes` belum dibuat di DB aktif. |
| 8 | `AppNotification` | **TIDAK ADA** | Pending (`2026_11_20_000001`) | Tabel `app_notifications` belum dibuat di DB aktif. |
| 9 | `Order::$fillable` | `orders` (23 kolom) | Pending (`2026_11_20_000001`) | Kolom `created_by` ada di Model `$fillable` tetapi belum ada di tabel `orders` aktual. |
| 10 | `OrderDetail::$fillable`| `order_details` (13 kolom) | Pending (`2026_11_20_000001`) | Kolom `started_at` dan `ready_at` ada di Model `$fillable` tetapi belum ada di tabel `order_details` aktual. |
| 11 | `User::$fillable` | `users` (11 kolom) | Ran | Sesuai. Kolom `username`, `email`, `role`, `is_active`, `pin` selaras. |
| 12 | `Menu::$fillable` | `menus` (13 kolom) | Ran | Sesuai. Relasi ke `menu_categories` valid. |
| 13 | `Table::$fillable` | `tables` (7 kolom) | Ran | Sesuai. Kolom `table_number`, `status`, `qr_token` selaras. |
| 14 | `AuditLog::$fillable` | `audit_logs` (13 kolom) | Ran | Sesuai. Telah mencatat log login gagal secara konsisten. |
| 15 | `Tabel Legacy` | 3 tabel ada di DB | Legacy Ran | `legacy_attendance_photos`, `legacy_inventory_logs`, `legacy_transactions` ada di DB namun tidak lagi dipetakan ke Model Eloquent mana pun. |

---

## D. Hasil Audit Data Integrity

Pemeriksaan konsistensi seluruh rekaman data aktif menggunakan query read-only (SELECT-only):

| No | Pemeriksaan Integritas Data | Jumlah Temuan | Status | Keterangan & Detail |
|---|---|---|---|---|
| 1 | Pengguna dengan role tidak valid | 0 | PASS | Seluruh 5 user memiliki role valid (`owner`: 1, `manager`: 1, `kasir`: 1, `barista_kitchen`: 2). |
| 2 | Pengguna dengan credential NULL | 0 | PASS | Seluruh user memiliki `username` dan `email` yang terisi lengkap dan unik. |
| 3 | Menu tanpa kategori / kategori orphan | 0 | PASS | Seluruh 11 menu terhubung ke kategori yang valid (`menu_categories`). |
| 4 | Bahan baku dengan stok negatif | 0 | PASS | Stok bahan baku (`ingredients`) non-negatif. |
| 5 | Selisih total belanja pesanan vs item | 0 | PASS | Seluruh 14 pesanan memiliki `total_amount` persis sama dengan $\sum (\text{unit\_price} \times \text{qty})$ dari `order_details`. |
| 6 | Orphan `order_details` (tanpa `order`) | 0 | PASS | Seluruh 15 order items memiliki referensi `order_id` yang valid. |
| 7 | Orphan `payments` (tanpa `order`) | 0 | PASS | Seluruh 14 data pembayaran terhubung ke order yang valid. |
| 8 | Order selesai tapi pembayaran pending | 0 | PASS | Seluruh pesanan yang ada saat ini berstatus `menunggu_pembayaran` dengan pembayaran `pending`. |
| 9 | Duplikasi nomor pesanan (`order_number`) | 0 | PASS | Seluruh 14 nomor pesanan unik (dijamin unique constraint). |
| 10 | Duplikasi nomor transaksi pembayaran | 0 | PASS | Tidak ada duplikasi nomor transaksi pada tabel `payments`. |
| 11 | Resep orphan (tanpa menu / bahan) | 0 | PASS | Tidak ada resep orphan (tabel resep saat ini 0 baris). |
| 12 | Pesanan dengan meja tidak valid | 0 | PASS | Seluruh pesanan terhubung ke meja yang valid di tabel `tables`. |
| 13 | Sinkronisasi Sequence PostgreSQL | 0 | PASS | Seluruh 17 sequence PostgreSQL berada pada atau di atas `MAX(id)` masing-masing tabel. |
| 14 | Kehadiran tanpa check-out | 0 | PASS | Sesi kehadiran karyawan ID 8 memiliki pasangan `check_in` dan `check_out` yang sinkron. |

---

## E. Hasil Audit Business Logic

Evaluasi kesiapan skema database terhadap 12 alur bisnis Cafe Éxito:

| No | Proses Bisnis | Status | Masalah / Hambatan Aktual |
|---|---|---|---|
| 1 | **QR Self-Order** | PASS WITH WARNING | Skema tabel `orders`, `order_details`, `tables` lengkap. Peringatan: kolom `created_by` pada model belum ada di tabel `orders` (migrasi pending). |
| 2 | **Pembayaran QRIS** | **FAIL** | Tabel `payment_proofs` **belum ada di database aktif**. Alur wajib bisnis (pelanggan upload bukti transfer $\le 3\text{ MB}$, kasir verifikasi bukti) tidak dapat berjalan sebelum migrasi diterapkan. |
| 3 | **Pembayaran Tunai** | PASS | Tabel `payments` mendukung metode `tunai`, amount received, dan kasir verifikator. |
| 4 | **POS Kasir** | PASS | Terdukung oleh `orders`, `order_details`, `payments`, `tables`, dan `users`. |
| 5 | **Barista Station** | PASS WITH WARNING | Tabel `order_tickets` dan `order_details.station` tersedia. Namun kolom timestamp `started_at` dan `ready_at` pada `order_details` belum ada di DB. |
| 6 | **Kitchen Station** | PASS WITH WARNING | Sama dengan Barista Station: antrean stasiun siap, namun tracking waktu mulai & selesai per item menunggu migrasi. |
| 7 | **Inventory & Stock** | PASS | Skema `ingredients`, `recipes`, `stock_movements` tersedia dengan integrity FK restrict. |
| 8 | **Purchasing & Supplier** | PASS | Skema `suppliers`, `purchases`, `purchase_details`, `goods_receipts`, `goods_receipt_details` lengkap. |
| 9 | **Cancellation & Refund** | **FAIL** | Seluruh skema pembatalan & refund (`order_item_cancellations`, `refund_requests`, `refund_request_items`, `refund_executions`, `refund_events`, `refund_conflict_notes`, `app_notifications`) **belum ada di database aktif**. |
| 10 | **Attendance & Shift** | PASS | Skema `attendances` dan `attendance_events` (GPS, selfie, accuracy meters) tersedia dan data contoh tersimpan valid. |
| 11 | **Dashboard Owner & Manager** | PASS WITH WARNING | Query agregasi transaksi dasar dapat berjalan, namun widget metrik refund & notifikasi sistem akan error karena tabel refund belum ada. |
| 12 | **Laporan Penjualan & Keuangan** | PASS WITH WARNING | Perhitungan omzet kotor dapat dilakukan, namun rekonsiliasi refund belum didukung skema database aktif. |

---

## F. Daftar Masalah yang Ditemukan

### 1. CRITICAL
- **[CRIT-01] Dua Migrasi Kritis Belum Diterapkan pada Database Aktif:**
  - `2026_11_19_000001_create_payment_proofs_table` (Pending)
  - `2026_11_20_000001_add_customer_item_cancellation_and_refunds` (Pending)
  - *Dampak:* Fitur Pembayaran QRIS (upload bukti) dan Fitur Pembatalan Item/Refund akan mengalami error SQL `relation does not exist` saat diuji pada Task 3 (API Testing) dan Task 5 (Integration Testing).

### 2. HIGH
- **[HIGH-01] Inkonsistensi Kolom Model Eloquent vs Database Aktual:**
  - Model `App\Models\Order` menyertakan kolom `created_by` dalam `$fillable`, padahal kolom tersebut berada pada migrasi pending `2026_11_20_000001`.
  - Model `App\Models\OrderDetail` menyertakan kolom `started_at` dan `ready_at` dalam `$fillable`, padahal kolom tersebut juga berada pada migrasi pending.
  - *Dampak:* Operasi mass assignment `Order::create()` atau `OrderDetail::update()` yang menyertakan atribut tersebut akan gagal atau mengabaikan field sebelum migrasi dijalankan.

### 3. MEDIUM
- **[MED-01] Delapan Model Eloquent Tanpa Tabel Fisik di Database:**
  - `PaymentProof`, `OrderItemCancellation`, `RefundRequest`, `RefundRequestItem`, `RefundExecution`, `RefundEvent`, `RefundConflictNote`, `AppNotification`.
  - *Dampak:* Setiap panggilan query builder terhadap model-model ini akan memicu exception `QueryException: relation does not exist`.

### 4. LOW
- **[LOW-01] Keberadaan Tabel Legacy yang Tidak Lagi Digunakan:**
  - `legacy_attendance_photos`, `legacy_inventory_logs`, `legacy_transactions` (semuanya 0 baris).
  - *Dampak:* Tidak mengganggu operasional sistem karena tidak dipanggil oleh kode aplikasi, namun menambah ukuran katalog skema dan berpotensi menimbulkan kebingungan saat pemeliharaan.

### 5. INFORMATION
- **[INFO-01] Data Transaksi Aktif Berada dalam Status Awal (Seeding/Testing):**
  - Terdapat 14 pesanan aktif yang semuanya berstatus `menunggu_pembayaran` dan pembayaran `pending`.
  - Data ini aman dan dipertahankan untuk kebutuhan pengujian alur verifikasi kasir pada tahap API dan Integration Testing berikutnya.

---

## G. Rekomendasi Perbaikan

| Prioritas | Masalah Terkait | File / Tabel Terdampak | Rekomendasi Solusi | Risiko Perbaikan |
|---|---|---|---|---|
| **1 (Wajib)** | CRIT-01, HIGH-01, MED-01 | Database `cafe_exito`, Migrasi `2026_11_19_*` & `2026_11_20_*` | Jalankan perintah standar Laravel `php artisan migrate` secara terarah untuk mengeksekusi 2 migrasi pending **tanpa** opsi reset (`migrate:fresh` dilarang). | **Sangat Rendah:** Migrasi hanya menambahkan tabel baru dan menambahkan 3 kolom nullable ke tabel yang sudah ada (`orders.created_by`, `order_details.started_at`, `order_details.ready_at`), sehingga data 14 transaksi yang ada tetap 100% utuh. |
| **2 (Opsional / Task 8)** | LOW-01 | `legacy_*` tables | Pertahankan tabel legacy selama masa testing dan UAT. Pembersihan tabel legacy dijadwalkan pada Task 8 (Finalisasi Database Produksi). | **Nol:** Tidak ada risiko jika dibiarkan selama pengujian. |

---

## H. Kesimpulan Akhir

### Penilaian: **PASS WITH WARNINGS**

**Ulasan Kelayakan Database:**
1. **Integritas Data:** Sangat baik (**PASS**). Tidak ditemukan orphan records, duplicate keys, selisih perhitungan harga pesanan, stok negatif, maupun sequence out-of-sync. Seluruh relasi referensial dan constraints berjalan solid.
2. **Kesesuaian Source Code:** Ditemukan ketertinggalan skema akibat **2 migrasi baru yang berstatus PENDING** pada database aktif `cafe_exito`.
3. **Kesiapan Menuju Task 3 (API Testing):** Database sudah siap untuk pengujian operasional reguler (Katalog Menu, Meja, Presensi, POS Kasir Tunai, Autentikasi). Namun, untuk menguji alur Pembayaran QRIS dan Pembatalan/Refund, migrasi pending tersebut **harus dieksekusi terlebih dahulu**.

Dokumen ini disimpan secara aman sebagai basis audit resmi Task 2.
