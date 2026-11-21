# Status Implementasi

Diperbarui: 13 November 2026. **Perbarui berkas ini setiap selesai mengerjakan sesuatu.**

## Ringkasan jujur
| Bagian | Dibuat | Diverifikasi (benar-benar dijalankan) | BELUM diverifikasi |
|---|---|---|---|
| Frontend (React) | 24 halaman, 18 komponen/layout, service per modul, mock; beranda katalog pelanggan dengan login karyawan melalui modal tanpa perubahan URL | `npm test`: **16 tes Vitest lulus**, termasuk alur pelanggan, login Kasir/Barista/Kitchen/Manager/Owner, logout, pemulihan sesi saat refresh, error login, dan proteksi route; `npm run build` lulus | Tampilan visual belum dibandingkan piksel-per-piksel dengan Figma; belum diuji di peramban nyata/perangkat; integrasi login belum diuji terhadap backend Laravel nyata |
| Backend (Laravel) | 21 migrasi, 20 model, 13 layanan, 25 controller, 76 rute, 20 FormRequest, 13 Resource, 5 middleware, seeder, factory | Semua berkas PHP lulus `php -l`. **24 tes unit** (level stok, mesin status, matriks izin, kode galat) lulus dengan PHPUnit 9.6. Seluruh migrasi dijalankan pada **PostgreSQL 16** (via penerjemah sementara): 21 tabel, 30 constraint CHECK, indeks parsial; penegakan constraint diuji; SQL mentah laporan/penomoran diuji | **Aplikasi Laravel belum pernah dijalankan** (`composer install`/`artisan` tidak tersedia di lingkungan pembuat). **Tes fitur (6 berkas) ditulis tetapi BELUM dijalankan.** Integrasi Eloquent, middleware, Sanctum, CORS belum teruji sungguhan |
| Integrasi FE–BE | Klien Axios, token, interceptor galat, `VITE_USE_MOCK` | Kecocokan bentuk data diperiksa manual terhadap rute dan Resource | Belum pernah dijalankan terhadap backend nyata |
| Database lokal | Migrasi + seeder | Hanya di sandbox pembuat | **Belum dijalankan ke PostgreSQL pengguna** (sesuai instruksi: tunggu konfirmasi koneksi) |

Kemungkinan galat yang paling mungkin muncul saat pertama kali dijalankan: nama paket/versi Composer, penyesuaian kecil Laravel 13, dan tes fitur yang perlu disesuaikan. Itu normal; perbaiki sambil menjalankan `composer test`.

## Langkah pertama yang disarankan (urut)
1. Ikuti `docs/DEVELOPMENT_GUIDE.md`: `composer install`, isi `.env`, buat database `cafe_exito` dan `cafe_exito_test`.
2. `composer test:unit` (harus lulus tanpa database).
3. Konfirmasi koneksi PostgreSQL, lalu `php artisan migrate --seed`.
4. `composer test` (tes fitur); perbaiki kegagalan yang muncul.
5. Jalankan backend, set `VITE_USE_MOCK=false` di frontend, uji alur penuh dengan akun seeder.

## Modul
| Modul | Backend | Frontend | Catatan |
|---|---|---|---|
| Autentikasi, 5 role staf + izin | Selesai (belum dijalankan) | Selesai (mock) | Login email atau username (K-T1) |
| Pelanggan: QR, meja, katalog, keranjang, bayar, status | Selesai | 7 halaman selesai | QR umum vs per meja PENDING (K-T2) |
| Kasir: dashboard, pesanan, verifikasi, struk | Selesai | Selesai | Cetak lewat `window.print` |
| Barista & Kitchen | Selesai (tiket per stasiun) | Selesai (halaman bersama) | Kitchen **tanpa desain Figma** (ASSUMPTION) |
| Menu + kategori + resep | Selesai | Selesai | Foto menu: backend siap, UI unggah belum |
| Inventori + riwayat stok + opname | Selesai | Selesai | |
| Supplier, pembelian, penerimaan | Selesai | Selesai | **Tanpa desain Figma** (ASSUMPTION) |
| Pengguna, izin khusus, meja & QR | Selesai | Selesai | **Tanpa desain Figma**; QR belum dirender sebagai gambar |
| Dashboard Owner/Manager, laporan | Selesai | Selesai | Tanpa HPP/laba bersih (PENDING) |
| Audit log | Tulis + endpoint baca | Belum ada layar | |

