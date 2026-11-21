# Cafe Exito — Pre-Testing Checkpoint

**Tanggal pemeriksaan terakhir:** 19 November 2026 (Asia/Jakarta), implementasi Task 2C\
**Cakupan:** audit repo, perbaikan aman, pemeriksaan read-only database utama, tes otomatis pada database testing, dan persiapan UAT.\
**Status:** **NOT READY**\
**Baseline:** implementasi dan laporan sebelumnya di `IMPLEMENTATION_STATUS.md`, laporan kesiapan database, serta dua perubahan lokal yang sudah ada di `CashierPosTest.php` dan `StaffAttendancePage.jsx` sebelum pekerjaan ini. Perubahan lokal tersebut dipertahankan dan diselaraskan; tidak ada file sumber yang di-reset.

## Ringkasan pekerjaan

Audit menemukan mismatch pada POS, modul resep, pembatalan pelanggan, simulasi mock, dan tes lama. Perbaikan diterapkan tanpa migration atau perubahan data. Modul stok, supplier, pembelian, penerimaan, resep operasional, dan laporan stok tidak tersedia sebagai route aktif. UI menu tidak lagi meminta resep atau stok.

POS membuat payment berstatus pending untuk tunai maupun QRIS. Tunai dicatat saat Kasir memverifikasi pembayaran; QRIS diverifikasi manual sebelum tiket masuk antrean. Kasir tetap satu-satunya role yang menyelesaikan status order; status order selesai tidak otomatis melunasi payment. Catatan checkpoint 19 November bahwa tombol pembatalan pelanggan dihapus karena route tidak tersedia bersifat historis dan digantikan implementasi Customer/Owner cancellation AUD-01 tanggal 20 November.

## Rekonsiliasi hasil tes dan bukti perubahan

Git hanya memiliki dua commit (`9cd91d1` tanggal 11 November dan `aa79ae6` tanggal 16 November); tidak ada commit/tag khusus untuk 13 atau 19 November. Karena itu, angka 69/511 dan 65/480 berasal dari catatan hasil yang tersimpan, bukan log test per-file atau snapshot Git bertanggal. HEAD memiliki 69 metode `test_`; working tree kini memiliki 66. Ini mendukung hitungan metode, tetapi tidak membuktikan bahwa source pada 13 November identik byte-per-byte dengan HEAD.

| Perubahan file tes | Bukti dari diff | Dampak cakupan |
|---|---|---|
| `PurchasingTest.php` | 5 test method diganti 1 test method | Empat skenario aktif purchasing lama dihapus: penerimaan menambah stok tepat sekali; over-receipt ditolak; draft tidak dapat diterima; pembelian tidak dapat dibatalkan setelah penerimaan terkonfirmasi; dan uji role penerima. Test baru memeriksa enam path inventory/purchasing/receipt/stock-usage memberi 404. Pengurangan ini sengaja mengikuti keputusan bahwa modul tersebut keluar dari scope operasional. |
| `OrderFlowTest.php` | 9 method tetap 9 pada baseline checkpoint | Catatan tentang pengujian pembatalan pelanggan 404 merupakan baseline 19 November dan digantikan oleh route/test kuantitas Customer pada 20 November. Pengujian stok pada file ini hanya sejarah; stok bukan bagian dari operasional aktif. |
| `CashierPosTest.php` | 4 method menjadi 5 | Tes menguji status payment pending saat membuat POS order, verifikasi QRIS manual, dan pencatatan tunai setelah order selesai. `amount_received` bukan bagian payload POS; hanya dicatat saat verifikasi payment. |
| `AuthorizationTest.php` | 8 method tetap 8 | Dua ekspektasi untuk route purchasing/inventory berubah dari 403 menjadi 404 karena route tidak lagi didaftarkan. |

Urutan angka yang tercatat: 13 November **69/511**; run pertama 19 November **65/480**; setelah satu tes validasi POS ditambahkan, retest **66/487**. Perubahan 69 ke 65 adalah net minus empat metode (lima test purchasing menjadi satu); 65 ke 66 adalah plus satu metode. Total assertions run turun 31 dari 511 ke 480, lalu naik 7 dari 480 ke 487.

