# Task 5.2 — Role and Permission Retest

**Status:** backend positive authorization BLOCKED; route/permission inspection PASS STATIC.

Role Owner, Manager, Kasir, Barista, dan Kitchen tidak dites dengan HTTP bearer sah karena kredensial/token aman tidak tersedia. Tidak ada 403 lintas role yang diklaim.

Inspeksi deklaratif menemukan route staff di bawah `auth:sanctum` + `active`, endpoint dibatasi `permission:*`, dan permission config memisahkan hak Owner, Manager, Kasir serta role `barista_kitchen`. Manager memiliki `tables.manage`; permission `refund.review` ada pada Owner. Ini hanya bukti konfigurasi.

Token tidak sah pada endpoint protected memberi 401; uji itu tidak mengonfirmasi perbedaan izin antar role. Lihat [ret test matrix](INTEGRATION_TESTING_RETEST_MATRIX.md).
