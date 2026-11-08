# Cafe Exito

Aplikasi self-order QR dan operasional kafe (proyek mata kuliah Projek Sistem Informasi, Kelompok 4, mitra Cafe Exito).
Frontend dan backend **berdiri terpisah** dan hanya berkomunikasi lewat REST API JSON (`/api/v1`).

```
cafe-exito/
├── frontend/   React 19 + Vite + Tailwind CSS 4 + React Router + Axios (jalan sendiri dengan data mock)
├── backend/    Laravel 13 REST API-only + PostgreSQL + Sanctum (tanpa Blade)
├── docs/       Dokumentasi bersama (mulai dari IMPLEMENTATION_STATUS.md)
└── AGENTS.md   Aturan untuk Codex / Antigravity / Claude
```

## Mulai cepat

**Frontend (tanpa backend, data mock):**
```
cd frontend
copy .env.example .env
npm ci
npm run dev          # http://localhost:5173
```
Login demo (kata sandi `password`): `owner`, `manager`, `ucup` (kasir), `nana` (barista), `dewi` (kitchen). Pelanggan: buka `/order`.

**Backend:** lihat [docs/DEVELOPMENT_GUIDE.md](docs/DEVELOPMENT_GUIDE.md) (setup Windows, PostgreSQL, Laragon).

## Dokumen
| Dokumen | Isi |
|---|---|
| [IMPLEMENTATION_STATUS](docs/IMPLEMENTATION_STATUS.md) | Yang sudah dibuat, hasil uji (yang dijalankan dan yang BELUM), pekerjaan tersisa |
| [DECISIONS_PENDING](docs/DECISIONS_PENDING.md) | Keputusan CONFIRMED / ASSUMPTION / PENDING |
| [TECHNICAL_BLUEPRINT](docs/TECHNICAL_BLUEPRINT.md) | Arsitektur, hubungan frontend–backend–database–modul |
| [API_DOCUMENTATION](docs/API_DOCUMENTATION.md) | Kontrak API (envelope, endpoint, izin) |
| [DATABASE_SCHEMA](docs/DATABASE_SCHEMA.md) | Skema PostgreSQL |
| [FIGMA_MAPPING](docs/FIGMA_MAPPING.md) | Layar Figma ke halaman, API, tabel |
| [BACKEND_GAP_ANALYSIS](docs/BACKEND_GAP_ANALYSIS.md) | Selisih Figma/laporan/blueprint dan penyelesaiannya |
| [DEVELOPMENT_GUIDE](docs/DEVELOPMENT_GUIDE.md) | Setup, perintah, konvensi, cara mengganti mock dengan backend |

Status keputusan: **CONFIRMED** = disepakati tim · **ASSUMPTION** = dibuat sementara, mudah diubah · **PENDING** = menunggu keputusan pemilik proyek.
