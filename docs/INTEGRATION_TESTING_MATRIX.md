# Integration Testing Matrix — Task 5

| ID | Modul | Skenario | Metode | Expected Result | Actual Result | Status | Bukti/Catatan |
|---|---|---|---|---|---|---|---|
| CON-01 | Connectivity | Health API | GET nyata | 200 JSON | 200, envelope `{success,message,data,meta}` | PASS | Body detail dibatasi |
| CON-02 | Connectivity | Base URL dari Vite env | Axios/Vite SSR | Request ke `/api/v1` | Request mencapai localhost:8000 | PASS | Mock dipaksa false per proses |
| CON-03 | Connectivity | Origin frontend | GET dengan Origin | CORS mengizinkan 5173 | 200, `Access-Control-Allow-Origin: http://localhost:5173` | PASS | curl, bukan browser |
| AUTH-01 | Auth | `/auth/me` tanpa bearer | GET via Axios | 401 dinormalisasi | 401 `UNAUTHENTICATED` | PASS | API nyata |
| AUTH-02 | Auth | `/auth/me` token fiktif | GET via Axios | 401; sesi lokal dibuang | 401; key sesi fiktif dibersihkan | PASS | Tidak memakai token DB |
| AUTH-03 | Auth | Dashboard kasir tanpa bearer | Axios `cashierApi.dashboard()` | 401 tanpa data privat | 401 `UNAUTHENTICATED` | PASS | API nyata |
| AUTH-04 | Auth | Login sukses | HTTP autentikasi positif | Token/user sah | Kredensial tidak tersedia | BLOCKED | Tidak menebak password |
| AUTH-05 | Auth | Pulihkan sesi valid/`auth/me` | Sesi API valid | User dan role pulih | Tidak ada token terverifikasi yang aman dipakai | BLOCKED | 23 token asalnya UNRESOLVED |
| AUTH-06 | Auth | Logout dan invalidasi token | POST authenticated | Token aktif dicabut | Tidak dijalankan | BLOCKED | Tidak memakai/mencabut token historis |
| RBAC-01 | Role | Owner membaca dashboard/role | GET authenticated | Owner diizinkan sesuai permission | Tidak ada kredensial/token terverifikasi | BLOCKED | Backend policy diperiksa statis |
| RBAC-02 | Role | Manager dan tabel/menu | GET authenticated | Sesuai permission Manager | Tidak ada sesi API valid | BLOCKED | `tables.manage` tercantum untuk Manager |
| RBAC-03 | Role | Kasir POS/order/payment | GET authenticated | Hak Kasir sesuai | Tidak ada sesi API valid | BLOCKED | Mutasi POS/payment tidak dicoba |
| RBAC-04 | Role | Barista station | GET authenticated | Station barista saja | Tidak ada sesi API valid | BLOCKED | Filter fixture bukan API auth |
| RBAC-05 | Role | Kitchen station | GET authenticated | Station kitchen saja | Tidak ada sesi API valid | BLOCKED | Filter fixture bukan API auth |
| RBAC-06 | Role | Customer dengan QR/public token valid | GET authenticated via table/public token | Data tabel/order sesuai token | Token valid tidak dibaca dari DB | BLOCKED | Token order/table bersifat rahasia |
| RBAC-07 | Role | Daftar meja tanpa bearer | Axios `usersApi.tables()` | 401 tanpa data meja | 401 `UNAUTHENTICATED` | PASS | Permission Manager tidak diprobe dengan token |
| RBAC-08 | Role | Laporan sales tanpa bearer | Axios `reportsApi.sales()` | 401 tanpa data laporan | 401 `UNAUTHENTICATED` | PASS | API nyata |
| CUST-01 | Customer | Menu | Axios `customerApi.menus()` | Data `data[]` terpetakan | 200, 11 item, key item terbaca | PASS | Tidak mencetak isi bisnis |
| CUST-02 | Customer | Informasi pembayaran | Axios `customerApi.paymentInfo()` | Object payment-info terpetakan | 200, tiga key yang diharapkan | PASS | Read-only |
| CUST-03 | Customer | QR invalid | Axios `customerApi.table()` | Error 404 dipetakan | 404 `NOT_FOUND` | PASS | QR token fiktif |
| CUST-04 | Customer | QR valid/table detail | GET dengan QR token nyata | Data meja sesuai | Token valid tidak tersedia untuk penggunaan aman | BLOCKED | Tidak memeriksa token tersimpan |
| CUST-05 | Customer | Public order status valid | GET dengan public token | Status order dipetakan | Token order valid tidak tersedia | BLOCKED | Order lama bukan objek eksperimen |
| CUST-06 | Customer | Public order token invalid | Axios `customerApi.order()` | 404 dipetakan | 404 `NOT_FOUND` | PASS | Token fiktif |
| PAY-01 | Payment | Batas upload 3 MB | Inspeksi UI + FormRequest | >3 MB ditolak | Tombol disabled di atas 3 MiB; backend `max:3072` | PASS | Tidak unggah file |
| PAY-02 | Payment | Aturan QRIS/manual verify | Inspeksi source/API report | Upload bukti, Kasir manual, tidak ganti QRIS ke tunai | Sesuai request/backend service terdokumentasi | PASS | Static only, bukan alur runtime |
| PAY-03 | Payment | Upload bukti nyata | POST multipart | Tersimpan tanpa efek bisnis samping | Tidak dijalankan | BLOCKED | Akan menulis file/DB |
| PAY-04 | Payment | Status bukti pada order valid | GET dengan token order | Status sesuai backend | Tidak ada public token valid | BLOCKED | Tidak membuka transaksi historis |
| PAY-05 | Payment | Kasir verifikasi/penolakan | POST authenticated | Payment/ticket transisi sesuai | Tidak dijalankan | BLOCKED | Mutasi DB aktif dilarang |
| ORD-01 | Orders | POS/create order | POST | Order baru sesuai kontrak | Tidak dijalankan | BLOCKED | Mutasi DB aktif dilarang |
| ORD-02 | Orders | Daftar order tanpa token | Axios `ordersApi.list()` | 401 tanpa data order | 401 `UNAUTHENTICATED` | PASS | API nyata |
| ORD-03 | Orders | Detail order dengan valid id/token role | GET authenticated | Detail sesuai permission | Tidak ada sesi sah | BLOCKED | Tidak memakai transaksi lama sebagai fixture |
| ORD-04 | Orders | Cancellation/refund | POST flow | Status/audit konsisten | Tidak dijalankan | BLOCKED | Positif Task 3 masih terblokir |
| ST-01 | Station | Tickets tanpa token | GET via Axios | 401 dan tak ada ticket bocor | 401 `UNAUTHENTICATED` | PASS | API nyata |
| ST-02 | Station | Queue Barista/Kitchen dengan role sah | GET authenticated | Data terpisah by `users.station` | Tidak ada token role aman | BLOCKED | UI filter mock bukan bukti backend |
| ST-03 | Station | `updated_since` refresh | GET dengan role sah | Delta valid atau 422 untuk format buruk | Tidak dijalankan pada API ber-token | BLOCKED | Tidak memanipulasi tiket lama |
| SEC-01 | Security | 401 | GET tanpa token | Error seragam tanpa data private | 401 dan ApiError code `UNAUTHENTICATED` | PASS | Axios aktual |
| SEC-02 | Security | 403 | GET dengan token valid tapi kurang izin | Forbidden, tanpa data | Tidak ada kredensial/token terverifikasi | BLOCKED | RBAC backend tidak disimpulkan dari UI |
| SEC-03 | Security | 404 | QR/order token invalid | Error dinormalisasi | 404 `NOT_FOUND` | PASS | Axios aktual |
| SEC-04 | Security | 405 | POST ke URI health GET-only | 405 tanpa dispatch mutasi | 405 JSON | PASS | Route list hanya mendaftarkan GET/HEAD health |
| SEC-05 | Security | 422 | Validasi API | Pesan field terpetakan | Tidak dites; tidak mengirim mutation invalid | NOT TESTED | Status frontend sudah mapper generik |
| SEC-06 | Security | 429 | Rate limit | Error ramah dan state aman | Tidak dites | NOT TESTED | Tidak melakukan request berulang |
| SEC-07 | Security | 500 | Internal failure | Tidak bocor stack trace | Tidak dipancing | NOT TESTED | Tidak merusak layanan |
| DB-01 | Data integrity | Counts sebelum/sesudah | `php artisan db:show --counts` | Count baseline sama | Semua count relevan identik | PASS | Lihat inventory |
| REG-01 | Regression | Frontend test dan build | `npm.cmd test`, `npm.cmd run build` | Lulus | 26/26, build 159 modul | PASS | Mock + production build |

**Agregat 44 skenario:** 21 PASS, 0 FAIL, 20 BLOCKED, 3 NOT TESTED. Status blocked dipisah dari hasil PASS read-only.
