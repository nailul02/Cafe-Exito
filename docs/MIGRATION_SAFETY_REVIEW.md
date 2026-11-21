# LAPORAN ANALISIS KEAMANAN MIGRASI PENDING (MIGRATION SAFETY REVIEW)
**Task 2.2 — Migration Safety Review Cafe Éxito**\
**Tanggal Evaluasi:** 21 November 2026\
**Target Database:** `cafe_exito` (PostgreSQL 16.14)\
**Dokumen Acuan:** `docs/DATABASE_TESTING_AUDIT_REPORT.md`\
**File Output:** `docs/MIGRATION_SAFETY_REVIEW.md`\

---

## A. Daftar Seluruh Pending Migrations

Berdasarkan pemeriksaan perintah `php artisan migrate:status`, ditemukan tepat **2 migrasi** yang berstatus **Pending**:

| No | Timestamp / Nama Migrasi | File Path | Status |
|---|---|---|---|
| 1 | `2026_11_19_000001_create_payment_proofs_table` | `backend/database/migrations/2026_11_19_000001_create_payment_proofs_table.php` | **Pending** |
| 2 | `2026_11_20_000001_add_customer_item_cancellation_and_refunds` | `backend/database/migrations/2026_11_20_000001_add_customer_item_cancellation_and_refunds.php` | **Pending** |

---

## B. Analisis Setiap Migration

| Migration | Operasi DDL & Perubahan Skema | Dampak Data Lama | Risiko | Status |
|---|---|---|---|---|
| **`2026_11_19_000001_create_payment_proofs_table`** | - **CREATE TABLE**: `payment_proofs`<br>- **ADD FOREIGN KEY**: `payment_id -> payments(id)` (`restrictOnDelete`), `reviewed_by -> users(id)` (`nullOnDelete`)<br>- **ADD UNIQUE INDEX**: `payment_proofs_sha256_unique`<br>- **ADD INDEX**: `payment_proofs_payment_id_id_index`<br>- **ADD CHECK CONSTRAINT**: `status IN ('pending','verified','rejected')`<br>- **DROP/MODIFY/DELETE**: Tidak ada | **Nol (0%)**.<br>Tabel `payment_proofs` adalah tabel baru. Tidak ada modifikasi kolom maupun data pada tabel `payments`, `orders`, atau tabel eksisting lainnya. | **Sangat Rendah**.<br>Memiliki pengaman `if (Schema::hasTable('payment_proofs')) return;` yang menjamin idempoten jika tabel sudah ada. | **SAFE TO MIGRATE** |
| **`2026_11_20_000001_add_customer_item_cancellation_and_refunds`** | - **ALTER TABLE `order_details`**: Menambahkan kolom `started_at` (`timestampTz->nullable()`) dan `ready_at` (`timestampTz->nullable()`)<br>- **ALTER TABLE `orders`**: Menambahkan kolom `created_by` (`foreignId->nullable()->constrained('users')->nullOnDelete()`)<br>- **CREATE TABLE** (7 tabel baru):<br>  1. `order_item_cancellations`<br>  2. `refund_requests`<br>  3. `refund_request_items`<br>  4. `refund_events`<br>  5. `refund_executions`<br>  6. `refund_conflict_notes`<br>  7. `app_notifications`<br>- **ADD FOREIGN KEYS**: Terhubung ke `orders`, `order_details`, `payments`, `users`, dan relasi antar tabel refund baru.<br>- **ADD UNIQUE INDEXES & CHECKS**: Constraint validasi alur refund (qty > 0, status valid, actor type, range check, transfer proof rule).<br>- **DROP/MODIFY/DELETE**: Tidak ada | **Nol (0%)**.<br>3 kolom tambahan pada `orders` dan `order_details` seluruhnya berstatus `nullable()` tanpa default yang membebani. 14 baris `orders` dan 15 baris `order_details` tetap 100% utuh dengan nilai default `NULL` pada kolom baru. | **Sangat Rendah**.<br>Semua tabel induk tujuan FK (`orders`, `order_details`, `payments`, `users`) sudah ada dan valid. DDL dieksekusi dalam transaksi PostgreSQL. | **SAFE TO MIGRATE** |

