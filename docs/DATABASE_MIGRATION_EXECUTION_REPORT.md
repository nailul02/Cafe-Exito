# LAPORAN PELAKSANAAN MIGRASI DATABASE (DATABASE MIGRATION EXECUTION REPORT)
**Task 2 — Database Testing & Final Migration Cafe Éxito**\
**Tanggal & Waktu Pelaksanaan:** 21 November 2026, 04:28 WIB\
**Status Keseluruhan:** **SUCCESS (PASS — 100% Ran, Zero Data Loss)**\
**File Output:** `docs/DATABASE_MIGRATION_EXECUTION_REPORT.md`\

---

## 1. Waktu Pelaksanaan
- **Percobaan 1:** 2026-11-21 04:04 WIB (Gagal pada `REFERENCES` tabel `payments`).
- **Perbaikan Izin 1:** `GRANT REFERENCES ON TABLE public.payments TO cafe_exito_user;`
- **Percobaan 2:** 2026-11-21 04:20 WIB (`payment_proofs` sukses Ran [6], migrasi kedua gagal kepemilikan tabel `order_details`).
- **Perbaikan Izin 2:** Pengalihan ownership `orders` dan `order_details` kepada `cafe_exito_user`.
- **Pelaksanaan Final:** 2026-11-21 04:28:52 WIB.
- **Durasi Migrasi Final:** 240.72 ms (`2026_11_20_000001_add_customer_item_cancellation_and_refunds`).

---

## 2. Database Target
- **DBMS:** PostgreSQL 16.14
- **Host / Port:** `127.0.0.1:5432`
- **Database:** `cafe_exito`
- **User Runtime:** `cafe_exito_user`
- **Schema:** `public`

---

## 3. Identitas Backup Pengaman
- **Status Backup:** **Tersedia & Terverifikasi Utuh**
- **File Path:** `E:\CafeExitoBackups\cafe_exito_pre_migration_20261121.dump`
- **Ukuran:** 137.481 bytes (~137,5 KB)
- **Format:** PostgreSQL Custom Format (`-F c`), 385 TOC entries valid terverifikasi via `pg_restore --list`.

---

## 4. Status Seluruh Migrasi Setelah Pelaksanaan Final

Berdasarkan pemeriksaan `php artisan migrate:status`:

| No | Nama Migrasi | Status | Batch | Keterangan |
|---|---|---|---|---|
| 1 | `2026_11_19_000001_create_payment_proofs_table` | **Ran** | [6] | Sukses membuat tabel bukti pembayaran QRIS |
| 2 | `2026_11_20_000001_add_customer_item_cancellation_and_refunds` | **Ran** | [7] | Sukses membuat 7 tabel refund & menambahkan 3 kolom baru |

*Seluruh 31 file migrasi Laravel pada project Cafe Éxito kini telah berstatus **Ran (100% UP TO DATE)**.*

---

## 5. Hasil Verifikasi Tabel Baru (8 Tabel)

Total tabel di database `cafe_exito` meningkat dari **33 tabel** menjadi **41 tabel**. Delapan tabel baru telah terverifikasi aktif:

| No | Nama Tabel Baru | Jumlah Kolom | Primary Key | Foreign Key Target | Status |
|---|---|---|---|---|---|
| 1 | `payment_proofs` | 13 | `id` | `payments(id)`, `users(id)` | **PASS** |
| 2 | `order_item_cancellations` | 12 | `id` | `orders(id)`, `order_details(id)`, `users(id)` | **PASS** |
| 3 | `refund_requests` | 21 | `id` | `orders(id)`, `payments(id)`, `users(id)` | **PASS** |
| 4 | `refund_request_items` | 5 | `id` | `refund_requests(id)`, `order_item_cancellations(id)` | **PASS** |
| 5 | `refund_events` | 6 | `id` | `refund_requests(id)`, `users(id)` | **PASS** |
| 6 | `refund_executions` | 8 | `id` | `refund_requests(id)`, `users(id)` | **PASS** |
| 7 | `refund_conflict_notes` | 11 | `id` | `refund_requests(id)`, `users(id)` | **PASS** |
| 8 | `app_notifications` | 5 | `id` | `users(id)` | **PASS** |

---

## 6. Hasil Verifikasi Kolom Baru pada Tabel Eksisting

