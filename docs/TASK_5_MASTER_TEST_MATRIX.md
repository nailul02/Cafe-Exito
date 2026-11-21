# Task 5 Master Test Matrix — Unique Scenario Reconciliation

Tanggal: 2026-11-21. Original Task 5 IDs dipertahankan. Retest checks yang sama dipetakan kembali ke ID lama dan tidak dihitung ulang. Delapan baris `T53/T54` adalah pemeriksaan unik tambahan.

| ID | Modul | Skenario | Preconditions | Test type | Expected result | Actual result | Status | Evidence | Notes |
|---|---|---|---|---|---|---|---|---|---|
| CON-01 | Connectivity | Health API | Backend lokal | Live HTTP | 200 JSON | 200 | PASS | T5.2 report | Read-only |
| CON-02 | Connectivity | Vite API base mapping | API base configured | Axios/Vite SSR | Reaches API v1 | Request reached backend | PASS | T5 report | No browser |
| CON-03 | Connectivity | CORS frontend GET | Origin localhost:5173 | Live HTTP | Origin allowed | 200, ACAO matches | PASS | T5.2 report | Not browser-enforced |
| AUTH-01 | Auth | `/auth/me` no bearer | None | Live HTTP | 401 normalized | 401 `UNAUTHENTICATED` | PASS | Auth retest | Read-only |
| AUTH-02 | Auth | Fake bearer | Throwaway string | Live HTTP/Axios | 401 and local token cleared | 401; shim cleared | PASS | Auth retest | No DB token |
| AUTH-03 | Auth | Cashier dashboard no bearer | None | Live HTTP | 401, private data hidden | 401 | PASS | Original matrix | No actor |
| AUTH-04 | Auth | Valid login | Approved credentials | Feature/HTTP | Token/user returned | No safe credential | BLOCKED | Auth report | No password guess |
| AUTH-05 | Auth | Valid session/profile | Valid token | HTTP | Profile restored | No token fixture | BLOCKED | Auth report | No DB token read |
| AUTH-06 | Auth | Logout/revocation | Valid token | Feature/HTTP | Token revoked | Would delete token | BLOCKED | Auth report | No revocation |
| RBAC-01 | Role | Owner access | Owner actor | HTTP/Feature | Authorized by policy | Static only | BLOCKED | Role report | No token |
| RBAC-02 | Role | Manager table/menu/report | Manager actor | HTTP/Feature | Manager scope respected | Static only | BLOCKED | Role report | No token |
| RBAC-03 | Role | Cashier POS/payment | Cashier actor | HTTP/Feature | Cashier scope respected | Static only | BLOCKED | Role report | Mutation omitted |
| RBAC-04 | Role | Barista station | Barista actor | HTTP/Feature | Barista station only | Static only | BLOCKED | Station report | No token |
| RBAC-05 | Role | Kitchen station | Kitchen actor | HTTP/Feature | Kitchen station only | Static only | BLOCKED | Station report | No token |
| RBAC-06 | Role | Customer valid QR/public token | Valid secret token | HTTP | Scoped public data | No valid token used | BLOCKED | Customer report | No token extraction |
| RBAC-07 | Role | Tables without bearer | None | Live HTTP/Axios | 401 | 401 | PASS | Original matrix | Not an RBAC positive |
| RBAC-08 | Role | Sales report without bearer | None | Live HTTP/Axios | 401 | 401 | PASS | Original matrix | Not an RBAC positive |
| CUST-01 | Customer | Public menu | None | Live HTTP/Axios | Data array mapped | 200, 11 items | PASS | Customer report | Read-only |
| CUST-02 | Customer | Payment info | None | Live HTTP/Axios | Object mapped | 200; expected keys | PASS | Customer report | Read-only |
| CUST-03 | Customer | Invalid table QR | Fake token | Live HTTP/Axios | 404 normalized | 404 `NOT_FOUND` | PASS | Customer report | Negative |
| CUST-04 | Customer | Valid table QR | Valid QR token | HTTP | Table returned | No safe token | BLOCKED | Customer report | — |
| CUST-05 | Customer | Valid order status | Public order token | HTTP | Status mapped | No safe token | BLOCKED | Customer report | — |
| CUST-06 | Customer | Invalid public order token | Fake token | Live HTTP/Axios | 404 normalized | 404 `NOT_FOUND` | PASS | Customer report | Negative |
| PAY-01 | Payment | 3 MiB upload boundary | Test file | Static/Feature | >3 MiB rejected | UI + `max:3072` checked statically | PASS | Payment report | No runtime file upload |
| PAY-02 | Payment | QRIS proof/manual verify/no method switch | Source contract | Static | Cashier verifies manually; QRIS cannot become cash | Guard present; POS gap fixed | PASS | Payment retest | Static only; runtime separate below |
| PAY-03 | Payment | Upload proof | Safe order fixture | Feature/HTTP | Proof stored privately | Not run | BLOCKED | Payment report | File + DB write |
| PAY-04 | Payment | Valid proof/order status | Valid order token | HTTP | Status correct | No token | BLOCKED | Payment report | — |
| PAY-05 | Payment | Cashier verify/reject | Cashier + payment | Feature/HTTP | Correct transitions | Not run | BLOCKED | Payment report | DB mutation |
| ORD-01 | Orders | POS/create order | Cashier actor | Feature/HTTP | Order/payment created | Not run | BLOCKED | Order report | DB mutation |
| ORD-02 | Orders | Orders list no bearer | None | Live HTTP/Axios | 401 | 401 | PASS | Order report | Read-only |
| ORD-03 | Orders | Authenticated detail | Valid staff actor | HTTP | Scoped detail | No actor | BLOCKED | Order report | — |
| ORD-04 | Orders | Cancellation/refund | Valid actor + order | Feature | Status/audit consistent | Not run | BLOCKED | Refund report | DB mutation |
| ST-01 | Station | Tickets no bearer | None | Live HTTP/Axios | 401 | 401 | PASS | Station report | Read-only |
| ST-02 | Station | Barista/Kitchen queue | Valid station actors | HTTP | Rows separated by station | Static only | BLOCKED | Station report | No tokens |
| ST-03 | Station | `updated_since` behavior | Valid station actor | HTTP | Delta/422 correct | Not run | BLOCKED | Station report | — |
| SEC-01 | Security | 401 response | No token | Live HTTP/Axios | JSON error, no private data | 401 mapped | PASS | Security report | — |
| SEC-02 | Security | Cross-role 403 | Valid restricted actor | HTTP | 403 | No safe actor | BLOCKED | Security report | — |
| SEC-03 | Security | 404 mapping | Invalid public token | Live HTTP/Axios | `NOT_FOUND` | 404 normalized | PASS | Security report | — |
| SEC-04 | Security | 405 method mismatch | POST health | Live HTTP | 405 JSON | 405 `METHOD_NOT_ALLOWED` | PASS | Security report | No route mutation |
| SEC-05 | Security | Mutating endpoint 422 validation | Safe Feature fixture | Feature | Field errors, no writes | Not run | NOT TESTED | Security report | Cart preview 422 is different scope |
| SEC-06 | Security | 429 rate limit | Controlled requests | HTTP | Rate limited | No burst sent | NOT TESTED | Security report | — |
| SEC-07 | Security | 500 non-disclosure | Fault fixture | HTTP | Safe error | No fault injection | NOT TESTED | Security report | — |
| DB-01 | Data | Active row counts before/after | Read-only DB | CLI | Same counts | All monitored counts same | PASS | Data integrity report | Counts, not row hashes |
| REG-01 | Frontend | Vitest + production build | Existing dependencies | Mock/build | Tests/build pass | 26/26; build 159 modules | PASS | Retest report | Mock is not E2E |
| T53-CART-01 | Customer | Valid cart preview | Active public menu | Live Axios POST, read-only service | Preview returned without persistence | 1 item, numeric total, no issues | PASS | T5.2 customer report | `OrderService::preview` |
| T53-CART-02 | Customer | Missing cart items | Empty invalid payload | Live Axios validation | 422 before store | 422 `items` | PASS | T5.2 matrix | No storage route |
| T53-CART-03 | Customer | Quantity zero | Invalid item | Live Axios validation | 422 before store | 422 `items.0.qty` | PASS | T5.2 matrix | No storage route |
| T53-CORS-01 | Connectivity | CORS preflight | Origin localhost:5173 | Live HTTP OPTIONS | Preflight allowed | 204; origin header present | PASS | T5.2 security report | — |
| T53-UNIT-01 | Regression | Pure backend Unit suite | Native PHPUnit tests | PHPUnit Unit | No DB writes | 24 tests/161 assertions pass | PASS | T5.4 regression report | No Feature tests |
| T53-DB-01 | Test safety | Current PHPUnit target resolution | Process vars unset; no config cache | Static config simulation | Resolves test DB | `cafe_exito_test` | PASS | T5.3 DB report | Future process override remains possible |
| T54-AUTH-01 | Auth | Non-string login limiter regression | Auth Feature fixture | Feature | 422, no limiter type error | Test added, not run | BLOCKED | Auth report | `RefreshDatabase` |
| T54-PAY-01 | Payment | QRIS POS→cash service regression | POS fixture | Feature | Reject with no state change | Test added, not run | BLOCKED | Payment report | `RefreshDatabase` |
| T54-PINT-01 | Quality | PHP formatting on touched files | Dependencies installed | Static linter | No formatting changes needed | Pint reports style fixers in service | FAIL | Regression report | Test import order fixed; no broad rewrite |

## Final pre-push audit reconciliation — 21 November 2026

The legacy 53-scenario snapshot above predates the guarded full-suite run. Updated execution evidence: Feature 75/75 (632 assertions) on `cafe_exito_test`; Unit 24/24 (161 assertions); Vitest 26/26; production build passed; test database guard and active DB before/after counts passed. `T54-AUTH-01` and `T54-PAY-01` are now PASS. A targeted report cancellation regression passed as part of the Feature suite. The targeted Pint finding is now PASS on the 10-file relevant PHP set. Working-tree whitespace check passes; cached whitespace findings remain in the unchanged index pending user review and manual staging refresh. Playwright login rendering smoke passed using system Chrome, while full browser workflows, viewport/accessibility, HTTP 429/500, and Figma comparison remain untested. Overall Task 5 remains BLOCKED.

## Rekap unique

**53 skenario unik:** 27 PASS (3 berupa static/config evidence), 1 FAIL (formatting check), 22 BLOCKED, 3 NOT TESTED. Test references yang sama dari 44-case Task 5 matrix dan 48-case Task 5.2 retest tidak dihitung dua kali.