Analisis statis pemanggilan `assert*` pada empat file yang berubah (HEAD → working tree) adalah: Authorization 26→26, Cashier POS 27→35, Order Flow 78→76, Purchasing 31→6; total 162→143 pemanggilan eksplisit. Ini bukan rincian 511/480 PHPUnit assertions: assertion response Laravel dapat menghitung lebih dari satu assertion internal, dan tidak ada output historis per-file. Maka penyebab rinci tepat untuk seluruh selisih runtime 31 assertions **tidak dapat diverifikasi**. Yang dapat ditautkan ke diff adalah penghapusan assertions penerimaan/stock/role pada lima tes purchasing, penggantian assertions stok pada OrderFlow, dan penambahan assertions POS serta route-absence. Tidak ada bukti bahwa cakupan purchasing lama digabung ke tes lain; cakupan itu dikeluarkan karena modulnya dipensiunkan.

**Cakupan yang hilang dan batasnya:** regresi perilaku pembelian/penerimaan tidak lagi diuji karena API tidak aktif. Test route-absence tidak membuktikan data historis inventory/purchasing tetap utuh; pemeriksaan integrity historis terbaru belum dijalankan. Selain itu, test QRIS saat ini memverifikasi pembayaran tanpa mengunggah bukti, sehingga suite PASS tidak menutup gap bukti QRIS.

### Kondisi sebelum dan sesudah

| Area | Sebelum audit | Kondisi setelah perbaikan |
|---|---|---|
| POS tunai | Form meminta uang diterima saat membuat order, tetapi service menyimpan payment pending dan pesan sukses menyatakan sudah dibayar | Form tidak mengumpulkan pelunasan saat order dibuat; nominal dicatat saat endpoint verifikasi; pesan menjelaskan status pending |
| POS QRIS | Verifikasi manual ada, tetapi alur upload bukti belum ada | Verifikasi manual dipertahankan; gap upload bukti tetap terbuka dan memblokir kesiapan |
| Recipe | Route baca/tulis aktif, editor UI meminta data inventory | Route recipe dinonaktifkan; editor menu hanya mengelola informasi menu dan ketersediaan |
| Stok/purchasing | Kode lama dan tes mengasumsikan stok memengaruhi menu/order | Tidak ada route operasional; mock dan tes order tidak mengubah atau memeriksa stok |
| Pembatalan pelanggan | Pada audit 19 November, route belum tersedia | Digantikan pada 20 November oleh pembatalan kuantitas unstarted oleh Customer/Owner dan refund request sesuai AUD-01 |
| Status order | Mock mengubah status order ketika station mulai/selesai tiket | Mock station hanya mengubah tiket; status order selesai lewat aksi Kasir |
| Absensi | Tes check-in mencoba akun mock yang sudah check-in | Tes memakai akun itu untuk menguji check-out; perilaku aplikasi tidak dilonggarkan |

## Temuan audit dan tingkat risiko

| Tingkat | Temuan | Kondisi / tindakan |
|---|---|---|
| High | QRIS belum mewajibkan bukti pembayaran sebelum verifikasi dan produksi | Belum ada upload bukti pelanggan/POS, penyimpanan bukti payment, atau pemeriksaan bukti oleh Kasir. **Belum diperbaiki**; butuh implementasi end-to-end dan perubahan schema yang additive. |
| High | Batas geofence 30 meter belum ditegakkan backend | `AttendanceService` menerima koordinat tanpa menghitung jarak. Keputusan proyek menyebut koordinat numerik pusat kafe harus diverifikasi sebelum geofence diaktifkan. **Belum diperbaiki**; jangan mengarang koordinat. |
| Historical / superseded | Workflow pembatalan dan refund belum tersedia | Temuan ini berlaku untuk baseline 19 November. Pada 20 November workflow AUD-01 ditambahkan: Customer/Owner cancel unstarted quantity, verified payment membuat/merge request, Owner memutuskan, Cashier mengeksekusi. Refund tetap bukan otomatis. |
| High | Ekspor laporan `.xlsx` dan closing harian tidak tersedia | Fitur ini diminta dalam prompt checkpoint, tetapi tidak ditemukan endpoint/workflow aktif yang memenuhinya. |
| Medium | Kebijakan retensi foto dan lokasi absensi belum ditetapkan | Foto berada di private storage; retensi dan penghapusan belum diputuskan. |
| Medium | Integrasi FE ke backend nyata dan uji perangkat belum dilakukan | Tes otomatis menggunakan mock atau database testing. Kamera/GPS fisik, browser, QRIS nyata, printer, serta responsif perangkat belum diuji. |
| Medium | OpenAPI belum divalidasi parser | Path operasi dibandingkan dengan route list dan request yang tersedia; tidak ada validator YAML/OpenAPI dijalankan. |
| Informational | Berkas halaman inventory/purchasing dan beberapa layanan mock lama masih ada | Berkas tersebut tidak terhubung ke route React aktif/navigation dan API backend tidak memiliki route terkait. Dipertahankan untuk menghindari penghapusan berkas historis tanpa kebutuhan. |

