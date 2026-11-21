# AUDIT INDEPENDEN STRUKTUR REPOSITORY CAFE ÉXITO
**Evaluasi Komparatif Arsitektur Monolitik vs. Arsitektur Terpisah (Client-Server / API-Driven SPA)**

*   **Tanggal Audit:** 19 November 2026
*   **Auditor:** Tim Senior Software Architect, Repository Auditor & Technical Reviewer
*   **Objek Audit:** Repository Proyek *Cafe Éxito* (`E:\laragon\www\cafe-exito`)
*   **Dokumen Acuan:** Laporan Proyek Sistem Informasi Manajemen Operasional pada Cafe Exito (UIN Imam Bonjol Padang, 2026), Dokumen Project Charter, Analisis Kebutuhan & Desain Sistem (UML, ERD, Wireframe).
*   **Status Dokumen:** Laporan Audit Resmi & Panduan Rekayasa Perangkat Lunak

---

## 1. Eksekutif Ringkasan

Audit independen ini dilakukan untuk mengevaluasi dua pendekatan struktur repository pada proyek sistem informasi operasional Cafe Éxito:
1.  **Struktur Lama (Monolitik Laravel di Root):** Struktur yang sebelumnya di-inisialisasi dan di-push ke branch utama GitHub (`main`) oleh anggota tim, di mana direktori Laravel (`app/`, `resources/views/`, `routes/web.php`) berada langsung pada root repository dan menyajikan halaman melalui template Laravel Blade, serta terdapat subdirektori duplikat `cafe-exito/`.
2.  **Struktur Baru (Pemisahan Terstruktur `backend/` dan `frontend/`):** Struktur yang disiapkan pada branch kerja `cleanup/cafe-exito-structure`, memisahkan aplikasi secara tegas menjadi RESTful API berbasis Laravel 13 (`backend/`) dan antarmuka Single Page Application (SPA) berbasis React 19 + Vite (`frontend/`).

### Putusan Audit Singkat
Berdasarkan analisis kebutuhan fungsional, karakteristik pengguna (multi-terminal: smartphone pelanggan via QR meja, tablet kasir POS, display antrean Barista/Kitchen secara *real-time*, dan laptop manajemen Owner), serta evaluasi pemeliharaan kode dan kolaborasi tim:

> **PUTUSAN: STRUKTUR BARU (PEMISAHAN BACKEND DAN FRONTEND) DINILAI LEBIH TEPAT DAN UNGGUL SECARA TEKNIS.**
> Struktur monolitik lama di root terbukti hanya berupa kerangka prototipe statis (*dummy views*) yang tidak terhubung ke database dan tidak mampu mengakomodasi kebutuhan interaktivitas *real-time* yang digambarkan pada diagram sekuens (*Sequence Diagram*) laporan proyek. Namun demikian, penyelesaian transisi ke struktur baru memerlukan pembersihan menyeluruh (*cleanup*) terhadap file-file residu di root repository agar tidak membingungkan pengembang dan tidak menimbulkan inkonsistensi pelacakan Git.

---

## 2. Latar Belakang dan Ruang Lingkup Proyek Berdasarkan Laporan Resmi

Berdasarkan dokumen resmi *Laporan Sistem Informasi Manajemen Operasional pada Cafe Exito* (Mata Kuliah Proyek Sistem Informasi, Program Studi Sistem Informasi, Fakultas Sains dan Teknologi, UIN Imam Bonjol Padang, 2026):

### 2.1 Informasi Umum & Anggota Tim
*   **Mitra Usaha:** Cafe Éxito (Sungai Bangek, Kota Padang) — Owner: Riski Ramadhan.
*   **Dosen Pengampu:** Haditya Prasetyo, S.Kom., M.Kom.
*   **Tim Pengembang (Kelompok 4):**
    1.  Anggi Septiani (NIM 2317020059) – *Project Manager*
    2.  Cindini Milasari (NIM 2317020057) – *System Designer*
    3.  Muhammad Zaky Dzikra Saputra (NIM 2317020012) – *Programmer*
    4.  Alfia Dingga Dastika (NIM 2317020022) – *Programmer*
    5.  Nailul Husni (NIM 2317020009) – *Programmer*

### 2.2 Kebutuhan Inti Sistem (*System Core Requirements*)
1.  **Pemesanan Mandiri Pelanggan (*Customer Self-Order via QR Code*):**
    Pelanggan memindai QR code di meja menggunakan kamera smartphone tanpa perlu mengunduh aplikasi native (diakses via web browser), memasukkan nomor meja, memilih menu, dan mengirim pesanan.
2.  **Transaksi & Pembayaran:**
    Mendukung dua metode pembayaran: Tunai (verifikasi manual oleh kasir di meja kasir) dan QRIS statis (pencatatan dan verifikasi).
