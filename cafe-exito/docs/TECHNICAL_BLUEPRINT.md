# Technical Blueprint (versi implementasi awal)

## Gambaran
```
Pelanggan (HP, via QR)       Staf (tablet/PC)
        │                          │
        └──────── React SPA (frontend/) ────────┐
                      Axios + src/api            │  REST JSON /api/v1
                      (mock ⇄ real via VITE_USE_MOCK)
                                                 ▼
                  Laravel API (backend/)  ── Middleware: ForceJson → auth:sanctum → active → permission/station
                    Controller (tipis) → FormRequest (validasi) → Service (aturan bisnis, transaksi) → Model → Resource
                                                 ▼
                                      PostgreSQL (21 tabel, CHECK + indeks parsial)
```
- **Real-time = polling** 3–5 detik (`useResource(..., {poll:true})` + `updated_since`/`meta.server_time`); tanpa WebSocket/queue/scheduler.
- **Satu pintu**: stok hanya lewat `StockService`; status hanya lewat Service pemilik; semua aksi multi-tabel dalam `DB::transaction` dengan penguncian baris.
- **Frontend tidak memegang aturan bisnis**: harga, total, ketersediaan, dan status selalu dari backend.

## Alur bisnis inti
1. Pelanggan scan QR → (nomor meja) → katalog → keranjang (di klien) → pilih QRIS/Tunai → `POST /customer/orders` → pesanan `menunggu_pembayaran` + pembayaran `pending`.
2. Kasir `verify` (QRIS statis/tunai, manual) → pembayaran `verified`, pesanan `dibayar`, nomor transaksi dibuat, **tiket per stasiun** dibuat (minuman → Barista, makanan → Kitchen).
3. Barista/Kitchen `start` → stok bahan dikurangi menurut resep (`stock_movements`), tiket `diproses`, pesanan `diproses`; `complete` → tiket `selesai`; bila semua tiket selesai → pesanan `selesai`.
4. Pembelian: draft → dipesan → penerimaan (draft) → **konfirmasi** (stok +, `stock_movements`) → status pembelian diterima sebagian/lengkap.
5. Laporan dihitung dari pembayaran terverifikasi; dashboard per role dari layanan baca.

## Modul ↔ lokasi kode
| Modul | Backend | Frontend |
|---|---|---|
| Auth & izin | `Auth/AuthController`, `PermissionService`, `config/permissions.php`, middleware | `context/AuthContext`, `layouts/ProtectedRoute`, `pages/auth` |
| Pelanggan/meja | `Customer/*`, `OrderService`, `Admin/TableController` | `pages/customer/*`, `context/CartContext` |
| Menu & kategori & resep | `Admin/MenuController`, `RecipeController`, `MenuAvailabilityService` | `pages/manager/MenusPage` |
| Pesanan & pembayaran | `OrderService`, `PaymentService`, `Cashier/*`, `Order/*` | `pages/cashier/*` |
| Barista & Kitchen | `TicketService`, `Station/StationController`, `EnsureStationAccess` | `pages/station/*` (komponen sama, parameter `station`) |
| Inventori & stok | `StockService`, `Inventory/*` | `pages/manager/InventoryPage` |
| Supplier/Pembelian/Penerimaan | `PurchaseService`, `GoodsReceiptService`, `Purchasing/*` | `pages/manager/PurchasingPage` |
| Dashboard & laporan | `DashboardService`, `ReportService`, `Report/*` | `pages/owner/*`, `pages/manager/*`, `components/reports` |

## Keamanan
CSRF tidak relevan (bearer); validasi sisi server; harga/total/status tidak diterima dari klien; `public_token` ULID untuk pelanggan (bukan ID berurut); rate limit login (5/mnt), pelanggan (60/mnt/IP), staf (300/mnt); CORS dari environment (default menolak); kata sandi di-hash; token dicabut saat akun dinonaktifkan; respons galat produksi tanpa detail internal; kredensial seeder dari `.env`.
