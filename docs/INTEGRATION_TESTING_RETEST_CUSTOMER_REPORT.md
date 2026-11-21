# Task 5.2 — Customer Integration Retest

| Skenario | Hasil |
|---|---|
| Health | PASS — GET 200 |
| Menu publik | PASS — GET 200; Axios menerima 11 item |
| Payment info | PASS — GET 200; object payment info terpetakan |
| Cart preview | PASS — pratinjau satu item, response tidak menyimpan data menurut implementasi `OrderService::preview` |
| Cart tanpa `items` | PASS negatif — 422, field `items` |
| `qty=0` | PASS negatif — 422, field `items.0.qty` |
| QR token invalid | PASS negatif — 404 `NOT_FOUND` |
| Token order invalid | PASS negatif — 404 `NOT_FOUND` |
| QR/token/order valid | BLOCKED — tidak mengekstrak token historis dari DB |
| Pembuatan order/customer checkout | BLOCKED — akan menulis transaksi |

Hasil validasi menggunakan service Axios frontend. Tidak ada order dibuat atau diubah.
