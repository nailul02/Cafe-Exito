<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'station')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('station', 10)->nullable();
            });
        }

        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_users_role');
        DB::statement("UPDATE users SET station = 'barista' WHERE role = 'barista' AND station IS NULL");
        DB::statement("UPDATE users SET station = 'kitchen' WHERE role = 'kitchen' AND station IS NULL");
        DB::statement("UPDATE users SET role = 'barista_kitchen' WHERE role IN ('barista','kitchen')");

        $unmapped = DB::table('users')->where('role', 'barista_kitchen')->whereNull('station')->exists();
        if ($unmapped) {
            throw new \RuntimeException('Akun barista_kitchen tanpa station ditemukan; migration dihentikan tanpa menetapkan station secara tebakan.');
        }

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('owner','manager','kasir','barista_kitchen'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_station_role_check CHECK ((role = 'barista_kitchen' AND station IN ('barista','kitchen')) OR (role <> 'barista_kitchen' AND station IS NULL))");
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback ditolak: konversi role/station berisi pemetaan data dan tidak boleh dibalik otomatis.');
    }
};
