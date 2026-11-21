# Task 5.2 — Order Integration Retest

| Flow | Hasil |
|---|---|
| Cart preview valid | PASS — request read-only menghasilkan 1 line, numeric total, tanpa issue |
| Cart payload tidak valid | PASS negatif — 422 untuk items kosong/hilang dan quantity nol |
| Public order token invalid | PASS negatif — 404 `NOT_FOUND` melalui Axios |
| Public order status valid | BLOCKED — tidak menggunakan token dari transaksi historis |
| Self-order QRIS/tunai positif | BLOCKED — membuat order/payment |
| POS Kasir | BLOCKED — membuat order/payment |
| Pembatalan order/item | BLOCKED — mengubah status/audit/refund |

Tidak ada mutation order yang dikirim. API error standar dinormalisasi menjadi `ApiError` dengan status/code/field errors.
