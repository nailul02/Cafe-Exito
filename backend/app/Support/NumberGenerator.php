<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Nomor berurut harian (tanggal Asia/Jakarta): EX-20261101-0007, TRX-..., PO-..., GR-...
 * Dipanggil DI DALAM transaksi; advisory lock PostgreSQL mencegah nomor ganda saat permintaan serentak.
 */
final class NumberGenerator
{
    public static function next(string $prefix, string $table, string $column, int $pad = 4): string
    {
        $day = now()->format('Ymd');
        DB::select('select pg_advisory_xact_lock(?)', [crc32("$table:$column:$day")]);
        $like = "$prefix-$day-%";
        $max = (int) DB::table($table)->where($column, 'like', $like)->max(DB::raw("CAST(RIGHT($column, $pad) AS integer)"));

        return sprintf('%s-%s-%0'.$pad.'d', $prefix, $day, $max + 1);
    }
}
