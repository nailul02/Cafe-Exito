<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jejak pergerakan stok (append-only): tidak pernah diubah atau dihapus oleh aplikasi.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->string('type', 25);
            $table->decimal('quantity_change', 12, 3);
            $table->decimal('stock_after', 12, 3);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_ticket_id')->nullable()->constrained('order_tickets')->restrictOnDelete();
            $table->foreignId('goods_receipt_detail_id')->nullable()->constrained('goods_receipt_details')->restrictOnDelete();
            $table->string('note')->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->index(['ingredient_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_type_check CHECK (type IN ('penerimaan_pembelian','pemakaian_pesanan','koreksi','pembalikan_pemakaian'))");
        DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_change_check CHECK (quantity_change <> 0)');
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_reference_check CHECK (
            (type = 'penerimaan_pembelian' AND goods_receipt_detail_id IS NOT NULL AND order_ticket_id IS NULL)
            OR (type IN ('pemakaian_pesanan','pembalikan_pemakaian') AND order_ticket_id IS NOT NULL AND goods_receipt_detail_id IS NULL)
            OR (type = 'koreksi' AND order_ticket_id IS NULL AND goods_receipt_detail_id IS NULL AND note IS NOT NULL)
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
