<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu tiket per stasiun per pesanan (pesanan campuran minuman + makanan menghasilkan dua tiket).
        Schema::create('order_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('station', 10);
            $table->string('status', 15);
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('ready_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->unique(['order_id', 'station']);
            $table->index(['station', 'status', 'created_at']);
            $table->index('updated_at');
        });
        DB::statement("ALTER TABLE order_tickets ADD CONSTRAINT order_tickets_station_check CHECK (station IN ('barista','kitchen'))");
        DB::statement("ALTER TABLE order_tickets ADD CONSTRAINT order_tickets_status_check CHECK (status IN ('antre','diproses','selesai','dibatalkan'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tickets');
    }
};
