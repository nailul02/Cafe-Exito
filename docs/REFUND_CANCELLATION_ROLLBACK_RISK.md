# Risiko rollback migration pembatalan dan refund

Migration `2026_11_20_000001_add_customer_item_cancellation_and_refunds` bersifat additive pada `up()`. Namun `down()` menjatuhkan tabel `app_notifications`, `refund_conflict_notes`, `refund_executions`, `refund_events`, `refund_request_items`, `refund_requests`, dan `order_item_cancellations`, lalu menghapus kolom waktu proses dan `orders.created_by`.

Karena itu, rollback setelah fitur mencatat transaksi **tidak aman untuk histori**: catatan pembatalan, permintaan/keputusan/eksekusi refund, bukti yang direferensikan, dan notifikasi akan hilang dari database. Jangan menjalankan `migrate:rollback` atau `migrate:reset` pada database yang berisi penggunaan fitur ini tanpa backup terverifikasi dan prosedur pemulihan/ekspor histori yang telah disetujui.

Sebelum menerapkan migration ke database utama, buat backup database penuh, verifikasi bahwa backup dapat dibaca/dipulihkan pada lingkungan terpisah, dan simpan di lokasi yang terpisah dari host aplikasi. Migration utama belum dijalankan oleh audit/perbaikan ini.
