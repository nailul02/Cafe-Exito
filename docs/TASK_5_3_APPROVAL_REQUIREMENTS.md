# Task 5.3 — Approval Requirements

Tidak ada approval untuk membaca laporan, menjalankan Unit suite murni, lint, Vitest, build, atau query read-only. Tindakan berikut memerlukan persetujuan terpisah.

## Gate A — PHPUnit Feature suite

- **Target:** database PostgreSQL `cafe_exito_test` di `127.0.0.1:5432`, schema `public`, role `cafe_exito_user`. Bukan `cafe_exito`.
- **Perintah yang diusulkan (PowerShell, child process terisolasi):**

  ```powershell
  cmd.exe /d /s /c "set DB_DATABASE=cafe_exito_test&& vendor\bin\phpunit --testsuite Feature"
  ```

- **Dampak:** Laravel `RefreshDatabase` memanggil `migrate:fresh` dan membuat schema kembali, lalu Feature tests membuat/ubah fixture, token, order, payment, audit, attendance, refund, dan storage fake di database tes.
- **Reversibilitas:** tabel dapat direkonstruksi dari migration, tetapi snapshot/schema khusus yang sudah ada tidak dijamin dapat dipulihkan identik. Tanpa backup, perlakukan reset schema sebagai destruktif terhadap `cafe_exito_test`.
- **Safeguard:** persetujuan harus menyebutkan suite Feature dan database test secara eksplisit; jalankan hanya dengan child-process env di atas; verifikasi target sebelum test suite; jangan ubah `phpunit.xml`/`.env`; ambil counts `cafe_exito` sebelum/sesudah.
- **Status:** **APPROVED FOR `cafe_exito_test` (User persetujuan eksplisit: APPROVE FEATURE)**. Pengujian **BELUM DIJALANKAN**. Persetujuan ini tetap mewajibkan verifikasi preflight target database untuk melindungi database aktif `cafe_exito` dari `RefreshDatabase`/`migrate:fresh`. Approval untuk satu suite tidak mencakup migration manual, seeder, atau perubahan env permanen.

## Gate B — Playwright + Chromium

- **Package yang disarankan:** `@playwright/test@1.65.1` (rilis stable terkini saat audit, 21 Nov 2026).
- **Perintah instalasi yang diusulkan:** `npm.cmd install --save-dev @playwright/test@1.65.1`, lalu `npx.cmd playwright install chromium`.
- **Dampak:** menambah package.json/package-lock dan mengunduh browser binary ke `%USERPROFILE%\AppData\Local\ms-playwright`; Chromium menggunakan beberapa ratus MB (sekitar 281 MB contoh ukuran resmi). Install runner dan binary dua langkah.
- **Status:** **APPROVED FOR INSTALLATION (User persetujuan eksplisit: APPROVE PLAYWRIGHT)** untuk `@playwright/test@1.65.1` dan Chromium. Status saat ini: paket dan browser binary **BELUM DIPASANG/DIJALANKAN**.

## Gate C — config hardening (opsional)

`phpunit.xml` saat ini memakai env `force=false`, sehingga process `DB_DATABASE` dapat override target. Usulan jangka panjang: tambah preflight guard yang menolak database selain `cafe_exito_test`, atau `force="true"` untuk `DB_DATABASE`. Tidak ada perubahan config dibuat. Persetujuan perubahan file tersebut dibutuhkan sebelum implementasi.

## Rekap Status & Tindak Lanjut

1. **Persetujuan User:** Diberikan untuk Gate A (`cafe_exito_test` saja) dan Gate B (`@playwright/test@1.65.1` + Chromium).
2. **Kewajiban Preflight:** Gate A wajib diverifikasi child process env-nya sebelum eksekusi untuk menjamin `cafe_exito` terlindungi 100%.
3. **Eksekusi:** Belum ada Feature Test maupun browser testing yang dijalankan.
4. **Tahapan Proyek:** Task 5 tetap **BLOCKED**; Task 6 System Testing tetap **DITUNDA**.
