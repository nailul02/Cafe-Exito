# Keputusan fitur refund, pembatalan, dan POS

## AUD-01 — pembatalan dan refund

- Customer dan Owner dapat membatalkan kuantitas item yang belum mulai diproses dengan alasan wajib.
- Untuk payment pending, nominal order/payment dikurangi sesuai kuantitas yang dibatalkan. Untuk payment verified, dibuat atau digabungkan refund request aktif; tidak ada refund otomatis.
- Owner memutuskan request refund. Cashier melaksanakan refund yang disetujui.
- Manager boleh mendokumentasikan pengecualian konflik kepentingan; Owner memverifikasi dokumentasi Manager. Dokumentasi Owner langsung terverifikasi. Manager tidak dapat menyetujui refund.
- Pembatalan ber-idempotency key yang sama dan payload sama tidak boleh menghitung kuantitas/nominal ulang; payload berbeda ditolak.

## POS dan pembayaran

- Tunai POS dan QRIS POS dimulai dengan payment pending.
- Order dapat selesai saat payment tunai masih pending. Penyelesaian order tidak menandai payment paid; Cashier tetap dapat mencatat pembayaran kemudian tanpa mengubah status order selesai.
- QRIS POS diverifikasi manual oleh Cashier. QRIS customer tetap memakai upload bukti dan verifikasi bukti manual.
- Inventory/Purchasing tidak diaktifkan oleh fitur ini.