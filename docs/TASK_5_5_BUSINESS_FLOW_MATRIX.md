# Task 5.5 — Business Flow Matrix

| ID | Modul | Alur | Test type | Status | Evidence / blocker |
|---|---|---|---|---|---|
| T55-C01 | Customer | Menu/payment info | Live HTTP + Axios | PASS | Menu 200 (11 rows), payment info 200 |
| T55-C02 | Customer | Invalid QR/order tokens | Live HTTP + Axios | PASS | 404 `NOT_FOUND` |
| T55-C03 | Customer | Cart preview | Live HTTP + Axios | PASS | Read-only preview result |
| T55-C04 | Customer | Cart validation invalid | Live HTTP + Axios | PASS | 422 missing items/qty 0 |
| T55-C05 | Customer | Valid QR checkout/order lookup | HTTP | BLOCKED | No safe token; create mutates DB |
| T55-P01 | QRIS | Manual verify/proof contract | Static | PASS (STATIC) | Request/service/UI contracts inspected |
| T55-P02 | QRIS | Upload / size / MIME / ownership | HTTP/Feature | BLOCKED | Writes storage and DB |
| T55-P03 | Payment | QRIS→cash prevention | Static + Feature | PARTIAL; runtime BLOCKED | Service guard fixed; Feature test not run |
| T55-P04 | Cash | Delayed cash settlement | Feature | BLOCKED | Status mutation |
| T55-P05 | POS | Create, price, receipt, idempotency | Feature | BLOCKED | Writes orders/payments |
| T55-S01 | Station | Station isolation and queue | HTTP/Feature | BLOCKED | Valid role fixture unavailable |
| T55-S02 | Station | Ticket state changes | Feature | BLOCKED | Writes ticket/history |
| T55-R01 | Cancellation | Item/order cancellation | Feature | BLOCKED | Writes transaction and audit data |
| T55-R02 | Refund | Request and Owner decision | Feature | BLOCKED | Requires DB fixture/Owner actor |
| T55-R03 | Refund | Amount/idempotency/execution | Feature | BLOCKED | Writes records/status/storage |

Tidak ada business flow positif yang memerlukan write dijalankan pada `cafe_exito`.
