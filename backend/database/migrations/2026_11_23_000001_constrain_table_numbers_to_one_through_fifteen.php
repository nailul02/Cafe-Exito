<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tables')) {
            return;
        }

        if (DB::table('pg_constraint')->where('conname', 'tables_number_range_check')
            ->whereRaw('conrelid = ?::regclass', ['tables'])->exists()) {
            return;
        }

        // Keep the legacy Meja 99 row and its historical references intact. It is
        // excluded from QR/POS selection by Table::numbered(), but may remain in
        // the database until its separately approved cleanup.
        $invalid = DB::table('tables')->whereRaw("table_number !~ '^(?:[1-9]|1[0-5]|99)$'")->exists();
        if ($invalid) {
            throw new RuntimeException('Cannot constrain table numbers: existing tables contain values outside 1–15 and the preserved legacy value 99. Review table/order relations before migrating.');
        }

        DB::statement("ALTER TABLE tables ADD CONSTRAINT tables_number_range_check CHECK (table_number ~ '^(?:[1-9]|1[0-5]|99)$')");
    }

    public function down(): void
    {
        if (Schema::hasTable('tables')) {
            DB::statement('ALTER TABLE tables DROP CONSTRAINT IF EXISTS tables_number_range_check');
        }
    }
};
