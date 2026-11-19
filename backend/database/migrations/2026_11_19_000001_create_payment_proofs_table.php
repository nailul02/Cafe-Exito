<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_proofs')) return;

        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->unsignedInteger('size_bytes');
            $table->char('sha256', 64)->unique();
            $table->string('status', 12)->default('pending');
            $table->string('reject_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('uploaded_at');
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();
            $table->index(['payment_id', 'id']);
        });
        DB::statement("ALTER TABLE payment_proofs ADD CONSTRAINT payment_proofs_status_check CHECK (status IN ('pending','verified','rejected'))");
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback ditolak agar riwayat bukti pembayaran tidak terhapus.');
    }
};
