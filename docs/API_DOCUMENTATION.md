# Kontrak API (v1)

Basis: `{APP_URL}/api/v1`. Respons API JSON, kecuali endpoint foto absensi yang mengirim file gambar. **Kontrak ini mengikat frontend dan backend**: perubahan bentuk data wajib diperbarui di sini, di `backend/app/Http/Resources`, dan di `frontend/src/mocks`. Spesifikasi formal: [`docs/api/openapi.yaml`](api/openapi.yaml).

> Status final: endpoint inventori, stok, supplier, purchasing/receiving, dan laporan penggunaan stok tidak aktif. POS dan QR meja aktif. Payment tunai POS dibuat `pending` dan baru tercatat setelah Kasir memverifikasi pembayaran; QRIS juga tetap `pending` sampai verifikasi manual Kasir. Pada QR Self-Order, bukti diunggah pelanggan dan dapat dilihat Kasir secara privat; upload maupun penolakan bukti tidak melunasi order. Pembayaran dapat dicatat setelah order `selesai` tanpa mengubah status order. Pembatalan kuantitas oleh Customer/Owner dan workflow refund mengikuti AUD-01 pada addendum 20 November di bawah.

## Konvensi
- Header: `Accept: application/json`; staf `Authorization: Bearer <token>`; pelanggan opsional `X-Table-Token`; opsional `X-Request-Id` (diteruskan di respons).
- **Sukses:** `{ "success": true, "message": "…", "data": …, "meta": { "request_id", "server_time", "pagination"? } }`
- **Gagal:** `{ "success": false, "message": "…", "error": { "code": "…", "details": … }, "errors"?: { "field": ["…"] }, "meta": {…} }`
- HTTP: 200/201 sukses · 401 belum login · 403 tanpa izin · 404 tidak ada · 405 · 409 konflik status · 422 validasi · 429 batas laju · 500 galat server (pesan generik). Tidak pernah ada redirect atau HTML.
- Uang: **bilangan bulat rupiah**. Waktu: ISO 8601 `+07:00`. Enum: `{ "code", "label" }` (frontend memakai `code`).
- Daftar bisa dipaginasi: `?page=&per_page=` (maks 100) → `meta.pagination`. Polling: `?filter[status]=`, `?updated_since=<ISO8601>` (tiket).
- Kode galat: `VALIDATION_FAILED`, `UNAUTHENTICATED`, `FORBIDDEN`, `NOT_FOUND`, `METHOD_NOT_ALLOWED`, `TOO_MANY_REQUESTS`, `TABLE_INVALID`, `MENU_UNAVAILABLE`, `INVALID_STATE_TRANSITION`, `PAYMENT_ALREADY_PROCESSED`, `AMOUNT_TOO_LOW`, `ORDER_NOT_CANCELLABLE`, `SERVER_ERROR`.

## Endpoint aktif

Daftar berikut dicocokkan ulang dengan `php artisan route:list --path=api/v1 --json` pada 20 November 2026: **81 operasi HTTP** (HEAD otomatis untuk GET tidak dihitung sebagai operasi terpisah). Request dan status POS di bawah mengikuti FormRequest/service saat ini; bentuk resource response yang belum dijabarkan rinci tetap memakai envelope umum.