3.  **Pengelolaan Antrean Dapur Real-Time (*Kitchen Display System - KDS*):**
    Pesanan otomatis terdistribusi ke stasiun kerja yang sesuai: kategori minuman ke layar **Barista** dan kategori makanan ke layar **Kitchen**. Barista/Kitchen memperbarui status pengerjaan secara interaktif tanpa komunikasi lisan.
4.  **Kasir & Point of Sale (POS):**
    Kasir memantau pesanan masuk, memverifikasi pembayaran, mencetak struk transaksi, dan membuat pesanan langsung (takeaway/dine-in).
5.  **Manajemen Operasional & Kehadiran:**
    Pencatatan absensi karyawan dengan foto selfie dan koordinat GPS (geofence), serta pengaturan jadwal shift.
6.  **Laporan Penjualan & Dashboard Eksekutif:**
    Owner dan Manager memantau omzet, performa menu terlaris, dan rekapitulasi transaksi harian/bulanan.

---

## 3. Komparasi Detail: Struktur Lama vs. Struktur Baru

### 3.1 Struktur Lama (Monolitik di Root GitHub)

Struktur ini diinisialisasi pada commit `78158ab` (*"Inisialisasi project Laravel sistemkasir"*) dan terdapat pada branch `main` remote GitHub:

```text
repository-root/
├── app/
│   ├── Http/Controllers/Controller.php (Hanya 1 file kosong)
│   └── Models/User.php
├── bootstrap/
├── config/
├── database/
│   └── migrations/ (Hanya 3 migration bawaan Laravel: users, cache, jobs)
├── public/
│   ├── index.php
│   └── image/menu/
├── resources/
│   ├── css/app.css
│   ├── js/app.js
│   └── views/ (29 file Blade: katalogmenu.blade.php, dashboardkasir.blade.php, dll.)
├── routes/
│   ├── console.php
│   └── web.php (Berisi closure Route::get yang hanya merender view statis)
├── storage/
├── tests/
├── artisan
├── composer.json (laravel/laravel standar, tanpa sanctum)
├── package.json (Laravel Vite plugin biasa, tanpa library frontend)
├── vite.config.js
└── cafe-exito/ (FOLDER DUPLIKAT berisi kode rar terunggah)
    ├── backend/
    ├── frontend/
    └── docs/
```

#### Temuan Kritis pada Struktur Lama:
1.  **Tidak Ada Logika Bisnis (*No Business Logic*):**
    Direktori `app/Http/Controllers/` hanya berisi `Controller.php` bawaan. Tidak ada satupun controller fungsional untuk Order, Menu, Payment, Absensi, atau Karyawan.
2.  **Routing Statis dan Menipu (*Mocked Web Routes*):**
    File `routes/web.php` hanya berisi rute langsung ke view tanpa autentikasi nyata:
    ```php
    Route::get('/dashboard', function () { return view('dashboardkasir'); })->name('dashboard');
    Route::get('/pesanan-kasir', function () { return view('pesanankasir'); })->name('pesanan-kasir');
    Route::get('/detail-transaksi', function () { return view('detailtransaksi'); })->name('detail-transaksi');
    ```
3.  **Ketiadaan Skema Database Cafe Éxito:**
    Di `database/migrations/` hanya ada migration bawaan Laravel (`create_users_table`, `create_cache_table`, `create_jobs_table`). Tidak ada tabel `orders`, `order_details`, `payments`, `menus`, `menu_categories`, `tables`, `ingredients`, atau `attendances`. Aplikasi Blade ini sama sekali tidak dapat menyimpan data ke database.
4.  **Tampilan Blade Bersifat Hardcoded:**
    Seluruh file di `resources/views/` (seperti `dashboardkasir.blade.php`, `katalogmenu.blade.php`, `laporanpenjualan.blade.php`) menggunakan angka, tabel, dan daftar statis yang di-hardcode di dalam HTML dengan memanggil Tailwind CDN (`<script src="https://cdn.tailwindcss.com"></script>`).
5.  **Anomali Folder Bersarang (`cafe-exito/`):**
    Adanya folder `cafe-exito/` di dalam root repository merupakan akibat dari proses uncompress/upload arsip `.rar` (commit `4e8a67a`). Ini menciptakan redundansi ekstrem di mana dua proyek berbeda berada di dalam satu repository Git yang sama.

---

### 3.2 Struktur Baru (Pemisahan Terstruktur `backend/` dan `frontend/`)

Struktur ini dirancang pada branch kerja `cleanup/cafe-exito-structure` untuk merealisasikan sistem yang sepenuhnya fungsional:

