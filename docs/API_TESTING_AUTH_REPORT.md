# LAPORAN PENGUJIAN API — TASK 3.4: AUTHENTICATION TESTING
**Cafe Éxito Backend API Testing**

---

## 1. Ringkasan Eksekutif

| Parameter | Keterangan |
| :--- | :--- |
| **Tanggal Pengujian** | 2026-11-21 04:54:41 WIB |
| **Lingkungan** | Localhost (`http://127.0.0.1:8000/api/v1`) |
| **Database Aktif** | PostgreSQL `cafe_exito` (Host: `127.0.0.1:5432`) |
| **Total Skenario** | 36 Skenario (termasuk retest BUG-AUTH-01) |
| **Hasil** | **30 PASS** / **0 FAIL** / **5 BLOCKED** / **1 NOT TESTED** |
| **Status Integritas Data** | **PASS — 100% DATA UTUH (Zero Modification)** |
| **Temuan Bug** | BUG-AUTH-01 diperbaiki; input bertipe tidak valid menghasilkan HTTP 422, dan throttle tetap membatasi request |

---

## 2. Kepatuhan Batasan & Keamanan

Pengujian dijalankan dengan kepatuhan penuh terhadap seluruh batasan keamanan:
1. **Zero Data Loss & Zero Modification**: Tidak ada akun baru, password tidak diubah, tidak ada data transaksi lama (orders, payments, order_details, users) yang terhapus atau termodifikasi.
2. **Tidak Menggunakan PHPUnit / RefreshDatabase**: Pengujian dieksekusi secara nyata melalui protokol HTTP (cURL) dan runtime Laravel murni tanpa mock/sandbox test runner.
3. **Kerahasiaan Kredensial**: Tidak ada token atau password yang dicetak ke dalam laporan maupun log sistem.
4. **Tidak Melakukan Brute-Force**: Kredensial valid HTTP yang tidak diketahui ditandai secara jujur sebagai `BLOCKED` sesuai aturan audit.

---

## 3. Matriks Hasil Pengujian Detail

### A. Skenario Login Negatif (HTTP)

| ID | Endpoint | Method | Deskripsi Skenario | Expected | Actual | Status | Catatan |
| :--- | :--- | :---: | :--- | :---: | :---: | :---: | :--- |
| **AUTH-01** | `/auth/login` | POST | Request body kosong `{}` | 422 | 422 | **PASS** | Ditangkap oleh validation envelope standar |
| **AUTH-02** | `/auth/login` | POST | Username tidak terdaftar | 422 | 422 | **PASS** | Pesan error aman terpadu: `Email/username atau kata sandi salah.` |
| **AUTH-03** | `/auth/login` | POST | Username valid (`owner`), password salah | 422 | 422 | **PASS** | Audit log `auth.login_failed` tercatat, respons 422 |
| **AUTH-04** | `/auth/login` | POST | Email tidak terdaftar | 422 | 422 | **PASS** | Pesan error terpadu aman |
| **AUTH-05** | `/auth/login` | POST | Field `login` tidak dikirim | 422 | 422 | **PASS** | Validasi `login: required` bekerja |
| **AUTH-06** | `/auth/login` | POST | Field `password` tidak dikirim | 422 | 422 | **PASS** | Validasi `password: required` bekerja |
| **AUTH-07** | `/auth/login` | POST | Field `login` bertipe array `['a','b']` (retest setelah BUG-AUTH-01) | 422 | 422 | **PASS** | Limiter menormalkan tipe non-string ke key aman; `LoginRequest` mengembalikan `VALIDATION_FAILED` |
| **AUTH-07b**| `/auth/login` | POST | Field `password` bertipe array `['a','b']` | 422 | 422 | **PASS** | Ditangkap oleh validasi `LoginRequest` (string) |
| **AUTH-07c**| `/auth/login` | POST | Percobaan login akun nonaktif | 403 | - | **NOT TESTED** | Seluruh 5 akun pada database aktif berstatus `is_active = true`. Modifikasi data akun dilarang. |

### B. Skenario Login Kredensial Valid (HTTP) — BLOCKED

Sesuai instruksi audit ("Gunakan kredensial yang benar-benar diketahui dan telah dikonfirmasi. Jangan menebak atau mencoba daftar password umum. Jika password tidak tersedia, tandai BLOCKED"):

