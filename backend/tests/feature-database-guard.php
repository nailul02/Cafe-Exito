<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

foreach ([
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'pgsql',
    'DB_DATABASE' => 'cafe_exito_test',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '5432',
    'DB_USERNAME' => 'cafe_exito_user',
    'DB_URL' => '',
] as $name => $value) {
    putenv("$name=$value");
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$connection = config('database.connections.'.config('database.default'));
$expected = [
    'driver' => 'pgsql',
    'host' => '127.0.0.1',
    'port' => '5432',
    'database' => 'cafe_exito_test',
    'username' => 'cafe_exito_user',
    'search_path' => 'public',
];

foreach ($expected as $key => $value) {
    if (($connection[$key] ?? null) !== $value) {
        $actual = $connection[$key] ?? '(unset)';
        throw new RuntimeException("Feature DB guard refused: Laravel connection setting [$key] resolved to [$actual], not the approved test target.");
    }
}

if (! empty($connection['url'])) {
    throw new RuntimeException('Feature DB guard refused: Laravel DB_URL must be empty.');
}

$identity = DB::selectOne('SELECT current_database() AS database, current_user AS username, current_schema() AS schema');

if ($identity->database !== 'cafe_exito_test'
    || $identity->username !== 'cafe_exito_user'
    || $identity->schema !== 'public'
    || $identity->database === 'cafe_exito') {
    throw new RuntimeException('Feature DB guard refused: PostgreSQL identity did not match the approved test target.');
}

fwrite(STDOUT, "Feature DB guard passed: cafe_exito_test / cafe_exito_user / public.\n");
