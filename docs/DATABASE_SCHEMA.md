# Database Schema — QRIS Proof, Pembatalan Item, dan Refund

Dokumen ini mendokumentasikan skema database tambahan yang diperkenalkan pada rilis ini:
1. `2026_11_19_000001_create_payment_proofs_table.php` (Bukti QRIS)
2. `2026_11_20_000001_add_customer_item_cancellation_and_refunds.php` (Pembatalan Item & Refund)

---

## 1. Tabel Bukti Pembayaran QRIS (`payment_proofs`)

- `id`: BIGSERIAL PRIMARY KEY
- `payment_id`: FK ke `payments.id` (RESTRICT on delete)
- `path`: VARCHAR(500) — lokasi berkas di disk privat `local` (`payment-proofs/`)
- `mime_type`: VARCHAR(100) — `image/jpeg`, `image/png`, `image/webp`
- `size_bytes`: INTEGER UNSIGNED — ukuran berkas maksimal 3 MB (3.145.728 bytes)
- `sha256`: CHAR(64) UNIQUE — hash konten berkas untuk integritas dan mencegah unggahan duplikat
- `status`: VARCHAR(12) CHECK (`pending`, `verified`, `rejected`), default `pending`
- `reject_reason`: VARCHAR(255) NULL — alasan penolakan bukti oleh kasir
- `reviewed_by`: FK NULLABLE ke `users.id` — kasir yang memverifikasi atau menolak bukti
- `uploaded_at`: TIMESTAMPTZ — waktu pengunggahan berkas oleh pelanggan
- `reviewed_at`: TIMESTAMPTZ NULLABLE — waktu review bukti oleh kasir
- `created_at`, `updated_at`: TIMESTAMPTZ
- Indeks: `(payment_id, id)`

---

## 2. Perubahan pada Tabel Eksisting

- `orders`:
  - `created_by`: BIGINT NULLABLE FK ke `users.id` (ON DELETE SET NULL) — mencatat staf pembuat pesanan (kasir POS atau owner/manager).
- `order_details`:
  - `started_at`: TIMESTAMPTZ NULLABLE — waktu detail menu mulai diproses oleh stasiun.
  - `ready_at`: TIMESTAMPTZ NULLABLE — waktu detail menu selesai diproses oleh stasiun.

---

## 3. Tabel Pembatalan Item (`order_item_cancellations`)

- `id`: BIGSERIAL PRIMARY KEY
- `order_id`: FK ke `orders.id`
- `order_detail_id`: FK ke `order_details.id`
- `qty`: INTEGER (CHECK `qty > 0`) — jumlah porsi yang dibatalkan
- `unit_price`: INTEGER — harga satuan saat pemesanan (untuk referensi refund historis)
- `subtotal`: INTEGER — nominal total porsi yang dibatalkan (`qty * unit_price`)
- `source`: VARCHAR(50) — `customer_cancellation` atau `owner_cancellation`
- `actor_type`: VARCHAR(30) — `customer`, `owner`, `manager`
- `actor_id`: BIGINT NULLABLE FK ke `users.id`
- `reason`: TEXT — alasan pembatalan wajib
- `idempotency_key`: VARCHAR(100) NULLABLE
- `created_at`, `updated_at`: TIMESTAMPTZ
- Indeks: UNIQUE `(order_id, idempotency_key)` WHERE `idempotency_key IS NOT NULL`

---

## 4. Tabel Pengajuan Refund (`refund_requests`)

