# Task 5 — Remaining Blockers

## Final pre-push audit addendum — 21 November 2026

This addendum supersedes resolved items in the checkpoint list below. The forced PHPUnit environment and pre-suite DB identity guard verified `cafe_exito_test`; 75/75 Feature tests passed and active DB row counts remained unchanged. Playwright is installed and a login-screen smoke check passed with system Chrome; bundled Chromium download timed out.

Remaining: browser customer/staff workflows and responsive/accessibility checks; no Figma reference available; the staged index still contains the earlier documentation whitespace because it was not changed; working-tree whitespace checks and targeted Pint checks pass; HTTP 429/500 scenarios remain untested. Task 5 remains BLOCKED and Task 6 stays deferred.

1. **Feature test target safeguard:** current process resolves PHPUnit to `cafe_exito_test`, but `phpunit.xml` env has force=false and can be overridden by an inherited `DB_DATABASE`. Preflight verification of the explicit child-process test DB target remains strictly mandatory prior to execution to protect active database `cafe_exito` from accidental `migrate:fresh`.
2. **Feature suite execution pending:** user has explicitly granted approval (APPROVE FEATURE) to run the Feature suite against `cafe_exito_test`. However, all 11 Feature classes inherit `RefreshDatabase` and trigger `migrate:fresh`/fixtures; the suite has NOT YET BEEN RUN and remains blocked pending verified database target safety preflight.
3. **Authentication:** valid login/profile/logout/token revocation and 5 staff role actors are untested; credentials/fixtures unavailable.
4. **Business writes:** checkout, QRIS upload/verify, cash settlement, POS, tickets, cancellation, and refund need approved DB/storage fixture. No such flow was run on active data.
5. **QRIS fix proof:** service guard and regression test exist but test is not executed.
6. **Browser E2E execution pending:** user has explicitly granted approval (APPROVE PLAYWRIGHT) for installing `@playwright/test@1.65.1` and Chromium. However, package installation, browser binary download, and browser E2E test execution have NOT YET BEEN PERFORMED; responsive, Figma visual, accessibility keyboard/focus/contrast checks remain untested.
7. **Figma reference:** no design file/asset was found in repository inventory.
8. **HTTP errors:** 429 and 500 not tested; 403 requires authenticated insufficient-permission actor.
9. **Formatting check:** Pint `--test` fails on style/import ordering in already-dirty files. No broad auto-format was applied.

Task 5 remains BLOCKED. Do not begin Task 6 System Testing until blockers 1–6 are resolved or explicitly accepted in a scoped test plan.
