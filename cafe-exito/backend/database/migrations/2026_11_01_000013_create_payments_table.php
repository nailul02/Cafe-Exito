<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->string('method', 10);
            $table->string('status', 12);
            $table->bigInteger('amount');
            $table->bigInteger('amount_received')->nullable();         // tunai; opsional (Figma tidak memuat kolomnya)
            $table->string('transaction_number', 30)->nullable()->unique();  // TRX-YYYYMMDD-NNN, dibuat saat verifikasi
            $table->timestampTz('paid_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reject_reason')->nullable();
            $table->timestampsTz();
            $table->index('status');
        });
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_method_check CHECK (method IN ('tunai','qris'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('pending','verified','rejected','cancelled','refunded'))");
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_check CHECK (amount >= 0)');
        // Maksimal satu pembayaran pending dan satu terverifikasi per pesanan.
        DB::statement("CREATE UNIQUE INDEX payments_one_pending_per_order ON payments (order_id) WHERE status = 'pending'");
        DB::statement("CREATE UNIQUE INDEX payments_one_verified_per_order ON payments (order_id) WHERE status = 'verified'");
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