## Pekerjaan tersisa
- [ ] Jalankan dan perbaiki backend + tes fitur (lihat langkah di atas).
- [ ] `docs/api/openapi.yaml` (kontrak OpenAPI formal; saat ini kontraknya `API_DOCUMENTATION.md`).
- [ ] Render gambar QR meja (frontend) dan unggah foto menu/gambar QRIS statis (UI).
- [ ] Layar Kitchen, pengguna, supplier/pembelian, meja: minta desain Figma lalu sesuaikan.
- [ ] Jawaban atas keputusan PENDING di `DECISIONS_PENDING.md` (terutama K-B1, K-B2, K-B3, K-B10, K-T1, K-T2).
- [ ] Peninjauan visual terhadap Figma (spacing/tipografi), uji responsif perangkat nyata.
- [ ] Endpoint refresh token / kebijakan sesi layar dapur (K-S2).
- [ ] Hosting gratis (ditunda sampai aplikasi stabil lokal).
- [ ] Pengujian black-box dan UAT bersama Owner.

## Status aktual per 12 November 2026

`artisan test` lulus 62 tes (448 assertion) pada suite sebelum perubahan role/absensi terbaru; suite khusus perubahan terbaru lulus 37 tes (256 assertion). Vitest lulus 17 tes dan `npm.cmd run build` berhasil. Backend memakai PHP 8.3.30. Database PostgreSQL utama `cafe_exito` masih legacy: 22 migrasi canonical awal berstatus Pending. Hanya migrasi login users yang scoped dan migrasi timestamp absensi tercatat; tidak ada `migrate:fresh`, `db:wipe`, atau rekonsiliasi luas. Backup sebelum remediation yang lama dan backup terbaru sebelum rekonsiliasi telah divalidasi dan dipertahankan.

Belum siap live: skema tabel legacy (`orders`, `order_items`, `transactions`, dan lainnya) belum kompatibel dengan service POS, pembayaran, dan antrean canonical. Role produksi sedang disatukan menjadi `barista_kitchen` dengan station akun; foto/GPS absensi sudah diimplementasikan di kode, tetapi event table belum tersedia di main. Tes backend berjalan pada database pengujian dan bukan uji E2E pada `cafe_exito`. POS/takeaway disetujui, QRIS tetap verifikasi manual, dan stok dikurangi saat verifikasi pembayaran dalam transaksi atomik. Migrasi produksi belum dijalankan karena seluruh tabel lama dimiliki `postgres`, dan kredensial administrator belum tersedia secara lokal.

## Status at latest verification — 12 November 2026

Latest backend test run: 63 passed, 441 assertions (`artisan test --env=testing`). Frontend: 17 passed; production build succeeded. All migration PHP files pass syntax lint. `route:list --path=api/v1/attendance` shows all six attendance endpoints including protected photo retrieval.

The new preflight, station, payment-backfill, and runtime-grant migrations are implemented but **not run on the main database**. Main migration status confirms the preflight, all 22 canonical migrations, and the three final reconciliation/grant migrations remain Pending; the two earlier scoped repairs remain Ran. A fresh verified backup is available at `backend/database/backups/cafe_exito_before_reconciliation_20261112_214744.backup`. The old backup remains unchanged.

No end-to-end request has been made to the live API after reconciliation because the main schema has not been migrated and a local postgres passfile is not yet available. The report remains NOT READY until migration, post-migration data/permission checks, and live authentication/operational checks finish.

## Hasil rekonsiliasi main � 12 November 2026 (pembaruan final)

PostgreSQL main `cafe_exito` berhasil direkonsiliasi dengan PHP 8.3.30. Semua 28 migration repository berstatus Ran; 17 catatan migration historis tetap dipertahankan. Data utama tidak hilang: users 5, kategori 3, menu 10, pesanan 14, detail 15, meja 5. Payments berisi 14 status pending yang dipetakan dari pesanan legacy pending.

Skema akhir: 33 tabel, 38 FK dan 41 CHECK tervalidasi, 0 orphan pada relasi order detail/payment/ticket yang diperiksa. DML seluruh tabel, akses 30 sequence, dan default privileges untuk objek baru tersedia bagi `cafe_exito_user`. Konfigurasi Laravel runtime tetap memakai akun tersebut. Lima akun aktif tetap menggunakan hash yang sudah ada. Route login berhasil diuji dengan username dan email (10/10), disusul logout (10/10).

Tes terbaru: backend 63/63 (441 assertions) pada database testing, frontend 17/17, Vite build sukses, lint 28 migration sukses. Database readiness dan login selesai; operasional live end-to-end lain, integrasi frontend ke API, serta UAT belum selesai, sehingga sistem tidak diklaim siap produksi. Lihat `database/FINAL_DATABASE_AND_SYSTEM_READINESS_REPORT.md`.


