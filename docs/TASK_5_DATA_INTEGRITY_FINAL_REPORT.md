# Task 5 — Data Integrity Final Report

Database aktif `cafe_exito` sebelum dan sesudah seluruh pekerjaan: PostgreSQL 16.14, `127.0.0.1:5432`, schema `public`, role `cafe_exito_user`, 41 tables, 1.63 MB. Snapshot menggunakan `php artisan db:show --counts` read-only.

| Tabel | Awal aktual | Akhir | Delta |
|---|---:|---:|---:|
| users | 5 | 5 | 0 |
| orders | 14 | 14 | 0 |
| payments | 14 | 14 | 0 |
| order_details | 15 | 15 | 0 |
| audit_logs | 12 | 12 | 0 |
| personal_access_tokens | 23 | 23 | 0 |
| payment_proofs | 0 | 0 | 0 |
| order_tickets | 0 | 0 | 0 |
| order_status_histories | 0 | 0 | 0 |
| order_item_cancellations | 0 | 0 | 0 |
| refund_requests | 0 | 0 | 0 |
| refund_request_items | 0 | 0 | 0 |
| refund_events | 0 | 0 | 0 |
| refund_executions | 0 | 0 | 0 |
| refund_conflict_notes | 0 | 0 | 0 |
| stock_movements | 0 | 0 | 0 |

Counts tambahan sama: tables 6, ingredients 1, suppliers 1, purchases/purchase_details/goods_receipts/goods_receipt_details 0, attendances 1, attendance_events 2. Tidak ada akun/token/order/data dibuat atau diubah. Tidak ada migration/seeder/PHPUnit Feature dijalankan. `.env` dan `phpunit.xml` tidak diedit. Ini count evidence, bukan row hashes.