| ID | Akun | Role | Endpoint | Expected | Status | Alasan BLOCKED |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| **AUTH-08** | `owner` | Owner | `POST /auth/login` | 200 | **BLOCKED** | Password live database tidak diketahui; dilarang menebak password |
| **AUTH-09** | `manager` | Manager | `POST /auth/login` | 200 | **BLOCKED** | Password live database tidak diketahui; dilarang menebak password |
| **AUTH-10** | `ucup` | Kasir | `POST /auth/login` | 200 | **BLOCKED** | Password live database tidak diketahui; dilarang menebak password |
| **AUTH-11** | `nana` | Barista | `POST /auth/login` | 200 | **BLOCKED** | Password live database tidak diketahui; dilarang menebak password |
| **AUTH-12** | `dewi` | Kitchen | `POST /auth/login` | 200 | **BLOCKED** | Password live database tidak diketahui; dilarang menebak password |

### C. Penerbitan Sanctum Token & Akses Read-Only (`GET /auth/me`)

Token pengujian sesi Task 3.4 diterbitkan langsung melalui runtime Laravel Sanctum resmi (`$user->createToken()`) untuk masing-masing role dan diuji mengakses endpoint HTTP terproteksi:

| ID | Username | Role Target | Station | HTTP Exp | HTTP Act | Status | Atribut Terverifikasi |
| :--- | :--- | :--- | :--- | :---: | :---: | :---: | :--- |
| **AUTH-13** | `owner` (ID: 6) | `owner` | `null` | 200 | 200 | **PASS** | Role: `Owner`, Permissions: 16 item |
| **AUTH-14** | `manager` (ID: 7) | `manager` | `null` | 200 | 200 | **PASS** | Role: `Manager`, Permissions: 13 item |
| **AUTH-15** | `ucup` (ID: 8) | `kasir` | `null` | 200 | 200 | **PASS** | Role: `Kasir`, Permissions: 10 item |
| **AUTH-16** | `nana` (ID: 9) | `barista_kitchen` | `barista` | 200 | 200 | **PASS** | Role: `Barista & Kitchen`, Station: `barista`, Perms: 4 item (`queue.barista`) |
| **AUTH-17** | `dewi` (ID: 10) | `barista_kitchen` | `kitchen` | 200 | 200 | **PASS** | Role: `Barista & Kitchen`, Station: `kitchen`, Perms: 4 item (`queue.kitchen`) |

### D. Keamanan Format Token & Header (HTTP)

| ID | Endpoint | Method | Kondisi Header Authorization | Expected | Actual | Status | Respons |
| :--- | :--- | :---: | :--- | :---: | :---: | :---: | :--- |
| **AUTH-18** | `/auth/me` | GET | Header tidak disertakan | 401 | 401 | **PASS** | `code: UNAUTHENTICATED` |
| **AUTH-19** | `/auth/me` | GET | Bearer token palsu/random | 401 | 401 | **PASS** | `code: UNAUTHENTICATED` |
| **AUTH-20** | `/auth/me` | GET | Header tanpa skema `Bearer ` (skema salah) | 401 | 401 | **PASS** | `code: UNAUTHENTICATED` |
| **AUTH-21** | `/auth/me` | GET | Header `Authorization: Bearer ` (string kosong) | 401 | 401 | **PASS** | `code: UNAUTHENTICATED` |
| **AUTH-22** | `/auth/me` | GET | Bearer token dengan ID fiktif (`999999\|hash`) | 401 | 401 | **PASS** | `code: UNAUTHENTICATED` |

### E. Pencabutan Token & Logout (HTTP)

Pengujian memverifikasi bahwa `POST /auth/logout` benar-benar menghapus record access token dari database `personal_access_tokens` dan request berikutnya langsung ditolak:

| ID | Akun | Role | Logout HTTP | Record DB Terhapus? | Akses Post-Logout `/auth/me` | Status |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| **AUTH-23** | `owner` | Owner | 200 OK | **YA (Deleted)** | 401 Unauthenticated | **PASS** |
| **AUTH-24** | `manager` | Manager | 200 OK | **YA (Deleted)** | 401 Unauthenticated | **PASS** |
| **AUTH-25** | `ucup` | Kasir | 200 OK | **YA (Deleted)** | 401 Unauthenticated | **PASS** |
| **AUTH-26** | `nana` | Barista | 200 OK | **YA (Deleted)** | 401 Unauthenticated | **PASS** |
| **AUTH-27** | `dewi` | Kitchen | 200 OK | **YA (Deleted)** | 401 Unauthenticated | **PASS** |
| **AUTH-28** | *(unauth)* | - | 401 | N/A | POST `/auth/logout` tanpa token | **PASS** |

