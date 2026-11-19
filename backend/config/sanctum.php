<?php

return [
    // API-only dengan bearer token: tanpa mode cookie/SPA (keputusan F8, T2).
    'stateful' => [],

    // Kosong = tidak memeriksa guard session; hanya token bearer.
    'guard' => [],

    // Masa berlaku token (menit). null = tidak kedaluwarsa. Bawaan 12 jam.
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION_MINUTES', 720),

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
];
