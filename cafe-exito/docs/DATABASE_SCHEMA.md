# Skema Database PostgreSQL

21 migrasi di `backend/database/migrations/`. Uang `bigint` (rupiah); jumlah bahan `numeric(12,3)`; waktu `timestamptz` (zona sesi = `APP_TIMEZONE`, Asia/Jakarta). Status disimpan sebagai `varchar` + CHECK (mudah diubah, bukan tipe ENUM). **Skema ini dijalankan pada PostgreSQL 16 sandbox dan penegakan constraint-nya diuji.**

| Tabel | Fungsi | Kolom kunci | FK | Aturan DB |
|---|---|---|---|---|
| `users` | Akun staf | name, username (unik), email (unik), password, role, is_active | — | CHECK role ∈ 5 role |
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
**Perubahan terhadap ERD laporan:** lihat `BACKEND_GAP_ANALYSIS.md` butir 18 (laporan perlu direvisi).
