<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengaturan kecil: gambar QRIS statis, identitas kafe untuk struk (K-T8, ASUMSI).
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->jsonb('value')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
