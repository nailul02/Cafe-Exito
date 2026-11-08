<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('qty_ordered', 12, 3);
            $table->decimal('qty_received', 12, 3)->default(0);   // hanya bertambah saat penerimaan dikonfirmasi
            $table->bigInteger('unit_price')->default(0);         // pencatatan pembelian, bukan akuntansi
            $table->timestampsTz();
            $table->unique(['purchase_id', 'ingredient_id']);
        });
        DB::statement('ALTER TABLE purchase_details ADD CONSTRAINT purchase_details_qty_check CHECK (qty_ordered > 0)');
        DB::statement('ALTER TABLE purchase_details ADD CONSTRAINT purchase_details_received_check CHECK (qty_received >= 0 AND qty_received <= qty_ordered)');
        DB::statement('ALTER TABLE purchase_details ADD CONSTRAINT purchase_details_price_check CHECK (unit_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
