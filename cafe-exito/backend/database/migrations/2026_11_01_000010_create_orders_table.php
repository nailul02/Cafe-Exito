<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->char('public_token', 26)->unique();        // ULID acak untuk akses pelanggan
            $table->string('order_number', 24)->unique();      // EX-YYYYMMDD-NNNN
            $table->uuid('client_request_id')->nullable()->unique();  // anti pesan ganda
            $table->foreignId('table_id')->constrained('tables')->restrictOnDelete();
            $table->string('status', 24);
            $table->timestampTz('ordered_at');
            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('total');
            $table->timestampsTz();
            $table->index(['status', 'ordered_at']);
            $table->index('paid_at');
            $table->index('updated_at');
        });
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('menunggu_pembayaran','dibayar','diproses','selesai','dibatalkan'))");
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_total_check CHECK (total >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
