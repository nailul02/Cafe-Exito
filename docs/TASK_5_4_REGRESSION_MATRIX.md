# Task 5.4 — Regression Matrix

| ID | Regression | Type | Result | Notes |
|---|---|---|---|---|
| T54-01 | Domain enums/business rules | PHPUnit Unit | PASS | Termasuk metode pembayaran hanya cash/QRIS dan role/station enums |
| T54-02 | Permission matrix source | PHPUnit Unit | PASS | Role keys, allowed permission keys, Owner admin boundaries |
| T54-03 | Order/payment/ticket status transitions | PHPUnit Unit | PASS | Pure enum tests; tidak menyentuh DB |
| T54-04 | Stock level rules | PHPUnit Unit | PASS | Pure calculation |
| T54-05 | Login limiter non-string | Feature | BLOCKED | Test baru; `AuthTest` memakai `RefreshDatabase` |
| T54-06 | QRIS POS tidak berubah ke tunai | Feature | BLOCKED | Test baru; `CashierPosTest` memakai `RefreshDatabase` |
| T54-07 | SaveUserRequest validation | Feature/static | BLOCKED runtime | Factory and request tests exist in Feature suite |
| T54-08 | Station endpoint authorization | Feature/static | BLOCKED runtime | Requires authenticated DB fixture |
| T54-09 | Payment proof/verification | Feature | BLOCKED | Writes payment/proof/order/ticket state; storage fake does not isolate DB |
| T54-10 | Refund and cancellation | Feature | BLOCKED | Writes refund/audit/notification/status rows |
| T54-11 | Public route and error mapping | HTTP/Axios | PASS | Health/menu/payment info, fake QR/order, 401/404/405/422, CORS, cart preview |
| T54-12 | Frontend regression | Vitest | PASS | 26/26 using mock |
| T54-13 | Production compilation | Build | PASS | 159 modules |
| T54-14 | PHP formatting conventions | Pint `--test` | FAIL | Reports style fixers in `PaymentService.php`; test import order fixed narrowly; broad auto-format skipped |

Aggregate raw PHPUnit Unit run: 24 PASS. No Feature tests were run.
