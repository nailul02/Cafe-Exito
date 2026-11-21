# Laporan Integrasi `cafe-exito-publish` ke workspace utama

Tanggal: 20 November 2026\
Tujuan: folder workspace utama Cafe Exito\
Sumber: folder pembanding Cafe Exito Publish

## A. Ringkasan integrasi

Inventaris awal membandingkan isi file, bukan hanya nama, dengan mengecualikan `.git`, `.env`, `vendor`, `node_modules`, storage, backup, dan output build.

| Kelompok | Jumlah awal | Hasil |
|---|---:|---|
| Hanya di folder utama | 411 | Seluruhnya dipertahankan; tidak ada file dihapus |
| Hanya di publish | 9 | 6 file implementasi diintegrasikan; 3 file konfigurasi repo tidak disalin |
| Isi sama | 272 | Tidak diubah |
| Isi berbeda | 71 | 26 file relevan direkonsiliasi; versi utama terbaru dipertahankan untuk sisanya |
| Konflik kode yang belum terselesaikan | 0 | — |

Hitungan di atas adalah snapshot sebelum integrasi. Tiga file publish-only yang tidak disalin adalah `.editorconfig`, `.gitattributes`, dan `.npmrc`; aturan line ending/npm tersebut berdampak luas dan tidak diperlukan untuk fitur yang digabung. File `backend/config/attendance.php` termasuk 6 file implementasi yang diintegrasikan.

## B. Perubahan yang dilakukan

**File baru dari implementasi publish:**

- `backend/app/Http/Requests/UploadPaymentProofRequest.php`
- `backend/app/Models/PaymentProof.php`
- `backend/app/Services/PaymentProofService.php`
- `backend/database/migrations/2026_11_19_000001_create_payment_proofs_table.php`
- `backend/tests/Feature/QrisPaymentProofTest.php`
- `backend/config/attendance.php`

**File penting yang diperbarui/diracik:**

- Backend: `app/Providers/AppServiceProvider.php`, `app/Services/PaymentService.php`, `app/Services/AttendanceService.php`, `app/Models/Payment.php`, `app/Http/Requests/RejectPaymentRequest.php`, `app/Http/Resources/PaymentResource.php`, `app/Http/Resources/CustomerOrderResource.php`, `app/Http/Controllers/Api/V1/Customer/CustomerOrderController.php`, `app/Http/Controllers/Api/V1/Cashier/CashierController.php`, `app/Http/Controllers/Api/V1/Cashier/PaymentController.php`, `routes/api.php`, `tests/Feature/ApiTestCase.php`, `tests/Feature/AttendanceTest.php`, dan `.env.example`.
- Frontend: `src/api/services/customer.js`, `src/api/services/cashier.js`, `src/pages/customer/OrderStatusPage.jsx`, `src/pages/cashier/TransactionDetailPage.jsx`, `src/mocks/db.js`, `src/mocks/index.js`, dan `src/test/flow.test.jsx`.
- Dokumentasi: `docs/API_DOCUMENTATION.md`, `docs/DATABASE_SCHEMA.md`, `docs/DECISIONS_PENDING.md`, `docs/IMPLEMENTATION_STATUS.md`, dan `docs/api/openapi.yaml`.
- `ApiTestCase` menambahkan fixture upload QRIS untuk tes alur yang mengasumsikan pelanggan sudah mengirim bukti. Tes QRIS khusus tetap menguji penolakan jika bukti belum ada.

**File/fungsi utama yang dipertahankan dari folder utama:**

- Workflow refund/cancellation 20 November, audit, notifikasi, POS terbaru, dashboard/laporan, otorisasi, customer ordering, produksi Barista/Kitchen, dan struktur attendance lokal.
- Seluruh file unik folder utama, backup, `.env`, serta direktori Laravel lama dan `cafe-exito/` bersarang. Aplikasi aktif tetap `backend/` API dan `frontend/` SPA sesuai panduan proyek; struktur lama tidak dipindah atau dihapus.

Tidak ada konflik implementasi yang membutuhkan keputusan untuk menghentikan penggabungan. Satu keputusan bisnis masih terbuka: implementasi upload bukti mengikuti batas 3 MB dari publish, sedangkan K-S3 menyebut rekomendasi Figma 2 MB. Dicatat sebagai asumsi A18 untuk dikonfirmasi.

