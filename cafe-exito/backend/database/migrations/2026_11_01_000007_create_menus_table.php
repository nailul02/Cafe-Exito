<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 30)->nullable();
            $table->string('name', 120);
            $table->foreignId('category_id')->constrained('menu_categories')->restrictOnDelete();
            $table->bigInteger('price');                       // bilangan bulat rupiah (T4)
            $table->bigInteger('cost_price')->nullable();      // HPP manual, opsional (K-B3 PENDING)
            $table->string('station', 10);                     // barista|kitchen; bawaan dari kategori
            $table->boolean('is_available')->default(true);    // saklar manual; ketersediaan efektif dihitung dari stok
            $table->string('image_path')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
        DB::statement('ALTER TABLE menus ADD CONSTRAINT menus_price_check CHECK (price >= 0)');
        DB::statement('ALTER TABLE menus ADD CONSTRAINT menus_cost_check CHECK (cost_price IS NULL OR cost_price >= 0)');
        DB::statement("ALTER TABLE menus ADD CONSTRAINT menus_station_check CHECK (station IN ('barista','kitchen'))");
        DB::statement('CREATE UNIQUE INDEX menus_sku_unique ON menus (sku) WHERE sku IS NOT NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
