<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return; // Tests use an isolated database and do not alter production grants.
        }

        $canSetPostgresDefaults = (int) DB::selectOne("SELECT CASE WHEN pg_has_role(current_user, 'postgres', 'MEMBER') OR (SELECT rolsuper FROM pg_roles WHERE rolname = current_user) THEN 1 ELSE 0 END AS allowed")->allowed === 1;
        if (! $canSetPostgresDefaults) {
            throw new \RuntimeException('Migration privileges harus dijalankan sementara oleh postgres agar default privileges runtime dapat diatur.');
        }

        DB::statement('GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO cafe_exito_user');
        DB::statement('GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA public TO cafe_exito_user');
        DB::statement('ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO cafe_exito_user');
        DB::statement('ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO cafe_exito_user');
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback privileges ditolak agar hak akses runtime tidak dicabut tanpa audit.');
    }
};
