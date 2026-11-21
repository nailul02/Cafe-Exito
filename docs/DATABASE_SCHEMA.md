# Skema Database PostgreSQL

31 migration repository di `backend/database/migrations/` (termasuk payment proof dan refund/cancellation). Uang `bigint` (rupiah); jumlah bahan `numeric(12,3)`; waktu `timestamptz` (zona sesi = `APP_TIMEZONE`, Asia/Jakarta). Status disimpan sebagai `varchar` + CHECK (mudah diubah, bukan tipe ENUM). **Skema ini dijalankan pada PostgreSQL 16 sandbox dan penegakan constraint-nya diuji.**

| Tabel | Fungsi | Kolom kunci | FK | Aturan DB |
|---|---|---|---|---|
| `users` | Akun staf | name, username (unik), email (unik), password, role, is_active | — | CHECK role ∈ 4 role |
| `personal_access_tokens` | Token Sanctum | tokenable, token, expires_at | — | — |
| `user_permissions` | Izin khusus | user_id, permission, granted_by | users | unik(user, permission) |
| `app_settings` | Pengaturan kecil | key (unik), value jsonb | — | — |
| `tables` | Meja & QR | table_number (unik), qr_token (unik), status | — | CHECK status |
| `menu_categories` | Kategori | name (unik), display_group, station, sort_order | — | CHECK group/station |
| `menus` | Katalog | sku, name, category_id, price, cost_price, station, is_available, image_path, soft delete | menu_categories | CHECK price≥0; SKU unik (parsial, belum dihapus) |
| `ingredients` | Bahan | name, category, unit, stock, min_stock, is_active, soft delete | — | **CHECK stock≥0**; nama unik tak peduli huruf (parsial) |
| `recipes` | Resep (BOM) | menu_id, ingredient_id, quantity | menus, ingredients | CHECK quantity>0; unik(menu, bahan) |
| `orders` | Pesanan | public_token (unik), order_number (unik), client_request_id (unik), table_id, status, ordered_at, paid_at, total | tables | CHECK status, total≥0 |
| `order_details` | Rincian | order_id, menu_id, menu_name, qty, unit_price, subtotal, station (snapshot) | orders, menus | CHECK qty>0 |
| `order_tickets` | Tiket per stasiun | order_id, station, status, started_at, ready_at, handled_by | orders, users | unik(order, station); CHECK status |
| `payments` | Pembayaran | order_id, method, status, amount, amount_received, transaction_number (unik), paid_at, verified_by | orders, users | CHECK method∈{tunai,qris}; **maks. 1 pending & 1 verified per pesanan** (indeks parsial) |
| `payment_proofs` | Riwayat upload bukti QRIS pelanggan | payment_id, path privat, mime_type, size_bytes, sha256 (unik), status, reject_reason, reviewed_by, uploaded_at, reviewed_at | payments, users | CHECK status pending/verified/rejected; rollback ditolak agar riwayat tidak hilang |
| `order_status_histories` | Riwayat status | order_id, order_ticket_id, station, from/to_status, user_id | orders, tickets, users | append-only |
| `suppliers` | Supplier | name, phone, address, is_active, soft delete | — | — |
| `purchases` | Pembelian | purchase_number (unik), supplier_id, ordered_by, status | suppliers, users | CHECK status |
| `purchase_details` | Rincian pembelian | purchase_id, ingredient_id, qty_ordered, qty_received, unit_price | purchases, ingredients | **CHECK 0≤qty_received≤qty_ordered**; unik(purchase, bahan) |
| `goods_receipts` | Penerimaan | receipt_number (unik), purchase_id, status, received_at, created_by, confirmed_by | purchases, users | CHECK status |
| `goods_receipt_details` | Rincian penerimaan | goods_receipt_id, purchase_detail_id, qty_received | receipts, purchase_details | CHECK qty>0; unik |
| `stock_movements` | Riwayat stok (append-only) | ingredient_id, type, quantity_change, stock_after, user_id, order_ticket_id, goods_receipt_detail_id, note | ingredients, users, tickets, receipt_details | **CHECK referensi sesuai tipe**; quantity_change≠0 |
| `audit_logs` | Jejak aksi sensitif | user_id, action, subject_type/id, changes jsonb, ip | users | — |

