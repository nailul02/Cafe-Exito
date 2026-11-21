# Frontend Security Report — Task 4.6

Pemeriksaan ini terbatas pada inspeksi statis dan test mock; bukan penetration test atau validasi browser production.

| Area | Hasil | Severity |
|---|---|---|
| Route guard dan role redirect | Guard menguji user dan role; unauthenticated staf diarahkan ke `/login` dengan tujuan asal. Perbaikan kecil dilakukan dan diuji. | Tidak ada temuan tersisa pada jalur uji |
| Penyimpanan token | `sessionStorage`; dibersihkan ketika logout dan response 401. Pilihan penyimpanan final masih PENDING pada keputusan proyek. | INFO |
| XSS | Tidak ditemukan `innerHTML` atau `dangerouslySetInnerHTML` di `frontend/src`; React escaping default berlaku. | PASS (static) |
| Console | Tidak ditemukan pemanggilan `console.log/error/warn` di source frontend. | PASS (static) |
| Upload | File input membatasi pilihan berdasarkan `accept`, tetapi validasi client-side ukuran/isi berkas belum terbukti. | MEDIUM — cakupan belum lengkap |
| Error leakage | Pesan API generik diteruskan ke UI; body/details internal dapat ikut menjadi `ApiError` dan komponennya belum ditinjau menyeluruh. | LOW — perlu audit pesan backend/UI |
| Role/URL manipulation | Beberapa silang role diuji fixture; tidak membuktikan keamanan API. | BLOCKED untuk API nyata |

Bug diperbaiki: route staf tanpa sesi kembali ke katalog; kini menuju login. Urutan logout di StationLayout dan ManagementLayout disesuaikan agar route guard tidak mengalahkan navigasi eksplisit ke publik. Ini bukan pengganti authorization backend; backend tetap sumber otoritatif.

Tidak ada upload atau payload berbahaya yang dikirim ke server. Console browser, cookies/session nyata, dan CSP tidak diamati karena browser automation host tidak tersedia.
