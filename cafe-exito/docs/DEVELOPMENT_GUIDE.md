# Panduan Pengembangan (Windows)

## Versi yang dibutuhkan (samakan di seluruh tim)
| Alat | Versi | Catatan |
|---|---|---|
| PHP | **8.3 atau lebih** (Laravel 13) | Ekstensi: `openssl, curl, mbstring, fileinfo, pdo_pgsql, pgsql, zip` (+ `intl` bila perlu). **`gd` tidak diperlukan.** Periksa `php -m` |
| Composer | 2.x | |
| PostgreSQL | 16 atau 17 (satu versi mayor untuk semua) | |
| Node.js | **22 LTS** (Vite 8 butuh ≥ 20.19 / 22.12) | |
| Git | terbaru | Backend sudah memuat `.gitattributes`/`.editorconfig` bawaan Laravel; frontend belum: atur `core.autocrlf` konsisten |

Tidak mengasumsikan Laragon/XAMPP. **Bila memakai Laragon:** buka *Terminal* Laragon (PATH sudah berisi PHP/Composer/Node). Periksa versi PHP bawaan Laragon; bila di bawah 8.3, ganti lewat menu Laragon > PHP. Letakkan proyek di `E:\laragon\www\cafe-exito` dan jangan di folder yang disinkronkan OneDrive.

## Backend
```
cd backend
composer install
copy .env.example .env
php artisan key:generate
```
Edit `.env`: `DB_PASSWORD` (jangan di-commit), `SEED_DEFAULT_PASSWORD` (≥ 8 karakter, **ganti nilainya**), `CORS_ALLOWED_ORIGINS` (alamat frontend, mis. `http://localhost:5173`).

**Menghubungkan PostgreSQL**
1. Pastikan layanan PostgreSQL berjalan (port 5432).
2. Buat dua database kosong lewat pgAdmin atau `psql -U postgres`: `CREATE DATABASE cafe_exito;` dan `CREATE DATABASE cafe_exito_test;`
3. Isi `DB_USERNAME`/`DB_PASSWORD` di `.env` (dan sesuaikan `DB_DATABASE` di `phpunit.xml` bila nama tes berbeda).
4. Uji koneksi: `php artisan db:show`. **Jalankan migrasi hanya setelah koneksi berhasil:**
```
php artisan migrate --seed       # 21 tabel + akun/menu/bahan contoh
php artisan storage:link         # untuk foto menu
php artisan serve                # http://localhost:8000  → cek GET http://localhost:8000/api/v1/health
```
Akun seeder: `owner`, `manager`, `ucup` (kasir), `nana` (barista), `dewi` (kitchen) dengan kata sandi = `SEED_DEFAULT_PASSWORD`.

**Tes**
```
composer test:unit    # tanpa database (24 tes)
composer test         # semua tes; fitur memakai database cafe_exito_test (belum pernah dijalankan: lihat IMPLEMENTATION_STATUS)
```

## Frontend
```
cd frontend
copy .env.example .env
npm ci
npm run dev           # http://localhost:5173
npm test              # Vitest (5 tes)
npm run build
```
Mode **mock** (bawaan): `VITE_USE_MOCK=true`; data di `localStorage` (tab berbeda berbagi data: buka tab pelanggan, kasir, barista bersamaan). Tombol "Reset data demo" ada di halaman login.

**Beralih ke backend asli:** di `frontend/.env` set `VITE_USE_MOCK=false` dan `VITE_API_BASE_URL=http://localhost:8000/api/v1`, jalankan ulang `npm run dev`. Pastikan `CORS_ALLOWED_ORIGINS` backend memuat `http://localhost:5173`. Tidak ada komponen yang perlu diubah: seluruh UI hanya memakai `src/api`.

## Konvensi
- Backend: Controller tipis → FormRequest → Service → Resource; logika bisnis hanya di Service; stok hanya lewat `StockService`; migrasi baru untuk setiap perubahan skema; tes untuk aturan baru.
- Frontend: halaman di `src/pages/<role>`, komponen bersama di `src/components`, akses data hanya via `src/api` (+ `useResource`/`useAction`); token di `sessionStorage` lewat `tokenStorage`; mock di `src/mocks` harus mengikuti `docs/API_DOCUMENTATION.md`.
- Git (disarankan): `main` stabil, `develop` integrasi, cabang `feature/<modul>`, PR dengan satu reviewer; migrasi/enum/`config/permissions.php` diubah lewat PR kecil tersendiri.
- Jangan commit `.env`, kata sandi, atau gambar QRIS pembayaran asli.
