# Task 5.3 — Database Isolation Report

**Tanggal audit:** 2026-11-21. Semua query audit hanya SELECT; PHPUnit Feature tidak dijalankan.

| Properti | `cafe_exito` aktif | `cafe_exito_test` |
|---|---|---|
| PostgreSQL | 16.14 | 16.14 |
| Host / port | 127.0.0.1 / 5432 | 127.0.0.1 / 5432 |
| Database | cafe_exito | cafe_exito_test |
| Schema | public | public |
| Koneksi / role | cafe_exito_user | cafe_exito_user |
| Database owner | postgres | cafe_exito_user |
| Tabel public | 41 | 32 |
| Owner tabel | 11 cafe_exito_user, 30 postgres | 32 cafe_exito_user |
| migrations rows | 48 | 31 |
| Business rows | aktif; snapshot ada di laporan integritas | 0 pada seluruh tabel bisnis |
| Privilege role | CONNECT, public USAGE/CREATE | CONNECT, public USAGE/CREATE |

Object privilege check for `cafe_exito_user`: on `cafe_exito` it has SELECT/INSERT/UPDATE/DELETE on 41 public tables and TRUNCATE on 29; on `cafe_exito_test` it has those five privileges on all 32 tables. A misdirected test could mutate active tables; explicit target isolation is essential.

`backend/phpunit.xml` menetapkan pgsql + `cafe_exito_test`. Sebelum Laravel bootstrap, process `DB_DATABASE` dan `DB_URL` tidak terpasang; config cache tidak ada; config `pgsql.url` juga kosong. Simulasi read-only menerapkan aturan env PHPUnit (default `force=false`) lalu bootstrap Laravel; hasilnya default connection `pgsql`, database `cafe_exito_test`. Tidak ada DB connection dibuka oleh simulasi.

**Batas keselamatan:** pengaturan XML dapat ditimpa oleh process environment `DB_DATABASE` yang sudah ada karena `force` tidak diaktifkan. Jadi tidak terbukti tahan terhadap semua cara pemanggilan. Gunakan wrapper yang menetapkan `DB_DATABASE=cafe_exito_test` hanya untuk child process, dan ambil approval sebelum Feature suite. Tidak ada koneksi khusus/hardcoded `DB::connection()` di test; test default connection dipakai melalui `ApiTestCase`.

Sumber implementasi risiko: `ApiTestCase` memakai `RefreshDatabase`; Laravel 13.42 `RefreshDatabase` memanggil `migrate:fresh` sebelum transaksi test pertama.
