# Final Client Validation UAT Checklist

Use a non-production environment with representative test accounts and data. Never run these scenarios against production unless explicitly planned and backed up. Record tester, date, account role, result, and evidence for each item.

*Status UAT saat ini (21 November 2026):* UAT belum dijalankan dan belum ada skenario yang dinyatakan lulus. User telah memberikan persetujuan eksplisit untuk Feature Test pada `cafe_exito_test` dan instalasi Playwright, namun pengujian tersebut belum dieksekusi. Task 5 tetap BLOCKED dan Task 6 ditunda. Seluruh checklist di bawah ini tetap berstatus terbuka (untested).

## Before UAT

- [ ] Resolve attendance report visibility and provide cafe geofence coordinates.
- [ ] Confirm test database is isolated and contains no production customer data.
- [ ] Prepare Owner, Manager, Cashier, Barista, and Kitchen accounts with correct stations.
- [ ] Confirm the static QRIS image/account used for the test is the cafe's approved one.
- [ ] Confirm camera/GPS permissions and a device physically inside and outside the 30m boundary.

## Ordering and payments

- [ ] Scan each table QR and verify the displayed/recorded table identity.
- [ ] Attempt to substitute another table identity; API rejects the mismatch.
- [ ] Create dine-in and takeaway orders through their authorized paths.
- [ ] Verify only Cashier can use POS; Manager is denied by backend.
- [ ] Create a cash order; it enters production while payment remains unpaid.
- [ ] Verify cashier sees unpaid cash orders and can explicitly record settlement once.
- [ ] Verify order completion does not automatically settle cash.
- [ ] Upload QRIS proof; verify cashier can inspect it and accept/reject with auditable result.
- [ ] Verify QRIS Self-Order cannot be verified or rejected without pending proof and cannot be switched to cash during cashier verification.
- [ ] Verify POS QRIS can be manually verified without customer proof upload.
- [ ] Verify unverified or rejected QRIS orders do not enter production; verified ones do.
- [ ] Verify duplicate settlement and duplicate verification are rejected safely.

## Production and cancellation/refund

- [ ] Confirm food appears in Kitchen and drinks in Barista queues for a mixed order.
- [ ] Verify station accounts see their queues and cannot change overall order status.
- [ ] Verify Cashier can mark the order complete only after all required items are ready.
- [ ] Verify FIFO with no priority behavior.
- [ ] Verify Customer and Owner can cancel only unstarted item quantities and must provide a reason.
- [ ] Verify a verified-payment cancellation creates/merges a pending refund request; this does not execute a refund.
- [ ] Verify only Owner decides refund requests; Cashier executes approved refunds; Manager records conflict documentation and Owner verifies it.
- [ ] Verify cancellation is blocked after the item starts, and an identical idempotency replay does not duplicate cancellation or refund value.
- [ ] Verify transactions remain present and every request/decision is auditable.

## Attendance

- [ ] Check in with camera and GPS inside 30m; verify photo/coordinates/accuracy and server timestamp.
- [ ] Check out with a separate photo and GPS event.
- [ ] Attempt event outside 30m and confirm backend rejection.
- [ ] Deny camera or GPS permission and confirm attendance is not submitted.
- [ ] Check in after 16:00; confirm the event is accepted and marked late.
- [ ] Verify employee and Manager cannot correct attendance; Owner correction requires reason and records before/after, actor and time.
- [ ] Verify attendance visibility follows the client decision recorded in business decisions.

## Accounts, reports, and audit

- [ ] Verify Owner manages employee accounts; Manager cannot access CRUD APIs.
- [ ] Verify employees can change their own password and cannot change another account.
- [ ] Verify last Owner cannot be disabled or stripped of Owner access.
- [ ] Test daily, weekly, monthly, and custom date-range filters in Asia/Jakarta.
- [ ] Reconcile order value, cash received, QRIS received, unpaid, cancellation, refund and transaction count against test fixtures.
- [ ] Verify net profit is not fabricated when validated cost data is absent.
- [ ] Verify important transitions record actor/time/object/reason and before/after when applicable; ordinary users cannot alter audit history.

## Manual resilience and sign-off

- [ ] Simulate internet/device outage in tabletop review and follow the cafe's manual fallback process.
- [ ] Owner signs off on workflows and reports; Cashier and production staff sign off on their station workflows.
- [ ] Record any physical camera/GPS/browser coverage that could not be tested and keep release status at PASS WITH LIMITATIONS or NOT TESTED as applicable.