**Relasi inti:** menu —M:N→ bahan lewat `recipes`; pesanan 1:N rincian, 1:N tiket (maks. satu per stasiun), 1:N pembayaran (satu aktif); pembelian 1:N rincian, 1:N penerimaan; setiap perubahan stok = satu baris `stock_movements`.
**Kenapa order, pembayaran, dan transaksi dipisah:** satu pesanan dapat gagal bayar lalu diulang; verifikasi QRIS dan tunai punya siklus berbeda. "Transaksi" = pembayaran terverifikasi (`transaction_number`), tanpa tabel tersendiri.
**Perubahan terhadap ERD laporan:** lihat `audit/BACKEND_GAP_ANALYSIS.md` butir 18 (laporan perlu direvisi).

## Catatan skema dan keputusan terbaru (12 November 2026)

Skema mencakup `users.station` (`barista|kitchen`) untuk role tunggal `barista_kitchen`; permission antrean disaring menurut station. Absensi memakai `attendances` harian dan `attendance_events` dengan jenis event, waktu server, lokasi, akurasi opsional (`accuracy_meters numeric(10,2)`), metadata perangkat, dan path foto private. Tabel lama `attendance_photos` tetap dipertahankan. Migration absensi telah diterapkan pada database utama; seluruh migration berstatus Ran.

Stok pesanan dikonsumsi ketika pembayaran berhasil diverifikasi melalui transaksi database dan `StockService`, bukan saat tiket dimulai. Tiket mulai/selesai tidak melakukan konsumsi stok.

Bukti QRIS pelanggan disimpan privat di `storage/app/private/payment-proofs/`. Upload bukti tidak mengubah status payment; Kasir memverifikasi secara manual. Bukti yang ditolak tetap menjadi riwayat, dan pelanggan dapat mengunggah bukti baru pada payment pending berikutnya.

## Kondisi aktual database utama setelah rekonsiliasi � 12 November 2026

Skema target di atas telah diterapkan pada database `cafe_exito`: 33 tabel; semua 38 FK dan 41 CHECK tervalidasi. `users` berisi 5 akun. Data lama dipertahankan: 3 kategori, 10 menu, 14 orders, 15 order_details, 5 meja. Empat belas payment pending dibentuk dari order lama yang juga masih pending. Tiga tabel lama yang kosong dan ambigu dipertahankan sebagai `legacy_transactions`, `legacy_inventory_logs`, dan `legacy_attendance_photos`. Pada 13 November 2026, `accuracy_meters` ditambahkan nullable dan additive oleh `2026_11_13_000001_add_accuracy_meters_to_attendance_events`; status migration Ran pada main dan testing.

Akun Nana dan Dewi sekarang memakai role `barista_kitchen` dengan station `barista` dan `kitchen`. Runtime Laravel tetap terhubung sebagai `cafe_exito_user`; seluruh tabel dan sequence dapat diakses sesuai kebutuhan runtime. Uji login API username/email untuk lima akun berhasil. Data rinci dan batas uji operasional tercatat dalam `database/FINAL_DATABASE_AND_SYSTEM_READINESS_REPORT.md`.

## Addendum: pembatalan kuantitas dan refund parsial (20 November 2026)

Migration additive `2026_11_20_000001_add_customer_item_cancellation_and_refunds` menambahkan:

- `order_details.started_at`, `ready_at`: waktu mulai/siap per baris item; tombol mulai item menetapkan semua kuantitas tersisa pada baris tersebut.
- `order_item_cancellations`: catatan append-only berisi detail, kuantitas, rentang kuantitas, harga snapshot, sumber, tipe/pelaku, alasan, idempotency key.
- `refund_requests`, `refund_request_items`, `refund_events`, `refund_executions`: request, rincian pembatalan, jejak lifecycle, dan eksekusi pembayaran kembali.
- `refund_conflict_notes`: dokumentasi pengecualian dan verifikasi Owner.
- `app_notifications`: notifikasi in-app persisten dan status baca.

Tidak ada tabel transaksi atau item lama yang dihapus. Bukti disimpan pada private storage. Status `payments.refunded` hanya dipakai ketika nilai refund yang selesai telah mencapai nilai payment.
