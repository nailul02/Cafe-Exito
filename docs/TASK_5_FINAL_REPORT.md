# Task 5 — Final Report (Tasks 5.1–5.7)

Tanggal: 2026-11-21. **Status: BLOCKED. Task 5 is not ready to close, and Task 6 System Testing should not start yet.**

## Unique scenario reconciliation

| Status | Unique scenarios |
|---|---:|
| PASS | 27 |
| FAIL | 1 |
| BLOCKED | 22 |
| NOT TESTED | 3 |
| **Total unique** | **53** |

`TASK_5_MASTER_TEST_MATRIX.md` preserves the original Task 5 IDs and maps repeated Task 5.2 retest checks to the same scenarios rather than double-counting. The one FAIL is a Pint style check; no runtime application failure appeared in the safe slice. Static/config checks are called out as such.

## Task 5.3 — Safe environment

Current process vars `DB_DATABASE`/`DB_URL` are unset; config cache is absent; a read-only PHPUnit-environment simulation resolves to `cafe_exito_test`. The test database has 32 tables and no business rows (31 migration rows). User approval has been explicitly granted for executing the Feature suite against `cafe_exito_test` (APPROVE FEATURE). However, because PHPUnit XML `force=false` can be overridden by a future process `DB_DATABASE` and all Feature classes use `RefreshDatabase` (which calls `migrate:fresh`), database target safety preflight remains mandatory prior to execution. The Feature suite has not yet been executed. Active database `cafe_exito` must never be touched. See Task 5.3 isolation, risk, and approval reports.

## Task 5.4 — Regression and auth

- PHP Unit suite: **24/24 PASS, 161 assertions**, no DB setup.
- PHP syntax checks: pass on the service and regression test files.
- Vitest: **26/26 PASS**; Vite production build: **PASS, 159 modules**.
- Pint `--test`: **FAIL** for formatting/import-order differences in dirty PHP files; no broad rewrite.
- QRIS POS→cash guard and non-string login limiter regression test were added, but both Feature tests remain BLOCKED. No positive auth or role 403 test was run.

## Task 5.5 — Business flows

Customer read-only menu/payment/cart-preview and invalid-token/error cases passed in HTTP/Axios. Static review covers QRIS manual verification, 3 MiB proof limit, cash settlement, service-layer QRIS→cash guard, station split, and Owner-only refund permission. Positive checkout/POS/payment upload/verify/tickets/cancellation/refund flows remain BLOCKED because they write DB/storage or require actor credentials.

## Task 5.6 — Browser

No browser runner/config/executable was previously found. User has now granted explicit approval for installing `@playwright/test@1.65.1` and Chromium (APPROVE PLAYWRIGHT). However, package installation and browser test execution have not yet been performed. Browser E2E, six requested viewports, visual Figma comparison (no repository Figma asset found), and accessibility browser checks were not run and remain BLOCKED.

## Data integrity

Database `cafe_exito` counts before and after are identical: users 5, orders 14, payments 14, order_details 15, audit_logs 12, personal_access_tokens 23; proof/ticket/history/cancellation/refund/stock rows remain 0. No `.env`, `phpunit.xml`, migration, seeder, account, token, or active transaction data was changed.

## Changes made

- Code: service guard in `backend/app/Services/PaymentService.php`; Feature regression in `backend/tests/Feature/CashierPosTest.php`; malformed login regression in `backend/tests/Feature/AuthTest.php`.
- Documentation: Task 5.3–5.7 reports/matrices added. Existing Task 5 reports remain in place.
- No dependency, browser binary, or schema change.

Do not close Task 5 until approved execution proves Feature auth/role/business regressions and the browser coverage, and the formatting FAIL is handled or explicitly waived. Task 5 remains BLOCKED. No Feature tests or Playwright tests have been run yet, and no Task 6 System Testing, UAT, or deployment was started.

## Final pre-push audit addendum — 21 November 2026

The preceding status describes the earlier checkpoint and is superseded by this addendum where results differ.

- **Feature safety and execution:** `phpunit.xml` now forces the approved test connection and clears `DB_URL`. Feature suite bootstrap checks Laravel's resolved driver, host, port, database, role, schema, and PostgreSQL identity before any test using `RefreshDatabase` loads. A non-mutating preflight with process `DB_DATABASE=cafe_exito` and a `DB_URL` aimed at the active DB still passed only for `cafe_exito_test`. Full Feature result: **75/75 passed, 632 assertions**. Unit: **24/24 passed, 161 assertions**.
- **Report correction:** `ReportService` excludes `dibatalkan` orders from detail-driven payment/category/top-menu/series aggregates and average service time. Remaining quantities use `order_item_cancellations`; refunds remain separate and are not subtracted again from revenue. A Feature regression covers partial and full cancellation.
- **Additional regression fixes:** POS order creation now rejects `amount_received`; a Feature test showed it previously returned 201 despite the no-cash-at-creation rule. QRIS proof test helpers avoid duplicate pending uploads and use unique proof content. Order lifecycle tests follow final decisions: legacy stock is out of active scope and Cashier explicitly marks an order complete after station tickets finish.
- **Frontend/browser:** Vitest **26/26 passed**; production build passed (159 modules). `@playwright/test@1.65.1` installed. The Playwright Chromium download timed out; a Playwright smoke check using installed Google Chrome passed on `/login` (heading, username field, submit button). Full customer/staff browser flows, viewports, accessibility, and Figma comparison remain untested.
- **Quality/data:** PHP syntax checks passed for changed PHP files. Pint initially failed on six relevant files; formatting was applied only to those files and the recheck passed across the 10 relevant PHP files. Active DB `cafe_exito` remained at 41 tables and the same row counts before/after Feature execution (users 5, orders 14, payments 14, details 15, audit logs 12, tokens 23).
- **Git:** previous staged work was retained. No Git staging/commit/push/reset/clean/checkout/restore was run. The 53 staged whitespace findings were corrected in working-tree copies across 15 staged-only documents, preserving Markdown hard breaks. `git diff --check` passes; `git diff --cached --check` still flags the unchanged index version, so user review and manual staging are needed to update it.

**Current decision: Task 5 remains BLOCKED; do not start Task 6.** Browser workflow/viewport/accessibility evidence and review/update of staged whitespace remain. The Feature run is not a live-database or UAT pass.
