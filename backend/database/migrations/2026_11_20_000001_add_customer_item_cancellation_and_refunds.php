<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('ready_at')->nullable();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::create('order_item_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('order_detail_id')->constrained('order_details')->restrictOnDelete();
            $table->unsignedSmallInteger('qty');
            $table->unsignedSmallInteger('qty_start');
            $table->unsignedSmallInteger('qty_end');
            $table->bigInteger('unit_price');
            $table->string('source', 30);
            $table->string('actor_type', 20);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 500);
            $table->string('idempotency_key', 80)->nullable();
            $table->timestampsTz();
        });
        DB::statement('CREATE UNIQUE INDEX order_item_cancellations_idempotency ON order_item_cancellations(order_id,idempotency_key)');
        DB::statement("ALTER TABLE order_item_cancellations ADD CONSTRAINT order_item_cancellations_qty_check CHECK (qty > 0)");
        DB::statement("ALTER TABLE order_item_cancellations ADD CONSTRAINT order_item_cancellations_source_check CHECK (source IN ('customer_cancellation','owner_cancellation'))");
        DB::statement('ALTER TABLE order_item_cancellations ADD CONSTRAINT order_item_cancellations_range_check CHECK (qty_start > 0 AND qty_end >= qty_start AND qty = qty_end - qty_start + 1)');
        DB::statement("ALTER TABLE order_item_cancellations ADD CONSTRAINT order_item_cancellations_actor_check CHECK (actor_type IN ('customer','owner'))");

        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->string('source', 30);
            $table->string('status', 24)->default('requested');
            $table->bigInteger('amount');
            $table->string('reason', 500);
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision_reason', 500)->nullable();
            $table->timestampTz('withdrawn_at')->nullable();
            $table->foreignId('withdrawn_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('withdraw_reason', 500)->nullable();
            $table->timestampTz('approval_revoked_at')->nullable();
            $table->foreignId('approval_revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_revoke_reason', 500)->nullable();
            $table->timestampTz('conflict_of_interest_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('decision_late_notified_at')->nullable();
            $table->timestampTz('execution_late_notified_at')->nullable();
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE refund_requests ADD CONSTRAINT refund_requests_source_check CHECK (source IN ('customer_cancellation','owner_cancellation','manual','mixed'))");
        DB::statement("ALTER TABLE refund_requests ADD CONSTRAINT refund_requests_status_check CHECK (status IN ('requested','approved','rejected','withdrawn','approval_revoked','completed','conflict_of_interest'))");
        DB::statement('ALTER TABLE refund_requests ADD CONSTRAINT refund_requests_amount_check CHECK (amount > 0)');
        DB::statement("CREATE UNIQUE INDEX refund_requests_one_requested_per_payment ON refund_requests(payment_id) WHERE status = 'requested'");
        Schema::create('refund_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_request_id')->constrained('refund_requests')->restrictOnDelete();
            $table->foreignId('cancellation_id')->unique()->constrained('order_item_cancellations')->restrictOnDelete();
            $table->unsignedSmallInteger('qty');
            $table->bigInteger('amount');
            $table->timestampsTz();
        });
        DB::statement('ALTER TABLE refund_request_items ADD CONSTRAINT refund_request_items_positive_check CHECK (qty > 0 AND amount > 0)');
        Schema::create('refund_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_request_id')->constrained('refund_requests')->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type', 20);
            $table->string('event', 32);
            $table->jsonb('details')->nullable();
            $table->timestampsTz();
        });
        Schema::create('refund_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_request_id')->unique()->constrained('refund_requests')->restrictOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('method', 20);
            $table->bigInteger('amount');
            $table->string('reference_number')->nullable();
            $table->string('proof_path')->nullable();
            $table->timestampTz('executed_at');
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE refund_executions ADD CONSTRAINT refund_executions_method_check CHECK (method IN ('cash','bank_transfer'))");
        DB::statement("ALTER TABLE refund_executions ADD CONSTRAINT refund_executions_amount_check CHECK (amount > 0)");
        DB::statement("ALTER TABLE refund_executions ADD CONSTRAINT refund_executions_transfer_check CHECK (method <> 'bank_transfer' OR (reference_number IS NOT NULL AND proof_path IS NOT NULL))");
        Schema::create('refund_conflict_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_request_id')->constrained('refund_requests')->restrictOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('handled_by', 160);
            $table->date('resolved_on');
            $table->string('outcome', 500);
            $table->string('reason', 500);
            $table->bigInteger('amount');
            $table->string('proof_path')->nullable();
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();
        });
        DB::statement('ALTER TABLE refund_conflict_notes ADD CONSTRAINT refund_conflict_notes_amount_check CHECK (amount >= 0)');
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 80);
            $table->jsonb('data');
            $table->timestampTz('read_at')->nullable();
            $table->timestampsTz();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('refund_conflict_notes');
        Schema::dropIfExists('refund_executions');
        Schema::dropIfExists('refund_events');
        Schema::dropIfExists('refund_request_items');
        Schema::dropIfExists('refund_requests');
        Schema::dropIfExists('order_item_cancellations');
        Schema::table('orders', fn (Blueprint $table) => $table->dropConstrainedForeignId('created_by'));
        Schema::table('order_details', fn (Blueprint $table) => $table->dropColumn(['started_at', 'ready_at']));
    }
};
