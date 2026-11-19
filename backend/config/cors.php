<?php

/*
 | CORS dibaca dari environment (keputusan F7). Default MENOLAK: daftar origin kosong = tidak ada akses lintas-origin.
 | Wildcard "*" hanya boleh saat APP_ENV=local (dicek di AppServiceProvider).
 */
$list = static fn (string $key, string $default = ''): array => array_values(array_filter(
    array_map('trim', explode(',', (string) env($key, $default))),
    static fn ($v) => $v !== '',
));

return [
    'paths' => ['api/*'],
    'allowed_methods' => $list('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS'),
    'allowed_origins' => $list('CORS_ALLOWED_ORIGINS'),
    'allowed_origins_patterns' => $list('CORS_ALLOWED_ORIGIN_PATTERNS'),
    'allowed_headers' => $list('CORS_ALLOWED_HEADERS', 'Accept,Authorization,Content-Type,X-Table-Token,X-Request-Id'),
    'exposed_headers' => $list('CORS_EXPOSED_HEADERS', 'X-Request-Id'),
    'max_age' => (int) env('CORS_MAX_AGE', 600),
    'supports_credentials' => filter_var(env('CORS_SUPPORTS_CREDENTIALS', false), FILTER_VALIDATE_BOOL),
];
