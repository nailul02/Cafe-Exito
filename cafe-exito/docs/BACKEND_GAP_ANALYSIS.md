# Gap Analysis: Figma vs Laporan vs Blueprint (dan penyelesaiannya di kode)

| # | Temuan | Penyelesaian saat ini | Status |
|---|---|---|---|
| 1 | Stok masuk Figma menambah stok langsung (konflik dengan "pembelian ≠ penerimaan") | Frontend: panel "Terima barang" memilih PO; backend: stok hanya bertambah saat `confirm` | ASSUMPTION (K-B10) |
| 2 | Login Figma = email; laporan = username | `login` menerima email atau username; kolom `users.email` ditambahkan | ASSUMPTION (K-T1) |
| 3 | Satu QR umum + nomor meja manual vs token per meja | `X-Table-Token` opsional; nomor meja diketik; bila token ada harus cocok | PENDING (K-T2) |
| 4 | Struk memisahkan No Transaksi dan No Pesanan | `payments.transaction_number` dibuat saat verifikasi | ASSUMPTION |
| 5 | Kategori Figma 3 grup (pelanggan) vs kategori rinci (Manager) | Tabel `menu_categories` (`display_group`, `station`) | ASSUMPTION (K-T3) |
| 6 | Tab kategori bahan di Kelola Stok | `ingredients.category` | ASSUMPTION |
| 7 | Level stok 4 tingkat (Kritis/Menipis/Aman/Habis + persentase) | `StockLevel` + `stock_percent` | ASSUMPTION (A7) |
| 8 | Figma tidak punya kolom uang diterima / alasan tolak (blueprint mewajibkan) | Keduanya opsional | ASSUMPTION |
| 9 | Kasir memilih metode di panel verifikasi | `verify` menerima `method` (dicatat audit) | ASSUMPTION |
| 10 | Manager menjalankan tiket di Figma; matriks lama hanya baca | Tetap baca (izin khusus bisa diberikan) | PENDING (K-B11) |
| 11 | Owner "Order Supplier"/"Buka Kasir POS" | Tidak dibuat; Owner baca saja | PENDING |
| 12 | Penonaktifan menu otomatis tercatat audit (Figma) vs dihitung dinamis | Dinamis; tanpa audit | PENDING (K-T4) |
| 13 | Status berbeda per layar ("Siap Antar", "Takeaway") | 3 status tiket; label dipetakan di `lib/status.js` | PENDING (K-B12, K-B2) |
| 14 | Tidak ada endpoint baca audit log | `GET /audit-logs` | Selesai |
| 15 | Laporan: per jam, per kasir, kustom rentang, waktu saji | `ReportService` | Selesai |
| 16 | Desain memuat shift/absensi, HPP/laba, EDC, multi-outlet, PB1, modifier, promo | Tidak dibangun | PENDING (K-B*) |
| 17 | Tidak ada layar Kitchen, pengguna, meja/QR, supplier/pembelian | Halaman dibuat dengan penanda ASUMSI | ASSUMPTION |
| 18 | Tabel `stock_movements`, `order_tickets`, `user_permissions`, `app_settings`, `audit_logs` di luar ERD laporan | Dibuat; **laporan perlu direvisi** (ERD baru, PostgreSQL, modul pembelian, dua role) | Tindakan tim |

**Risiko:** lingkup melebar (shift/finansial); backend belum pernah dijalankan; jadwal implementasi sempit (minggu 10–14).
