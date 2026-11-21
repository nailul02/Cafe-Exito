# API Testing Report — Task 3.5 RBAC

Tanggal: 21 November 2026. Target: API lokal `127.0.0.1:8000/api/v1`. Pengujian memakai HTTP dan token Sanctum sementara yang dihapus setelah penggunaan. Tidak menjalankan PHPUnit, migration, seeder, atau operasi Git yang mengubah repository.

## Hasil

- **90/90 PASS** untuk 18 endpoint yang diuji pada lima profil (Owner, Manager, Kasir, Barista, Kitchen). Uji mencakup akses dashboard, user/tables, orders/transactions/reports/audit, refund list, permission, attendance, notifications, menu, dan station.
- **33/33 PASS** protected GET tanpa token menolak akses dengan 401.
- **39/39 PASS** mutation dengan payload invalid atau ID yang tidak tersedia menghasilkan respons aman non-2xx (422/403/404); tidak ada 500 atau operasi sukses yang tidak diharapkan.
- Endpoint station membatasi data/aksi berdasarkan station; station yang salah menerima 404. Barista dan Kitchen tetap memiliki izin terpisah.
- Manager tidak dapat menjalankan akses khusus Owner/Kasir yang diuji. Kebijakan Owner memblokir pengelolaan akun Owner dan perubahan akun sendiri. Matriks permission dikonfirmasi dari konfigurasi dan respons HTTP.
- Logout mencabut token yang dipakai; token yang dicabut, kedaluwarsa, dan token acak menerima 401. Pengujian throttle login menerima 429 setelah batas percobaan.

## Batas pengujian

Tidak ada tiket, permintaan refund, proof, atau conflict note yang dapat dipakai sebagai fixture positif. Membuat lalu menghapus transaksi akan menambah audit log dan efek samping, sehingga alur mutasi positif ditandai **BLOCKED**. Keputusan refund nyata dan aksi station yang sukses belum dibuktikan melalui mutation HTTP.

Token pengujian yang dibuat selama pemeriksaan dibersihkan berdasarkan ID/nama uji. Tidak ada akun, role, atau password yang diubah.
