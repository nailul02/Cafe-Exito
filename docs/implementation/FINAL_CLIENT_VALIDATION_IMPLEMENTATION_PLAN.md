# Final Client Validation Implementation Plan

**Status:** Historical Phase 2 plan; it is not a current implementation status. The payment POS implementation is tracked in `CAFE_EXITO_PRE_TESTING_CHECKPOINT.md`. The cancellation/refund item below is superseded by the final AUD-01 decision in `DECISIONS_PENDING.md` (20 November 2026); do not wait for a new cancellation cutoff decision. Retain historical data and all existing worktree changes.

## Preserve

- QR self-order, table identity, takeaway, and cashier-only POS.
- Manual cash and static QRIS; station-specific Barista/Kitchen accounts and queues.
- Existing camera/GPS attendance capture, employee account controls, audit logs, and all historical database rows.
- PostgreSQL/Laravel REST API/Sanctum and separate React frontend.

## Repair

1. **Orders and payments:** model order lifecycle separately from settlement and QRIS verification. Cash orders may enter production unpaid; add explicit cashier settlement and unpaid monitoring. QRIS requires proof and cashier verification before tickets are created. Do not implicitly settle on production completion.
2. **Cancellation/refund (superseded by AUD-01):** Customer/Owner may cancel unstarted quantities with a reason. A verified-payment cancellation creates/merges a request for Owner decision; Cashier executes an approved refund. Manager documents conflicts and Owner verifies Manager documentation. Cancellation is not refund execution; preserve history.
3. **Production:** station users confirm readiness; cashier alone changes overall order state. Preserve all-items-ready aggregation and FIFO; no partial order completion.
4. **Inventory/purchasing:** remove menu/navigation/dashboard access and block operational APIs. Remove stock checks/consumption from menu availability, ordering, payment, and ticket flows. Preserve all schema and history.
5. **Attendance:** server-side Haversine/radius validation, required camera/GPS, late marking against 16:00, Owner-only correction with reason and before/after audit, and Owner-approved leave/sick/vacation records. Geofence behavior depends on authoritative cafe coordinates. Report visibility depends on resolving the G/I conflict.
6. **Reports:** arbitrary date-range query in Asia/Jakarta; separate order value, cash received, QRIS received, unpaid, cancelled, and refunded measures; explicit weekly/monthly ranges; `.xlsx` export; no net-profit figure without validated costs. Closing workflow is dependent on the unpaid-cash closing rule.
7. **Authorization/audit:** regression-test account CRUD and last Owner protection; cover all sensitive state transitions with actor, time, reason and before/after where applicable.

## Additive database changes (only after design/tests)

Potential additions, subject to schema review and isolated-database tests:

- QRIS payment proof metadata/storage reference and verification metadata.
- Explicit payment settlement/audit fields if current payment history cannot represent cash settlement cleanly.
- Cancellation/refund request and decision history, or equivalent append-only event representation.
- Attendance correction/leave records and audit metadata.
- Cafe geofence settings sourced from owner-provided coordinates.
- Daily closing record and detail snapshot only after close/unpaid behavior is specified.

Use new migrations only. Never drop inventory or purchasing tables, rewrite old migrations, wipe data, or run broad seeders. Back up before any primary-database migration; first execute and validate against the isolated test database. Apply only migrations required by reviewed implementation.

## Disable safely

- Remove inventory/purchasing links and pages from user-facing navigation/dashboard.
- Reject operational inventory/purchase API requests at backend authorization/route boundary while keeping historical read paths only if required for integrity or existing reports. Define and test any retained read-only route.
- Preserve recipes/history until dependency audit proves they can be left unused; remove stock coupling from orderability and production.

## Dependencies and sequence

1. Resolve attendance permission conflict, cafe coordinates, and unpaid-at-close behavior.
2. Write state transition tables and migration/API contracts for payments and cancellation/refund.
3. Implement service-level transitions and authorization; then controllers/resources/routes.
4. Update cashier, customer, station, owner/manager UIs and remove inventory/purchasing operations.
5. Implement attendance and report changes; implement closing only after its rule is confirmed.
6. Add isolated DB migrations and service/feature/frontend tests; review old pending rows for compatibility.
7. Update API, database schema, decisions, implementation status, and UAT documents.
8. Deploy/apply migrations to the primary database only after backup verification and isolated test pass; report exact changes.

## Test plan

- Backend: role matrix, QR table binding, order type, QRIS proof/verify/reject, unpaid cash ticket and explicit settlement, duplicate-settlement protection, Owner-only cancel/refund decisions, station readiness and cashier-only order state, historical-row compatibility, inventory APIs blocked, attendance inside/outside radius and late threshold, Owner corrections/leave decisions, report ranges/totals/export auth, audit immutability.
- Frontend: customer proof upload and payment state, cashier unpaid/payment/cancellation workflows, station queues/readiness, Owner attendance/account/report actions, Manager allowed monitoring only, inventory/purchasing absent from operational navigation, custom date and export controls.
- Run backend and frontend suites with PHP 8.3.30 and the project-supported Node runtime. Use isolated testing DB for mutations. Record counts and failures verbatim.
- Manual UAT: physical camera/GPS permissions, real 30m boundary, station handoff, proof review, cash settlement, close/export, manual outage process.

## Approval gates and current blockers

Implementation cannot safely finalize attendance reports/geofence or daily close until the audit's three questions are answered. Work on payment, order/cancel, and inventory retirement can proceed only after tests and transition design demonstrate no incompatible business decision; preserve unrelated existing modifications throughout.

*Catatan Status Testing (21 November 2026):* User telah memberikan persetujuan eksplisit untuk eksekusi Feature Test pada database pengujian `cafe_exito_test` (APPROVE FEATURE) dan instalasi Playwright + Chromium (APPROVE PLAYWRIGHT). Persetujuan ini belum mencakup eksekusi yang telah dijalankan atau dinyatakan lulus; pengamanan target database tetap wajib diverifikasi sebelum eksekusi Feature suite. Task 5 tetap berstatus BLOCKED dan Task 6 ditunda.
