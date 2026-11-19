<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master attendance may already exist on a populated legacy installation.
        if (! Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('attendance_date');
                $table->string('status', 20)->default('present');
                $table->text('notes')->nullable();
                $table->timestampsTz();
                $table->unique(['user_id', 'attendance_date']);
                $table->index(['attendance_date', 'status']);
            });
            DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('present','absent','sick','leave'))");
        }

        // 2. Detail Kejadian Absensi (Check-in & Check-out Terpisah Penuh dengan Foto Selfie & Koordinat GPS)
        if (! Schema::hasTable('attendance_events')) {
            Schema::create('attendance_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('event_type', 20);
                $table->timestampTz('recorded_at');
                $table->string('photo_path', 255);
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->string('device_info', 255)->nullable();
                $table->text('notes')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->unique(['attendance_id', 'event_type']);
                $table->index(['user_id', 'recorded_at']);
            });
            DB::statement("ALTER TABLE attendance_events ADD CONSTRAINT attendance_events_type_check CHECK (event_type IN ('check_in','check_out'))");
        }
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback ditolak agar migration tidak menghapus data absensi.');
    }
};