```text
repository-root/
├── backend/                       # Laravel 13 RESTful API Server
│   ├── app/
│   │   ├── Enums/                 # 12 Domain Enums (OrderStatus, PaymentMethod, TicketStatus, dll.)
│   │   ├── Exceptions/            # Custom Domain Exceptions & JSON ApiRenderer
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/# 27 API Controllers (Auth, Customer, Cashier, Station, Admin, Report, Attendance)
│   │   │   ├── Middleware/        # 5 Custom Middleware (ForceJson, EnsureActive, EnsurePermission, StationAccess, ValidateTableToken)
│   │   │   ├── Requests/          # 20 FormRequest validasi ketat
│   │   │   └── Resources/         # 13 Eloquent API Resources
│   │   ├── Models/                # 20 Eloquent Models
│   │   ├── Services/              # 13 Domain Services (OrderService, PaymentService, TicketService, AttendanceService, dll.)
│   │   └── Support/               # Helper (ApiResponse, NumberGenerator, Rp currency)
│   ├── config/                    # Konfigurasi (sanctum, cors, permissions matrix, cafe rules)
│   ├── database/
│   │   ├── migrations/            # 29 Migration PostgreSQL lengkap (constraints, indexes, foreign keys)
│   │   └── seeders/               # DatabaseSeeder, CanonicalAccountsSeeder
│   ├── routes/
│   │   ├── api.php                # 61 Endpoint RESTful API ber-prefix /api/v1
│   │   └── console.php
│   ├── tests/                     # 71 Automated Tests (PHPUnit Feature & Unit, 537 assertions; hasil validasi Codex terbaru, tidak dijalankan ulang dalam rekonsiliasi ini)
│   ├── artisan
│   ├── composer.json              # cafe-exito/backend (Laravel 13, Sanctum 4)
│   └── phpunit.xml                # Konfigurasi pengujian terisolasi (cafe_exito_test)
│
├── frontend/                      # React 19 + Vite Single Page Application (SPA)
│   ├── public/                    # Aset publik (logo, gambar QRIS, favicon)
│   ├── src/
│   │   ├── api/                   # Axios HTTP client, tokenStorage, ApiError, service per modul
│   │   ├── components/            # Reusable UI (Buttons, Modal, DataTable, Cards, Charts, States)
│   │   ├── config/                # Environment reader (VITE_API_BASE_URL, VITE_USE_MOCK)
│   │   ├── context/               # AuthContext, CartContext, ToastContext
│   │   ├── hooks/                 # Custom React hooks (useAction, useResource)
│   │   ├── layouts/               # CustomerLayout, ManagementLayout, StationLayout, ProtectedRoute
│   │   ├── lib/                   # Formatting utilities (Rupiah, Date, Status Badges)
│   │   ├── mocks/                 # Full In-Memory Mock Engine untuk pengujian mandiri tanpa backend
│   │   ├── pages/                 # 24 Halaman React (Customer, Cashier, Barista, Kitchen, Manager, Owner)
│   │   ├── test/                  # Automated UI integration tests (Vitest 20/20 PASS)
│   │   ├── App.jsx                # Client-side router configuration
│   │   ├── main.jsx               # Entry point
│   │   └── index.css              # Tailwind CSS 4 setup
│   ├── package.json               # cafe-exito-frontend (React 19, React Router 7, Axios, Vitest)
│   └── vite.config.js             # Konfigurasi Vite + React plugin
│
├── docs/                          # Dokumentasi internal lokal; sengaja tidak dilacak/diunggah ke GitHub
├── .gitignore                     # Proteksi komprehensif file sensitif dan isolasi monolitik lama
└── README.md                      # Dokumentasi pengoperasian proyek terpadu
```

---

## 4. Evaluasi Kesesuaian Terhadap Laporan Proyek Akademik

### 4.1 Analisis Arsitektur Sistem
Dalam Laporan Bab II (Analisis Kebutuhan dan Perancangan Sistem), sistem dirancang untuk melayani berbagai pemangku kepentingan yang memiliki karakteristik interaksi yang sangat berbeda:
*   **Pelanggan:** Membutuhkan pengalaman pemesanan yang cepat pada peramban ponsel (*mobile web*), di mana pemilihan menu, penambahan ke keranjang, dan pemantauan status pesanan tidak boleh memicu muat ulang halaman secara penuh (*full page reload*).
*   **Stasiun Barista & Kitchen (*Kitchen Display System*):** Membutuhkan antrean visual yang dinamis, responsif terhadap perubahan status (Antre $\rightarrow$ Diproses $\rightarrow$ Selesai), dan dapat melakukan polling pembaruan secara berkala tanpa mengganggu tampilan layar kerja.
*   **Kasir POS & Dashboard Owner:** Membutuhkan antarmuka yang padat informasi, interaktif, dengan modal dialog (seperti modal pemilihan meja, modal verifikasi pembayaran, dan grafik analitik penjualan).

