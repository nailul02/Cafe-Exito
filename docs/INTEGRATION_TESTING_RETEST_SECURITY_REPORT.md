# Task 5.2 — Security Integration Retest

| Kontrol | Hasil |
|---|---|
| Tanpa token | PASS — protected `/auth/me` dan cashier dashboard 401 |
| Token palsu | PASS — 401; token dibersihkan dari sessionStorage shim |
| Invalid QR/order | PASS — 404 `NOT_FOUND` |
| Method mismatch | PASS — POST `/health` 405 `METHOD_NOT_ALLOWED` |
| Cart validation | PASS — missing items / qty nol 422; tidak masuk controller penyimpan |
| CORS GET + preflight | PASS — `http://localhost:5173` diizinkan |
| Unauthorized role 403 | BLOCKED — butuh token sah dengan permission terbatas |
| Upload invalid size/MIME | NOT TESTED — tidak mengunggah file |
| Rate limit 429 | NOT TESTED — tidak melakukan burst request |
| Error 500 / info leakage | NOT TESTED — tidak ada fault injection |

Respons error tidak menampilkan secret/token dari hasil yang diamati. Klaim ini tidak mencakup semua error path atau browser enforcement.
