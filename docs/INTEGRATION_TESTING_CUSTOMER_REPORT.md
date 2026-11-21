# Task 5 Customer Integration Report

Request tests ran through the actual Vite-loaded Axios `customerApi` service where indicated. No valid QR/public order token was read from the database.

| Flow | Actual |
|---|---|
| Menu | PASS — `GET /customer/menus` returned 200; Axios mapped `data` to an array of 11 items |
| Payment info | PASS — 200; Axios mapped object keys `cafe_name`, `qris_image_url`, `instructions` |
| Invalid QR table token | PASS negative — 404 `NOT_FOUND` |
| Invalid public order token | PASS negative — 404 `NOT_FOUND` |
| Valid QR table information | BLOCKED — no safe valid QR token available |
| Existing order status | BLOCKED — public token is secret and cannot be extracted from historical transaction |
| Cart validation/order creation/status after creation | BLOCKED — would require a mutating order flow |

These results validate public read mapping and not the full customer journey. No transaction was created or changed.
