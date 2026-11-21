# Task 5.1 — Integration Remediation Readiness Matrix

**Tanggal:** 2026-11-21

| # | Area / skenario | Status | Bukti / alasan |
|---:|---|---|---|
| 1 | Role UI lewat mock | READY | Vitest/jsdom dan mock role tersedia; hanya cakupan frontend mock |
| 2 | Fixture Owner backend | PARTIALLY READY | Factory dan helper `actingAsRole` ada; belum dijalankan |
| 3 | Fixture Manager backend | PARTIALLY READY | Pola factory tersedia; belum dijalankan |
| 4 | Fixture Kasir backend | PARTIALLY READY | Pola factory tersedia; belum dijalankan |
| 5 | Fixture Barista + station | PARTIALLY READY | Helper default station barista; perlu fixture station eksplisit |
| 6 | Fixture Kitchen + station | PARTIALLY READY | Role sama dengan barista; station harus dibedakan eksplisit |
| 7 | Customer QR publik | PARTIALLY READY | Helper order publik tersedia; integrasi token/API belum dijalankan |
| 8 | Login/logout token HTTP | BLOCKED | Tidak dicakup oleh `Sanctum::actingAs`; perlu DB tes dan API integration |
| 9 | Positif/negatif permission lintas role | BLOCKED | Memerlukan Feature API run yang dilarang sebelum isolasi disahkan |
| 10 | PostgreSQL khusus `cafe_exito_test` terdaftar | READY | Query katalog read-only mengonfirmasi database ada |
| 11 | `cafe_exito_test` terpisah dan kosong dari data aplikasi | READY | Nama DB berbeda; 32 tabel, seluruh tabel bisnis 0 baris; hanya tabel migrations berisi 31 baris |
| 12 | PHPUnit menunjuk DB tes | READY | XML menetapkan `pgsql` + `cafe_exito_test`; process DB_DATABASE tidak diset dan config cache tidak ada |
| 13 | Refresh/migrasi database tes | BLOCKED | Tidak dijalankan sesuai batasan retest; butuh gerbang terpisah sebelum Feature suite |
| 14 | Snapshot database aktif | READY | `db:show --counts` read-only tersedia; kontrol sebelum/sesudah dicatat |
| 15 | Upload pembayaran/refund/menu | PARTIALLY READY | Ada contoh `Storage::fake`; semua disk integration harus diarahkan ke temporary fake |
| 16 | Audit dan notifikasi | PARTIALLY READY | Ditulis ke tabel; aman hanya bila DB terisolasi |
| 17 | Queue/mail/session/cache | PARTIALLY READY | PHPUnit menyiapkan sync/array; tidak membuktikan side effect proses eksternal |
| 18 | Browser E2E tooling | BLOCKED | Tidak ada Playwright/Cypress/Puppeteer/WebDriver dalam dependency frontend |
| 19 | Install browser runner/binary | NEEDS APPROVAL | Perlu dependency/lockfile dan browser runtime baru |
| 20 | Vitest DOM suite | READY | Runner jsdom tersedia; mode test memaksa API mock |
| 21 | Browser smoke test API nyata | BLOCKED | Browser host/runner tidak tersedia dan backend integration belum disiapkan |
| 22 | SQLite sebagai pengganti PostgreSQL | BLOCKED | Belum terbukti kompatibel untuk SQL/locking yang ditarget PostgreSQL |
| 23 | PostgreSQL/container disposable baru | NEEDS APPROVAL | Pembuatan resource baru secara eksplisit dilarang tanpa persetujuan |
| 24 | Rollback sebagai satu-satunya isolasi | BLOCKED | Tidak mengisolasi file, worker, maupun proses HTTP/koneksi terpisah |
| 25 | Retest Task 5.2 read-only | PARTIALLY READY | Read-only API, validation, Vitest, dan build dapat dijalankan; alur mutatif/auth positif tetap terblokir |

**Rekap terbaru:** READY 6 · PARTIALLY READY 10 · BLOCKED 7 · NEEDS APPROVAL 2. Total 25 skenario.

Dokumen ini menilai kesiapan remediation. Tidak ada perubahan status PASS pada laporan Task 5 sebelumnya.
