<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tables')) {
            return;
        }

        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number', 10)->unique();
            $table->string('qr_token', 40)->unique();
            $table->unsignedSmallInteger('capacity')->default(4);
            $table->string('status', 15)->default('active');
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE tables ADD CONSTRAINT tables_status_check CHECK (status IN ('active','inactive','available','occupied','reserved'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
