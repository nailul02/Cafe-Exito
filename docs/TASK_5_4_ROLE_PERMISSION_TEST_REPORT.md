# Task 5.4 — Role and Permission Test Report

| Role | Runtime HTTP authenticated | Static evidence |
|---|---|---|
| Owner | BLOCKED — token/fixture valid tidak tersedia | Permission config memberi `refund.review`, administrasi dan audit |
| Manager | BLOCKED | Memiliki `tables.manage`, menu/laporan/audit sesuai config; tidak memiliki `refund.review` |
| Kasir | BLOCKED | POS, verifikasi pembayaran, refund request/execute menurut config |
| Barista | BLOCKED | Role `barista_kitchen`, station `barista`; queue filtered melalui station middleware |
| Kitchen | BLOCKED | Role `barista_kitchen`, station `kitchen`; queue filtered melalui station middleware |
| Customer publik | BLOCKED untuk token QR/order valid | Public menu/payment info dan token invalid diuji; protected route tanpa bearer 401 |

Route refund decision dilindungi `permission:refund.review`; permission tersebut hanya tercantum untuk Owner. `EnsureStationAccess` mengambil station dari route dan permission; nonmatching station menghasilkan 404 saat tidak berhak membaca. Itu bukti source/config, bukan runtime test.

Tidak ada hasil 403 antar role. Role UI mock tidak dihitung sebagai integrasi authorization backend.