---

## C. Analisis Foreign Key dan Ketergantungan (Dependency Analysis)

### 1. Ketergantungan terhadap Tabel yang Sudah Ada (Existing Tables)
- **`payment_proofs`**:
  - `payment_id` mereferensikan `payments(id)` dengan `RESTRICT ON DELETE`.
    - Status: Tabel `payments` memiliki 14 data aktif dengan ID 1 s.d. 14. Relasi valid.
  - `reviewed_by` mereferensikan `users(id)` dengan `SET NULL ON DELETE` (nullable).
    - Status: Tabel `users` memiliki 5 pengguna aktif. Relasi valid.
- **`orders.created_by`**:
  - Mereferensikan `users(id)` dengan `SET NULL ON DELETE`.
    - Status: Kolom bersifat `nullable`. Seluruh 14 transaksi lama otomatis bernilai `NULL` saat kolom dibuat, sehingga tidak ada pelanggaran foreign key check.
- **`order_item_cancellations`**:
  - `order_id` -> `orders(id)` (`RESTRICT`).
  - `order_detail_id` -> `order_details(id)` (`RESTRICT`).
  - `actor_id` -> `users(id)` (`SET NULL`, nullable).
- **`refund_requests`**:
  - `order_id` -> `orders(id)` (`RESTRICT`).
  - `payment_id` -> `payments(id)` (`RESTRICT`).
  - `requested_by`, `decided_by`, `withdrawn_by`, `approval_revoked_by` -> `users(id)` (`SET NULL`, nullable).
- **`refund_executions`**:
  - `cashier_id` -> `users(id)` (`RESTRICT`).
- **`refund_conflict_notes`**:
  - `recorded_by` -> `users(id)` (`RESTRICT`).
  - `verified_by` -> `users(id)` (`SET NULL`, nullable).
- **`app_notifications`**:
  - `user_id` -> `users(id)` (`CASCADE ON DELETE`).

### 2. Ketergantungan Urutan Antar Migrasi (Migration Execution Order)
- Urutan migrasi didasarkan pada timestamp file:
  1. `2026_11_19_000001_create_payment_proofs_table` (Batch N)
  2. `2026_11_20_000001_add_customer_item_cancellation_and_refunds` (Batch N)
- **Verifikasi Ketergantungan Silang:** Migrasi `2026_11_20_*` **tidak mereferensikan** tabel `payment_proofs`. Keduanya independen terhadap satu sama lain, namun keduanya mereferensikan tabel dasar yang sudah ada (`orders`, `payments`, `users`).
- Urutan eksekusi kronologis yang telah ditetapkan Laravel sudah tepat dan konsisten.

---

## D. Analisis Keamanan Rollback

### 1. Evaluasi Method `down()` pada `2026_11_19_000001` (`payment_proofs`)
```php
public function down(): void
{
    throw new \RuntimeException('Rollback ditolak agar riwayat bukti pembayaran tidak terhapus.');
}
```
- **Analisis Kritis:** Method `down()` secara sengaja melempar `RuntimeException`.
- **Implikasi Bisnis & Teknis:**
  - Ini adalah aturan integritas finansial/audit yang disengaja agar bukti transfer perbankan/QRIS tidak terhapus secara tidak sengaja melalui perintah `migrate:rollback` atau `migrate:reset`.
  - **Dampak Operasional:** Jika suatu saat dijalankan perintah `php artisan migrate:rollback`, eksekusi rollback akan **berhenti dan memunculkan error penolakan** ketika mencapai file migrasi ini.
  - Tindakan ini melindungi data audit finansial, namun pengembang harus mengetahui bahwa rollback otomatis melalui Laravel CLI tidak diizinkan untuk tabel ini.