### 4.2 Kesesuaian Teknologi & Skalabilitas
*   **Kelemahan Fatal Pendekatan Monolitik Blade:**
    Jika aplikasi dibangun murni menggunakan Laravel Blade monolitik:
    1.  Setiap kali pelanggan menambah item ke keranjang atau mengecek status, seluruh halaman HTML harus di-render ulang oleh server. Ini menghasilkan latensi tinggi dan konsumsi bandwidth yang tidak efisien pada jaringan seluler pelanggan.
    2.  Layar dapur Barista/Kitchen akan sulit diperbarui secara dinamis tanpa penulisan JavaScript jQuery/AJAX mentah yang rumit dan rentan bug di dalam file Blade.
*   **Keunggulan Pendekatan Terpisah (Backend REST API + Frontend React SPA):**
    1.  **Arsitektur Client-Server Standar Industri:** Backend Laravel berfungsi murni sebagai penyedia data bisnis (*headless API provider*) yang aman, terlindungi oleh middleware otorisasi, throttle, dan validasi server-side.
    2.  **Kemandirian Antarmuka (*Decoupled UI*):** Frontend React SPA menangani rendering antarmuka pengguna secara independen, sangat cepat, dan mulus (*smooth transition*).
    3.  **Dukungan Masa Depan (*Future Proofing*):** Jika kafe nantinya memutuskan untuk membuat aplikasi mobile native Android/iOS untuk kasir atau pelanggan, endpoint REST API backend yang ada dapat langsung digunakan kembali tanpa perlu menulis ulang logika server.

### 4.3 Pernyataan Laporan Mengenai Fleksibilitas Teknologi
Pada Laporan Proyek Halaman 12 Bagian 5 (*Technology Used*), tertulis catatan penting:
> *"Teknologi tersebut merupakan usulan awal yang masih perlu dikonfirmasi oleh tim pengembang. Penetapan teknologi final akan menyesuaikan kebutuhan sistem dan kesepakatan anggota tim."*

Ini membuktikan bahwa secara akademis, transisi dari usulan awal monolitik menuju arsitektur modern berbasis REST API + React SPA adalah langkah perbaikan yang sah, beralasan kuat, dan sepenuhnya sejalan dengan semangat rekayasa perangkat lunak dalam laporan.

---

## 5. Audit Teknis Mendalam

### 5.1 Backend (`backend/`)
*   **Framework & Dependency:** Menggunakan Laravel 13 dan PHP 8.3.30. Dependensi dikelola secara bersih via `backend/composer.json`, mencakup `laravel/sanctum` untuk otorisasi bearer token. Tidak ada paket frontend yang mengotori dependensi backend.
*   **Database PostgreSQL 16:** Skema database didukung oleh 29 migrasi yang teruji, mencakup integritas relasional, constraint `CHECK`, partial unique indexes (misal: satu pembayaran pending per pesanan), dan tipe data modern `timestamptz` serta `jsonb`.
*   **Keamanan & Otorisasi:** Rute API dilindungi oleh middleware multi-lapis: `throttle:staff` / `throttle:customer`, `auth:sanctum`, `active`, serta matriks izin peran (*Role-Based Access Control*) berbasis tabel `user_permissions` dan config `permissions.php`.
*   **Kualitas & Pengujian:** Memiliki suite pengujian otomatis lengkap (`backend/tests/`) dengan 71/71 tes (Feature & Unit), 537 assertions pada database pengujian terisolasi `cafe_exito_test`, berdasarkan laporan validasi Codex terbaru. Angka ini tidak dijalankan ulang dalam rekonsiliasi ini.

### 5.2 Frontend (`frontend/`)
*   **Teknologi:** React 19, Vite 8.3.5, Tailwind CSS 4, dan React Router 7.
*   **Isolasi Pengembangan (*Mock Mode*):** Frontend dilengkapi dengan *mock service engine* (`src/mocks/`) yang dikendalikan oleh konfigurasi `VITE_USE_MOCK` pada `.env`. Fitur ini memungkinkan pengembang antarmuka untuk tetap bekerja, menguji alur visual, dan mendemonstrasikan sistem bahkan ketika server backend sedang mati atau dalam proses migrasi database.
*   **Kemandirian Build & Pengujian:** Frontend memiliki dependensi mandiri di `frontend/package.json` dan suite pengujian unit/integrasi UI mandiri dengan Vitest (`npm test`: 20/20 tes lulus) serta build produksi yang terverifikasi (`npm run build` sukses menghasilkan bundel di `frontend/dist/`).

