# Task 5.1 — Auth Fixture Remediation Report

**Tanggal:** 2026-11-21\
**Status:** PARTIALLY READY

## Temuan

- API memakai `auth:sanctum`; login membuat personal access token dan logout menghapus token aktif (`backend/routes/api.php`, `backend/app/Http/Controllers/Api/V1/Auth/AuthController.php`).
- Feature test dasar sudah menyediakan `actingAsRole()` dengan `Sanctum::actingAs`, `User::factory()`, dan `RefreshDatabase` (`backend/tests/Feature/ApiTestCase.php`). Ini cocok untuk tes authorization API, tetapi tidak menguji siklus login/token HTTP sesungguhnya.
- UI Vitest mengaktifkan `VITE_USE_MOCK=true`; role demo pada mock hanya membuktikan alur UI dan bukan autentikasi API.
- PHPUnit menunjuk PostgreSQL `cafe_exito_test`; katalog PostgreSQL mengonfirmasi nama database itu ada. Keberadaan database tidak membuktikan bahwa isinya disposable atau telah diizinkan untuk di-refresh.

## Fixture yang disiapkan untuk rancangan

1. Owner — akses review refund, audit, laporan, dan administrasi.
2. Manager — pengelolaan menu/tabel, laporan, serta konflik refund.
3. Kasir — POS, pembayaran, dan alur refund yang diizinkan.
4. Barista — role `barista_kitchen`, station `barista`.
5. Kitchen — role `barista_kitchen`, station `kitchen`.
6. Customer — tidak dibuat sebagai akun staff; customer QR/table memakai fixture meja dan token publik dalam database khusus test.

Buat fixture dengan factory dan kredensial tes sementara hanya di database khusus yang terisolasi. Jangan memakai user, password, token, maupun tabel dari `cafe_exito` aktif. Login/logout end-to-end harus dites lewat endpoint HTTP pada lingkungan integration khusus; jangan menganggap `Sanctum::actingAs` mencakupnya.

## Keputusan

**READY:** rancangan role fixture dan pola `Sanctum::actingAs` sudah tersedia.\
**BLOCKED:** tes API positif/negatif yang menyentuh database belum boleh dijalankan sebelum database tes disahkan disposable dan isolasi side effect diverifikasi.\
**NEEDS APPROVAL:** menjalankan suite Feature, membuat/mengubah akun fixture persisten, atau menyediakan kredensial login melalui environment.

Tidak ada akun, token, role, atau `.env` yang diubah dalam remediasi ini.
