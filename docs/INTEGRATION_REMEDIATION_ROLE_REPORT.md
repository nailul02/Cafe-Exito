# Task 5.1 — Role Fixture and Authorization Plan

**Tanggal:** 2026-11-21\
**Status:** PARTIALLY READY

## Sumber kebenaran

Role dan permission backend didefinisikan pada `backend/config/permissions.php`; API menggabungkan autentikasi Sanctum, permission middleware, serta pembatasan station untuk barista/kitchen (`backend/routes/api.php`). Frontend mock role tidak menggantikan pemeriksaan backend.

| Fixture | Permission utama yang perlu diuji | Negatif yang perlu diuji |
|---|---|---|
| Owner | dashboard.owner, orders.cancel, refund.review/audit, users.manage, audit.view | Endpoint yang mensyaratkan station/kasir |
| Manager | menus.manage, tables.manage, reports.view, refund.conflict.document | `refund.review`, `users.manage`, `permissions.grant` |
| Kasir | orders.create_pos, payments.verify, refund.request/execute, attendance.record | `users.manage`, `refund.review`, akses audit owner |
| Barista | queue.barista dan attendance.record | akses queue kitchen dan administrasi |
| Kitchen | queue.kitchen dan attendance.record | akses queue barista dan administrasi |
| Customer publik | menu/order publik dengan token meja/order yang valid | API staff tanpa Sanctum dan token publik tidak valid |

`barista` dan `kitchen` menggunakan enum role `barista_kitchen` yang sama; fixture harus membedakan `station`, lalu menguji keduanya agar pembatasan station tidak tertutup oleh role yang sama. Daftar di atas adalah rancangan cakupan, bukan klaim bahwa semua route sudah tervalidasi melalui integration run.

## Pola fixture

Gunakan factory per test, `Sanctum::actingAs` untuk matriks authorization, dan request JSON melalui route API. Buat kasus token login/logout terpisah melalui HTTP untuk menguji lifecycle token. Seluruhnya membutuhkan PostgreSQL khusus yang aman dan tidak boleh menyentuh user atau token aktif.

**READY:** permission config dan helper fixture tersedia.\
**BLOCKED:** hasil status 2xx/403/401 lintas semua fixture belum diuji dalam suite integration yang diizinkan.\
**NEEDS APPROVAL:** eksekusi Feature test terhadap `cafe_exito_test`.
