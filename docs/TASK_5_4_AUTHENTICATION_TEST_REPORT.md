# Task 5.4 — Authentication Test Report

| Skenario | Status | Evidence |
|---|---|---|
| `/auth/me` tanpa token | PASS | Live HTTP 401 `UNAUTHENTICATED` pada Task 5.2 |
| Bearer token palsu | PASS | Live HTTP 401; Axios menghapus token test shim |
| Login valid / credential salah | BLOCKED | Feature `AuthTest` memakai DB fixture; password aktif tidak ditebak |
| Username tidak ditemukan | BLOCKED | Login failure menulis audit log; perlu DB tes approval |
| Input login berupa array | BLOCKED | Rate limiter static-safe (`is_string`); test regresi Feature ditambahkan namun belum dijalankan |
| Object/tipe lain malformed | NOT TESTED | Tidak dikirim ke endpoint login; perlu validasi HTTP terkontrol dan perhatian rate limit/cache |
| Rate limiter / 429 | NOT TESTED | Tidak ada request berulang |
| Token valid, profile, session restore | BLOCKED | Tidak ada credential/token aman |
| Logout dan reuse token | BLOCKED | Logout mencabut token; token database aktif tidak digunakan |
| Session handling browser | BLOCKED | Browser automation belum tersedia |

Tidak dilakukan password guessing, login probe terhadap akun aktif, pembuatan token, atau pencabutan token. `AuthTest` menyimpan fixture lewat factory sehingga tidak aman dijalankan sebelum gate Feature disetujui.
