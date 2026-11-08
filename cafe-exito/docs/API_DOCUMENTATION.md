# Kontrak API (v1)

Basis: `{APP_URL}/api/v1`. Semua respons JSON. **Kontrak ini mengikat frontend dan backend**: perubahan bentuk data wajib diperbarui di sini, di `backend/app/Http/Resources`, dan di `frontend/src/mocks`. (OpenAPI formal belum dibuat: lihat IMPLEMENTATION_STATUS.)

## Konvensi
- Header: `Accept: application/json`; staf `Authorization: Bearer <token>`; pelanggan opsional `X-Table-Token`; opsional `X-Request-Id` (diteruskan di respons).
- **Sukses:** `{ "success": true, "message": "…", "data": …, "meta": { "request_id", "server_time", "pagination"? } }`
- **Gagal:** `{ "success": false, "message": "…", "error": { "code": "…", "details": … }, "errors"?: { "field": ["…"] }, "meta": {…} }`
- HTTP: 200/201 sukses · 401 belum login · 403 tanpa izin · 404 tidak ada · 405 · 409 konflik status/stok · 422 validasi · 429 batas laju · 500 galat server (pesan generik). Tidak pernah ada redirect atau HTML.
- Uang: **bilangan bulat rupiah**. Jumlah bahan: **string desimal** (`"1800.000"`). Waktu: ISO 8601 `+07:00`. Enum: `{ "code", "label" }` (frontend memakai `code`).
- Daftar bisa dipaginasi: `?page=&per_page=` (maks 100) → `meta.pagination`. Polling: `?filter[status]=`, `?updated_since=<ISO8601>` (tiket).
- Kode galat: `VALIDATION_FAILED`, `UNAUTHENTICATED`, `FORBIDDEN`, `NOT_FOUND`, `METHOD_NOT_ALLOWED`, `TOO_MANY_REQUESTS`, `TABLE_INVALID`, `MENU_UNAVAILABLE`, `INSUFFICIENT_STOCK`, `INVALID_STATE_TRANSITION`, `PAYMENT_ALREADY_PROCESSED`, `AMOUNT_TOO_LOW`, `ORDER_NOT_CANCELLABLE`, `PURCHASE_NOT_EDITABLE`, `OVER_RECEIPT`, `RECEIPT_ALREADY_CONFIRMED`, `SERVER_ERROR`.

## Endpoint (76 rute). "Izin" = kunci di `config/permissions.php`
**Umum:** `GET /health` (publik).

**Auth:** `POST /auth/login` {login, password} → {token, expires_at, user} (throttle 5/mnt) · `POST /auth/logout` · `GET /auth/me` · `PUT /auth/me` {name?, email?} · `POST /auth/change-password` {current_password, password}.

**Pelanggan (tanpa login):** `GET /customer/tables/{qr_token}` · `GET /customer/menus` · `POST /customer/cart/validate` {items:[{menu_id, qty}]} · `GET /customer/payment-info` · `POST /customer/orders` {table_number, payment_method: tunai|qris, items, client_request_id?} · `GET /customer/orders/{public_token}` · `POST /customer/orders/{public_token}/cancel` (hanya `menunggu_pembayaran`).

**Kasir:** `GET /cashier/dashboard` (dashboard.cashier) · `GET /cashier/orders` · `GET /cashier/orders/{id}` · `POST /cashier/payments/{id}/verify` {method?, amount_received?} · `POST /cashier/payments/{id}/reject` {reason?} (payments.verify) · `GET /cashier/transactions/{order_id}/receipt`.

**Barista/Kitchen:** `GET /station/{barista|kitchen}/dashboard|tickets|tickets/{id}` (akses stasiun; stasiun lain = 404) · `POST …/tickets/{id}/start|complete` (butuh `queue.<stasiun>`).

**Pesanan (Owner/Manager):** `GET /orders`, `GET /orders/{id}` (orders.view) · `POST /orders/{id}/cancel` {reason, restore_stock?} (orders.cancel) · `GET /transactions` (transactions.view).

**Admin:** `GET /admin/menu-categories` · `GET|POST /admin/menus`, `GET|PUT|DELETE /admin/menus/{id}`, `PATCH /admin/menus/{id}/availability`, `GET|PUT /admin/menus/{id}/recipe` · `GET|POST /admin/tables`, `PUT|DELETE /admin/tables/{id}`, `POST /admin/tables/{id}/regenerate-qr` · `GET|POST /admin/users`, `PUT /admin/users/{id}`, `PATCH /admin/users/{id}/status`, `POST /admin/users/{id}/reset-password`, `GET|PUT /admin/users/{id}/permissions`.

**Inventori:** `GET|POST /inventory/ingredients`, `PUT|DELETE /inventory/ingredients/{id}`, `GET /inventory/summary`, `GET /inventory/stock-movements`, `POST /inventory/stock-adjustments` {ingredient_id, quantity_change, note}.

**Pembelian:** `GET|POST /purchasing/suppliers`, `PUT|DELETE …/{id}` · `GET|POST /purchasing/purchases`, `GET|PUT …/{id}`, `POST …/{id}/place|cancel` · `POST /purchasing/purchases/{id}/receipts` {details:[{purchase_detail_id, qty_received}], note?} · `GET|PUT /purchasing/receipts/{id}` · `POST /purchasing/receipts/{id}/confirm` (**menambah stok; sekali saja**).

**Laporan:** `GET /dashboard/owner`, `GET /dashboard/manager` · `GET /reports/sales?period=daily|weekly|monthly&from=&to=&granularity=hour|day` · `GET /reports/stock-usage` · `GET /audit-logs` · `GET /staff/alerts`.

## Bentuk data utama
- **User:** `{id, name, username, email, role{code,label}, is_active, permissions[]}`
- **Menu (staf):** `{id, sku, name, price, cost_price, category{id,name}, display_group, station, is_available, is_orderable, unavailable_reason, has_recipe, max_portions, image_url}`; **pelanggan** tanpa sku/HPP/stok/porsi.
- **Order:** `{id, public_token, order_number, display_number, table{id,number}, status{}, ordered_at, paid_at, completed_at, total, items[{id,menu_id,name,qty,unit_price,subtotal,station}], payment{id,method,status,amount,amount_received,change,paid_at,verified_by,transaction_number}, tickets[{id,station,status,started_at,ready_at}]}` (versi pelanggan tanpa `id`, kasir, dan nomor transaksi).
- **Ticket:** `{id, order_id, order_number, display_number, table_number, station, status{}, ordered_at, started_at, ready_at, handled_by, order_status{}, items[{name,qty}]}` (hanya item stasiun itu).
- **Ingredient:** `{id, name, category, unit, stock, min_stock, stock_percent, level{}, is_active}`
- **Purchase:** `{id, purchase_number, supplier{}, status{}, ordered_at, note, total, details[{id,ingredient{},qty_ordered,qty_received,qty_outstanding,unit_price}], receipts[{id,receipt_number,status{},received_at,note,details[],confirmed_by,confirmed_at}]}`
- **Laporan penjualan:** `{period, from, to, totals{orders,revenue,average_order,items}, by_method[], by_category[], series[{label,amount,orders}], top_menus[], cashier_recap[], avg_service_seconds}`