---

## 4. Rincian Hak Akses & Peran Pengguna (Dari Endpoint `/auth/me`)

Berdasarkan respons aktual dari API `GET /auth/me` terhadap 5 akun resmi:

### 1. Akun Owner (`owner`, ID: 6)
- **Role Code**: `owner`
- **Role Label**: `Owner`
- **Station**: `null`
- **Total Permissions**: 16
- **Daftar Permissions**:
  `dashboard.owner`, `orders.view`, `orders.cancel`, `transactions.view`, `queue.view_all`, `menus.view`, `reports.view`, `attendance.view_all`, `attendance.report`, `users.manage`, `permissions.grant`, `refund.review`, `refund.audit`, `audit.view`, `refund.conflict.document`, `notifications.view`

### 2. Akun Manager (`manager`, ID: 7)
- **Role Code**: `manager`
- **Role Label**: `Manager`
- **Station**: `null`
- **Total Permissions**: 13
- **Daftar Permissions**:
  `dashboard.manager`, `orders.view`, `transactions.view`, `queue.view_all`, `menus.view`, `menus.manage`, `tables.manage`, `reports.view`, `audit.view`, `attendance.view_all`, `attendance.record`, `refund.conflict.document`, `notifications.view`

### 3. Akun Kasir (`ucup`, ID: 8)
- **Role Code**: `kasir`
- **Role Label**: `Kasir`
- **Station**: `null`
- **Total Permissions**: 10
- **Daftar Permissions**:
  `dashboard.cashier`, `payments.verify`, `transactions.view`, `menus.view`, `orders.create_pos`, `attendance.record`, `refund.view`, `refund.execute`, `refund.request`, `notifications.view`

### 4. Akun Barista (`nana`, ID: 9)
- **Role Code**: `barista_kitchen`
- **Role Label**: `Barista & Kitchen`
- **Station**: `barista`
- **Total Permissions**: 4
- **Daftar Permissions**:
  `dashboard.station`, `queue.barista`, `attendance.record`, `notifications.view`

### 5. Akun Kitchen (`dewi`, ID: 10)
- **Role Code**: `barista_kitchen`
- **Role Label**: `Barista & Kitchen`
- **Station**: `kitchen`
- **Total Permissions**: 4
- **Daftar Permissions**:
  `dashboard.station`, `queue.kitchen`, `attendance.record`, `notifications.view`

---

## 5. Analisis Temuan Bug

### BUG-AUTH-01: HTTP 500 saat Input `login` Berbentuk Array
- **Lokasi Kode**: `backend/app/Providers/AppServiceProvider.php:36`
- **Baris Bermasalah**:
  ```php
  RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by(mb_strtolower((string) $r->input('login')).'|'.$r->ip()));
  ```
- **Penyebab**: Jika client mengirim payload JSON `{ "login": ["item1", "item2"], "password": "..." }`, fungsi `(string) $r->input('login')` dievaluasi di dalam closure RateLimiter sebelum middleware FormRequest validasi sempat dijalankan. Di PHP 8.2+, casting array ke string menghasilkan notice/error `Array to string conversion` yang ditangkap exception handler Laravel dan menyebabkan crash HTTP 500 alih-alih HTTP 422 `VALIDATION_FAILED`.
- **Rekomendasi Solusi**: Ubah baris tersebut menjadi tipe-aman:
  ```php
  RateLimiter::for('login', function (Request $r) {
      $login = $r->input('login');
      $normalizedLogin = is_string($login) ? mb_strtolower($login) : '';

      return Limit::perMinute(5)->by($normalizedLogin.'|'.$r->ip());
  });
  ```

**Status perbaikan:** Diterapkan pada `backend/app/Providers/AppServiceProvider.php`. Nilai login hanya dinormalisasi jika bertipe string; nilai array, object, angka, null, dan tipe lain memakai key kosong yang digabung dengan IP. Request tetap melewati `LoginRequest`; aturan autentikasi tidak berubah.

### Retest HTTP BUG-AUTH-01 (21 November 2026)

Request dikirim ke API lokal melalui HTTP dengan JSON sintetis (tidak memakai kredensial akun). Tidak ada PHPUnit, Feature Test, seeder, atau migration yang dijalankan.

