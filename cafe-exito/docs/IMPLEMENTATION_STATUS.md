# Status Implementasi

Diperbarui: awal November 2026 (pembuatan fondasi awal). **Perbarui berkas ini setiap selesai mengerjakan sesuatu.**

## Ringkasan jujur
| Bagian | Dibuat | Diverifikasi (benar-benar dijalankan) | BELUM diverifikasi |
|---|---|---|---|
| Frontend (React) | 24 halaman, 18 komponen/layout, service per modul, mock | `vite build` lulus; **5 tes Vitest lulus** (alur pelanggan→status, login kasir, proteksi role, login salah, daftar stok); alur mock end-to-end diuji lewat skrip Node | Tampilan visual belum dibandingkan piksel-per-piksel dengan Figma; belum diuji di peramban nyata/perangkat |
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
