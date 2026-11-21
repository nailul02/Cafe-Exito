# Integration Testing Inventory — Task 5

Tanggal: 21 November 2026. Backend target dari konfigurasi: Laravel `/api/v1`, PostgreSQL 16 database `cafe_exito`. Test aktual menggunakan GET/HEAD, satu POST pada URI GET-only untuk memverifikasi 405, serta frontend Axios melalui Vite SSR dengan `VITE_USE_MOCK=false` dan base URL eksplisit `http://localhost:8000/api/v1`. Tidak membaca atau mengubah `.env`.

## Tooling dan batas

- Backend aktif: 79 route (38 GET/HEAD, 41 mutasi). Route inventory/purchasing: 0; `/admin/tables`: 5 method-route aktif.
- Frontend: React/Vite/Axios. Contoh env menetapkan `VITE_USE_MOCK=true`; integration harness memaksakan false tanpa mengubah file. Nilai efektif `.env` aplikasi tidak diperiksa.
- Vitest/jsdom tersedia, tetapi tidak ada Playwright/Cypress/WebDriver. Browser CUA unavailable, sehingga browser-level UI/CORS enforcement tidak dilihat.
- Autentikasi positif tidak dicoba: kredensial tidak tersedia dan 23 token historis masih UNRESOLVED. Hanya token fiktif `task5-invalid-token` dipakai untuk negative GET; tidak ada token database yang dibaca.
- Mutasi bisnis tidak dilakukan. Tidak ada order, proof, refund, ticket, akun, atau upload yang dibuat.

## Endpoint yang benar-benar dipakai

| Request | Hasil aktual |
|---|---|
| `GET /api/v1/health` | 200 JSON envelope sukses |
| `GET /api/v1/customer/menus` via Axios `customerApi.menus()` | 200, 11 menu dipetakan sebagai array `data` |
| `GET /api/v1/customer/payment-info` via Axios | 200, object berisi `cafe_name`, `qris_image_url`, `instructions` |
| `GET /api/v1/auth/me` tanpa token | 401 `UNAUTHENTICATED` |
| `GET /api/v1/auth/me` dengan token fiktif | 401; interceptor menghapus token sesi fiktif |
| `GET /api/v1/admin/tables` tanpa token | 401 `UNAUTHENTICATED` |
| `GET /api/v1/cashier/dashboard` tanpa token | 401 `UNAUTHENTICATED` |
| `GET /api/v1/reports/sales` tanpa token | 401 `UNAUTHENTICATED` |
| `GET /api/v1/station/barista/tickets` tanpa token | 401 `UNAUTHENTICATED` |
| `GET /api/v1/customer/tables/task5-invalid-qr` | 404 `NOT_FOUND` |
| `GET /api/v1/customer/orders/task5-invalid-public-token` via Axios | 404 `NOT_FOUND` |
| `POST /api/v1/health` (path terdaftar GET-only) | 405 JSON; tidak ada route/controller POST untuk URI ini |
| GET `/health` dengan Origin `http://localhost:5173` | 200, `Access-Control-Allow-Origin` sesuai origin frontend |

Response Axios telah diuji pada implementasi service aktual melalui loader Vite SSR; browser page tidak berjalan. Body bisnis pribadi tidak dicetak.

## Matriks database sebelum/sesudah

`php artisan db:show --counts` dijalankan sebelum dan setelah request/test. Kedua snapshot identik untuk seluruh tabel yang tercantum.

| Tabel | Sebelum | Sesudah |
|---|---:|---:|
| users | 5 | 5 |
| orders | 14 | 14 |
| payments | 14 | 14 |
| order_details | 15 | 15 |
| audit_logs | 12 | 12 |
| personal_access_tokens | 23 | 23 |
| payment_proofs | 0 | 0 |
| order_tickets | 0 | 0 |
| order_status_histories | 0 | 0 |
| order_item_cancellations | 0 | 0 |
| refund_requests | 0 | 0 |
| refund_request_items | 0 | 0 |
| refund_events | 0 | 0 |
| refund_executions | 0 | 0 |
| refund_conflict_notes | 0 | 0 |
| stock_movements | 0 | 0 |
| tables | 6 | 6 |
| ingredients | 1 | 1 |
| suppliers | 1 | 1 |
| purchases / purchase_details / goods_receipts / goods_receipt_details | 0 | 0 |
| attendances / attendance_events | 1 / 2 | 1 / 2 |

Hitungan membuktikan count tidak berubah selama cakupan ini; bukan hash per-row audit. Isi token tidak dibaca.
