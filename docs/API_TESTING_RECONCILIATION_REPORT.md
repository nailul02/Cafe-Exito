# Rekonsiliasi Final API Testing — Task 3.1–3.8

Tanggal pemeriksaan: 21 November 2026. Pemeriksaan route menggunakan `php artisan route:list --json --path=api/v1` dan pemeriksaan kode pada `backend/routes/api.php`. Database diperiksa dengan query SELECT melalui Laravel. Tidak ada PHPUnit, request HTTP, migration, seeder, atau mutasi database/Git yang dijalankan pada rekonsiliasi ini.

## 1. Rekonsiliasi route

`route:list` mengembalikan **79 registrasi route**. Pengelompokan aktual:

| Metode route:list | Jumlah registrasi | Klasifikasi |
|---|---:|---|
| `GET|HEAD` | 38 | Read-only; HEAD ikut dilayani route GET |
| `POST` | 31 | Mutasi |
| `PUT` | 5 | Mutasi |
| `PATCH` | 3 | Mutasi |
| `DELETE` | 2 | Mutasi |
| **Total** | **79** | **38 read-only + 41 mutasi** |

Matriks mempunyai 79 baris bernomor. Setelah membandingkan signature metode dan URI (prefix `/api/v1` dinormalisasi), semua baris cocok satu per satu dengan route aktif. Tidak ada signature aktif yang hilang dari matriks, signature matriks yang tidak aktif, atau signature method+URI duplikat. Tidak ada perubahan route aplikasi yang diperlukan.

Terdapat **69 pola URI** untuk 79 registrasi, karena URI yang sama dapat memiliki beberapa metode. Contoh: `/api/v1/auth/me` memiliki GET dan PUT; `/api/v1/admin/menus/{menu}` memiliki GET, PUT, dan DELETE. Route GET ditampilkan sebagai `GET|HEAD`; HEAD adalah alias metode HTTP untuk registrasi GET dan tidak dihitung sebagai route tambahan. `route:list` tidak menunjukkan route bernama/alias tambahan.

Angka lama **43 GET/HEAD + 36 mutasi** berjumlah 79 tetapi tidak cocok dengan metode pada route aktif maupun baris metode pada matriks. Angka terbaru **38 + 41** sesuai route aktual. Tidak ditemukan lima route tertentu yang ditambahkan/dihapus atau berubah metode untuk menjelaskan selisih; seluruh signature sekarang sudah cocok. Karena snapshot route lama 43/36 tidak tersedia, penyebab historis salah hitung per route **tidak dapat dipastikan**. Statistik ringkasan `docs/API_TESTING_MATRIX.md` telah dikoreksi menjadi 38 read-only dan 41 mutasi.

## 2. `personal_access_tokens`

Query SELECT saat rekonsiliasi menemukan **23 baris**. Kolom `last_used_at` tersedia. Tabel berikut hanya metadata; token secret/isi kolom token tidak dibaca.

| Token ID | User ID pemilik | Dibuat (nilai DB) | Terakhir digunakan (nilai DB) |
|---:|---:|---|---|
| 15 | 6 | 2026-11-12 23:19:52 | NULL |
| 16 | 7 | 2026-11-12 23:19:53 | NULL |
| 17 | 7 | 2026-11-12 23:19:53 | NULL |
| 18 | 8 | 2026-11-12 23:19:53 | NULL |
| 19 | 8 | 2026-11-12 23:19:54 | NULL |
| 20 | 9 | 2026-11-12 23:19:54 | NULL |
| 21 | 9 | 2026-11-12 23:19:55 | NULL |
| 22 | 10 | 2026-11-12 23:19:55 | NULL |
| 23 | 10 | 2026-11-12 23:19:55 | NULL |
| 24 | 6 | 2026-11-12 23:31:11 | NULL |
| 25 | 6 | 2026-11-12 23:31:11 | NULL |
| 26 | 7 | 2026-11-12 23:31:11 | NULL |
| 27 | 7 | 2026-11-12 23:31:12 | NULL |
| 28 | 8 | 2026-11-12 23:31:12 | NULL |
| 29 | 8 | 2026-11-12 23:31:12 | NULL |
| 30 | 9 | 2026-11-12 23:31:13 | NULL |
| 31 | 9 | 2026-11-12 23:31:13 | NULL |
| 32 | 10 | 2026-11-12 23:31:13 | NULL |
| 33 | 10 | 2026-11-12 23:31:13 | NULL |
| 34 | 7 | 2026-11-13 01:24:58 | 2026-11-13 01:24:59 |
| 39 | 9 | 2026-11-13 01:51:29 | 2026-11-13 02:30:50 |
| 41 | 6 | 2026-11-13 20:28:43 | 2026-11-13 20:29:03 |
| 45 | 7 | 2026-11-13 20:53:25 | 2026-11-13 22:06:53 |

Baseline yang tercatat saling bertentangan:

- `docs/API_TESTING_AUTH_REPORT.md` menyatakan `personal_access_tokens` 0 sebelum dan sesudah Task 3.4.
- `docs/DATABASE_TESTING_AUDIT_REPORT.md` mencatat 23 pada snapshot auditnya.
- Catatan baseline pada awal eksekusi Task 3.5–3.8 merekam total 23 sebelum pengujian gabungan; pemeriksaan akhir Task tersebut juga merekam 23, tetapi catatan awal tidak menyimpan daftar ID baseline.

