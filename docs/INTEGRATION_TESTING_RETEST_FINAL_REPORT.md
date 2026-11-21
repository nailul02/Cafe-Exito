# Task 5.2 — Integration Testing Retest Final Report

Tanggal: 2026-11-21\
Kesimpulan cakupan: **PASS WITH ISSUES** untuk slice read-only; Task 5 secara keseluruhan **BLOCKED** untuk penutupan penuh.

## Ringkasan

Retest dilakukan terhadap backend lokal melalui HTTP, service Axios frontend yang dimuat dengan Vite SSR (`VITE_USE_MOCK=false` khusus proses test), Vitest, dan build produksi. Tidak ada transaksi, akun, token database, atau berkas bukti dibuat. Probe cart hanya menggunakan endpoint pratinjau yang pada `OrderService::preview()` tidak menyimpan data; request invalid gagal pada FormRequest sebelum controller.

| Hasil | Jumlah |
|---|---:|
| PASS | 20 |
| FAIL | 0 |
| BLOCKED | 24 |
| NOT TESTED | 4 |
| Total | 48 |

Detail skenario terdapat di [matrix retest](INTEGRATION_TESTING_RETEST_MATRIX.md). Hasil API per area ada pada laporan auth, role, customer, payment, order, station, security, browser, dan data integrity.

## Hasil penting

- HTTP nyata: health, menu, payment information, invalid QR/order token, protected endpoint, method mismatch, CORS, valid cart preview, serta 422 validation.
- Axios memetakan sukses dan `ApiError` untuk 401/404/422; bearer token fiktif dibersihkan dari session storage shim.
- `npm.cmd test`: 26/26 pass. `npm.cmd run build`: pass, 159 modules.
- `cafe_exito_test` terpisah dan tanpa data bisnis; PHPUnit diarahkan ke database tersebut. Feature suite tidak dijalankan karena batas retest melarang migration/seeder dan test itu memakai `RefreshDatabase`.
- Tidak tersedia browser runner; tidak ada hasil browser E2E atau responsive UI.

## Temuan / perbaikan

Satu bug kontrak terkonfirmasi: `PaymentService::verify()` menolak perubahan metode hanya pada QRIS Self-Order, sehingga QRIS POS dapat dikirim sebagai tunai. Guard kini menolak QRIS → tunai untuk semua order dan regression Feature test ditambahkan. PHP lint lulus; Feature test sengaja BLOCKED karena menjalankan `RefreshDatabase`/migration tidak diizinkan pada retest ini.

Perubahan kode: `backend/app/Services/PaymentService.php` (guard verifikasi) dan `backend/tests/Feature/CashierPosTest.php` (regression test). Perubahan dokumentasi: dua laporan Task 5.1 diperbarui dan sebelas laporan Task 5.2 ditambahkan. `.env`, `phpunit.xml`, data, dan seluruh perubahan repository yang sudah ada sebelum tugas ini tetap dipertahankan.

## Keputusan

Task 5 **belum layak ditutup penuh**. Penutupan membutuhkan alur autentikasi positif per role, tes 403 station/permission, eksekusi regression Feature yang belum dijalankan, transaksi/order/payment/refund dengan fixture yang disetujui, batas upload runtime, dan browser E2E. Tidak ada Task 6, UAT, atau deployment.
