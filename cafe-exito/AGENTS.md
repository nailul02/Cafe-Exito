# Petunjuk untuk agen AI (Codex, Antigravity, Claude)

Proyek: **Cafe Exito**: aplikasi pemesanan self-order QR + operasional kafe. Frontend dan backend **terpisah**.

## Wajib dibaca sebelum mengubah apa pun
1. `docs/IMPLEMENTATION_STATUS.md`: apa yang sudah jadi, yang BELUM diuji, pekerjaan tersisa.
2. `docs/DECISIONS_PENDING.md`: keputusan CONFIRMED / ASSUMPTION / PENDING. Jangan mengubah keputusan CONFIRMED.
3. `docs/API_DOCUMENTATION.md`: kontrak API (frontend dan backend harus sama).
4. `docs/DEVELOPMENT_GUIDE.md`: setup Windows dan cara menjalankan.

## Aturan keras
- Backend = Laravel **REST API-only** (`/api/v1`), **PostgreSQL**, Sanctum bearer token. Dilarang menambah Blade, CSS/JS frontend, atau halaman UI di `backend/`.
- Pembayaran hanya **tunai** dan **QRIS statis (verifikasi manual kasir)**. Dilarang menambah payment gateway/EDC/transfer.
- Barista dan Kitchen **terpisah** (izin `queue.barista` / `queue.kitchen`).
- Semua perubahan stok HANYA lewat `App\Services\StockService`. Semua perubahan status lewat Service pemiliknya. Jangan menaruh logika bisnis di Controller.
- Stok hanya bertambah saat **penerimaan barang dikonfirmasi** (bukan saat pembelian dipesan).
- Uang = bilangan bulat rupiah. Jumlah bahan = string desimal 3 digit. Waktu = ISO 8601 (+07:00).
- Frontend: komponen UI hanya mengimpor dari `src/api` (bukan `src/mocks` atau `axios` langsung).
- Perubahan skema = migration baru + pembaruan `docs/DATABASE_SCHEMA.md` + catatan di `docs/DECISIONS_PENDING.md`.
- Jangan menambah fitur di luar MVP (shift/absensi, POS kasir, PB1, modifier, promo, HPP/laba bersih, multi-outlet, WhatsApp) tanpa persetujuan pemilik proyek.
- Jangan commit kredensial. Jangan mengklaim tes berhasil jika belum dijalankan.