Pada task ini jumlah tetap 23. Semua 23 token saat ini memiliki timestamp pembuatan 12–13 November; metadata itu tidak membuktikan siapa yang menerbitkan atau untuk tujuan apa. Catatan sesi Task 3.5–3.8 menyebut token sementara yang dibuat untuk pengujian dibersihkan, tetapi baseline tanpa ID dan konflik laporan Task 3.4 membuat atribusi per token aktif tidak dapat diverifikasi. **Asal-usul 23 token: UNRESOLVED.** Tidak ada token yang dihapus/dicabut atau diubah dalam rekonsiliasi ini.

## 3. Rekonsiliasi jumlah data

| Tabel | Baseline terdokumentasi | Jumlah saat ini | Penilaian |
|---|---:|---:|---|
| `users` | 5 (Task 3.4) | 5 | Jumlah sama |
| `orders` | 14 (Task 3.3/3.4) | 14 | Jumlah sama |
| `payments` | 14 (Task 3.3/3.4) | 14 | Jumlah sama |
| `order_details` | 15 (Task 3.3/3.4) | 15 | Jumlah sama |
| `audit_logs` | 5 pada audit DB lama; 12 pada awal Task 3.5–3.8 | 12 | Sama dengan baseline gabungan; baseline audit lama berbeda |
| `personal_access_tokens` | 0 di laporan Auth; 23 di audit DB lama dan preflight Task 3.5–3.8 | 23 | Baseline bertentangan; asal-usul UNRESOLVED |
| `payment_proofs` | 0 (Task 3.3/3.4) | 0 | Jumlah sama |
| `order_tickets` | 0 pada audit DB lama | 0 | Jumlah sama pada baseline yang tersedia |
| `order_status_histories` | Tidak ada baseline perbandingan yang dapat dipastikan | 0 | Baseline tidak tersedia |
| `order_item_cancellations` | 0 (Task 3.3) | 0 | Jumlah sama |
| `refund_requests` | 0 (Task 3.3) | 0 | Jumlah sama |
| `refund_request_items` | Tidak ada baseline perbandingan yang dapat dipastikan | 0 | Baseline tidak tersedia |
| `refund_events` | Tidak ada baseline perbandingan yang dapat dipastikan | 0 | Baseline tidak tersedia |
| `refund_executions` | Tidak ada baseline perbandingan yang dapat dipastikan | 0 | Baseline tidak tersedia |
| `refund_conflict_notes` | Tidak ada baseline perbandingan yang dapat dipastikan | 0 | Baseline tidak tersedia |
| `stock_movements` | Tidak ada baseline perbandingan yang dapat dipastikan | 0 | Baseline tidak tersedia |

Jumlah inti transaksi cocok dengan baseline laporan. Sebelum/sesudah Task 3.5–3.8 juga tercatat sama: users 5, orders 14, payments 14, order_details 15, audit_logs 12, dan token 23. Pemeriksaan jumlah baris tidak membuktikan isi setiap baris identik; tidak tersedia snapshot/hash per baris untuk audit ini. Karena itu kesimpulan dibatasi pada jumlah dan catatan bahwa pengujian gabungan tidak membuat transaksi bisnis. `audit_logs` berjumlah 12 sekarang dan pada awal Task 3.5–3.8, tetapi baseline lebih lama mencatat 5; penyebab selisih historis tidak dapat ditetapkan dari count saja.

## 4. Repository dan perubahan kode

`git status --short` dan `git diff` dijalankan read-only. Working tree memang sudah memuat banyak perubahan staged, unstaged, dan untracked dari pekerjaan sebelumnya; tidak ada yang dibersihkan atau ditimpa. Perubahan yang diperiksa tetap ada:

- `backend/app/Http/Requests/SaveUserRequest.php`: normalisasi username/email hanya dilakukan untuk nilai string; tipe lain mencapai validasi request.
- `backend/app/Http/Controllers/Api/V1/Station/StationController.php`: `updated_since` divalidasi sebagai tanggal sebelum parsing Carbon.
- `backend/app/Providers/AppServiceProvider.php`: limiter login memakai nilai login yang dinormalisasi hanya jika berupa string; perlindungan rate limit tetap aktif.

Diff `StationController` juga menunjukkan formatting pada `startItem`; bagian itu tidak diubah selama rekonsiliasi ini dan tidak disentuh. Tidak ditemukan kerusakan pada tiga perbaikan tersebut.

## 5. Status Task 3 dan rekomendasi

Task 3 tetap **PASS WITH ISSUES**. Rekonsiliasi route dan count berhasil, namun skenario mutasi positif bisnis (QRIS proof, POS, tiket, cancellation/refund) masih **BLOCKED** dalam laporan Task 3 gabungan. Rekomendasi: lanjut ke Task 4 hanya jika Task 4 secara eksplisit dapat berjalan dengan batasan blocker tersebut; untuk sign-off perilisan, siapkan environment/fixture terisolasi agar alur mutasi positif dapat diuji tanpa menyentuh data produksi/lokal aktif. Jangan menganggap rekonsiliasi ini sebagai PASS penuh.