- **Publik:** `GET /health`.
- **Auth:** `POST /auth/login`, `POST /auth/logout`, `GET /auth/me`, `PUT /auth/me`, `POST /auth/change-password`.
- **Pelanggan dan QR meja:** `GET /customer/tables/{qrToken}`, `GET /customer/menus`, `POST /customer/cart/validate`, `GET /customer/payment-info`, `POST /customer/orders`, `GET /customer/orders/{publicToken}`, `POST /customer/orders/{publicToken}/cancel`, `POST /customer/orders/{publicToken}/payment-proof` (multipart bukti QRIS). Middleware menerima `X-Table-Token` opsional. Pembatalan item pelanggan memerlukan `detail_id`, `qty`, dan `reason`; `Idempotency-Key` opsional.
- **Absensi:** `GET /attendance/today-status`, `POST /attendance/check-in`, `POST /attendance/check-out`, `GET /attendance/my-history` (`attendance.record`); `GET /attendance/list` (`attendance.view_all`); `PATCH /attendance/{attendance}` (`attendance.report`, Owner); `GET /attendance/events/{event}/photo` (`attendance.record` atau `attendance.report`, data sendiri atau pemegang izin laporan). Request check-in/out multipart membutuhkan foto, latitude, longitude; detail field ada pada OpenAPI. Backend menguji koordinat dalam radius 30 meter dari pusat pada konfigurasi server; bila koordinat pusat belum disetel/valid, pencatatan ditolak (503, fail-closed).
- **Kasir/POS:** `GET /cashier/dashboard` (`dashboard.cashier`); `GET /cashier/tables` dan `POST /cashier/pos/orders` (`orders.create_pos`); `GET /cashier/orders`, `GET /cashier/orders/{order}`, `POST /cashier/orders/{order}/complete`, `POST /cashier/payments/{payment}/verify`, `POST /cashier/payments/{payment}/reject` (`payments.verify`); `GET /cashier/transactions/{order}/receipt` (`payments.verify` atau `transactions.view`). POS mendukung dine-in/takeaway dan tunai/QRIS. Request POS memerlukan `client_request_id`, `service_type`, `payment_method`, dan `items[{menu_id,qty}]`; `table_number` dan `customer_name` opsional. `amount_received` **bukan** field pembuatan order; nominal tunai dicatat melalui `POST /cashier/payments/{payment}/verify` setelah uang diterima. Kedua metode membuat payment `pending`; QRIS diproses masuk antrean setelah verifikasi manual Kasir, sedangkan order tunai dapat diproses ketika payment masih pending. Filter `filter[status]=menunggu_pembayaran` pada `GET /cashier/orders` mencari payment pending, termasuk order yang sudah selesai. Verifikasi payment setelah order selesai mengubah payment menjadi `verified` dan mengisi `paid_at` tanpa mengubah status order `selesai`. `GET /cashier/payments/{payment}/proof` hanya untuk Kasir berizin.
- **Stasiun:** untuk `{station}` = `barista` atau `kitchen`: `GET /station/{station}/dashboard`, `GET /station/{station}/tickets`, `GET /station/{station}/tickets/{ticket}` (`station:read`); `POST /station/{station}/tickets/{ticket}/start` dan `/complete` (`station:act`). Akses dibatasi ke station akun.
- **Order dan transaksi:** `GET /orders`, `GET /orders/{order}` (`orders.view`); `POST /orders/{order}/cancel` dan `POST /orders/{order}/items/cancel` (`orders.cancel`, Owner); `GET /transactions` (`transactions.view`). Pembatalan Owner maupun Customer harus memberi alasan. Pembatalan baris/kuantitas berbayar membuat refund request untuk keputusan Owner; pengembalian uang tetap tindakan terpisah dan tidak otomatis dilakukan.
- **Admin:** `GET /admin/menu-categories`; `GET|POST /admin/menus`; `GET|PUT|DELETE /admin/menus/{menu}`; `PATCH /admin/menus/{menu}/availability`; `GET|POST /admin/tables`; `PUT|DELETE /admin/tables/{table}`; `POST /admin/tables/{table}/regenerate-qr`; `GET|POST /admin/users`; `PUT /admin/users/{user}`; `PATCH /admin/users/{user}/status`; `POST /admin/users/{user}/reset-password`; `GET|PUT /admin/users/{user}/permissions`. Recipe tidak termasuk API aktif karena inventory operasional dipensiunkan.
- **Dashboard/laporan:** `GET /dashboard/owner`, `GET /dashboard/manager`, `GET /reports/sales`, `GET /audit-logs`, `GET /staff/alerts`.

