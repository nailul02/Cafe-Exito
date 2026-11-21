# Task 5.5 — Cancellation and Refund Test Report

| Skenario | Status | Batas |
|---|---|---|
| Batalkan item yang belum dimulai | BLOCKED | Menulis cancellation/order/history/refund/audit |
| Tolak pembatalan item yang sudah diproses | BLOCKED runtime | Domain guard diperiksa statis; tidak memutasi order aktif |
| Refund request | BLOCKED | Menambah refund request/event/notification/audit |
| Owner approve/reject | BLOCKED runtime | Route memakai `refund.review`, hanya Owner mendapat izin di config |
| Refund execution/idempotency/amount | BLOCKED | Menulis execution/payment status/audit dan mungkin file bukti |
| Audit trail | BLOCKED untuk alur mutatif | Tidak membuat perubahan transaksi untuk menghasilkan audit |
| Manager/Kasir mencoba keputusan | BLOCKED runtime | Perlu token terautentikasi dan fixture |

Tidak ada cancellation/refund record dibuat. Inspeksi permission/service bukan pengganti tes HTTP role.

## Final pre-push audit addendum — 21 November 2026

Guarded Feature suite completed **75/75 passed (632 assertions)** on `cafe_exito_test`. It exercised customer and Owner cancellation, started-item rejection, refund request/decision/execution, permissions, and idempotency. The report-specific regression also verifies partial quantity reduction and excludes a fully cancelled order. No records were written to active `cafe_exito`.
