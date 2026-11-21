# Task 5.4 — Regression Test Report

**Status:** PASS for safe Unit/frontend suites; Feature regressions BLOCKED.

| Check | Result | Bukti |
|---|---|---|
| PHP Unit suite tanpa DB | PASS — 24 tests / 161 assertions | `vendor\bin\phpunit --testsuite Unit`; 4 pure Unit classes, native PHPUnit TestCase, tanpa Laravel DB setup |
| PHP syntax | PASS | `php -l` pada `PaymentService.php`, `CashierPosTest.php`, `AuthTest.php`; tidak ada syntax error |
| Vitest regression | PASS — 26/26 | `npm.cmd test`, jsdom + mock |
| Vite production build | PASS | `npm.cmd run build`, 159 modules |
| POS QRIS→cash Feature regression | BLOCKED | Test baru ditambahkan ke `CashierPosTest`; class memakai `ApiTestCase`/`RefreshDatabase` |
| Non-string login limiter Feature regression | BLOCKED | Test baru ditambahkan ke `AuthTest`; class memakai `ApiTestCase`/`RefreshDatabase` |
| Feature tests lainnya | BLOCKED | Seluruh 11 class Feature mewarisi base DB-refresh |

## Final pre-push audit addendum — 21 November 2026

- Full Feature suite: **75/75 passed, 632 assertions** on guarded `cafe_exito_test`; malformed login limiter and QRIS POS-to-cash regressions passed.
- A new report regression verifies partial cancellation quantity and exclusion of a fully cancelled order from report aggregates.
- POS `amount_received` at order creation was unexpectedly accepted; the request now prohibits it and the Feature regression passes.
- Legacy stock tests were aligned with the final decision that inventory/stock is outside active operational scope. No stock behavior was activated.
- Pint remains **FAIL** on formatting fixers in touched/previously dirty PHP code. No broad formatting was applied.

## Static formatting check

`vendor\bin\pint --test app\Services\PaymentService.php tests\Feature\CashierPosTest.php tests\Feature\AuthTest.php` returned **FAIL**. Pint reports formatting fixers in `PaymentService.php` only. The CashierPosTest import order was corrected narrowly; no automatic formatting was applied to the service because it already contained unrelated edits and formatting it wholesale would create a broad diff.

## Bug dan regresi

- **BUG-AUTH-01:** `AppServiceProvider` memeriksa input login dengan `is_string` sebelum `mb_strtolower`; `LoginRequest` mensyaratkan string. Static inspection menunjukkan perlindungan untuk input array telah ada. Regression Feature test ditambahkan, tetapi belum dijalankan.
- **BUG-PAY-QRIS-POS:** guard service kini menolak QRIS→tunai untuk semua source order. Regression POS test memeriksa response 422, status/metode tetap, dan tidak ada audit `payment.verified`; hanya PHP lint/static review lulus. Runtime Feature hasilnya belum tersedia.
- Validasi `SaveUserRequest`, station controller, payment, permission, public route, dan response error juga memiliki Feature tests/config relevan; seluruhnya belum dieksekusi pada Task 5.4 karena gate DB.

Tidak ada PHPStan/Psalm script terpasang. Pint test-only check telah dijalankan tanpa menulis.

## Current final audit status — 21 November 2026

This note supersedes the earlier Feature/BLOCKED and Pint/FAIL results above. The guarded Feature suite passed 75/75 with 632 assertions; auth limiter, role, QRIS POS-to-cash, cancellation/refund, and report regression cases passed. Pint `--test` now passes on the 10-file relevant PHP set; formatting was applied only to the six files Pint had identified. Task 5 remains BLOCKED for browser workflow and viewport/accessibility coverage.

## Final checkpoint audit update � 21 November 2026

The guarded Feature suite passed 75/75 (632 assertions), including auth, role, QRIS POS-to-cash, cancellation/refund, and report regressions. Targeted Pint checks pass on the 10 relevant PHP files; only the six Pint-identified files were formatted. Working-tree git diff --check passes. Cached whitespace findings remain in the unchanged staged index pending user review and manual staging refresh. Browser workflow and viewport/accessibility evidence remain incomplete; Task 5 remains BLOCKED.