| Skenario | Hasil HTTP | Hasil |
| :--- | :---: | :---: |
| `login` berupa array | 422 `VALIDATION_FAILED`, field `login` | **PASS** |
| `login` berupa object JSON | 422 `VALIDATION_FAILED`, field `login` | **PASS** |
| `login` berupa angka | 422 `VALIDATION_FAILED`, field `login` | **PASS** |
| `login` string kosong | 422 `VALIDATION_FAILED`, field `login` | **PASS** |
| `login` string tidak terdaftar | 422 `VALIDATION_FAILED`, penolakan pada field `login` sesuai AuthController | **PASS** |
| `password` kosong | 422 `VALIDATION_FAILED`, field `password` | **PASS** |
| Rate limit, key/IP yang sama | 5 request diizinkan sebagai 422; request ke-6 mendapat 429 | **PASS** |

Tidak ada HTTP 500 atau PHP ErrorException pada skenario tersebut. Login sukses **BLOCKED**: kredensial valid tidak tersedia dan tidak ditebak.

---

## 6. Verifikasi Integritas Database

Pemeriksaan kuantitas data tabel sebelum dan sesudah seluruh siklus pengujian Task 3.4:

| Nama Tabel | Baseline Task 2 / 3.3 | Pasca Task 3.4 | Perubahan | Status Integritas |
| :--- | :---: | :---: | :---: | :---: |
| `orders` | 14 | 14 | 0 | **PASS (Utuh)** |
| `payments` | 14 | 14 | 0 | **PASS (Utuh)** |
| `order_details` | 15 | 15 | 0 | **PASS (Utuh)** |
| `users` | 5 | 5 | 0 | **PASS (Utuh)** |
| `payment_proofs` | 0 | 0 | 0 | **PASS (Utuh)** |
| `personal_access_tokens` | 0 | 0 | 0 | **PASS (Bersih)** |

> [!NOTE]
> Seluruh token pengujian sementara yang diterbitkan selama Task 3.4 telah dicabut melalui endpoint `POST /auth/logout` dan diverifikasi tidak meninggalkan sisa pada tabel `personal_access_tokens`.

### Verifikasi pasca perbaikan BUG-AUTH-01 (SELECT saja)

| Tabel | Sebelum retest | Sesudah retest | Perubahan |
| :--- | ---: | ---: | ---: |
| `orders` | 14 | 14 | 0 |
| `payments` | 14 | 14 | 0 |
| `order_details` | 15 | 15 | 0 |
| `users` | 5 | 5 | 0 |

Jumlah dibaca melalui query `SELECT COUNT(*)` sebelum dan sesudah HTTP retest. Tidak ada order, payment, detail order, atau user yang dibuat atau diubah. Request username tak terdaftar mengikuti implementasi yang mencatat `auth.login_failed` ke audit log; tidak membuat transaksi.

---

## 7. Kesimpulan & Rekomendasi

1. **Autentikasi Sanctum Berfungsi Sangat Baik**: Token yang valid berhasil mengautentikasi dan mengenali identitas 5 role secara presisi, termasuk stasiun kerja barista dan kitchen.
2. **Pencabutan Token Efektif**: Mekanisme logout menghapus token dari database secara permanen, mencegah penggunaan ulang token (replay).
3. **Penolakan Token Ilegal Handal**: Semua variasi request tanpa token, token acak, format non-Bearer, maupun token kosong ditolak secara konsisten dengan HTTP 401 `UNAUTHENTICATED`.
4. **BUG-AUTH-01**: Diperbaiki dan diuji ulang; input non-string ditangani validasi dengan HTTP 422, dan limiter mengembalikan HTTP 429 setelah batas lima request per menit tercapai.
5. **Kredensial HTTP Valid**: Status `BLOCKED` dicatat secara formal untuk pengujian HTTP login 5 role karena ketiadaan password akun aktif; pengujian berhasil dijembatani secara aman menggunakan penerbitan token via runtime tanpa modifikasi password.

## 8. File yang diubah untuk BUG-AUTH-01

- `backend/app/Providers/AppServiceProvider.php` — normalisasi key RateLimiter aman untuk nilai non-string, tetap 5 request per menit per login/IP.
- `docs/API_TESTING_AUTH_REPORT.md` — memperbarui status bug, mencatat hasil HTTP retest, status limiter, dan integritas empat tabel yang diwajibkan.
