<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendances')) {
            throw new RuntimeException('Tabel attendances tidak tersedia; migrasi dasar absensi memerlukan tabel master yang sudah ada.');
        }

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'check_in_at')) {
                $table->timestampTz('check_in_at')->nullable();
            }
            if (! Schema::hasColumn('attendances', 'check_out_at')) {
                $table->timestampTz('check_out_at')->nullable();
            }
        });

        $unique = DB::select("SELECT indexdef FROM pg_indexes WHERE schemaname = current_schema() AND tablename = 'attendances'");
        $hasUniqueDailyRecord = collect($unique)->contains(fn ($index) =>
            str_contains(strtolower($index->indexdef), 'unique')
            && str_contains(strtolower($index->indexdef), '(user_id, attendance_date)')
        );

        if (! $hasUniqueDailyRecord) {
            $duplicates = DB::table('attendances')
                ->select('user_id', 'attendance_date')
                ->groupBy('user_id', 'attendance_date')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($duplicates) {
                throw new RuntimeException('Duplikasi absensi harian ditemukan; indeks unik tidak dibuat agar data lama tidak dihapus.');
            }

            DB::statement('CREATE UNIQUE INDEX attendances_user_date_unique ON attendances (user_id, attendance_date)');
        }
    }

    public function down(): void
    {
        // Keamanan data: perubahan ini additive dan tidak menghapus timestamp/indeks saat rollback.
    }
};