### 5.3 Integrasi API & Keamanan Jaringan
*   **CORS (Cross-Origin Resource Sharing):** Dikelola secara dinamis melalui `backend/config/cors.php`, membaca variabel lingkungan `CORS_ALLOWED_ORIGINS` untuk mengizinkan komunikasi aman dari domain frontend (`http://localhost:5173`).
*   **Autentikasi Token Sanctum:** Token sesi kasir dan staf disimpan aman di `localStorage` melalui lapisan `tokenStorage.js` dan secara otomatis disematkan sebagai header `Authorization: Bearer <token>` pada setiap panggilan Axios.
*   **Pelanggan Tanpa Login (Token Meja & ULID):** Pelanggan tidak memerlukan login staf, melainkan diotentikasi melalui token meja (`X-Table-Token`) dan pengidentifikasi pesanan berbasis ULID (`public_token`), mencegah kerentanan IDOR (*Insecure Direct Object Reference*).

### 5.4 Kebersihan Repository & Analisis File Residu
Pemeriksaan status pelacakan Git menunjukkan kondisi berikut:
1.  **Struktur di Branch `cleanup/cafe-exito-structure`:**
    Branch ini telah merefleksikan arsitektur yang benar: penghapusan file-file monolitik lama di root telah di-stage (`git status` mencatat *deleted* untuk file-file Blade lama dan *renamed* untuk pemindahan `cafe-exito/backend` $\rightarrow$ `backend` serta `cafe-exito/frontend` $\rightarrow$ `frontend`).
2.  **File Residu Fisik di Root Lokal:**
    Pada disk kerja lokal (`E:\laragon\www\cafe-exito`), file-file lama seperti `artisan`, `composer.json`, `package.json`, direktori `app/`, `resources/views/`, serta folder `cafe-exito/` masih tertinggal secara fisik karena proses staging Git belum di-commit dan belum di-clean.
3.  **File Sampah / Anomali:**
    *   File `ntent .npmrc` pada root (terindikasi akibat kesalahan pengetikan perintah terminal saat membuat file `.npmrc`).
    *   File `DomainError.php` di root (file duplikat dari `backend/app/Exceptions/DomainError.php`).
4.  **Keamanan `.gitignore`:**
    File `.gitignore` di root telah diperbarui dengan sangat baik: secara eksplisit mengabaikan file lingkungan rahasia (`.env`), direktori penyimpanan file privat (`storage/app/private/`), cadangan database (`backups/`), serta mengabaikan file residu lama agar tidak kembali terdorong ke GitHub.

---

## 6. Evaluasi Kolaborasi Tim Pengembang

Dalam proyek tim yang terdiri dari 3 programmer (Zaky, Dingga, Nailul), struktur direktori memiliki dampak langsung terhadap produktivitas dan keharmonisan integrasi:

| Kriteria Evaluasi | Struktur Lama (Monolitik di Root) | Struktur Baru (`backend/` + `frontend/`) | Analisis & Dampak |
|---|---|---|---|
| **Pembagian Kerja (*Separation of Concerns*)** | Buruk. Logika server, query database, styling, dan markup tercampur dalam file Blade. | Sangat Baik. Batas antara backend engineer (API & DB) dan frontend engineer (UI/UX) terdefinisi jelas. | Pada struktur lama, jika programmer A mengubah tampilan kasir dan programmer B mengubah logika login, keduanya mengedit file yang sama dan rawan konflik merge. |
| **Konflik Kode (*Git Merge Conflicts*)** | Sangat Tinggi. Semua anggota tim bekerja di direktori root yang sama (`resources/views/`). | Sangat Rendah. Programmer backend bekerja di `backend/`, programmer frontend bekerja di `frontend/`. | Pemisahan direktori meminimalkan tumpang tindih file saat commit bersamaan. |
| **Manajemen Dependensi** | Kacau. Dependensi PHP dan Node.js bercampur di root, menimbulkan kebingungan versi package. | Terisolasi & Rapi. `backend/composer.json` khusus PHP; `frontend/package.json` khusus Node.js. | Menghilangkan konflik versi dan menyederhanakan instalasi CI/CD. |
| **Pengujian Otomatis (*Automated Testing*)** | Lemah. Hanya ada pengujian dasar Laravel tanpa pengujian antarmuka pengguna. | Komprehensif. PHPUnit di backend (71 test, 537 assertions; hasil validasi Codex terbaru) dan Vitest di frontend (20 test) berjalan independen. | Memungkinkan pengujian fitur backend tanpa harus menjalankan server frontend, dan sebaliknya. |
| **Fleksibilitas Deployment** | Kaku. Backend dan frontend harus di-deploy ke satu server web berbasis PHP. | Fleksibel & Ekonomis. Backend dapat berjalan di VPS PHP/Docker, frontend dapat di-deploy gratis di CDN (Cloudflare Pages/Vercel). | Mengurangi beban server utama dan meningkatkan kecepatan akses bagi pelanggan kafe. |

