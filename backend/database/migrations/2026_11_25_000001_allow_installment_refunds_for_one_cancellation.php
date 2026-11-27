<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('refund_request_items')) return;

        DB::statement('ALTER TABLE refund_request_items DROP CONSTRAINT IF EXISTS refund_request_items_cancellation_id_unique');
        DB::statement('CREATE UNIQUE INDEX refund_request_items_payment_cancellation_unique ON refund_request_items(refund_request_id, cancellation_id)');
    }

    public function down(): void
    {
        if (! Schema::hasTable('refund_request_items')) return;

        $splitCancellations = DB::table('refund_request_items')->select('cancellation_id')
            ->groupBy('cancellation_id')->havingRaw('COUNT(*) > 1')->exists();
        if ($splitCancellations) {
            throw new RuntimeException('Cannot restore one-refund-row-per-cancellation while an item cancellation has refunds split across installments.');
        }

        DB::statement('DROP INDEX IF EXISTS refund_request_items_payment_cancellation_unique');
        DB::statement('CREATE UNIQUE INDEX refund_request_items_cancellation_id_unique ON refund_request_items(cancellation_id)');
    }
};
