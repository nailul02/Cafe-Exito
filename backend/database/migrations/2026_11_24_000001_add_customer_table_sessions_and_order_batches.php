<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->restrictOnDelete();
            $table->string('customer_name', 100);
            $table->string('status', 12)->default('active');
            $table->timestampTz('opened_at');
            $table->timestampTz('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('closing_total')->nullable();
            $table->bigInteger('closing_paid')->nullable();
            $table->bigInteger('closing_balance')->nullable();
            $table->timestampsTz();
            $table->index(['table_id', 'status']);
        });
        DB::statement("ALTER TABLE table_sessions ADD CONSTRAINT table_sessions_status_check CHECK (status IN ('active','closed'))");
        DB::statement("CREATE UNIQUE INDEX table_sessions_one_active_per_table ON table_sessions(table_id) WHERE status = 'active'");

        Schema::create('table_session_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_session_id')->constrained('table_sessions')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampsTz();
            $table->index(['table_session_id', 'revoked_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('table_session_id')->nullable()->after('table_id')->constrained('table_sessions')->restrictOnDelete();
        });

        Schema::create('table_session_order_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_session_id')->constrained('table_sessions')->restrictOnDelete();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->uuid('client_request_id');
            $table->char('payload_hash', 64);
            $table->timestampsTz();
            $table->unique(['table_session_id', 'client_request_id'], 'session_order_requests_idempotency_unique');
        });

        Schema::table('order_tickets', function (Blueprint $table) {
            $table->unsignedSmallInteger('batch_number')->default(1);
        });
        DB::statement('ALTER TABLE order_tickets DROP CONSTRAINT IF EXISTS order_tickets_order_id_station_unique');
        DB::statement('CREATE UNIQUE INDEX order_tickets_order_station_batch_unique ON order_tickets(order_id, station, batch_number)');

        Schema::create('order_ticket_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_ticket_id')->constrained('order_tickets')->cascadeOnDelete();
            $table->foreignId('order_detail_id')->constrained('order_details')->cascadeOnDelete();
            $table->timestampsTz();
            $table->unique(['order_ticket_id', 'order_detail_id'], 'order_ticket_details_pair_unique');
            $table->unique('order_detail_id', 'order_ticket_details_detail_unique');
        });

        Schema::create('payment_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('order_detail_id')->constrained('order_details')->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->timestampsTz();
            $table->unique(['payment_id', 'order_detail_id'], 'payment_order_details_pair_unique');
        });
        DB::statement('ALTER TABLE payment_order_details ADD CONSTRAINT payment_order_details_amount_check CHECK (amount > 0)');

        // Existing orders predate item-level allocations. Allocate their remaining
        // line values to payment rows deterministically so refunds remain bounded.
        DB::statement(<<<'SQL'
            WITH detail_amounts AS (
                SELECT od.id AS detail_id, od.order_id,
                    GREATEST(0, od.unit_price * (od.qty - COALESCE(cancelled.qty, 0)))::bigint AS amount
                FROM order_details od
                LEFT JOIN (
                    SELECT order_detail_id, SUM(qty)::integer AS qty
                    FROM order_item_cancellations GROUP BY order_detail_id
                ) cancelled ON cancelled.order_detail_id = od.id
            ), detail_ranges AS (
                SELECT detail_id, order_id, amount,
                    COALESCE(SUM(amount) OVER (PARTITION BY order_id ORDER BY detail_id ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING), 0) AS line_start,
                    SUM(amount) OVER (PARTITION BY order_id ORDER BY detail_id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS line_end
                FROM detail_amounts
            ), payment_ranges AS (
                SELECT id AS payment_id, order_id, amount::bigint,
                    COALESCE(SUM(amount) OVER (PARTITION BY order_id ORDER BY id ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING), 0) AS payment_start,
                    SUM(amount) OVER (PARTITION BY order_id ORDER BY id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS payment_end
                FROM payments WHERE status IN ('pending', 'verified', 'refunded') AND amount > 0
            )
            INSERT INTO payment_order_details (payment_id, order_detail_id, amount, created_at, updated_at)
            SELECT p.payment_id, d.detail_id,
                (LEAST(p.payment_end, d.line_end) - GREATEST(p.payment_start, d.line_start))::bigint,
                CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM payment_ranges p JOIN detail_ranges d ON d.order_id = p.order_id
            WHERE LEAST(p.payment_end, d.line_end) > GREATEST(p.payment_start, d.line_start)
        SQL);

        DB::statement('DROP INDEX IF EXISTS payments_one_verified_per_order');
    }

    public function down(): void
    {
        $multipleVerified = DB::table('payments')->select('order_id')
            ->where('status', 'verified')->groupBy('order_id')->havingRaw('COUNT(*) > 1')->exists();
        if ($multipleVerified) {
            throw new RuntimeException('Cannot roll back table sessions/order batches: multiple verified installments exist, so the legacy one-verified-payment-per-order index cannot be restored safely.');
        }
        foreach (['table_sessions', 'table_session_order_requests', 'payment_order_details', 'order_ticket_details'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                throw new RuntimeException("Cannot roll back table sessions/order batches while [$table] contains operational history.");
            }
        }
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS payments_one_verified_per_order ON payments(order_id) WHERE status = \'verified\'');
        Schema::dropIfExists('payment_order_details');
        Schema::dropIfExists('order_ticket_details');
        DB::statement('DROP INDEX IF EXISTS order_tickets_order_station_batch_unique');
        Schema::table('order_tickets', function (Blueprint $table) {
            $table->dropColumn('batch_number');
            $table->unique(['order_id', 'station']);
        });
        Schema::dropIfExists('table_session_order_requests');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_session_id');
        });
        Schema::dropIfExists('table_session_tokens');
        Schema::dropIfExists('table_sessions');
    }
};