**Critical:** tidak ada temuan Critical yang dikonfirmasi dalam pemeriksaan ini. Temuan High di atas tetap mencegah status READY.

## Perbaikan dan perubahan

### Backend dan API

- Pesan endpoint POS kini menyatakan pembayaran menunggu konfirmasi.
- `amount_received` bukan field POS create dan tidak dipakai untuk membuat order; nominal hanya dicatat saat endpoint verifikasi payment.
- Request pembatalan tidak lagi menerima `restore_stock`; pemanggilan service pembatalan tidak membawa opsi stok.
- Route `GET/PUT /admin/menus/{menu}/recipe` dinonaktifkan. Route inventory, purchasing, receipt, dan stock usage tidak ada.
- Tes fitur diperbarui agar memeriksa perilaku tanpa mutasi stok, pembatalan Owner, dan ketiadaan endpoint yang sudah dipensiunkan.
- Tidak ada migration baru dan tidak ada perubahan skema.

### Frontend dan mock

- POS tidak menampilkan input uang diterima/kembalian sebelum transaksi pembayaran dicatat.
- Editor menu tidak menampilkan atau meminta resep/bahan.
- Mock POS mempertahankan status pending; verifikasi terpisah; stock tidak berubah; ticket tidak menggandakan; station tidak mengubah status order.
- Pada baseline checkpoint 19 November, tombol/API pembatalan pelanggan dihapus karena route belum tersedia. Status historis ini digantikan oleh implementasi pembatalan Customer/Owner sesuai AUD-01 pada 20 November.
- Tes absensi menggunakan check-out pada akun mock yang sudah memiliki check-in hari ini.

### API, route, dan dokumentasi

- `php artisan route:list --path=api/v1 --json`: **61 route entries**, tidak ada path recipe, inventory, purchasing, receipt, atau stock usage.
- `API_DOCUMENTATION.md` dan `api/openapi.yaml` diselaraskan dengan route recipe yang dihapus serta aturan pembayaran POS. Kontrak OpenAPI belum diparse dengan validator.
- `IMPLEMENTATION_STATUS.md` diperbarui dengan hasil pemeriksaan tanggal 19 November 2026.

## Cleanup file dan folder

| Tindakan | File | Alasan |
|---|---|---|
| Dihapus | Tidak ada | Tidak ada file yang dihapus dalam pekerjaan ini. |
| Dipindahkan | Tidak ada | Tidak ada file yang dipindahkan. |
| Dibuat | `docs/CAFE_EXITO_PRE_TESTING_CHECKPOINT.md` | Satu checkpoint resmi, sesuai permintaan. |
| Dipertahankan | Seluruh `.md` yang ada, laporan historis, `Setting.json`, konfigurasi, dan `backups/` | Dokumen/laporan dan file tersebut tidak terbukti aman untuk dihapus; backup tidak disentuh. |
| Diperiksa | Berkas `*.ms`, `*.tmp`, `*.bak`, `*.old` | Pencarian repo tidak menemukan hasil. |

`.env` backend/frontend tidak tercatat di Git dan di-ignore. Credential tidak ditampilkan dalam checkpoint. Tidak ada commit, push, deployment, perubahan hosting, penghapusan backup, atau perubahan akun.

## Pemeriksaan database