## C. Status fitur

| Fitur | Status setelah integrasi |
|---|---|
| Refund dan cancellation | Implementasi utama dipertahankan; tes workflow yang masuk suite lulus |
| Bukti QRIS | Customer upload file JPEG/PNG/WEBP sampai 3 MB; file privat; status tetap pending sampai verifikasi manual Kasir; penolakan beralasan memungkinkan upload ulang |
| QRIS POS dan tunai | POS QRIS tetap dikonfirmasi manual Kasir tanpa bukti upload pelanggan; pembayaran tunai tetap alur sebelumnya |
| Customer ordering | Status QRIS dan UI upload bukti tersambung ke API; mock mendukung alur demo |
| Barista/Kitchen | Dipertahankan; tidak ada perubahan aturan antrean |
| Attendance | Dipertahankan dan geofence server-side dari publish digabung; koordinat pusat masih harus dikonfirmasi/dikonfigurasi |
| Dashboard, reports, menu, auth/authorization | Dipertahankan; tidak ada penghapusan fitur utama |

## D. Hasil pengujian

- Backend `artisan test --env=testing`, database testing terisolasi: **87 lulus, 10 gagal dari 97**. Lima kegagalan `OrderFlowTest` menguji konsumsi stok yang sudah dikeluarkan dari scope aktif; lima `PurchasingTest` mengharapkan route purchasing yang memang tidak aktif menurut keputusan final. Keempat tes `QrisPaymentProofTest` dan tes geofence baru lulus.
- Frontend `VITE_USE_MOCK=true npm test`: **21 lulus, 1 gagal dari 22**. Kegagalan yang tersisa pada simulasi kamera/GPS absensi; bukan pada QRIS.
- `npm run build`: **lulus**. Vite memberi peringatan bundle JavaScript sedikit di atas 500 kB.
- PHP lint untuk `backend/app`, migration payment proof, dan tes QRIS/Attendance: **lulus**.
- `php artisan route:list --path=api/v1 --json`: **81 route entries**, termasuk upload customer dan akses bukti Kasir.
- `git diff --check`: tidak ada whitespace error; Git memberi peringatan normal bahwa line ending beberapa file akan dikonversi sesuai konfigurasi checkout.
- OpenAPI belum divalidasi parser karena parser YAML/OpenAPI tidak tersedia. Tidak ada migrasi dijalankan pada database utama.

**Catatan database pengujian frontend:** satu run Vitest awal dijalankan sebelum `VITE_USE_MOCK=true` dipaksakan dan memakai API lokal. Dua flow test melewati pemesanan dan pembatalan, sehingga kemungkinan ada order uji yang tertulis melalui database lokal. Tidak dilakukan cleanup atau perubahan database lanjutan. Periksa data operasional sekitar waktu run pertama (20 November 2026; waktu lokal disamarkan) sebelum tindak lanjut dan jangan menganggap database utama pasti tidak tersentuh. Run frontend sesudahnya memakai `VITE_USE_MOCK=true`.

## E. Status Git

- Folder utama: branch `main`, commit `aa79ae6` (`Initial commit`). Semua perubahan lokal yang sudah ada tetap ada; integrasi ini juga belum di-stage/commit.
- Tidak ada `git add`, commit, push, reset, clean, merge, rebase, atau pergantian branch.
- Folder publish tetap berada di tempatnya dan tidak diubah oleh integrasi. Pemeriksaan awal menunjukkan ia berada pada branch `publish/refund-pos`, commit `d594427`, tetapi memiliki 3 perubahan lokal yang sudah ada pada `CashierPosTest.php`, `DECISIONS_PENDING.md`, dan `TransactionDetailPage.jsx`; jadi working tree sumber saat diperiksa bukan bersih seperti deskripsi prompt.

## F. Kesimpulan

Folder utama sekarang memuat implementasi refund/cancellation yang sudah ada, upload bukti QRIS publish, dan geofence attendance publish; folder utama layak menjadi satu-satunya workspace pengembangan berikutnya. Status ini **bukan** klaim siap produksi: 10 tes lama masih gagal, 1 tes frontend kamera/GPS gagal, koordinat pusat attendance dan batas unggah 2 MB/3 MB perlu diputuskan, UAT/live API belum dilakukan, dan data uji frontend awal mungkin menyentuh database lokal.
