# Frontend Role Report — Task 4.4

Pengujian role dijalankan pada fixture `src/mocks/seed.js`, bukan akun database atau token nyata. Role/permission backend tidak diubah.

| Role | Cakupan UI | Hasil |
|---|---|---|
| Owner | Beranda, laporan, akun staf, refund, attendance | PASS (mock); suite memverifikasi landing dan akses akun |
| Manager | Dashboard, menu, laporan, absensi, konflik refund, meja & QR | PASS (mock); route Meja & QR memanggil service meja, akun karyawan Owner-only diuji |
| Kasir | Dashboard, pesanan, refund, attendance | PASS (mock); route manager ditolak |
| Barista | `/production`, queue filter barista | PASS (mock) |
| Kitchen | `/production`, queue filter kitchen | PASS (mock) |
| Customer | Katalog, keranjang, checkout/status publik | PASS (mock) |

Keterbatasan: tidak ada sesi browser yang diautentikasi melalui API. Suite hanya memverifikasi sebagian tombol/route; bukan pembuktian authorization server. Manager menu “Meja & QR” merender `UsersPage`, sehingga fungsi yang disajikan tampaknya akun pengguna, bukan meja/QR. Modul Inventory/Purchasing masih terlihat pada source halaman/API tetapi tidak ada navigasi manager untuk inventory; backend telah menyatakan keduanya retired/tidak terdaftar. Jangan mengaktifkan atau mengandalkan modul itu sebelum kontrak direkonsiliasi.
