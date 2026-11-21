# API Testing Report — Task 3.8 Security & Regression

Tanggal: 21 November 2026. Pengujian menggunakan HTTP localhost dan query read-only; tidak membaca `.env`.

## Hasil regresi

- BUG-AUTH-01: JSON login dengan `login` array, object, angka, atau string kosong menghasilkan 422, bukan 500. Percobaan invalid lanjut menerima 429 saat throttle tercapai. Login berhasil tidak diuji karena kredensial valid tidak disediakan.
- Token Sanctum kedaluwarsa, dicabut, atau tidak valid menerima 401; token valid menerima 200 sebelum logout dan menjadi 401 sesudah logout.
- Query string SQL-like pada pencarian tidak membuka baris; input XSS tidak direfleksikan; tanggal laporan invalid menerima 422. URL lama inventory/purchasing menerima 404.
- Ditemukan potensi exception pada `SaveUserRequest::prepareForValidation()`: casting `username`/`email` non-string menjadi string sebelum validation. Normalisasi kini hanya berjalan untuk string. HTTP `username` array dan `email` array masing-masing menghasilkan 422.
- `StationController` sebelumnya mem-parse `updated_since` langsung. Validasi `date` kini berjalan lebih dahulu. HTTP nilai invalid scalar/array menghasilkan 422 tanpa `exception`/`trace`; tanggal valid menghasilkan 200.
- PHP lint lulus untuk `SaveUserRequest.php` dan (sebelumnya) `StationController.php`.

## Batas keamanan

APP_DEBUG lokal dapat menampilkan detail exception ketika terjadi exception yang tidak tertangani; retest invalid-date setelah perbaikan tidak lagi memicu exception tersebut. Upload proof valid/oversize dan mutasi refund tidak dijalankan karena dapat mengubah DB atau menambah audit trail. Tidak ada kredensial atau nilai rahasia yang dicatat.
