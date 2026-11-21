# Task 5 — Next Phase Recommendation

**Recommendation: do not start Task 6 System Testing yet. Task 5 remains BLOCKED.**

User has granted explicit approvals:
- **APPROVE FEATURE:** Feature Test execution against database `cafe_exito_test` is approved, subject to mandatory database target safety verification.
- **APPROVE PLAYWRIGHT:** Installation of `@playwright/test@1.65.1` and Chromium is approved.

However, neither the Feature suite nor Playwright has been executed. The active database `cafe_exito` must remain strictly untouched and protected.

Final pre-push audit update (21 November 2026): the Feature suite now passes 75/75 on guarded `cafe_exito_test`; `cafe_exito` remained unchanged. Playwright package installation succeeded. Bundled Chromium download timed out, while a limited `/login` render smoke passed through installed Google Chrome. Continue with browser customer/staff workflows, viewport/accessibility checks, and review of Pint/whitespace findings. Task 5 remains BLOCKED; Task 6 should not start yet.

Recommended order of execution:

1. **Database Safety Preflight:** Perform child-process preflight verification ensuring `DB_DATABASE=cafe_exito_test` before running PHPUnit, preventing accidental override to `cafe_exito` from PHPUnit XML `force="false"`.
2. **Execute Approved Feature Suite:** Run Feature tests against `cafe_exito_test`, covering regressions for malformed login limiter input, QRIS POS→cash prevention, and business/auth flows. Confirm zero data change on `cafe_exito`.
3. **Install Playwright & Chromium:** Execute approved installation (`npm.cmd install --save-dev @playwright/test@1.65.1` and `npx.cmd playwright install chromium`), configure minimal browser specs, and execute desktop/mobile E2E flows.
4. **Reconcile Evidence:** Verify all test outputs, handle formatting check status, and resolve blockers 1–6.
5. **Evaluate Task 6 Readiness:** Only after Task 5 test evidence is fully collected and verified may Task 6 System Testing be considered.

Do not begin Task 6 System Testing until Task 5 is fully resolved and verified.
