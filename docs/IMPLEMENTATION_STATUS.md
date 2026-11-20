# Status Implementasi & Kesiapan Rilis — Cafe Éxito

Tanggal Audit & Pengujian: 20 November 2026
Branch Kerja: `publish/refund-pos`
Target Release: `main` (`https://github.com/nailul02/Cafe-Exito.git`)

---

## 1. Ringkasan Eksekutif

Sistem pemesanan QR self-order dan operasional kafe Cafe Éxito telah menyelesaikan seluruh integrasi fitur, perbaikan kode, audit keamanan, dan validasi runtime. Pengujian otomatis backend dan frontend berjalan 100% lulus (PASS) pada lingkungan pengujian terisolasi.

---

## 2. Hasil Verifikasi Pengujian

| Komponen Pengujian | Lingkup | Hasil | Detail |
|---|---|---|---|
| **Backend Unit Tests** | `phpunit --testsuite=Unit` | **PASS (24/24)** | 161 assertions |
| **Backend Feature Tests** | `phpunit --testsuite=Feature` | **PASS (70/70)** | 584 assertions |
| **Total Backend Tests** | `php artisan test` | **PASS (94/94)** | 745 assertions, 0 errors, 0 failures |
| **Frontend Tests** | `vitest run` (`npm test`) | **PASS (20/20)** | 20 passed, durasi 17.43s |
| **Frontend Build** | `vite build` (`npm run build`) | **SUCCESS** | Dist artifacts ter-render tanpa error |
| **API Route Verification** | `route:list --path=api/v1` | **PASS (79/79)** | 79 route aktif terdaftar dan terikat controller |
| **PHP Syntax Lint** | `php -l` pada semua file PHP baru/berubah | **PASS (35/35)** | Tidak ada syntax error |

---

## 3. Status Fitur Utama

### A. Customer QR Self-Order
- Navigasi menu dinamis via token meja / QR meja (`GET /customer/tables/{qrToken}`).
- Validasi keranjang server-side (`POST /customer/cart/validate`).
- Checkout tunai dan QRIS statis dengan token publik (`POST /customer/orders`).
- Pemantauan status pesanan real-time via polling (`GET /customer/orders/{publicToken}`).
- **Pembatalan Item oleh Pelanggan:** `POST /customer/orders/{publicToken}/cancel` untuk item yang belum mulai diproses.
  - Untuk pesanan tunai pending: tagihan langsung berkurang secara proporsional.
  - Untuk pesanan QRIS/tunai terverifikasi: terbuat refund request aktif yang menunggu keputusan Owner.
  - Replay request dengan `Idempotency-Key` yang sama menghasilkan representasi konsisten tanpa duplikasi.

### B. Bukti Pembayaran QRIS (QRIS Payment Proof)
- **Upload Pelanggan:** `POST /customer/orders/{publicToken}/payment-proof` (multipart/form-data: JPEG, PNG, WEBP, maks 3 MB).
- **Penyimpanan:** Disimpan privat pada disk `local` (`payment-proofs/`) dengan hashing SHA-256 untuk mendeteksi integritas dan file duplikat.
- **Inspeksi & Preview Kasir:** `GET /cashier/payments/{payment}/proof` dengan header `X-Content-Type-Options: nosniff`.
- **Verifikasi/Penolakan Kasir:** `POST /cashier/payments/{payment}/verify` dan `POST /cashier/payments/{payment}/reject`. Verifikasi pesanan QRIS customer mewajibkan adanya bukti pending yang sah.

### C. Kasir / POS
- Dashboard ringkasan omzet dan daftar pesanan aktif (`GET /cashier/dashboard`, `GET /cashier/orders`).
- Modal POS untuk pembuatan pesanan takeaway/dine-in langsung di kasir (`POST /cashier/pos/orders`).
- Settlement tunai tertunda: pesanan takeaway tunai dapat selesai (`OrderStatus::Selesai`), dan pembayaran tetap pending sampai kasir menerima fisik uang (`POST /cashier/payments/{payment}/verify`).
- Verifikasi kasir setelah order selesai mencatat pembayaran tanpa membatalkan atau mengubah status order yang telah selesai.

### D. Stasiun Produksi (Barista & Kitchen)
- Antrean tiket terpisah secara tegas: Barista (`station: barista`) dan Kitchen (`station: kitchen`).
- Tracking pemrosesan detail item (`POST /station/{station}/tickets/{ticket}/items/{detail}/start` dan `/start`).
- Item yang telah memiliki timestamp `started_at` tidak dapat dibatalkan oleh Pelanggan maupun Owner.
- Stasiun produksi tidak mengubah status pesanan utama, melainkan memperbarui status tiket masing-masing (`antre` $\rightarrow$ `diproses` $\rightarrow$ `selesai`). Penyelesaian pesanan utama tetap wewenang Kasir.

### E. Refund & Pembatalan Item (AUD-01)
- Pembatalan item oleh Owner: `POST /orders/{order}/items/cancel` dengan audit trail lengkap.
- Nominal refund dihitung otomatis berdasarkan harga historis saat pesanan dibuat (`unit_price`).
- Pemisahan hak akses:
  - **Review & Approval:** Hanya role `Owner` (`POST /refunds/{refund}/decision`, `/revoke`).
  - **Eksekusi:** Hanya role `Kasir` (`POST /refunds/{refund}/execute`). Mendukung metode tunai (`cash`) dan transfer bank (`bank_transfer`) dengan nomor referensi serta upload bukti transfer privat.
  - **Pencegahan Konflik Kepentingan:** Jika pesanan dibuat oleh Owner sendiri, status refund ditandai `conflict_of_interest`. Manager dapat mendokumentasikan catatan konflik (`POST /refunds/{refund}/conflict-notes`), yang kemudian wajib diverifikasi oleh Owner. Catatan yang dibuat langsung oleh Owner terverifikasi otomatis.
  - **Penarikan Pengajuan:** Kasir hanya dapat menarik (`withdraw`) pengajuan manual yang dibuat oleh kasir tersebut.

### F. Notifikasi & Monitoring
- In-app notification bell (`GET /notifications`, `POST /notifications/{notification}/read`).
- Artisan Command `refunds:notify-deadlines` untuk mendeteksi refund yang melebihi tenggat keputusan (24 jam) atau tenggat eksekusi (48 jam).

---

## 4. Keamanan & Kebersihan Repository

1. **Database Isolation:** Database operasional `cafe_exito` tidak tersentuh migrasi baru atau seeder selama audit/pengujian. Seluruh pengujian fitur dieksekusi secara terisolasi pada `cafe_exito_test`.
2. **Kredensial & Secrets:** File `.env`, `.env.testing`, database dumps, dan log runtime dipastikan berada dalam `.gitignore` dan tidak diikutsertakan dalam staging release.
3. **Penyimpanan Berkas Privat:** Berkas bukti bayar QRIS dan bukti transfer refund disimpan pada disk privat berotorisasi (`payment-proofs/` dan `refund-proofs/`), tidak dapat diakses langsung via URL statis publik.
