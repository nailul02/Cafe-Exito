<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 24)->unique();    // GR-YYYYMMDD-NNN
            $table->foreignId('purchase_id')->constrained('purchases')->restrictOnDelete();
            $table->string('status', 12);
            $table->date('received_at');
            $table->text('note')->nullable();                  // mis. no. faktur pengiriman
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampsTz();
            $table->index('purchase_id');
        });
        DB::statement("ALTER TABLE goods_receipts ADD CONSTRAINT goods_receipts_status_check CHECK (status IN ('draft','dikonfirmasi'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
