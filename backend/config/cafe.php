<?php

return [
    'frontend_url' => rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/'),
    // {token} diganti qr_token meja. Jalur final menunggu rute frontend (K-T2 PENDING).
    'frontend_qr_path' => env('FRONTEND_QR_PATH', '/order?t={token}'),

    // Interval polling yang dianjurkan untuk frontend (detik).
    'poll_interval_seconds' => 4,

    'cart' => ['max_lines' => 30, 'max_qty' => 99],

    // Ambang level stok terhadap stok minimum (K-T5, ASUMSI): habis = 0; kritis <= 50%; menipis <= 100%; aman > 100%.
    'stock_levels' => ['critical_ratio' => 0.5],

    // Pesanan menunggu pembayaran dapat kedaluwarsa otomatis (menit). null = tidak aktif (ASUMSI, opsional).
    'unpaid_order_ttl_minutes' => null,

    'upload' => ['image_max_kb' => 2048, 'image_mimes' => ['jpg', 'jpeg', 'png', 'webp']],
];
