<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number', 24)->unique();   // PO-YYYYMMDD-NNN
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('ordered_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 20);
            $table->timestampTz('ordered_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->text('note')->nullable();
            $table->timestampsTz();
            $table->index('status');
        });
        DB::statement("ALTER TABLE purchases ADD CONSTRAINT purchases_status_check CHECK (status IN ('draft','dipesan','diterima_sebagian','diterima_lengkap','dibatalkan'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
