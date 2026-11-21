# Task 5.1 — Browser Integration Readiness

**Tanggal:** 2026-11-21\
**Status:** BLOCKED untuk browser E2E; PARTIALLY READY untuk UI test berbasis DOM.

## Kondisi saat ini

- Frontend mempunyai Vitest, Testing Library, dan jsdom. Konfigurasi `frontend/vitest.config.js` mengaktifkan mock, jadi runner ini bukan browser sungguhan dan tidak menguji jaringan ke backend.
- `frontend/package.json` tidak mendeklarasikan Playwright, Cypress, Puppeteer, atau WebDriver. Tidak ada browser E2E script maupun konfigurasi browser yang terpasang.
- Laporan Task 4 mencatat browser automation host tidak tersedia. Tidak ada bukti sesi browser terkendali yang dapat menjalankan smoke test UI.

## Remediation plan

1. Tambahkan Playwright (atau tool browser yang disepakati), browser binaries, script/config, dan CI job setelah dependensi/browser install disetujui.
2. Jalankan aplikasi dengan API dan data integration khusus; gunakan base URL dan kredensial fixture sementara, bukan server/data aktif.
3. Tambahkan smoke flow login staff, akses ditolak lintas role, daftar/menu, order, dan logout; customer QR diuji sebagai alur publik.
4. Simpan trace/screenshot hanya di artefak lokal/CI yang tidak memuat kredensial atau token.

## Keputusan

**PARTIALLY READY:** UI dapat diuji dalam jsdom dengan mock yang tersedia.\
**BLOCKED:** browser-level integration belum dapat dieksekusi tanpa runner dan host browser.\
**NEEDS APPROVAL:** instalasi dependency/browser, perubahan lockfile, dan menjalankan server/browser terhadap lingkungan integration.

Tidak ada dependency atau browser binary yang dipasang.