- `php artisan migrate:status --no-ansi` pada database utama: **PASS, read-only**; semua migration repo berstatus `Ran`.
- Migration, seeder, DML, penghapusan transaksi, perubahan akun, dan perubahan ownership/grant **tidak dijalankan**.
- Tes fitur menggunakan konfigurasi PHPUnit/testing terpisah dari database utama; nama database dan nilai konfigurasi lokal tidak dicantumkan.
- Pemeriksaan integrity command lama tidak dijalankan: perintah tersebut mengasumsikan jumlah record statis dan tabel inventory/purchasing wajib, sehingga tidak cocok sebagai validator kondisi operasional terbaru.

## Automated testing dan build

| Pemeriksaan | Perintah | Hasil |
|---|---|---|
| Runtime lokal | `php --version`, `node --version` | **PASS** — PHP 8.3.30 dan Node.js 24.16.0 |
| Versi Composer | `composer --version` | **PASS** — Composer 2.9.4; PHP 8.3.30 |
| Backend unit | `php artisan test --testsuite=Unit` | **PASS** — 24 tes, 161 assertions |
| Backend semua suite | `php artisan test` | **PASS** — 66 tes, 487 assertions; database testing terisolasi |
| Frontend | `npm.cmd test` | **PASS** — 20/20 tes Vitest |
| Production build | `npm.cmd run build` | **PASS** — Vite 8.3.5 menghasilkan build |
| Route inspection | `php artisan route:list --path=api/v1 --json` | **PASS** — 61 route entries; tidak ada endpoint fitur pensiun |
| Migration status | `php artisan migrate:status --no-ansi` | **PASS (read-only)** — semua migration repo `Ran` |
| `git diff --check` | `git diff --check` | **PASS** — tidak ada whitespace error |
| OpenAPI parser | Tidak tersedia/dijalankan | **NOT TESTED** |
| Live FE-to-BE/E2E | Tidak dijalankan | **NOT TESTED** |
| Browser/perangkat fisik, kamera/GPS, QRIS nyata | Tidak dijalankan | **NOT TESTED** |
| Security scanner/dependency audit terpisah | Tidak dijalankan pada rekonsiliasi ini | **NOT TESTED** |

## Known issues dan risiko tersisa

1. Upload bukti QRIS kini diterapkan pada kode dan diuji otomatis; migration baru masih Pending pada database utama, integrasi UI nyata dan UAT belum dilakukan.
2. Geofence 30 meter belum diverifikasi di backend; koordinat numerik pusat kafe belum dikonfirmasi pemilik.
3. Belum ada workflow request/approval pembatalan dan refund yang terpisah. Status payment verified setelah pembatalan bukan bukti bahwa uang sudah dikembalikan.
4. Belum ada ekspor `.xlsx` dan closing harian yang sesuai aturan transaksi tunai belum lunas.
5. Kebijakan retensi foto/lokasi absensi masih pending.
6. Perangkat kamera, GPS, radius di lokasi nyata, live API, dan peramban desktop/tablet/mobile belum diuji.
7. UI pengelolaan gambar QR meja, gambar QRIS, dan foto menu belum selesai/terverifikasi.
8. OpenAPI belum divalidasi parser dan konfigurasi production belum diuji.

## Skenario manual UAT

Seluruh skenario berstatus **NOT TESTED**. Jangan tandai lulus sebelum pengguna melakukan pengujian langsung dan mencatat bukti.

