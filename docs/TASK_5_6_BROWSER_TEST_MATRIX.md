# Task 5.6 — Browser Test Matrix

| ID | Area | Scenario | Test type | Expected | Actual | Status | Notes |
|---|---|---|---|---|---|---|---|
| T56-01 | Runner | Playwright/Cypress/Puppeteer/WebDriver discovery | Static package/config/CLI inspection | Runner installed | Chrome/Edge installed; no runner/config/CLI | BLOCKED | Install approved (APPROVE PLAYWRIGHT); not yet installed |
| T56-02 | Customer | QR, menu, cart, checkout, status | Browser E2E | Flow correct | Tidak dijalankan | BLOCKED | DB/token fixture unavailable |
| T56-03 | Cashier | Login, orders, proof, verify, cash, receipt | Browser E2E | Flow and role gates correct | Tidak dijalankan | BLOCKED | Requires auth and mutations |
| T56-04 | Barista | Login, queue, process/complete | Browser E2E | Barista queue only | Tidak dijalankan | BLOCKED | Requires auth and ticket mutation |
| T56-05 | Kitchen | Login, queue, process/complete | Browser E2E | Kitchen queue only | Tidak dijalankan | BLOCKED | Requires auth and ticket mutation |
| T56-06 | Manager | Dashboard, menu, table/QR, reports | Browser E2E | Manager permissions respected | Tidak dijalankan | BLOCKED | Requires valid actor |
| T56-07 | Owner | Dashboard, staff, refund decision | Browser E2E | Owner-only actions respected | Tidak dijalankan | BLOCKED | Requires DB fixture |
| T56-08 | Responsive | 320×568 | Browser | No clipped/overflow critical UI | Tidak dijalankan | BLOCKED | Runner unavailable |
| T56-09 | Responsive | 375×812 | Browser | Same | Tidak dijalankan | BLOCKED | Runner unavailable |
| T56-10 | Responsive | 430×932 | Browser | Same | Tidak dijalankan | BLOCKED | Runner unavailable |
| T56-11 | Responsive | 768×1024 | Browser | Same | Tidak dijalankan | BLOCKED | Runner unavailable |
| T56-12 | Responsive | 1366×768 | Browser | Same | Tidak dijalankan | BLOCKED | Runner unavailable |
| T56-13 | Responsive | 1920×1080 | Browser | Same | Tidak dijalankan | BLOCKED | Runner unavailable |
| T56-14 | Visual | Compare with Cafe Éxito Figma | Browser + design reference | Match documented reference | Reference not found | BLOCKED | No measurements invented |
| T56-15 | Accessibility | Keyboard/focus/forms/errors | Browser/manual | Basic keyboard access | Tidak dijalankan | NOT TESTED | No browser host |
| T56-16 | Accessibility | Labels/alt/semantics/contrast/touch target | Browser/manual | Basic accessible controls | Tidak dijalankan | NOT TESTED | No systematic audit |

No browser package, browser binary, or configuration was installed or created.