| Tabel | Kolom Baru | Tipe Data | Nullable | Relasi / Keterangan | Status |
|---|---|---|---|---|---|
| `orders` | `created_by` | bigint | YES (NULL) | FK ke `users(id)` on delete set null | **PASS** |
| `order_details` | `started_at` | timestamp with time zone | YES (NULL) | Tracking waktu mulai produksi | **PASS** |
| `order_details` | `ready_at` | timestamp with time zone | YES (NULL) | Tracking waktu siap disajikan | **PASS** |

---

## 7. Hasil Verifikasi Indeks dan Constraint Baru

- **Unique Constraints & Indexes:**
  - `payment_proofs_sha256_unique` (sha256)
  - `payment_proofs_payment_id_id_index` (payment_id, id)
  - `order_item_cancellations_idempotency` (order_id, idempotency_key)
  - `refund_requests_one_requested_per_payment` (payment_id WHERE status = 'requested')
  - `refund_request_items_cancellation_id_unique` (cancellation_id)
  - `refund_executions_refund_request_id_unique` (refund_request_id)
  - `app_notifications_user_id_read_at_index` (user_id, read_at)
- **Check Constraints:**
  - `payment_proofs_status_check` (`status IN ('pending','verified','rejected')`)
  - `order_item_cancellations_qty_check` (`qty > 0`)
  - `order_item_cancellations_source_check` (`source IN (...)`)
  - `order_item_cancellations_range_check` (`qty_start > 0 AND qty_end >= qty_start AND qty = qty_end - qty_start + 1`)
  - `refund_requests_source_check`, `status_check`, `amount_check` (`amount > 0`)
  - `refund_request_items_positive_check` (`qty > 0 AND amount > 0`)
  - `refund_executions_method_check`, `amount_check`, `transfer_check`
  - `refund_conflict_notes_amount_check` (`amount >= 0`)

---

## 8. Verifikasi Integritas Data Transaksi Lama (Zero Data Loss)

Perbandingan data sebelum dan sesudah pelaksanaan migrasi:

| Tabel | Jumlah Sebelum Migrasi | Jumlah Sesudah Migrasi | Status Integritas Data |
|---|---|---|---|
| `orders` | **14** | **14** | **100% UTUH / SAMA** |
| `payments` | **14** | **14** | **100% UTUH / SAMA** |
| `order_details` | **15** | **15** | **100% UTUH / SAMA** |
| `users` | **5** | **5** | **100% UTUH / SAMA** |
| `payment_proofs` | 0 | 0 | Bersih (Tabel baru siap pakai) |
| `order_item_cancellations`| 0 | 0 | Bersih (Tabel baru siap pakai) |
| `refund_requests` | 0 | 0 | Bersih (Tabel baru siap pakai) |

- **ID Pesanan yang Tersedia:** `1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14` (seluruh transaksi lama tetap ada tanpa perubahan data maupun relasi).
- **Nilai Kolom Baru pada Transaksi Lama:** Nilai kolom baru `orders.created_by`, `order_details.started_at`, dan `order_details.ready_at` pada seluruh data lama terisi `NULL` secara konsisten tanpa merusak data yang telah ada.

---

## 9. Kondisi Konfigurasi `.env` dan Git
- **Konfigurasi `.env`:** Tetap utuh, tidak ada perubahan connection string atau credentials.
- **Git State:** Tidak ada git commit atau git staging yang diubah oleh proses ini.

---

## 10. Kesimpulan Akhir: **PASS (100% READY)**

Migrasi database Cafe Éxito pada database aktif `cafe_exito` telah **selesai 100% dengan status LULUS (PASS)**:
1. Seluruh 31 migrasi telah berstatus **Ran**.
2. Seluruh 8 tabel baru untuk fitur QRIS Payment Proof dan Item Cancellation & Refund telah tersedia dan terverifikasi.
3. Seluruh 3 kolom baru pada `orders` dan `order_details` telah tersedia dan terverifikasi.
4. Sebanyak 14 data transaksi pesanan yang ada tetap 100% utuh tanpa kehilangan atau kerusakan data.
5. Database `cafe_exito` kini resmi sinkron dengan seluruh Model Eloquent aplikasi dan siap untuk melanjutkan ke tahapan pengujian berikutnya.