## Status terbaru - 13 November 2026

Absensi kamera/GPS dan halaman manajemen akun karyawan Owner telah diperbaiki. Migration `2026_11_13_000001_add_accuracy_meters_to_attendance_events` berstatus Ran pada database utama `cafe_exito`; seluruh migration berstatus Ran. Runtime terverifikasi sebagai `cafe_exito_user`; lima akun aktif beserta role/station yang ada dipertahankan. Backend test: 69/69 (511 assertions); frontend: 20/20; Vite build berhasil. Browser test BLOCKED oleh pemeriksaan keamanan Computer Use yang tidak dapat memastikan URL Chrome; kamera/GPS fisik dan alur UI ke live API NOT TESTED. Laporan: `implementation/ATTENDANCE_AND_EMPLOYEE_ACCOUNT_IMPLEMENTATION_REPORT.md`.
### Pending-work update - 2026-11-13

- [x] Draft OpenAPI 3.1 added at `docs/api/openapi.yaml`, based on current `backend/routes/api.php`. Artisan route extraction was unavailable because the shell PHP is 8.2.12 while the project requires PHP 8.3+; the OpenAPI document has not been parser-validated.
- [x] Historical 13 November route/documentation note: customer cancellation is active since 20 November; inventory, purchasing, and stock-usage remain intentionally inactive and are documented as such.
- [ ] Visual/UAT work, attendance data-retention policy, physical camera/GPS checks, and UI integration against the live API remain outstanding.

### Scope and OpenAPI update - 13 November 2026

Final client direction supersedes the earlier module table and historical notes above: inventory, ingredient stock, suppliers, purchasing/receipts, and stock-usage reporting are out of active scope. Their remaining code/controllers are legacy and are not described as active API endpoints. POS cashier and table QR remain in scope.

OpenAPI coverage was compared against `php artisan route:list --path=api/v1 --json` using Laragon PHP 8.3.30: 63 route operations and 63 matching method/path entries; no active route was missing from the spec and no extra operation was found. Request schemas were cross-checked against available FormRequests/controllers; unverified body shapes are left out rather than guessed. Cancellation currently requires `orders.cancel` (Owner in the role map). Refund approval is not implemented. Menu recipe endpoints still depend on ingredient records, and POS cash validation/message handling does not match the intended unpaid-until-paid semantics. No YAML/OpenAPI parser is installed in the available PHP or Node environments; therefore the document has not been parser-validated. No database, account, or application-code changes were made.

The inventory/purchasing rows in the earlier module table are retained only as historical implementation notes, not as features currently offered to staff.

## Pembatalan item dan refund parsial — implementasi 20 November 2026

Backend/frontend: pembatalan kuantitas customer/Owner, status mulai per baris item, request/refund approval/withdrawal/revocation/execution, bukti privat, catatan konflik, in-app notification, scheduler tenggat, permission, API, halaman dasar Owner/Cashier/Manager, customer UI, dan item start Barista/Kitchen ditambahkan. Migration additive dibuat tetapi **tidak dijalankan pada database utama**.

Verifikasi aktual: lint PHP untuk file backend baru/diubah lulus; route refund berhasil dimuat oleh Artisan; 4 tes `CustomerPartialCancellationTest` lulus. Unit suite awal 23/24, satu gagal karena permission baru belum didaftarkan; permission telah diperbarui, suite perlu dijalankan ulang. Full backend suite masih berjalan saat catatan ini dibuat. Frontend test/build dan tes refund lifecycle lengkap belum diverifikasi.

## Documentation correction - POS and AUD-01 (20 November 2026)

The 13 November OpenAPI comparison (63 operations) and its notes that refund approval was not implemented and POS cash validation/message handling was a gap are historical. Artisan route:list was rechecked on 20 November and returned 79 HTTP operations. Current POS create requests do not require or consume amount_received; both cash and static QRIS payments begin pending. Cashier records cash settlement through payment verification; QRIS is manually verified by Cashier. A pending payment may be verified after order completion without changing the completed order status. See API_DOCUMENTATION.md and api/openapi.yaml for the current contract.

AUD-01 cancellation/refund rules dated 20 November supersede the earlier statement that customer cancellation routes or refund approval were absent. Customer and Owner may cancel eligible unstarted quantities with a reason; verified-payment cancellation creates or merges a pending refund request for Owner decision. Cashier executes approved refunds. Manager documents conflict exceptions; Owner verifies Manager documentation. Refund is not automatic payment execution.

