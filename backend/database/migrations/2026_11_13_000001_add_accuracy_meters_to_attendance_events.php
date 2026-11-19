<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance_events')) {
            throw new RuntimeException('Tabel attendance_events harus tersedia sebelum menambah akurasi GPS.');
        }

        if (! Schema::hasColumn('attendance_events', 'accuracy_meters')) {
            Schema::table('attendance_events', function (Blueprint $table): void {
                $table->decimal('accuracy_meters', 10, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Rollback ditolak agar metadata akurasi lokasi absensi tidak dihapus.');
    }
};
