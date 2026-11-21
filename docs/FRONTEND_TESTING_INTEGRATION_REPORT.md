# Frontend API Integration Report — Task 4.5

Tidak ada request HTTP yang dikirim. Source diperiksa terhadap `docs/API_TESTING_MATRIX.md` dan layanan frontend. Base URL default `http://localhost:8000/api/v1`; adapter memilih mock secara default ketika `VITE_USE_MOCK` tidak diset.

## Pemetaan utama

| Area | Service/endpoint frontend | Hasil pemeriksaan |
|---|---|---|
| Auth | `auth.js`: login, logout, me | Axios interceptor menyisipkan Bearer token; 401 menghapus token dan memancarkan `auth:expired` |
| Customer | `customer.js`: table, menus, cart validate, payment info, order, status, proof | Header table token ditambahkan untuk path `/customer`; flow positif hanya diuji mock |
| Kasir | `cashier.js`: dashboard, tables, POS, orders, receipt, verify/reject | Mutasi tidak dipanggil ke backend; kontrak statis saja |
| Barista/Kitchen | `station.js`: dashboard, queue, start/complete | station berasal dari state role/query; mutasi tiket tidak dipanggil |
| Manager/Owner | reports, users, menus, attendance, refunds, orders | mapping source diperiksa; tidak ada sesi API nyata |

`http.js` menangani sukses envelope `data`, halaman `data/meta`, blob, dan error generik; status 401 membersihkan token. Error 403/404/429/500 tidak diuji melalui controlled server response. `useResource` mendukung loading/error/empty dan polling yang berhenti saat tab tersembunyi; belum ada pemeriksaan request count browser.

## Keamanan data dan blocker

Test suite menggunakan mock in-memory/localStorage dan bukan database cafe_exito. Login dan logout dalam mock, customer order, bukti QRIS, POS, pembayaran, refund, cancellation dan perubahan tiket tidak menyentuh backend aktif. Namun alur mutasi positif backend Task 3 masih belum diverifikasi; API real, mapping response nyata, pemulihan data refresh, serta error per endpoint berstatus BLOCKED/NOT TESTED. Screenshot Network/Console tidak tersedia karena browser host tidak aktif.

Terdapat service/route inventory dan purchasing di source meski backend melaporkan modul retired/tidak terdaftar. Jangan memanggil service tersebut.