The latest full backend run recorded before the final POS/Authorization edits was 74/88, with 14 failures. This audit did not run database tests; the current edited code is not backend-test-verified. Implementation remains NOT READY for push until affected tests are run in the permitted isolated testing environment and remaining failures are assessed.


## Integrasi publish/refund-pos ke workspace utama - 20 November 2026

Implementasi refund/cancellation lokal tetap dipertahankan; lima file backend payment proof dari publish digabung dengan layanan pembayaran/route/resource dan frontend lokal. Upload QRIS Self-Order disimpan privat, tidak melunasi payment, dan keputusan Kasir tetap manual. QRIS POS tidak memakai upload pelanggan. Attendance juga menerima geofence Haversine 30 meter yang fail-closed saat koordinat server kosong/tidak valid; `.env.example` menambahkan kunci koordinat kosong. Migration payment proof baru ditambahkan tetapi tidak dijalankan pada database utama.

Verifikasi: route:list memuat 81 entry termasuk dua route bukti QRIS; QrisPaymentProofTest termasuk dalam run full backend (4 skenario lulus). Full backend testing database cafe_exito_test: 87 lulus, 10 gagal (ekspektasi stok/purchasing lama yang sudah di luar scope); 97 tes dijalankan setelah skenario geofence ditambahkan. Frontend Vitest dengan VITE_USE_MOCK=true: 21/22 lulus setelah skenario upload QRIS ditambahkan; satu gagal pada simulasi kamera/GPS absensi. Production build dan PHP lint app/ lulus.

Catatan keselamatan test: run frontend pertama menggunakan konfigurasi lokal VITE_USE_MOCK=false dan backend/.env menunjuk cafe_exito; dua alur pemesanan dalam flow test berpotensi membuat order/cancellation uji pada DB utama. Tidak dilakukan penghapusan atau koreksi data. Periksa data operasional berdasarkan waktu run sebelum tindak lanjut; jangan anggap DB utama tidak tersentuh.

Keputusan batas file tetap terbuka: implementasi publish menerima sampai 3 MB, sementara K-S3 mencatat rekomendasi Figma 2 MB (A18).

## Status Database & Inventarisasi API — 21 November 2026

- **Task 2 (Database Testing & Final Migration):** Selesai dengan status PASS dan zero data loss. Seluruh 31 migration resmi berstatus `Ran` pada database aktif `cafe_exito` (PostgreSQL 16.14). Total tabel bertambah dari 33 menjadi 41 tabel (termasuk `payment_proofs`, `order_item_cancellations`, `refund_requests`, `refund_request_items`, `refund_events`, `refund_executions`, `refund_conflict_notes`, `app_notifications`). Sebanyak 14 orders, 14 payments, 15 order_details, dan 5 users lama tetap 100% utuh. Laporan: `docs/DATABASE_TESTING_AUDIT_REPORT.md` dan `docs/DATABASE_MIGRATION_EXECUTION_REPORT.md`.
- **Task 3.1 (API Endpoint Inventory & Test Planning):** Selesai. Total 79 endpoint terdaftar dianalisis dan dipetakan ke dalam 15 modul aktif (69 wajib autentikasi Sanctum, 10 publik). Matriks hak akses role staf (`owner`, `manager`, `kasir`, `barista`, `kitchen`) dan rencana 8 kategori skenario pengujian disusun lengkap. Modul inventory & purchasing terkonfirmasi berstatus retired (assert 404). Laporan: `docs/API_TESTING_MATRIX.md`.

## Final pre-push audit status — 21 November 2026

This dated addendum supersedes earlier testing counts/status notes above. Current checks: backend Unit 24/24 (161 assertions); guarded Feature 75/75 (632 assertions) on `cafe_exito_test`; frontend Vitest 26/26; production build passed (159 modules); PHP syntax checks passed. Report aggregation excludes fully cancelled orders and subtracts cancelled item quantities once; refund totals stay separate. Active database `cafe_exito` retained the same counts before/after tests. Playwright login rendering smoke passed through system Chrome; bundled Chromium download timed out. Targeted Pint `--test` passes on the 10 relevant PHP files, and working-tree `git diff --check` passes. Cached whitespace findings remain in the unchanged index pending user review/manual staging refresh. Full browser workflows/viewport/accessibility/Figma comparison remain outstanding. Task 5 is BLOCKED; no Task 6 or UAT was started.
