# Task 5.5 — Payment Test Report

## Static contract review

- QRIS Self-Order membuat payment status pending saat checkout; pesanan menunggu pembayaran. Bukti QRIS dibutuhkan sebelum verifikasi manual Cashier.
- Tidak ada OCR atau payment gateway.
- `UploadPaymentProofRequest` membatasi image MIME/type dan `max:3072` (3 MiB); service juga memeriksa ukuran dan file validity.
- `PaymentService` menolak perubahan QRIS→tunai di lapisan service untuk semua source order. Guard mencegah bypass lewat API, bukan hanya UI.
- Metode QRIS tetap pending setelah upload sampai Cashier melakukan verifikasi; upload sendiri tidak melunasi order.
- Cashier reject QRIS customer mengharuskan proof pending.

## Runtime status

Payment info GET: **PASS**. Bukti valid/invalid/oversize upload, ownership, QRIS verify/reject, duplicate verify, dan Cash settlement: **BLOCKED** karena alur tersebut menulis file/DB atau mengubah status. Tidak menguji file upload ke database aktif. Regression QRIS POS→tunai ditulis tetapi belum dieksekusi.
