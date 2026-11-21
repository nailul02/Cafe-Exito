# Task 5 — Bug Reconciliation

| ID | Severity | Root cause / finding | File | Fix | Test / result |
|---|---|---|---|---|---|
| BUG-AUTH-01 | P2 candidate | Login limiter must safely accept non-string `login`; current callback uses `is_string` before normalization, while `LoginRequest` requires string | `backend/app/Providers/AppServiceProvider.php`; regression in `AuthTest.php` | Existing guard is present; added malformed-array regression | Static review PASS; Feature test BLOCKED (RefreshDatabase) |
| BUG-PAY-QRIS-POS | P2 | Service only prohibited method change for `qr_customer`; QRIS POS could be changed to cash | `backend/app/Services/PaymentService.php` | Added service-layer QRIS→tunai rejection across order sources | PHP lint PASS; regression in `CashierPosTest.php` added; Feature execution BLOCKED |
| T5-01 | P2 blocker | Valid staff credential/token unavailable | Auth/report fixture workflow | No active token/password used | BLOCKED |
| T5-02 | P2 blocker | No browser E2E runner/host | Frontend test environment | Recommend approved Playwright install | BLOCKED |
| T5-03 | P2 blocker | Positive mutation flows require isolated DB/approval | Feature suite and business workflows | Gate documented; no active DB mutations | BLOCKED |
| T5-04 | P3 debt | Legacy Inventory/Purchasing frontend service/page references; no active backend routes | Existing frontend/docs | Left inactive, no route reactivation | Not retested; no scope change |
| T5-05 | P3 config | Frontend example defaults mock mode true; real integration environment must explicitly select API mode | `.env.example` / Vite env | No local environment changed | Open configuration action |
| QA-PINT-01 | P3 quality | Pint reports formatting differences in dirty payment service | `PaymentService.php` | Test import order fixed narrowly; no mass-format applied because the service has unrelated existing edits | FAIL static style check; no runtime impact established |

No new post-fix runtime regression was observed in the safe checks. The two new Feature regressions are not proven until approved execution.
