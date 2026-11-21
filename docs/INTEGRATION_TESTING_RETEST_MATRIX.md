# Task 5.2 — Integration Testing Retest Matrix

Tanggal: 2026-11-21. Retest non-mutatif memakai backend lokal, Axios frontend nyata, dan UI mock tests.

| ID | Area / skenario | Metode | Hasil | Status |
|---|---|---|---|---|
| R01 | Health | GET `/health` | 200 JSON | PASS |
| R02 | Katalog customer | GET `/customer/menus` | 200; Axios memetakan 11 item | PASS |
| R03 | Informasi pembayaran | GET `/customer/payment-info` | 200; object terpetakan | PASS |
| R04 | QR table invalid | GET token fiktif | 404 `NOT_FOUND` | PASS |
| R05 | QR table valid | Membutuhkan token QR sah | Tidak memakai token historis | BLOCKED |
| R06 | Public order token invalid | GET token fiktif | 404 `NOT_FOUND` | PASS |
| R07 | Public order valid | Membutuhkan token valid | Tidak memakai token transaksi lama | BLOCKED |
| R08 | Cart preview valid | Axios POST ke `validateCart` | 1 item dipetakan, tanpa simpan | PASS |
| R09 | Cart tanpa items | Axios POST validasi | 422 `VALIDATION_FAILED`, field `items` | PASS |
| R10 | Cart qty nol | Axios POST validasi | 422, field `items.0.qty` | PASS |
| R11 | Protected `/auth/me` tanpa token | GET | 401 `UNAUTHENTICATED` | PASS |
| R12 | Protected `/auth/me` token palsu | GET | 401; token test lokal terhapus | PASS |
| R13 | Dashboard Kasir tanpa token | GET | 401 | PASS |
| R14 | Method tidak diizinkan | POST `/health` | 405 `METHOD_NOT_ALLOWED` | PASS |
| R15 | CORS GET frontend | GET dengan origin localhost:5173 | 200; origin diizinkan | PASS |
| R16 | CORS preflight | OPTIONS menus | 204; origin diizinkan | PASS |
| R17 | Mapping sukses Axios | Menu/payment info | Envelope `data` dipetakan ke object/array | PASS |
| R18 | Mapping error Axios | 401/404/422 | `ApiError` status/code/fields sesuai | PASS |
| R19 | Login payload malformed | Tidak dikirim ke endpoint login | Login gagal dapat menulis audit; dihindari | NOT TESTED |
| R20 | Login sukses | Kredensial staff valid | Kredensial tidak tersedia | BLOCKED |
| R21 | Profile `/auth/me` authenticated | Token valid tidak tersedia | Tidak dites | BLOCKED |
| R22 | Logout/token revocation | Akan mencabut token | Tidak memakai token historis | BLOCKED |
| R23 | Owner auth + permission | Token Owner tidak tersedia | Hanya inspeksi deklaratif | BLOCKED |
| R24 | Manager auth + permission | Token Manager tidak tersedia | Hanya inspeksi deklaratif | BLOCKED |
| R25 | Kasir auth + permission | Token Kasir tidak tersedia | Hanya inspeksi deklaratif | BLOCKED |
| R26 | Barista auth + permission | Token Barista tidak tersedia | Hanya inspeksi deklaratif | BLOCKED |
| R27 | Kitchen auth + permission | Token Kitchen tidak tersedia | Hanya inspeksi deklaratif | BLOCKED |
| R28 | Pemisahan station backend | Memerlukan actor sah untuk tiap station | Tidak dites | BLOCKED |
| R29 | Customer dengan QR valid | Token QR valid tidak tersedia | Tidak dites | BLOCKED |
| R30 | Order status customer valid | Token publik valid tidak tersedia | Tidak dites | BLOCKED |
| R31 | QRIS self-order positif | Membuat order/pembayaran | Tidak dijalankan pada DB aktif | BLOCKED |
| R32 | Upload bukti QRIS | Menulis file dan row pembayaran | Tidak dijalankan | BLOCKED |
| R33 | Kasir verify/reject payment | Mengubah status/order/tiket | Tidak dijalankan | BLOCKED |
| R34 | Self-order tunai + bayar ke Kasir | Membuat/mengubah transaksi | Tidak dijalankan | BLOCKED |
| R35 | POS Kasir | Membuat order/payment | Tidak dijalankan | BLOCKED |
| R36 | Mutasi tiket Barista/Kitchen | Mengubah status tiket | Tidak dijalankan | BLOCKED |
| R37 | Pembatalan order/item | Menulis status/audit/refund | Tidak dijalankan | BLOCKED |
| R38 | Refund request dan keputusan Owner | Menulis workflow refund | Tidak dijalankan | BLOCKED |
| R39 | 403 lintas role | Membutuhkan token valid tanpa izin | Tidak ada token aman | BLOCKED |
| R40 | File upload lebih dari 3 MiB | Tidak diunggah | Batas diperiksa statis (`max:3072` dan UI) | NOT TESTED |
| R41 | Rate limit 429 | Tidak mengirim burst request | Tidak diuji | NOT TESTED |
| R42 | Error 500 | Tidak ada fault injection | Tidak diuji | NOT TESTED |
| R43 | Browser E2E/responsive UI | Runner/host browser tidak tersedia | Tidak diuji di browser | BLOCKED |
| R44 | Vitest | `npm.cmd test` | 1 file, 26/26 test lulus | PASS |
| R45 | Production build | `npm.cmd run build` | 159 modules; build berhasil | PASS |
| R46 | Isolasi DB PHPUnit | Query katalog + konfigurasi | DB berbeda/kosong dari data aplikasi; PHPUnit target DB tes | PASS |
| R47 | Kontrak bisnis statis | Inspeksi route/request/service/UI | Celah QRIS POS → tunai ditemukan dan diperbaiki; kontrak kini sesuai | PASS (STATIC, AFTER FIX) |
| R48 | Feature regression QRIS POS → tunai | Test baru pada `CashierPosTest` | Memastikan 422 dan payment tetap pending/QRIS | BLOCKED — PHPUnit Feature tidak dijalankan karena memakai `RefreshDatabase` |

## Rekap

**48 skenario:** 20 PASS (termasuk 1 PASS STATIC), 0 FAIL, 24 BLOCKED, 4 NOT TESTED.

PASS hanya berlaku pada probe yang tercantum. Tidak ada positive auth atau alur bisnis mutatif yang diklaim berhasil end-to-end.
