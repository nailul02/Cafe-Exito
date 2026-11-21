# Task 5.2 — Data Integrity Retest

Database aktif `cafe_exito` dibaca dengan `php artisan db:show --counts` sebelum dan sesudah retest. Jumlah seluruh tabel kontrol sama.

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

Kontrol tambahan juga sama: tables 6, ingredients 1, suppliers 1, purchases/purchase_details/goods_receipts/goods_receipt_details 0, attendances 1, attendance_events 2. Ini adalah perbandingan jumlah, bukan hash setiap row. Tidak ada migration/seeder/PHPUnit dijalankan. `.env` dan `phpunit.xml` tidak diedit. File kode yang berubah hanya `backend/app/Services/PaymentService.php` dan `backend/tests/Feature/CashierPosTest.php` untuk guard QRIS→tunai dan regression; tidak mengubah data.