| # | Skenario | Status |
|---:|---|---|
| 1 | Login Owner dengan akun uji yang disiapkan | NOT TESTED |
| 2 | Login Manager dengan akun uji yang disiapkan | NOT TESTED |
| 3 | Login Kasir dengan akun uji yang disiapkan | NOT TESTED |
| 4 | Login akun Barista dan Kitchen; verifikasi station masing-masing | NOT TESTED |
| 5 | Akses endpoint langsung: role tanpa permission menerima penolakan | NOT TESTED |
| 6 | Scan QR dine-in dan pastikan nomor meja sesuai token QR | NOT TESTED |
| 7 | Ubah nomor meja dari payload QR dan pastikan API menolak | NOT TESTED |
| 8 | Buat pesanan dine-in melalui QR meja | NOT TESTED |
| 9 | Buat pesanan takeaway melalui POS Kasir tanpa meja | NOT TESTED |
| 10 | Buat pesanan tambahan sebagai order baru | NOT TESTED |
| 11 | Pastikan order yang sudah dikirim tidak dapat diedit sepihak | NOT TESTED |
| 12 | Unggah bukti QRIS, verifikasi Kasir, dan pastikan order baru masuk produksi | NOT TESTED |
| 13 | Tolak bukti QRIS dan pastikan order tidak masuk antrean | NOT TESTED |
| 14 | Pastikan order QRIS tanpa bukti tidak dapat diverifikasi/diproduksi | NOT TESTED |
| 15 | Buat order tunai dan pastikan dapat diproses saat pembayaran pending | NOT TESTED |
| 16 | Pastikan kasir melihat tunai belum lunas dan hanya mencatat pelunasan setelah uang diterima | NOT TESTED |
| 17 | Verifikasi uang tunai kurang ditolak; nominal pas/lebih menghitung kembalian benar | NOT TESTED |
| 18 | Pastikan pembayaran verified tidak dapat diverifikasi dua kali | NOT TESTED |
| 19 | Order campuran menampilkan minuman pada antrean Barista | NOT TESTED |
| 20 | Order campuran menampilkan makanan pada antrean Kitchen | NOT TESTED |
| 21 | Station hanya menjalankan tiket yang diizinkan; tidak mengubah status order | NOT TESTED |
| 22 | Kasir menyelesaikan order hanya setelah seluruh tiket selesai | NOT TESTED |
| 23 | Owner menyetujui/menolak permintaan pembatalan dengan alasan dan audit | NOT TESTED |
| 24 | Uji batas pembatalan setelah tahap persiapan tidak dapat dibatalkan, termasuk tambah es | NOT TESTED |
| 25 | Owner memutuskan refund secara terpisah; verifikasi payment/audit dan bukti pengembalian | NOT TESTED |
| 26 | Check-in memakai selfie langsung dari kamera dan event foto tersimpan privat | NOT TESTED |
| 27 | Check-out memakai foto dan koordinat baru | NOT TESTED |
| 28 | Uji lokasi di dalam radius 30 meter dan di luar radius; backend menolak lokasi di luar | NOT TESTED |
| 29 | Tolak izin kamera/GPS dan pastikan absensi tidak terkirim; uji keterlambatan setelah 16.00 | NOT TESTED |
| 30 | Owner koreksi absensi dengan alasan; Manager hanya memantau tanpa foto/koordinat staf lain | NOT TESTED |
| 31 | Owner membuat/mengubah/nonaktifkan akun; Manager ditolak; Owner terakhir terlindungi | NOT TESTED |
| 32 | Karyawan mengganti password sendiri; sesi dicabut sesuai implementasi | NOT TESTED |
| 33 | Manager memantau operasional absensi dan tidak mendapat laporan lengkap khusus Owner | NOT TESTED |
| 34 | Owner melihat laporan penjualan harian, mingguan, bulanan, dan rentang bebas | NOT TESTED |
| 35 | Rekonsiliasi jumlah transaksi dan metode pembayaran tunai/QRIS dengan fixture uji | NOT TESTED |
| 36 | Closing harian ditolak saat transaksi tunai belum lunas, tidak membuat closing/menandai lunas; setelah pelunasan closing tercatat satu kali tanpa transaksi hilang/ganda | NOT TESTED |
| 37 | Ekspor `.xlsx` cocok dengan rentang tanggal dan total laporan | NOT TESTED |
| 38 | Audit log mencatat perubahan kritis beserta aktor, waktu, alasan, dan sebelum/sesudah | NOT TESTED |
| 39 | Pastikan stok/supplier/pembelian/resep tidak ada di UI/API aktif dan data historis tetap ada | NOT TESTED |
| 40 | Uji gangguan internet/perangkat dan fallback manual cafe | NOT TESTED |
| 41 | Periksa tampilan desktop, tablet, dan mobile serta alur loading/empty/error | NOT TESTED |
| 42 | Validasi bukti QRIS menerima hanya gambar JPEG/PNG/WEBP maksimal 3 MB; coba file rusak, ekstensi/MIME palsu, serta batas ukuran | NOT TESTED |
| 43 | Bukti QRIS yang sudah terikat ke satu pembayaran ditolak saat dipakai ulang pada pembayaran lain | NOT TESTED |
| 44 | Bukti milik order lain atau bukti yang sudah ditolak tidak dapat digunakan untuk meloloskan order ini | NOT TESTED |
| 45 | Kirim koordinat absensi yang dimanipulasi langsung ke API; backend menolak di luar radius dan memvalidasi rentang koordinat. Catat bahwa GPS spoofing di dalam radius tidak terbukti dapat dideteksi hanya dari koordinat browser | NOT TESTED |
| 46 | Bedakan persetujuan refund Owner dari pengembalian uang aktual; catat bukti kas/QRIS kembali dan jangan menyatakan lunas/refunded sebelum transaksi pengembalian benar-benar terjadi | NOT TESTED |
| 47 | Setelah bukti QRIS ditolak dengan alasan, order tetap menunggu pembayaran; pelanggan melihat alasan lalu mengunggah file berbeda dan kasir melihat bukti lama serta baru | NOT TESTED |
| 48 | Pastikan preview bukti hanya dapat diakses staf berizin melalui endpoint, dan tidak dapat diakses tanpa token/izin atau URL publik | NOT TESTED |
| 49 | Pastikan QRIS dari POS dapat diverifikasi manual tanpa upload pelanggan, sedangkan endpoint upload bukti menolak token order POS | NOT TESTED |
| 50 | Kasir tidak dapat menolak pembayaran tanpa alasan; alasan penolakan tampil pada status pelanggan dan audit | NOT TESTED |