### 2. Evaluasi Method `down()` pada `2026_11_20_000001` (Cancellations & Refunds)
```php
public function down(): void
{
    Schema::dropIfExists('app_notifications');
    Schema::dropIfExists('refund_conflict_notes');
    Schema::dropIfExists('refund_executions');
    Schema::dropIfExists('refund_events');
    Schema::dropIfExists('refund_request_items');
    Schema::dropIfExists('refund_requests');
    Schema::dropIfExists('order_item_cancellations');
    Schema::table('orders', fn (Blueprint $table) => $table->dropConstrainedForeignId('created_by'));
    Schema::table('order_details', fn (Blueprint $table) => $table->dropColumn(['started_at', 'ready_at']));
}
```
- **Analisis:** Method `down()` ditulis dengan urutan penghapusan yang benar (anak tabel dihapus sebelum induk tabel untuk menghindari `foreign key violation`).
- Kolom tambahan pada `orders` dan `order_details` dibuang dengan aman.

---

## E. Analisis Transaksional & Toleransi Kegagalan (PostgreSQL Transactional DDL)

- Database yang digunakan adalah **PostgreSQL 16.14**.
- PostgreSQL mendukung penuh **Transactional DDL** (Data Definition Language di dalam transaksi).
- Laravel secara default membungkus setiap file migrasi di dalam transaksi database (`BEGIN ... COMMIT`).
- **Skenario Kegagalan:** Jika salah satu statement SQL gagal (misalnya sintaks error atau timeout), PostgreSQL akan melakukan `ROLLBACK` penuh untuk transaksi migrasi tersebut. Skema database **tidak akan menggantung** dalam status setengah jadi.

---

## F. Rekomendasi Keselamatan

### **STATUS REKOMENDASI: SAFE TO MIGRATE**

**Alasan Teknis:**
1. Tidak ada operasi `DROP TABLE`, `DROP COLUMN`, atau `MODIFY COLUMN` pada data atau tabel lama di method `up()`.
2. Seluruh 3 kolom baru yang ditambahkan pada tabel eksisting (`orders.created_by`, `order_details.started_at`, `order_details.ready_at`) berstatus `nullable()`, sehingga 14 transaksi pesanan yang ada saat ini tidak terdampak.
3. Seluruh foreign key terhubung ke tabel dan kolom primary key yang valid.
4. Skema baru 100% selaras dengan 8 Model Eloquent yang saat ini mengalami error `relation does not exist` di source code.

---

## G. Prosedur Backup yang Disarankan (Pre-Migration Safety)

Meskipun migrasi berstatus non-destruktif dan sangat aman, sebagai standar operasional prosedur (SOP) senior database engineer, pencadangan database `cafe_exito` disarankan sebelum mengeksekusi migrasi.

### Lokasi PostgreSQL Binary di Lingkungan Sistem:
- Path binary: `E:\Program Files\PostgreSQL\16\bin\pg_dump.exe`

### Perintah Backup Skema & Data (Opsional / Standar Pencegahan):
Jalankan melalui PowerShell:
```powershell
& "E:\Program Files\PostgreSQL\16\bin\pg_dump.exe" -h 127.0.0.1 -p 5432 -U cafe_exito_user -F c -b -v -f "E:\laragon\www\cafe-exito\cafe_exito_backup_pre_task2_migrate.dump" cafe_exito
```

---

## H. Perintah yang Harus Dijalankan Setelah Mendapat Persetujuan

Setelah user menyetujui eksekusi migrasi, perintah yang dijalankan adalah:

```powershell
cd E:\laragon\www\cafe-exito\backend
E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate
```

*Catatan: DILARANG menggunakan opsi `--fresh`, `--seed`, atau `migrate:refresh`.*

---

## I. Kesimpulan Akhir

Kedua migrasi pending (`2026_11_19_000001` dan `2026_11_20_000001`) telah diaudit secara baris-per-baris dan dinyatakan **AMAN UNTUK DIEKSEKUSI (SAFE TO MIGRATE)** pada database aktif `cafe_exito`.

Migrasi ini tidak akan menghapus, mengubah, atau merusak 14 transaksi yang sedang ada, melainkan justru melengkapi tabel dan kolom yang dibutuhkan oleh sistem untuk mendukung alur Pembayaran QRIS dan Pembatalan/Refund.

Laporan ini bersifat read-only. Tidak ada migrasi atau perubahan database yang dijalankan sebelum instruksi eksplisit dari pengguna.
