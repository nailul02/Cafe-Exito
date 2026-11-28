<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('refund_executions') || Schema::hasColumn('refund_executions', 'executed_by')) {
            return;
        }

        Schema::table('refund_executions', function (Blueprint $table) {
            $table->foreignId('executed_by')->nullable()->after('cashier_id')->constrained('users')->restrictOnDelete();
        });

        DB::table('refund_executions')->whereNull('executed_by')->update(['executed_by' => DB::raw('cashier_id')]);
        DB::statement('ALTER TABLE refund_executions ALTER COLUMN cashier_id DROP NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('refund_executions') || ! Schema::hasColumn('refund_executions', 'executed_by')) {
            return;
        }

        if (DB::table('refund_executions')->whereNull('cashier_id')->exists()) {
            throw new RuntimeException('Cannot roll back refund executor audit while an execution was recorded by a non-cashier.');
        }

        Schema::table('refund_executions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('executed_by');
        });
        DB::statement('ALTER TABLE refund_executions ALTER COLUMN cashier_id SET NOT NULL');
    }
};
