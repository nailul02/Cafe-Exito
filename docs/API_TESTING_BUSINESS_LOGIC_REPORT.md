# API Testing Report — Task 3.7 Business Logic

Tanggal: 21 November 2026. Tidak menjalankan test runner, migration, seeder, atau mutation yang menyentuh data bisnis.

## Pemeriksaan

- Pemeriksaan statis dan request invalid mendukung alur terpisah antara status order dan payment. Request salah bentuk atau ID tidak tersedia ditolak; tidak ada perubahan data bisnis teramati.
- Scope operasional inventory/purchasing tidak aktif: route recipe, inventory, purchasing dan stock usage lama mengembalikan 404; tidak ada route operasional terkait pada route list.
- Pemisahan Barista/Kitchen dan pembatasan akses station diuji melalui role matrix. Refund/cancellation dan POS/QRIS diperiksa dari service, controller, request validation, dan route.
- QRIS Self-Order tetap memerlukan proof pending untuk keputusan Kasir; upload proof tidak menyatakan pembayaran lunas. QRIS POS memakai verifikasi Kasir tanpa proof pelanggan; POS tunai dibuat pending. Batas proof yang tertulis di implementasi adalah 3 MB.

## Yang belum dapat dibuktikan lewat HTTP

Tabel `payment_proofs`, `order_tickets`, dan `refund_requests` kosong, sehingga tidak tersedia fixture existing yang aman untuk menguji upload/verifikasi proof sukses, pembuatan POS/order baru, perubahan tiket produksi, atau keputusan/eksekusi refund/cancellation. Tes positif tersebut **BLOCKED** karena menjalankannya akan membuat transaksi/audit side effect pada database aktif. Tidak ada fixture sintetis yang dibuat.

Database utama tidak dimigrasikan dan tidak dijalankan `RefreshDatabase`. Jumlah inti setelah pemeriksaan tetap users=5, orders=14, payments=14, order_details=15; payment_proofs=0 dan refund_requests=0.