---

## 7. Tabel Putusan Akhir

| Aspek Penilaian | Struktur Lama (Monolith di Root) | Struktur Baru (`backend/` + `frontend/`) | Penilaian Auditor |
|---|---|---|---|
| **Kesesuaian Laporan Proyek** | Rendah (Hanya berupa prototipe tampilan tanpa database dan logika). | **Sangat Tinggi** (Mengimplementasikan seluruh alur use case, ERD, dan diagram sekuens). | Struktur baru merealisasikan fungsionalitas nyata yang dituntut laporan akademik. |
| **Kesesuaian Arsitektur Sistem** | Rendah (Monolitik SSR tidak cocok untuk multi-terminal KDS dan QR self-order). | **Sangat Tinggi** (Arsitektur Client-Server REST API + React SPA modern). | Struktur baru mendukung responsivitas mobile dan antrean dapur real-time. |
| **Pemisahan Tanggung Jawab** | Buruk (Semua dependensi dan file bercampur di root). | **Sempurna** (Backend dan frontend terpisah secara independen). | Struktur baru memenuhi prinsip rekayasa *Single Responsibility*. |
| **Manajemen Dependensi** | Rapuh (Bercampur antara PHP dan Node.js di satu level). | **Sangat Baik** (Masing-masing memiliki package manager terisolasi). | Mencegah konflik library dan mempermudah pembaruan modul. |
| **Integrasi API & Keamanan** | Tidak ada API nyata (hanya route web view). | **Sangat Baik** (Sanctum, RBAC, Rate Limiting, API Resources). | Keamanan data transaksi dan otorisasi staf terjamin kuat. |
| **Pengujian Sistem (*Testing*)** | Tidak ada pengujian fungsional bisnis. | **Sangat Lengkap** (71 PHPUnit tests, 537 assertions + 20 Vitest tests; berdasarkan validasi Codex terbaru). | Memberikan kepastian mutu perangkat lunak (*Quality Assurance*). |
| **Kemudahan Pemeliharaan (*Maintainability*)** | Sulit (Kode hardcoded di dalam HTML Blade). | **Sangat Tinggi** (Service-Repository pattern di backend, modular React di frontend). | Penambahan fitur baru di masa depan jauh lebih mudah dan aman. |
| **Kolaborasi Tim Mahasiswa** | Rawan konflik merge antar-programmer. | **Optimal** (Kerja paralel antar-anggota tim tanpa saling mengunci). | Sangat mendukung ritme kerja kelompok pengembang. |
| **Kesiapan Deployment** | Terbatas pada hosting monolitik tradisional. | **Siap Cloud & Multi-Platform** (Pemisahan API dan Static Assets). | Memungkinkan efisiensi biaya infrastruktur kafe. |
| **Kebersihan Repository** | Buruk (Terdapat folder ganda `cafe-exito/`). | **Sedang menuju Sempurna** (Perlu pembersihan akhir file residu di root). | Setelah di-clean, struktur baru menjadi standar repositori profesional. |

---

### KESIMPULAN AUDITOR:
> ### **PILIHAN 2: STRUKTUR BARU LEBIH TEPAT.**
> **Alasan Teknis & Pembuktian:**
> 1. Struktur lama monolitik di root terbukti tidak memiliki fungsionalitas backend nyata (hanya 3 migration dasar Laravel dan rute statis tanpa controller/database). Struktur lama tidak dapat digunakan untuk operasional Cafe Éxito.
> 2. Struktur baru memisahkan `backend/` dan `frontend/` secara profesional, mengimplementasikan 29 migrasi PostgreSQL lengkap, 61 rute REST API, otorisasi Sanctum, dan 24 halaman React interaktif yang lulus seluruh pengujian otomatis.
> 3. Struktur baru memenuhi seluruh kebutuhan sistem yang dirancang pada Laporan Proyek Akademik (Self-Order QR, Antrean Dapur Real-Time Barista/Kitchen, POS Kasir, Absensi GPS/Foto, dan Dashboard Owner).

---

## 8. Rekomendasi Akademik untuk Penyampaian ke Dosen Pembimbing/Penguji

Apabila tim mahasiswa diminta menjelaskan alasan perubahan arsitektur ini kepada Dosen Pembimbing atau Dosen Penguji, berikut poin-poin argumentasi teknis yang terstruktur, lugas, dan akademis:

1.  **Penerapan Arsitektur Modern Client-Server (*Decoupled Architecture*):**
    *   *Penjelasan:* Pada awal perancangan, tim mengusulkan monolitik sederhana. Namun saat memasuki tahap implementasi modul antrean dapur (*Kitchen Display System*) dan pemesanan mandiri pelanggan (*QR Self-Order*), tim menyadari bahwa arsitektur monolitik server-side rendering (Blade) memicu pemuatan ulang halaman (*page refresh*) yang mengganggu operasional kasir dan dapur.
    *   *Solusi:* Sistem ditingkatkan menjadi arsitektur Client-Server berbasis RESTful API (Laravel 13) dan Single Page Application (React 19 + Vite). Hal ini membuat antrean pesanan di layar Barista dan Kitchen dapat diperbarui secara dinamis tanpa me-reload browser.
2.  **Kepatuhan Terhadap Batasan Laporan (*Consistency with Project Scope*):**
    *   *Penjelasan:* Laporan Proyek Bab I Bagian 5 secara eksplisit menyatakan bahwa daftar teknologi awal adalah usulan yang dapat disesuaikan berdasarkan kesepakatan tim dan kebutuhan sistem. Pemisahan `backend/` dan `frontend/` tetap menggunakan teknologi dasar yang disetujui (PHP/Laravel di sisi server dan JavaScript di sisi antarmuka).
3.  **Efisiensi Kolaborasi & Pembagian Peran Anggota Tim:**
    *   *Penjelasan:* Kelompok memiliki 3 orang programmer. Dengan memisahkan `backend/` dan `frontend/`, pembagian tugas menjadi jauh lebih jelas dan terukur. Programmer A dan B dapat fokus membangun API, database, dan logika bisnis di `backend/`, sementara Programmer C fokus pada rancangan UI/UX responsif di `frontend/`, tanpa risiko saling menimpa kode (*merge conflicts*).
4.  **Standar Industri & Kualitas Pengujian (*Testability*):**
    *   *Penjelasan:* Struktur terpisah memungkinkan implementasi *Automated Unit & Feature Testing* yang komprehensif. Laporan validasi Codex terbaru mencatat 71 tes backend (537 assertions) dan 20 tes frontend. Hasil otomatis tersebut tidak membuktikan perangkat lunak bebas regresi maupun menyatakan UAT lulus; kesiapan tetap bergantung pada pengujian UAT berbukti.

---

## 9. Instruksi Tindak Lanjut untuk Codex

Bagian ini ditujukan sebagai panduan teknis bagi Codex untuk melakukan finalisasi repositori secara aman tanpa merusak integritas sistem:

### 9.1 Struktur Repositori Sasaran (*Target Clean Architecture*)
Repositori harus dibersihkan hingga hanya memiliki struktur kanonikal berikut di root:
```text
repository-root/
├── backend/                       # Seluruh kode Laravel REST API
├── frontend/                      # Seluruh kode React + Vite
├── docs/                          # Dokumentasi internal; dipertahankan lokal dan dikecualikan dari GitHub berdasarkan keputusan proyek
├── backups/                       # Cadangan data (lokal/diabaikan git)
├── .gitignore                     # Aturan ignore bersih
├── AGENTS.md                      # Instruksi agen
└── README.md                      # Dokumentasi proyek terpadu
```

### 9.2 File dan Direktori yang HARUS DIPERTAHANKAN (JANGAN DIHAPUS)
1.  **Direktori `backend/`:** Seluruh isi tanpa terkecuali (kode sumber, konfigurasi, migrasi, tes, `composer.json`, `phpunit.xml`).
2.  **Direktori `frontend/`:** Seluruh isi tanpa terkecuali (komponen, halaman, routing, API client, tes, `package.json`, `vite.config.js`).
3.  **Direktori `docs/`:** Seluruh dokumentasi teknis, laporan audit, dan kontrak API dipertahankan di workspace lokal. Berdasarkan keputusan pengelolaan repository, `docs/` sengaja diabaikan Git dan tidak masuk GitHub; keberadaannya di daftar struktur lokal tidak berarti harus dilacak Git.
4.  **Direktori `backups/`:** Berkas SQL dump dan backup historis database.
5.  **File Konfigurasi Kunci:**
    *   `backend/.env` dan `backend/.env.testing` disimpan lokal untuk runtime/pengujian dan tidak boleh masuk Git.
    *   `frontend/.env` disimpan lokal untuk konfigurasi frontend dan tidak boleh masuk Git.