## Riwayat perubahan checkpoint

| Tanggal | Catatan |
|---|---|
| 19 November 2026 | Checkpoint dibuat dari hasil audit dan tes aktual. Menetapkan status NOT READY dan semua skenario UAT NOT TESTED. |
| 19 November 2026 | Rekonsiliasi lanjutan: verifikasi Composer 2.9.4; backend 66/487 pada database testing terisolasi; 61 route; semua migration `Ran` pada pemeriksaan read-only; UAT diperluas menjadi 46 skenario. Status tetap NOT READY. |
| 19 November 2026 | Task 2C: QRIS proof workflow diimplementasikan; backend 71/537 pada database testing terisolasi, frontend 20/20, build PASS. Route menjadi 63. Migration payment proof additive Ran di testing dan Pending di main (read-only). UAT diperluas menjadi 50 skenario, tetap NOT TESTED; status NOT READY. |

## Rekomendasi dan status akhir

**NOT READY.** UAT fisik dan integrasi UI ke API nyata belum dijalankan. Workflow pembatalan/refund telah diimplementasikan sesuai AUD-01, tetapi tetap memerlukan UAT. Simulasi operasional juga menunggu koordinat pusat disahkan dan geofence diuji server-side. Ekspor `.xlsx`, closing harian, retensi data absensi, dan uji perangkat nyata masih terbuka. Build/test PASS membuktikan hanya pemeriksaan yang tercatat; bukan kelulusan UAT atau kesiapan produksi.

## Rekonsiliasi implementasi terarah - 20 November 2026

- Tunai boleh masuk antrean dan diproses saat payment `pending`. Penyelesaian tiket/pesanan tidak melunasi payment. Payment tunai selesai-pending tetap masuk hitungan tunggakan dan kasir dapat mencatat pelunasan lewat endpoint verifikasi; QRIS tetap diverifikasi manual. Aturan ini mengonfirmasi keputusan bisnis terbaru dan tidak mengubah status UAT.
- Geofence backend 30 meter kini dihitung dengan Haversine dari koordinat konfigurasi backend (`CAFE_LOCATION_LATITUDE`/`CAFE_LOCATION_LONGITUDE`). Konfigurasi kosong/tidak valid gagal tertutup. `.env.example` tidak mengisi koordinat; file `.env` lokal tidak tersedia pada workspace ini. Koordinat pusat tetap menunggu konfirmasi pemilik. Skenario UAT geofence tetap `NOT TESTED`; validasi koordinat tidak membuktikan GPS bebas spoofing.
- Catatan validasi 76/76 pada baris sebelumnya adalah hasil historis dan bukan hasil terbaru setelah perubahan POS/Authorization. Run full suite berikutnya tercatat 74/88 (14 gagal: Authorization 1, POS 3, OrderFlow stok 5, Purchasing 5), sebelum ekspektasi Authorization/POS diperbarui. Final audit ini tidak menjalankan ulang tes backend atau memakai database testing; status tes kode terbaru adalah belum diverifikasi. UAT dan kesiapan operasional tetap `NOT READY`.