- `id`: BIGSERIAL PRIMARY KEY
- `payment_id`: FK ke `payments.id`
- `order_id`: FK ke `orders.id`
- `source`: VARCHAR(50) — `cancellation_auto`, `manual_request`, `owner_cancellation`
- `status`: VARCHAR(30) — `requested`, `approved`, `rejected`, `withdrawn`, `approval_revoked`, `completed`, `conflict_of_interest`
- `amount`: INTEGER (CHECK `amount > 0`) — total nominal refund
- `reason`: TEXT — alasan pengajuan
- `decision_reason`: TEXT NULLABLE — alasan persetujuan / penolakan
- `requested_by`: BIGINT NULLABLE FK ke `users.id`
- `decided_by`: BIGINT NULLABLE FK ke `users.id`
- `withdrawn_by`: BIGINT NULLABLE FK ke `users.id`
- `revoked_by`: BIGINT NULLABLE FK ke `users.id`
- `decided_at`: TIMESTAMPTZ NULLABLE
- `withdrawn_at`: TIMESTAMPTZ NULLABLE
- `revoked_at`: TIMESTAMPTZ NULLABLE
- `decision_late_notified_at`: TIMESTAMPTZ NULLABLE (notifikasi tenggat 24 jam)
- `execution_late_notified_at`: TIMESTAMPTZ NULLABLE (notifikasi tenggat 48 jam)
- `created_at`, `updated_at`: TIMESTAMPTZ
- Indeks: UNIQUE `(payment_id)` WHERE `status = 'requested'` (hanya boleh 1 request aktif per payment)

---

## 5. Tabel Item Refund (`refund_request_items`)

- `id`: BIGSERIAL PRIMARY KEY
- `refund_request_id`: FK ke `refund_requests.id` (CASCADE on delete)
- `order_item_cancellation_id`: FK ke `order_item_cancellations.id` (RESTRICT on delete)
- `qty`: INTEGER
- `amount`: INTEGER
- `created_at`, `updated_at`: TIMESTAMPTZ

---

## 6. Tabel Eksekusi Refund (`refund_executions`)

- `id`: BIGSERIAL PRIMARY KEY
- `refund_request_id`: FK UNIQUE ke `refund_requests.id`
- `executed_by`: FK ke `users.id` (kasir yang mengeksekusi)
- `method`: VARCHAR(20) — `cash`, `bank_transfer`
- `amount`: INTEGER
- `reference_number`: VARCHAR(100) NULLABLE (wajib untuk transfer bank)
- `proof_path`: VARCHAR(500) NULLABLE — berkas bukti transfer privat (`refund-proofs/`)
- `executed_at`: TIMESTAMPTZ
- `created_at`, `updated_at`: TIMESTAMPTZ

---

## 7. Tabel Catatan Konflik Kepentingan (`refund_conflict_notes`)

- `id`: BIGSERIAL PRIMARY KEY
- `refund_request_id`: FK ke `refund_requests.id`
- `recorded_by`: FK ke `users.id` (Manager atau Owner)
- `handled_by`: VARCHAR(100) — pihak yang menangani resolusi
- `resolved_on`: DATE — tanggal penyelesaian
- `outcome`: VARCHAR(255) — hasil resolusi
- `reason`: TEXT — dasar pertimbangan
- `amount`: INTEGER — nominal yang disepakati
- `proof_path`: VARCHAR(500) NULLABLE — berkas pendukung privat
- `verified_by`: BIGINT NULLABLE FK ke `users.id` (hanya role Owner)
- `verified_at`: TIMESTAMPTZ NULLABLE
- `created_at`, `updated_at`: TIMESTAMPTZ

---

## 8. Tabel Notifikasi Dalam Aplikasi (`app_notifications`)

- `id`: BIGSERIAL PRIMARY KEY
- `user_id`: FK ke `users.id`
- `type`: VARCHAR(100) — tipe notifikasi (`refund.requested`, `refund.decision_overdue`, dll.)
- `data`: JSONB — metadata pesanan dan refund
- `read_at`: TIMESTAMPTZ NULLABLE
- `created_at`, `updated_at`: TIMESTAMPTZ
- Indeks: `(user_id, read_at)`

---

## 9. Rollback & Mitigasi Risiko

- Migration `payment_proofs` menolak rollback otomatis (`down()` throw RuntimeException) agar berkas riwayat audit pembayaran pelanggan terlindungi.
- Migration `2026_11_20_000001` menyediakan `down()` yang menghapus tabel pendukung refund. Pada production, backup database PostgreSQL lengkap (`pg_dump`) wajib diambil sebelum menjalankan migrasi baru.