**Tidak aktif dan di luar kebutuhan:** tidak ada route untuk inventori/bahan/stok, supplier, pembelian/penerimaan, atau penggunaan stok. `GET /reports/stock-usage` juga tidak aktif. Kode controller lama yang mungkin masih ada tidak membuat endpoint aktif tanpa route.

**Pembatalan dan refund:** detail lifecycle terkini mengikuti addendum AUD-01 di bawah. Pernyataan historis pada versi lebih lama bahwa pembatalan pelanggan tidak memiliki route, refund belum tersedia, atau pembatalan hanya Owner tidak berlaku lagi. Tidak ada field `restore_stock` pada kontrak pembatalan aktif.

## Bentuk data utama
- **User:** `{id, name, username, email, role{code,label}, station|null, is_active, permissions[]}`. Role produksi adalah `barista_kitchen`; `station` menentukan hak antrean `barista` atau `kitchen`.
- **Menu (staf):** `{id, sku, name, price, cost_price, category{id,name}, display_group, station, is_available, is_orderable, unavailable_reason, image_url}`; ketersediaan berdasarkan pengaturan manual, bukan stok; **pelanggan** tidak menerima data internal staf.
- **Order:** `{id, public_token, order_number, display_number, table{id,number}, service_type, order_source, customer_name, status{}, ordered_at, paid_at, completed_at, total, items[{id,menu_id,name,qty,unit_price,subtotal,station}], payment{id,method,status,amount,amount_received,change,paid_at,verified_by,transaction_number}, tickets[{id,station,status,started_at,ready_at}]}`. Customer response omits internal id, verifier, and transaction number.
- **Ticket:** `{id, order_id, order_number, display_number, table_number, station, status{}, ordered_at, started_at, ready_at, handled_by, order_status{}, items[{name,qty}]}` (hanya item stasiun itu).
- **Sales report:** `{period, from, to, totals{orders,revenue,average_order,items,refunds_approved,refunds_completed}, by_method[], by_category[], series[{label,amount,orders}], top_menus[], cashier_recap[], avg_service_seconds}`. Refund totals are integer rupiah amounts.

Respons API memakai envelope `success/message/data/meta`; `data` pada OpenAPI sengaja dibiarkan tanpa properti rinci untuk resource yang belum dicocokkan penuh ke Resource class. Jangan menganggap endpoint atau payload yang tidak disebut sebagai sudah didukung.

## Addendum: pembatalan item dan refund (20 November 2026)

Addendum ini menggantikan pernyataan lebih lama pada dokumen ini yang menyebut pembatalan pelanggan belum memiliki route atau refund hanya terkait pembatalan Owner.

- `POST /customer/orders/{publicToken}/cancel` menerima `detail_id`, `qty`, `reason` (wajib, teks bebas), serta header `Idempotency-Key` opsional. Pembatalan ditolak jika detail sudah mulai diproses atau kuantitas tersisa tidak cukup.
- `POST /orders/{order}/items/cancel` memakai `orders.cancel` (Owner) dan payload yang sama. Route pembatalan order lama tetap menerima alasan dan membatalkan kuantitas yang masih memenuhi syarat.
- Mulai produksi item: `POST /station/{station}/tickets/{ticket}/items/{detail}/start`. Seluruh kuantitas tersisa pada baris detail itu dianggap mulai.
- Refund: `GET|POST /refunds` (Cashier melihat/mengajukan manual; Owner melihat), `POST /refunds/{refund}/decision`, `/withdraw`, `/revoke`, `/execute`, `GET /refunds/{refund}/proof`. Pengajuan manual tidak digabung dengan request pembatalan otomatis; jika payment sudah memiliki request aktif berstatus `requested`, pengajuan manual mendapat 409.
- Konflik kepentingan: `POST /refunds/{refund}/conflict-notes`, `POST /refund-conflict-notes/{note}/verify`. Notifikasi in-app: `GET /notifications`, `POST /notifications/{notification}/read`.
- Cashier memakai permission `refund.view`, `refund.request`, `refund.execute`; Owner memakai `refund.review`; Manager hanya `refund.conflict.document`. Catatan Manager dimulai tanpa verifikasi dan wajib diverifikasi Owner; catatan yang dibuat Owner langsung terverifikasi. Manager tidak dapat memverifikasi catatan, memutuskan refund, atau mengakses bukti transfer.
- Pembatalan pending mengurangi payment amount/order total tersisa. Pembatalan payment verified membuat request refund (digabung selama status `requested`). Payment menjadi `refunded` hanya setelah refund kumulatif yang selesai mencapai jumlah payment.
- Bukti bank transfer disimpan pada disk `private`; akses melalui route bukti dibatasi Owner/Cashier. Scheduler `refunds:notify-deadlines` mengirim notifikasi 24/48 jam jika scheduler Laravel berjalan.
- Tiap item response menyertakan `qty_cancelled`, `qty_remaining`, `started_at`, `ready_at`, dan riwayat cancellation termasuk status refund terkait.

