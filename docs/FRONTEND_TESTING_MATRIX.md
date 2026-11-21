# Frontend Testing Matrix — Task 4

Status per 21 November 2026. PASS pada skenario statis berarti source/otomasi mock mendukung perilaku yang diperiksa; tidak menyatakan browser production telah diuji. Bukti utama: `src/App.jsx`, `src/layouts/*`, `src/api/*`, `src/test/flow.test.jsx`; lihat laporan kelompok.

| ID | Halaman/komponen | Langkah | Expected | Actual / bukti | Status |
|---|---|---|---|---|---|
| R01 | Daftar route | Inventaris route | Halaman aktif terpetakan | 33 halaman + wildcard dan parent: 35 deklarasi | PASS |
| R02 | Deep link tanpa sesi | Buka route staf | Ke login dan tujuan tersimpan | Guard diperbaiki; test mock baru | PASS |
| R03 | Role mismatch | Barista buka manager | Kembali ke beranda role | Suite memverifikasi redirect produksi | PASS |
| R04 | 404 | Buka URL tak dikenal | NotFoundPage | Fallback `*` terdaftar; browser belum dicoba | BLOCKED |
| R05 | Logout | Keluar dari staf | Token/state dibersihkan, halaman publik | Urutan guard/logout diperbaiki; suite | PASS |
| R06 | Refresh sesi valid | Muat route internal dengan sesi | `auth/me` memulihkan user | Dicakup mock suite; browser backend tidak dicoba | PASS (mock) |
| R07 | Link/tab/modal/pagination | Interaksi penuh tiap halaman | Semua aksi navigasi merespons | Tidak seluruhnya diotomasi/manual browser tersedia | NOT TESTED |
| U01 | Customer 375×812 | Inspeksi viewport | Tanpa overflow/cutoff | Browser connector tidak tersedia | BLOCKED |
| U02 | Customer 430×932 | Inspeksi viewport | Layout mobile nyaman | Browser connector tidak tersedia | BLOCKED |
| U03 | Tablet 768×1024 | Staff/management | Navigasi dan tabel usable | Browser connector tidak tersedia | BLOCKED |
| U04 | Desktop 1366×768 | Dashboard | Layout tidak terpotong | Browser connector tidak tersedia | BLOCKED |
| U05 | Desktop 1920×1080 | Dashboard | Layout konsisten | Browser connector tidak tersedia | BLOCKED |
| F01 | Login | Kredensial invalid | Error dapat dipahami | Test mock meliputi login gagal | PASS (mock) |
| F02 | Login | Input kosong | Browser required validation | `required` pada field; belum browser diuji | PASS (static) |
| F03 | Pembatalan item customer | Tanpa alasan / qty parsial | Tidak submit tanpa alasan; validasi jelas | Suite mock menguji alasan wajib/kuantitas | PASS (mock) |
| F04 | QRIS proof | Pilih file/submit | Bukti diminta; status tidak lunas otomatis | Simulasi fixture, bukan DB/API | PASS (mock) |
| F05 | Upload | File invalid/ukuran besar | Ditolak dengan pesan jelas | MIME `accept` ada; ukuran/error belum diuji | NOT TESTED |
| F06 | Semua form | 422/401/403/404/429/500 | Pesan terpetakan per status | Mapper generik; tidak ada simulasi semua status | NOT TESTED |
| F07 | Form aksi | Request berjalan | Disable submit/double-click aman | Beberapa memakai `busy`; seluruh form belum diaudit interaktif | NOT TESTED |
| RB01 | Owner | Login fixture | Menu Owner dan beranda | Suite memverifikasi owner route | PASS (mock) |
| RB02 | Manager | Login fixture | Menu Manager dan batas Owner | Suite memverifikasi route dan akun | PASS (mock) |
| RB03 | Kasir | Login fixture | Halaman kasir; bukan manager | Suite memverifikasi | PASS (mock) |
| RB04 | Barista | Login fixture | Antrean minuman saja | Suite memverifikasi filter station | PASS (mock) |
| RB05 | Kitchen | Login fixture | Antrean makanan saja | Suite memverifikasi filter station | PASS (mock) |
| RB06 | Customer | Buka flow publik | Tidak butuh login staf | Suite memverifikasi order mock | PASS (mock) |
| RB07 | Manager Meja & QR | Login fixture, klik navigasi | Route menampilkan UI meja/QR | Test regresi memverifikasi judul dan kolom tautan QR | PASS (mock) |
| API01 | Base URL | Inspect env + axios | URL API sesuai konfigurasi | Default `/api/v1`; override via Vite env | PASS (static) |
| API02 | Auth/menu/orders | Inspect service contract | Method/path sesuai API | Mappings diperiksa terhadap matrix; tidak ada panggilan aktif | PASS (static) |
| API03 | POS/payment/ticket/refund/cancel | Jalankan mutasi | Fixture/mock saja | Mock suite terbatas; API positif tetap blocker Task 3 | BLOCKED |
| API04 | Loading/empty/error/polling | Inspect hooks/screens | Tiga state dan polling terkendali | `useResource` menerapkan state/poll; belum semua layar diinteraksi | PASS (static) |
| S01 | Token/logout | Inspect storage + flow | Token tidak persisten setelah tab ditutup/logout | `sessionStorage`, clear saat logout/401 | PASS (static/mock) |
| S02 | XSS | Cari raw HTML rendering | Konten tak dipercaya di-escape | Tidak ditemukan `innerHTML`/`dangerouslySetInnerHTML` | PASS (static) |
| S03 | Console leakage | Cari console log | Token/data sensitif tidak dicatat | Tidak ditemukan console log/error/warn di `src` | PASS (static) |
| S04 | URL/role manipulation | Akses path silang role | Guard mengarahkan role salah | Suite mock mencakup beberapa jalur; semua kombinasi belum dicoba | PASS (partial) |
| A01 | Label/alerts/headings | Inspeksi markup | Form/error punya nama/role | Field labels dan `role=alert` ditemukan; belum audit penuh | PASS (static) |
| A02 | Keyboard/focus/kontras/target | Interaksi visual | Dapat digunakan keyboard/mobile | Browser connector tidak tersedia | BLOCKED |
| B01 | Vitest | `npm.cmd test` | Suite lulus | 26 test: 26 PASS | PASS |
| B02 | Production build | `npm.cmd run build` | Compile sukses | Vite 8.3.5, 159 modul | PASS |

**Jumlah skenario terdokumentasi: 38**. Agregat: **26 PASS, 8 BLOCKED, 4 NOT TESTED, 0 FAIL**. PASS pada test otomatis memakai mock, bukan API/database aktif. Responsivitas dan visual browser tetap BLOCKED.
