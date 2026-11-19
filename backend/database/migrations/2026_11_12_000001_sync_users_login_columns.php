<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                // Nullable supports safely upgrading an existing users table.
                $table->string('username', 50)->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestampTz('last_login_at')->nullable();
            });
        }

        if (! $this->hasUniqueUsernameIndex()) {
            DB::statement('CREATE UNIQUE INDEX users_username_unique ON users (username)');
        }

        // Existing deployments used chk_users_role (including the legacy "produksi" role).
        // This migration aligns the users table with the current distinct station roles.
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_users_role');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('owner','manager','kasir','barista_kitchen','barista','kitchen'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('owner','manager','kasir','produksi'))");

        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('username'));
        }
        if (Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('last_login_at'));
        }
    }

    private function hasUniqueUsernameIndex(): bool
    {
        $indexes = DB::select("SELECT indexdef FROM pg_indexes WHERE schemaname = current_schema() AND tablename = 'users'");

        foreach ($indexes as $index) {
            if (str_contains(strtolower($index->indexdef), 'unique') && str_contains(strtolower($index->indexdef), '(username)')) {
                return true;
            }
        }

        return false;
    }
};
