# Laporan Final Pengujian API Gabungan — Tasks 3.5–3.8

Tanggal: 21 November 2026. Pengujian dilakukan terhadap backend lokal dengan HTTP dan pemeriksaan statis. Tidak menjalankan PHPUnit/Feature Test, migration, seeder, atau mengubah `.env`. Task 4 tidak dikerjakan.

## Ringkasan status

| Area | Hasil |
|---|---|
| RBAC | 90/90 role probes PASS; 33/33 protected GET tanpa token PASS; 39/39 mutation invalid ditolak aman |
| Read-only | 38 GET routes diperiksa; total aktual 79 route API (38 GET/HEAD, 41 mutasi) |
| Security/regression | Authentication, token lifecycle, input invalid dan query injection probes lulus; dua validasi input diperketat |
| Positive business mutations | **BLOCKED** — tidak ada fixture existing yang aman; menjalankan alur akan membuat data/audit log |
| Overall Task 3 | **PASS WITH ISSUES — pengujian alur mutation positif tetap BLOCKED sebelum release sign-off** |

## Perbaikan selama pemeriksaan

- `backend/app/Http/Controllers/Api/V1/Station/StationController.php`: validasi `updated_since` sebelum Carbon parse, agar tanggal salah tidak memicu 500/debug trace.
- `backend/app/Http/Requests/SaveUserRequest.php`: normalisasi username/email hanya untuk string; input array dilewatkan ke validator dan menerima 422.

PHP lint untuk SaveUserRequest lulus. HTTP retest station: invalid scalar/array 422, timestamp valid 200. HTTP retest user: username/email array masing-masing 422. Request tidak membuat akun.

## Integritas database

Read-only count akhir: users=5, orders=14, payments=14, order_details=15, payment_proofs=0, order_tickets=0, refund_requests=0, audit_logs=12, personal_access_tokens=23. Token uji temporer dibersihkan secara spesifik setelah uji. Tidak ada akun, pesanan, pembayaran, proof, tiket, atau refund yang dibuat atau diubah.

## Catatan yang masih terbuka

- Rekonsiliasi route memperbarui angka matrix menjadi 38 GET/HEAD dan 41 mutasi (79 signature method+URI; 69 pola URI). Tidak ditemukan perbedaan route aktual dengan baris inventaris.
- Jumlah `personal_access_tokens` yang ditulis di laporan berbeda-beda; lihat `docs/API_TESTING_RECONCILIATION_REPORT.md`. Asal setiap token aktif belum dapat dipastikan.
- `docs/API_TESTING_PREFLIGHT.md` berisi status server/migrasi/token yang sudah usang.
- Sukses upload proof QRIS, verifikasi/penolakan, POS, alur cancellation/refund, dan mutasi tiket belum diuji terhadap fixture terisolasi. Belum ada bukti seluruh skenario bisnis mutation positif lulus.
- Belum menjalankan test suite backend. Perlu environment database testing disposable yang dikonfirmasi terisolasi sebelum menjalankan Feature Tests.

## Kesimpulan

Tidak ditemukan regresi pada skenario negatif/read-only yang diuji dan tidak ada perubahan data bisnis teramati. Hasil gabungan **PASS WITH ISSUES** untuk cakupan yang aman dijalankan; alur positif yang menulis data tetap **BLOCKED**, sehingga laporan ini bukan sign-off penuh untuk seluruh fitur backend.
