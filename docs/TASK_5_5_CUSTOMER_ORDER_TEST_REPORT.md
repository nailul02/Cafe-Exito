# Task 5.5 — Customer Order Test Report

| Flow | Result | Test type / limits |
|---|---|---|
| Customer menus/categories | PASS | Live HTTP/Axios; 11 menu rows returned |
| Invalid table QR | PASS negative | Live HTTP/Axios 404 |
| Valid/disabled table QR | BLOCKED | No approved valid token fixture; no token read from active data |
| Add/change/remove cart UI | PASS only in existing mock tests | Vitest mock, not live browser/API end-to-end |
| Cart preview | PASS | Live Axios POST to read-only `OrderService::preview()` |
| Missing items / qty 0 | PASS negative | Live 422 validation; no storage path reached |
| Server price computation / tampered price | BLOCKED | Positive order endpoint not invoked; price source inspected statically |
| Checkout and order create | BLOCKED | Mutates orders/payments/audit/tickets |
| Public order status valid | BLOCKED | Requires secret token not taken from historical order |
| Invalid public order token | PASS negative | Live HTTP/Axios 404 |

Tidak ada self-order yang dibuat. QRIS dipilih pada checkout sebagai pending payment; customer proof diperlukan dan Cashier melakukan verifikasi manual. Cash self-order tetap pending untuk dibayar ke Cashier sesuai kontrak source; runtime mutation belum diuji.