## Addendum: bukti QRIS pelanggan (20 November 2026)

- `POST /customer/orders/{publicToken}/payment-proof` menerima multipart `proof`: JPEG/PNG/WEBP valid maksimal 3 MB. Endpoint hanya menerima order QR Self-Order yang menunggu pembayaran; bukti identik ditolak.
- Upload disimpan privat dan berstatus `pending`; tidak mengubah status pembayaran atau memasukkan order ke produksi. Kasir melihat metadata di detail order dan file melalui `GET /cashier/payments/{payment}/proof` (`payments.verify`).
- Verifikasi Kasir menandai bukti sebagai `verified`, mengubah payment menjadi `verified`, lalu membuat tiket. Penolakan mewajibkan alasan, menandai bukti sebagai `rejected`, dan membiarkan order menunggu pembayaran agar pelanggan dapat mengunggah bukti baru. POS QRIS tetap diverifikasi langsung Kasir tanpa bukti upload pelanggan.
- QRIS Self-Order tidak dapat diubah menjadi tunai saat verifikasi. Kasir tidak dapat memverifikasi atau menolak pembayaran Self-Order sebelum ada bukti berstatus `pending`; percobaan tersebut mengembalikan 422 dan status payment tetap `pending`.
- Migration `2026_11_19_000001_create_payment_proofs_table` membuat riwayat bukti; berkas berada di disk privat dan tidak punya akses URL publik.

## Addendum: pembayaran POS (20 November 2026)

- `POST /cashier/pos/orders` tidak meminta `amount_received`; response `201` memakai envelope sukses dan resource Order dengan `payment.status.code=pending`, `payment.amount_received=null`, serta status order awal `menunggu_pembayaran`.
- Pada `payment_method=tunai`, tiket dapat dibuat dan diproses sementara payment tetap `pending`. Setelah menerima uang, Kasir mencatatnya dengan `POST /cashier/payments/{payment}/verify`, dengan `amount_received` opsional (jika tidak dikirim dianggap pas sebesar tagihan). Nominal tercatat dan status payment berubah menjadi `verified`.
- Pada `payment_method=qris`, `payment.status.code` dan status order awal tetap `pending`; tiket antrean dibuat setelah Kasir melakukan verifikasi manual melalui endpoint yang sama. Tidak ada gateway pembayaran.
- `POST /cashier/orders/{order}/complete` hanya menyelesaikan status order setelah semua tiket selesai; tidak mengubah status payment. Jika payment masih pending, Kasir tetap dapat memverifikasinya kemudian. Payment menjadi `verified`, sementara order tetap `selesai`.
- `GET /cashier/orders?filter[status]=menunggu_pembayaran` memfilter berdasarkan status payment, sehingga order selesai yang belum dibayar tetap tampil untuk dicatat pembayarannya.
