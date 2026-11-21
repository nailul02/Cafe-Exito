# Task 5.3 — Test Risk Classification

| Kategori | Test / aktivitas | Keputusan |
|---|---|---|
| A — aman tanpa DB writes | `tests/Unit/DomainRulesTest.php` | 4 native PHPUnit unit tests; lulus dalam suite 24 tests |
| A — aman tanpa DB writes | `tests/Unit/PermissionMatrixTest.php` | 7 pure config/enum tests; lulus |
| A — aman tanpa DB writes | `tests/Unit/StatusTransitionsTest.php` | 7 pure enum tests; lulus |
| A — aman tanpa DB writes | `tests/Unit/StockLevelTest.php` | 7 pure enum/calculation tests; lulus |
| A — aman | PHP syntax lint, static inspection, Vitest/jsdom, production build | Dijalan­kan; tidak membuka DB aplikasi |
| A — read-only runtime | GET/OPTIONS public/protected probes, CORS, invalid token, cart preview/validation | Pernah dijalankan pada retest; cart preview tidak menyimpan; tidak menjalankan transaksi |
| B — DB test + approval | Semua 11 class di `tests/Feature`: Attendance, Authorization, Auth, CashierPos, CorsAndReport, CustomerPartialCancellation, EmployeeAccountManagement, OrderFlow, Purchasing, QrisPaymentProof, RefundWorkflow | Semuanya mewarisi `ApiTestCase` → `RefreshDatabase`; berpotensi `migrate:fresh`, factory writes, transaksi/status/audit, atau test upload. User telah menyetujui (APPROVE FEATURE pada `cafe_exito_test`); eksekusi belum dijalankan dan wajib preflight keselamatan target DB |
| B — lebih rinci | Auth, user management, POS, order/cancellation, purchasing, attendance, payment proof, refund, authorization, reports/CORS | Tulis ke DB test; sebagian memakai `Storage::fake`. Disetujui untuk `cafe_exito_test`; belum dijalankan |
| C — runner baru | Browser E2E/responsive/accessibility | Runner/binary belum terpasang. User telah menyetujui instalasi (APPROVE PLAYWRIGHT untuk `@playwright/test@1.65.1` + Chromium); instalasi dan pengujian belum dijalankan |
| D — credential valid | Login, `/auth/me`, logout/revoke, role Owner/Manager/Kasir/Barista/Kitchen, valid customer tokens | Credential aman tidak tersedia; jangan gunakan token 23 row aktif |
| E — konfigurasi | Mengubah PHPUnit env `force`, menambah DB guard, atau mengubah `.env` | Tidak dilakukan; perubahan config perlu persetujuan terpisah |
| E — data/schema | Migration/seeder/RefreshDatabase, perubahan schema, transaksi positif | Hanya pada DB test dan setelah approval eksplisit; tidak pada `cafe_exito` |

Tidak ada `DatabaseMigrations`, `DatabaseTransactions`, `DatabaseTruncation`, atau seeder trait ditemukan selain `RefreshDatabase` pada base Feature test. Feature code memakai factory, dan ada upload/storage fakes di test QRIS, Attendance, dan Refund.
