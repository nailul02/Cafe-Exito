<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_detail_id')->constrained('purchase_details')->restrictOnDelete();
            $table->decimal('qty_received', 12, 3);
            $table->timestampsTz();
            $table->unique(['goods_receipt_id', 'purchase_detail_id']);
        });
        DB::statement('ALTER TABLE goods_receipt_details ADD CONSTRAINT goods_receipt_details_qty_check CHECK (qty_received > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_details');
    }
};
