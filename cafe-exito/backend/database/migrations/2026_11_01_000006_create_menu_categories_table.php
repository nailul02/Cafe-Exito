<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori terkelola (Figma: tab pelanggan 3 grup, kategori Manager lebih rinci) — K-T3, ASUMSI.
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('display_group', 10);
            $table->string('station', 10);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE menu_categories ADD CONSTRAINT menu_categories_group_check CHECK (display_group IN ('minuman','makanan','cemilan'))");
        DB::statement("ALTER TABLE menu_categories ADD CONSTRAINT menu_categories_station_check CHECK (station IN ('barista','kitchen'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};
