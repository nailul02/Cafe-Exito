# Task 5 Role Integration Report

## Actual and static evidence

- Protected backend GET routes tested without bearer returned 401; no role-specific information leaked in the observed Axios result.
- Backend route declarations attach `EnsurePermission` or `EnsureStationAccess` to active endpoints.
- `config/permissions.php` grants `tables.manage` to Manager; active table APIs are `/admin/tables`.
- Barista and Kitchen share role `barista_kitchen`; station middleware uses `{station}` and `users.station` as documented in the route and permissions config.
- Frontend fixture tests distinguish Barista and Kitchen queue selection; this is UI mock evidence only.

| Role | Authenticated backend integration |
|---|---|
| Owner | BLOCKED — no known credentials or verified token |
| Manager | BLOCKED — no known credentials or verified token |
| Kasir | BLOCKED — no known credentials or verified token |
| Barista | BLOCKED — no verified token/station identity |
| Kitchen | BLOCKED — no verified token/station identity |
| Customer | BLOCKED for valid QR/public-order token; general public menu/payment-info GETs passed separately |

No 403 permission denial was obtained because that requires an authenticated actor. Do not interpret hidden frontend menu items or fixture roles as backend authorization proof. Manager-vs-Owner refund authority and station separation were not integration-tested with credentials.