### 9.3 File dan Direktori Residu yang HARUS DIBERSIHKAN (SAFELY REMOVED)
Setelah memastikan branch `cleanup/cafe-exito-structure` di-merge secara benar ke branch utama:
1.  **Direktori Monolitik Usang di Root Repository:**
    *   `app/` (Hanya berisi controller kosong dan model User usang).
    *   `bootstrap/` di root (Laravel runtime root lama).
    *   `config/` di root (Config root lama).
    *   `database/` di root (Hanya berisi 3 migration kosong bawaan).
    *   `public/` di root (Kecuali jika digunakan sebagai reverse-proxy web server lokal, namun dalam arsitektur terpisah, backend dan frontend memiliki public/ sendiri).
    *   `resources/` di root (Berisi 29 view Blade statis yang sudah digantikan oleh React di `frontend/src/`).
    *   `routes/` di root (`web.php` dan `console.php` usang).
    *   `storage/` di root (Storage lama yang tidak terpakai).
    *   `tests/` di root (Testing skeleton kosong).
2.  **File Residu & Sampah di Root:**
    *   `DomainError.php` di root (Duplikat liar dari `backend/app/Exceptions/DomainError.php`).
    *   `ntent .npmrc` di root (File artefak typo).
    *   `artisan` di root (Gunakan `backend/artisan`).
    *   `composer.json` dan `composer.lock` di root (Gunakan `backend/composer.json`).
    *   `package.json` di root (Gunakan `frontend/package.json`).
    *   `phpunit.xml` di root (Gunakan `backend/phpunit.xml`).
    *   `vite.config.js` di root (Gunakan `frontend/vite.config.js`).
3.  **Direktori Duplikat `cafe-exito/` di Root:**
    *   Folder `cafe-exito/` lokal dapat tetap ada secara fisik pada tahap restrukturisasi ini. Perubahan staged untuk mengeluarkannya dari Git dipertahankan; pembersihan fisik tidak menjadi prasyarat commit/push dalam keputusan proyek saat ini.

### 9.4 Validasi Wajib Sebelum & Sesudah Pembersihan
Sebelum dan setelah melakukan penghapusan file residu, Codex **wajib menjalankan verifikasi teknis**:
1.  **Backend Test Suite:**
    ```bash
    cd backend
    php artisan test
    ```
    *Hasil validasi Codex terbaru:* 71/71 tes lulus, 537 assertions, pada database testing `cafe_exito_test`. Hasil ini dilaporkan sebelumnya dan tidak dijalankan ulang saat rekonsiliasi dokumen ini.
2.  **Frontend Test & Build Suite:**
    ```bash
    cd frontend
    npm test
    npm run build
    ```
    *Hasil validasi Codex terbaru:* 20/20 tes Vitest lulus dan frontend build berhasil. Hasil ini dilaporkan sebelumnya dan tidak dijalankan ulang saat rekonsiliasi dokumen ini.
3.  **Pemeriksaan Integritas Database:**
    *   DILARANG menjalankan `migrate:fresh`, `db:wipe`, atau `DROP TABLE` pada database utama `cafe_exito`.
    *   Pastikan runtime backend tetap membaca koneksi PostgreSQL `cafe_exito` dengan pengguna `cafe_exito_user`.

---
*Laporan Audit Independen ini disusun dengan integritas rekayasa perangkat lunak tertinggi untuk memastikan keberhasilan akademis dan keandalan operasional Cafe Éxito.*

## 10. Rekonsiliasi Final Repository (19 November 2026)

Bagian ini memperbarui rekomendasi pengelolaan repository berdasarkan keputusan proyek final dan menggantikan rekomendasi sebelumnya yang menyiratkan `docs/` harus masuk GitHub.

- Struktur kode resmi tetap `backend/` dan `frontend/`.
- Dokumentasi internal dalam `docs/` tetap disimpan di workspace lokal, tetapi sengaja tidak dilacak Git dan tidak diunggah ke GitHub. Daftar struktur pada dokumen ini menjelaskan susunan lokal; itu bukan daftar berkas yang harus masuk index.
- Folder backup, file environment rahasia, foto absensi, bukti pembayaran, `vendor/`, `node_modules/`, dan `dist/` tidak boleh masuk Git.
- Berkas Laravel lama di root boleh tetap ada secara fisik pada tahap ini. Penghapusan staged dari Git dipertahankan sebagai bagian restrukturisasi; jangan melakukan pembersihan fisik lokal dalam rekonsiliasi ini.
- Stash `backup sebelum sinkronisasi GitHub 2026-11-19` harus dipertahankan.
- Hasil validasi terbaru yang dilaporkan Codex: backend 71/71 tes dengan 537 assertions pada database testing `cafe_exito_test`; frontend 20/20 tes; frontend build berhasil. Rekonsiliasi dokumen ini tidak menjalankan ulang tes, sehingga angka tersebut bukan hasil eksekusi baru.
- Hasil di atas tidak cukup untuk menyatakan sistem READY operasional. Status kesiapan tetap **NOT READY** selama blocker implementasi/UAT yang tercatat pada dokumentasi checkpoint dan status implementasi belum ditutup dengan bukti.