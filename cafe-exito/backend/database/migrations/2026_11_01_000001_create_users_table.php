<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('username', 50)->unique();   // disimpan huruf kecil oleh aplikasi
            $table->string('email', 150)->unique();     // login Figma memakai email; username tetap ada (K-T1 PENDING)
            $table->string('password');
            $table->string('role', 20);
            $table->boolean('is_active')->default(true);
            $table->timestampTz('last_login_at')->nullable();
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('owner','admin','kasir','barista','kitchen'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
