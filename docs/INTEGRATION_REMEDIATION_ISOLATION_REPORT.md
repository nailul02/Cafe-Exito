# Task 5.1 — Database and Side Effect Isolation

**Tanggal:** 2026-11-21\
**Status:** Isolasi database terbukti terpisah; Feature suite tidak dijalankan sesuai batasan migration retest.

## Pemeriksaan konfigurasi

- `backend/phpunit.xml` memakai `APP_ENV=testing`, PostgreSQL, `DB_DATABASE=cafe_exito_test`, cache/session array, mail array, dan `QUEUE_CONNECTION=sync`.
- `backend/tests/Feature/ApiTestCase.php` memakai `RefreshDatabase`; setiap Feature test berpotensi mengubah skema dan data database yang dikonfigurasi. Karena itu keberadaan database saja tidak cukup sebagai izin menjalankan suite.
- Query katalog PostgreSQL read-only memastikan database `cafe_exito_test` ada dan berbeda dari `cafe_exito`. Target tersebut memakai schema `public`, owner `cafe_exito_user`; koneksi `cafe_exito_user` dapat CONNECT, USAGE, dan CREATE. Isinya 32 tabel: seluruh tabel bisnis 0 baris dan tabel `migrations` 31 baris. Ini membuktikan isolasi nama/database serta keadaan tanpa data aplikasi pada saat audit.
- `phpunit.xml` menetapkan `DB_CONNECTION=pgsql` dan `DB_DATABASE=cafe_exito_test`. Variable `DB_DATABASE` tidak diset di environment proses, dan file config cache Laravel tidak ada; sehingga tidak ada override lokal/cache yang tampak mengalihkan PHPUnit ke `cafe_exito`.
- Database dan aplikasi berada pada PostgreSQL host/port yang sama dan memakai role database yang sama. Isolasi ada pada database PostgreSQL terpisah; kesalahan konfigurasi manual di kemudian hari masih dapat mengarahkan koneksi ke DB aktif.
- Koneksi yang dipakai aplikasi aktif tercatat sebagai `127.0.0.1:5432`, database `cafe_exito`, role `cafe_exito_user`, schema `public`. Koneksi audit ke test menunjukkan database `cafe_exito_test`, host/port sama, role dan owner `cafe_exito_user`, schema `public`; secret/password tidak ditampilkan. Role owner memiliki `CONNECT` ke database serta `USAGE`/`CREATE` pada schema public.
- Target database aplikasi aktif adalah `cafe_exito`; tidak digunakan untuk test. SQLite memory tidak dipilih sebagai pengganti karena backend ditargetkan PostgreSQL dan sejumlah perilaku SQL/locking/JSONB mungkin berbeda.

## Side effect yang perlu diisolasi

- Upload bukti QRIS memakai local storage; refund proof memakai private storage; gambar menu memakai public storage. Beberapa test saat ini memakai `Storage::fake`, tetapi upload integration perlu memastikan semua disk/path diarahkan ke storage sementara.
- Audit log, notifikasi aplikasi, pembayaran, refund, order, stok, dan attendance menulis baris database. Test harus berada di database disposable.
- PHPUnit mengatur queue `sync`; queue production default bukan bukti bahwa worker atau side effect eksternal aman. Pastikan mail/cache/session juga tetap driver testing.
- Transaksi/rollback dalam proses test melindungi operasi database tertentu, tetapi tidak otomatis membatalkan file, worker/process terpisah, atau request HTTP eksternal yang memakai koneksi berbeda.

## Opsi isolasi dan rekomendasi

| Opsi | Penilaian |
|---|---|
| Mock/service tests tanpa database | Aman untuk aturan murni; tidak membuktikan integrasi DB/API |
| SQLite memory | Isolasi baik, kompatibilitas PostgreSQL belum memadai untuk suite penuh |
| PostgreSQL `cafe_exito_test` | Sudah dikonfigurasi dan ada; status disposable/aman belum diverifikasi |
| PostgreSQL/container sementara | Isolasi paling jelas untuk integration; perlu persetujuan/setup eksplisit |
| Rollback transaksi saja | Tidak cukup untuk proses HTTP eksternal dan storage/queue di luar transaksi |

## Gerbang sebelum run

1. Pemilik lingkungan mengesahkan database khusus test sebagai disposable dan tidak dipakai bersama.
2. Pastikan PHPUnit benar-benar memakai koneksi/database test, lalu verifikasi nama database sebelum migrasi/refresh.
3. Arahkan seluruh disk upload ke fake/temp storage dan pertahankan queue/mail/cache/session test drivers.
4. Ambil snapshot jumlah record `cafe_exito` sebelum/sesudah sebagai kontrol; jangan pernah menjalankan suite dengan koneksi aktif.

**Bukti cukup untuk menyatakan target PHPUnit saat ini terpisah:** database, schema, ownership, akses, isi tabel, dan pengaturan PHPUnit diverifikasi secara read-only. Namun suite Feature tidak dijalankan karena Task 5.2 melarang menjalankan migration/seeder serta memerintahkan validasi non-mutatif. Tidak ada migration, seed, container, maupun mutasi DB dilakukan pada retest ini.
