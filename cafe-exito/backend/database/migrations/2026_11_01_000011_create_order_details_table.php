<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete();
            $table->string('menu_name', 120);                  // snapshot nama
            $table->unsignedSmallInteger('qty');
            $table->bigInteger('unit_price');                  // snapshot harga
            $table->bigInteger('subtotal');
            $table->string('station', 10);                     // snapshot stasiun
            $table->timestampsTz();
            $table->index('order_id');
        });
        DB::statement('ALTER TABLE order_details ADD CONSTRAINT order_details_qty_check CHECK (qty > 0)');
        DB::statement("ALTER TABLE order_details ADD CONSTRAINT order_details_station_check CHECK (station IN ('barista','kitchen'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
