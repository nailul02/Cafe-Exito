# Task 5.2 — Station Integration Retest

Route source memastikan dashboard/tickets/details memakai `station:read`; start/start-item/complete memakai `station:act`. Permission config memakai role tunggal `barista_kitchen`; station pengguna membedakan `barista` dan `kitchen`. **PASS STATIC** untuk kontrak yang dideklarasikan.

GET station tanpa token mengikuti auth guard dan menghasilkan 401 pada retest sebelumnya; retest ini tidak mengirim token positif. Akses queue Barista vs Kitchen dengan identitas sah dan transisi tiket **BLOCKED** karena memerlukan token dan mutasi status. Tidak ada tiket berubah.
