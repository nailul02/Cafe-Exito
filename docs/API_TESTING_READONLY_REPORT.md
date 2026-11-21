# API Testing Report — Task 3.6 Read-only

Tanggal: 21 November 2026. Pemeriksaan dilakukan terhadap `route:list` aktual dan HTTP localhost tanpa menulis data.

## Hasil

- Aplikasi memiliki **79 route API aktif**: 38 GET/HEAD dan 41 route mutasi. Rekonsiliasi dengan `route:list` menemukan semua 79 signature method+URI pada matriks; ringkasan angka matrix telah diperbarui.
- 79 registrasi menggunakan 69 pola URI karena beberapa URI menerima lebih dari satu HTTP method. `GET|HEAD` adalah satu registrasi GET yang juga menerima HEAD, bukan dua route. Tidak ada signature method+URI duplikat atau route aplikasi yang perlu diubah.
- Seluruh **38 route GET** dicoba dengan role yang sesuai; respons sukses atau 404 untuk resource yang memang tidak tersedia. 33 route terproteksi juga diuji tanpa token dan semuanya memberi 401.
- Endpoint resource private yang tidak memiliki data uji (tiket, proof, refund, conflict note) mengembalikan 404 tanpa kebocoran exception. Endpoint foto attendance mengembalikan data binary terotorisasi.
- Respons order publik tidak menyertakan ID internal, kredensial atau token. Akses daftar attendance Owner mencakup data yang memang diizinkan oleh permission laporan; Manager tidak menerima event/koordinat orang lain pada pemeriksaan resource.
- Empat URL inventory/purchasing lama (`/inventory/ingredients`, `/purchasing/purchases`, `/reports/stock-usage`, `/admin/menus/1/recipe`) mengembalikan 404. Tidak ditemukan route operasional recipe/stock pada route list.

## Temuan dan batas

- `docs/API_TESTING_PREFLIGHT.md` masih menyatakan server tidak aktif dan angka migrasi/token yang sudah usang. Perbarui preflight sebelum menggunakannya kembali.
- Pemeriksaan read-only tidak membuktikan alur sukses pada resource kosong; verifikasi bisnis positif dicatat di laporan business logic sebagai BLOCKED.
- Filter `updated_since` pada daftar station sebelumnya memicu HTTP 500 untuk tanggal invalid. Validasi tanggal ditambahkan di `StationController`; setelah perbaikan nilai invalid dan array menerima 422 tanpa detail exception, dan timestamp valid tetap menerima 200.
