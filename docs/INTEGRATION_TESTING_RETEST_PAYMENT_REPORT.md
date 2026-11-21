# Task 5.2 — Payment Integration Retest

**Runtime PASS:** payment info GET 200 dan terpetakan oleh Axios; cart validation tidak menyimpan data.

**Static contract PASS after fix:** request upload membatasi `max:3072` dan image JPEG/PNG/WebP; UI menonaktifkan upload di atas 3 MiB. QRIS customer meminta bukti untuk verifikasi manual. Backend kini menolak QRIS → tunai untuk semua jenis order; Kasir UI tidak mengirim pilihan metode saat verifikasi. Regression Feature test POS ditambahkan tetapi tidak dijalankan.

**BLOCKED:** QRIS order positif, upload file, uji batas 3 MiB, verifikasi/penolakan Kasir, bukti pembayaran nyata, POS QRIS, dan tunai end-to-end. Semua dapat menulis file/DB atau mengubah status, sehingga tidak dijalankan terhadap database aktif. Batas file belum diuji runtime.

**Bug diperbaiki:** sebelumnya QRIS POS dapat dikirim sebagai tunai pada endpoint verifikasi. Guard ditambahkan pada `PaymentService`; regression ada di `CashierPosTest`. Belum ada runtime test pasca-perbaikan karena larangan Feature/migration.
