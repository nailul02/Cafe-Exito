# Frontend Form Report — Task 4.3

## Pemeriksaan yang dilakukan

- Login memiliki label, atribut `required`, `autocomplete` dan menampilkan error API umum atau error per-field dari `errors.login/password`.
- Fixture test meliputi login salah dan penolakan pembatalan customer tanpa alasan; submit dibatasi pada mock.
- Upload bukti QRIS menerima JPEG/PNG/WebP; bukti refund konflik menerima PNG/JPEG/PDF. Atribut `accept` hanya pembatas pemilih berkas, bukan validasi server.
- Form refund konflik menggunakan beberapa `window.prompt`; ini menyulitkan aksesibilitas, validasi inline dan usability, dan belum diuji interaktif.
- `useAction` menampilkan toast dan status busy, tetapi belum dibuktikan bahwa setiap form mencegah double submission.
- Error mapper mengubah status HTTP menjadi ApiError dengan code/message/errors; tidak ada fixture terpisah untuk 401, 403, 404, 422, 429 dan 500.

| Kasus | Status | Catatan |
|---|---|---|
| Field login wajib dan label | PASS (static) | Belum interaksi browser |
| Kredensial salah | PASS (mock) | Test suite |
| Alasan pembatalan wajib dan qty parsial | PASS (mock) | Hanya mock database browser-test |
| Upload QRIS tidak melunasi payment otomatis | PASS (mock) | Tidak upload ke transaksi aktif |
| MIME invalid, ukuran file, pesan batas | NOT TESTED | Browser/API nyata tidak digunakan |
| Format email/angka/tanggal, min/max karakter seluruh form | NOT TESTED | Audit per-field belum lengkap |
| 401/403/404/422/429/500 di setiap form | NOT TESTED | Belum disimulasikan |
| Loading, double submission, nilai bertahan setelah 422 | NOT TESTED | Perlu controlled API mock/browser |

Tidak ada payload mutasi yang dikirim ke backend. Uji upload positif nyata, POS dan pembayaran tetap BLOCKED sesuai Task 3.
