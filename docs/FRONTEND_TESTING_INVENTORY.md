# Frontend Testing Inventory — Task 4

Tanggal: 21 November 2026. Pemeriksaan dilakukan pada source frontend, package scripts, konfigurasi, dokumentasi API Task 3, serta suite mock Vitest. `.env` tidak dibaca; tidak ada request ke backend aktif.

## Struktur aktual

- Frontend terpisah di `frontend/`; React 19.3.1, React Router 7.18.5, Vite 8.3.5, JavaScript/JSX, Tailwind CSS 4.3.3.
- Package manager npm; lockfile `frontend/package-lock.json`; entry `src/main.jsx`, route table `src/App.jsx`.
- `VITE_API_BASE_URL` default `http://localhost:8000/api/v1`; `VITE_USE_MOCK` default `true`; `VITE_POLL_INTERVAL_MS` default 4000 ms.
- Router: `BrowserRouter`; protected route menguji autentikasi dan daftar role. Permission UI memakai `user.permissions` pada komponen tertentu.
- Sesi staf memakai `sessionStorage` (`exito.token`); tabel/meja pelanggan juga menyimpan token sesi. Keranjang memakai `sessionStorage`.
- Layout bersama: `CustomerLayout`, `StationLayout`, `ManagementLayout`; komponen UI bersama untuk modal, form, status, tabel, tab, toast, dan header.
- Test framework sudah ada: Vitest 5 + Testing Library + jsdom. Script tersedia: `test`, `dev`, `build`, `preview`; tidak ada script lint/typecheck.

## Inventaris route terdaftar

Terdapat 35 elemen deklarasi React Router: 33 halaman konkret, wildcard 404, dan route parent `/order` tanpa tampilan sendiri.

| Kelompok | Route halaman |
|---|---|
| Publik/customer | `/login`, `/`, `/order`, `/order/table`, `/order/menu`, `/order/cart`, `/order/payment`, `/order/done`, `/order/status/:token` |
| Kasir | `/cashier`, `/cashier/orders`, `/cashier/orders/:id`, `/cashier/refunds`, `/cashier/profile` |
| Produksi/staf | `/production`, `/production/profile`, `/attendance` |
| Manager | `/manager`, `/manager/production`, `/manager/menus`, `/manager/users`, `/manager/reports`, `/manager/attendance`, `/manager/refund-conflicts`, `/manager/profile` |
| Owner | `/owner`, `/owner/production`, `/owner/orders/cancellations`, `/owner/refunds`, `/owner/reports`, `/owner/attendance`, `/owner/users`, `/owner/profile` |
| Fallback | `*` → NotFoundPage |

**Koreksi audit lanjutan:** `/manager/users` memang merender `UsersPage` yang menampilkan judul “Meja & QR” dan memanggil `usersApi.tables()` (`GET /admin/tables`); controller dan lima route `/admin/tables` tercatat aktif. Catatan Task 4 sebelumnya yang menyebutnya sebagai pengelolaan akun keliru karena menyimpulkan dari nama file. Tidak ada layar khusus detail menu atau pilihan shift. Barista dan Kitchen memakai satu route `/production` dengan antrean difilter station.

## Pemetaan desain dan role

Repo menyediakan logo, gambar meja dan kopi; ekspor Figma yang dapat dibandingkan langsung tidak ditemukan. Layout customer menyebut lebar desain 412 px, layout management menyebut wireframe tablet, tetapi tanpa artefak pembanding yang memadai. Inventory/Purchasing masih memiliki file/service frontend, tetapi tidak diroute dan route API backend aktual berjumlah nol untuk kedua modul. Kode itu dipertahankan sebagai artefak legacy yang tidak aktif.

| Role | Beranda/navigasi utama |
|---|---|
| Owner | dashboard, monitoring produksi, pembatalan item, refund, laporan, kehadiran, akun karyawan, profil |
| Manager | dashboard, produksi, menu, meja & QR, laporan, rekap kehadiran, konflik refund, absensi sendiri, profil |
| Kasir | dashboard, pesanan, refund, absensi, profil |
| Barista | antrean produksi barista, absensi, profil |
| Kitchen | antrean produksi kitchen, absensi, profil |
| Customer | QR/meja, menu, keranjang, pembayaran, konfirmasi, status pesanan; tanpa akun staf |

Data role browser memakai fixture/mock; tidak ada autentikasi ke akun backend nyata.
