<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // The canonical test/clean schema is already compatible; this is a no-op there.
        if (Schema::hasTable('menu_categories') && Schema::hasTable('recipes')
            && Schema::hasTable('order_details') && Schema::hasColumn('orders', 'public_token')) {
            return;
        }
        if (! Schema::hasTable('users') && ! Schema::hasTable('categories') && ! Schema::hasTable('orders')) {
            return; // brand-new database; canonical migrations will create the target schema.
        }

        $this->renameIfOnlyLegacyExists('categories', 'menu_categories');
        $this->renameIfOnlyLegacyExists('menu_recipes', 'recipes');
        $this->renameIfOnlyLegacyExists('order_items', 'order_details');
        $this->renameIfOnlyLegacyExists('transactions', 'legacy_transactions');
        $this->renameIfOnlyLegacyExists('inventory_logs', 'legacy_inventory_logs');
        $this->renameIfOnlyLegacyExists('attendance_photos', 'legacy_attendance_photos');

        if (Schema::hasTable('legacy_inventory_logs') && DB::table('legacy_inventory_logs')->exists()) {
            throw new \RuntimeException('inventory_logs berisi data yang belum dapat dipetakan tanpa order/ticket reference. Migrasi berhenti sebelum konversi.');
        }
        if (Schema::hasTable('legacy_transactions') && DB::table('legacy_transactions')->exists()) {
            throw new \RuntimeException('transactions berisi data; konversi memerlukan pemetaan amount_paid/change_due yang dikonfirmasi sebelum migrasi.');
        }
        if (Schema::hasTable('legacy_attendance_photos') && DB::table('legacy_attendance_photos')->exists()) {
            throw new \RuntimeException('attendance_photos berisi foto lama tanpa GPS/event metadata. Migrasi berhenti agar data tidak dipetakan dengan nilai buatan.');
        }

        $this->prepareTables();
        $this->prepareCategories();
        $this->prepareMenus();
        $this->prepareIngredients();
        $this->prepareRecipes();
        $this->prepareOrders();
        $this->prepareOrderDetails();
        $this->prepareAuditLogs();
        $this->assertKnownLegacyTablesOnly();
    }

    private function renameIfOnlyLegacyExists(string $legacy, string $target): void
    {
        $old = Schema::hasTable($legacy);
        $new = Schema::hasTable($target);
        if ($old && $new) {
            throw new \RuntimeException("Kedua tabel $legacy dan $target ditemukan; migrasi ditolak agar tidak menggabungkan atau menimpa data secara spekulatif.");
        }
        if ($old) {
            Schema::rename($legacy, $target);
        }
    }

    private function prepareTables(): void
    {
        if (! Schema::hasTable('tables')) return;
        DB::statement('ALTER TABLE tables DROP CONSTRAINT IF EXISTS chk_tables_status');
        DB::statement('ALTER TABLE tables DROP CONSTRAINT IF EXISTS tables_status_check');
        DB::statement("ALTER TABLE tables ADD CONSTRAINT tables_status_check CHECK (status IN ('active','inactive','available','occupied','reserved'))");
    }

    private function prepareCategories(): void
    {
        if (! Schema::hasTable('menu_categories') || ! Schema::hasColumn('menu_categories', 'item_type_mapping')) return;
        Schema::table('menu_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_categories', 'display_group')) $table->string('display_group', 10)->nullable();
            if (! Schema::hasColumn('menu_categories', 'station')) $table->string('station', 10)->nullable();
            if (! Schema::hasColumn('menu_categories', 'sort_order')) $table->unsignedSmallInteger('sort_order')->default(0);
        });
        $unknown = DB::table('menu_categories')->whereNotIn('item_type_mapping', ['drink', 'food', 'snack'])->exists();
        if ($unknown) throw new \RuntimeException('Ada item_type_mapping kategori yang tidak dikenal.');
        DB::statement("UPDATE menu_categories SET display_group = CASE item_type_mapping WHEN 'drink' THEN 'minuman' WHEN 'food' THEN 'makanan' WHEN 'snack' THEN 'cemilan' END, station = CASE item_type_mapping WHEN 'drink' THEN 'barista' ELSE 'kitchen' END, sort_order = id");
        DB::statement('ALTER TABLE menu_categories ALTER COLUMN display_group SET NOT NULL');
        DB::statement('ALTER TABLE menu_categories ALTER COLUMN station SET NOT NULL');
        DB::statement('ALTER TABLE menu_categories DROP CONSTRAINT IF EXISTS chk_categories_item_type');
        DB::statement('ALTER TABLE menu_categories DROP CONSTRAINT IF EXISTS menu_categories_group_check');
        DB::statement('ALTER TABLE menu_categories DROP CONSTRAINT IF EXISTS menu_categories_station_check');
        DB::statement("ALTER TABLE menu_categories ADD CONSTRAINT menu_categories_group_check CHECK (display_group IN ('minuman','makanan','cemilan'))");
        DB::statement("ALTER TABLE menu_categories ADD CONSTRAINT menu_categories_station_check CHECK (station IN ('barista','kitchen'))");
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS menu_categories_name_unique ON menu_categories (name)');
    }

    private function prepareMenus(): void
    {
        if (! Schema::hasTable('menus')) return;
        if (Schema::hasColumn('menus', 'image_url') && ! Schema::hasColumn('menus', 'image_path')) {
            Schema::table('menus', fn (Blueprint $table) => $table->renameColumn('image_url', 'image_path'));
        }
        Schema::table('menus', function (Blueprint $table) {
            if (! Schema::hasColumn('menus', 'sku')) $table->string('sku', 30)->nullable();
            if (! Schema::hasColumn('menus', 'cost_price')) $table->bigInteger('cost_price')->nullable();
            if (! Schema::hasColumn('menus', 'station')) $table->string('station', 10)->nullable();
            if (! Schema::hasColumn('menus', 'image_path')) $table->string('image_path')->nullable();
            if (! Schema::hasColumn('menus', 'deleted_at')) $table->timestampTz('deleted_at')->nullable();
        });
        if (DB::table('menus')->whereRaw('price <> trunc(price)')->exists()) throw new \RuntimeException('Harga menu mengandung pecahan rupiah.');
        DB::statement("UPDATE menus SET station = menu_categories.station FROM menu_categories WHERE menus.category_id = menu_categories.id AND menus.station IS NULL");
        DB::statement('ALTER TABLE menus ALTER COLUMN price TYPE BIGINT USING ROUND(price)::BIGINT');
        DB::statement('ALTER TABLE menus ALTER COLUMN station SET NOT NULL');
        DB::statement('ALTER TABLE menus DROP CONSTRAINT IF EXISTS menus_station_check');
        DB::statement('ALTER TABLE menus ADD CONSTRAINT menus_station_check CHECK (station IN (\'barista\',\'kitchen\'))');
        DB::statement('ALTER TABLE menus DROP CONSTRAINT IF EXISTS menus_price_check');
        DB::statement('ALTER TABLE menus ADD CONSTRAINT menus_price_check CHECK (price >= 0)');
        DB::statement('ALTER TABLE menus DROP CONSTRAINT IF EXISTS menus_cost_check');
        DB::statement('ALTER TABLE menus ADD CONSTRAINT menus_cost_check CHECK (cost_price IS NULL OR cost_price >= 0)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS menus_sku_unique ON menus (sku) WHERE sku IS NOT NULL AND deleted_at IS NULL');
    }

    private function prepareIngredients(): void
    {
        if (! Schema::hasTable('ingredients')) return;
        if (Schema::hasColumn('ingredients', 'current_stock') && ! Schema::hasColumn('ingredients', 'stock')) {
            Schema::table('ingredients', fn (Blueprint $table) => $table->renameColumn('current_stock', 'stock'));
        }
        if (Schema::hasColumn('ingredients', 'minimum_stock') && ! Schema::hasColumn('ingredients', 'min_stock')) {
            Schema::table('ingredients', fn (Blueprint $table) => $table->renameColumn('minimum_stock', 'min_stock'));
        }
        Schema::table('ingredients', function (Blueprint $table) {
            if (! Schema::hasColumn('ingredients', 'category')) $table->string('category', 60)->default('Bahan Makanan');
            if (! Schema::hasColumn('ingredients', 'is_active')) $table->boolean('is_active')->default(true);
            if (! Schema::hasColumn('ingredients', 'deleted_at')) $table->timestampTz('deleted_at')->nullable();
        });
        DB::statement('ALTER TABLE ingredients ALTER COLUMN stock TYPE NUMERIC(12,3) USING stock::numeric(12,3)');
        DB::statement('ALTER TABLE ingredients ALTER COLUMN min_stock TYPE NUMERIC(12,3) USING min_stock::numeric(12,3)');
        DB::statement('ALTER TABLE ingredients DROP CONSTRAINT IF EXISTS ingredients_stock_check');
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_stock_check CHECK (stock >= 0)');
        DB::statement('ALTER TABLE ingredients DROP CONSTRAINT IF EXISTS ingredients_min_check');
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_min_check CHECK (min_stock >= 0)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS ingredients_name_unique ON ingredients (lower(name)) WHERE deleted_at IS NULL');
    }

    private function prepareRecipes(): void
    {
        if (Schema::hasTable('recipes') && Schema::hasColumn('recipes', 'quantity_required') && ! Schema::hasColumn('recipes', 'quantity')) {
            Schema::table('recipes', fn (Blueprint $table) => $table->renameColumn('quantity_required', 'quantity'));
        }
    }

    private function prepareOrders(): void
    {
        if (! Schema::hasTable('orders') || Schema::hasColumn('orders', 'public_token')) return;
        if (DB::table('orders')->whereRaw('total_amount <> trunc(total_amount)')->exists()) throw new \RuntimeException('Order total mengandung pecahan rupiah.');
        if (DB::table('orders')->whereNotIn('status', ['pending','confirmed','processing','completed','cancelled'])->exists()) throw new \RuntimeException('Status order legacy tidak dikenal.');
        Schema::table('orders', function (Blueprint $table) {
            $table->char('public_token', 26)->nullable();
            $table->uuid('client_request_id')->nullable();
            $table->string('service_type', 15)->default('dine_in');
            $table->string('order_source', 20)->default('qr_customer');
            $table->string('customer_name', 100)->nullable();
            $table->timestampTz('ordered_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->bigInteger('total')->nullable();
        });
        foreach (DB::table('orders')->orderBy('id')->pluck('id') as $id) {
            DB::table('orders')->where('id', $id)->update(['public_token' => (string) Str::ulid()]);
        }
        DB::statement('UPDATE orders SET client_request_id = idempotency_key::uuid WHERE idempotency_key IS NOT NULL');
        // The legacy CHECK permits the old English statuses only. Remove it before
        // translating values, then install the canonical CHECK after the update.
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS chk_orders_status');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
        DB::statement('UPDATE orders SET total = ROUND(total_amount)::bigint, ordered_at = COALESCE(created_at, CURRENT_TIMESTAMP), status = CASE status WHEN \'pending\' THEN \'menunggu_pembayaran\' WHEN \'confirmed\' THEN \'dibayar\' WHEN \'processing\' THEN \'diproses\' WHEN \'completed\' THEN \'selesai\' WHEN \'cancelled\' THEN \'dibatalkan\' END, cancel_reason = cancellation_reason');
        DB::statement("UPDATE orders SET completed_at = COALESCE(updated_at, ordered_at) WHERE status = 'selesai'");
        DB::statement("UPDATE orders SET cancelled_at = COALESCE(updated_at, ordered_at) WHERE status = 'dibatalkan'");
        DB::statement('ALTER TABLE orders ALTER COLUMN public_token SET NOT NULL');
        DB::statement('ALTER TABLE orders ALTER COLUMN total SET NOT NULL');
        DB::statement('ALTER TABLE orders ALTER COLUMN ordered_at SET NOT NULL');
        DB::statement('ALTER TABLE orders ALTER COLUMN table_id DROP NOT NULL');
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('menunggu_pembayaran','dibayar','diproses','selesai','dibatalkan'))");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_service_type_check CHECK (service_type IN ('dine_in','takeaway'))");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_order_source_check CHECK (order_source IN ('qr_customer','pos_cashier'))");
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_total_check CHECK (total >= 0)');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_table_id_foreign');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_table_id_foreign FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS orders_public_token_unique ON orders (public_token)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS orders_client_request_id_unique ON orders (client_request_id) WHERE client_request_id IS NOT NULL');
    }

    private function prepareOrderDetails(): void
    {
        if (! Schema::hasTable('order_details') || Schema::hasColumn('order_details', 'qty')) return;
        Schema::table('order_details', function (Blueprint $table) {
            $table->renameColumn('quantity', 'qty');
            $table->string('menu_name', 120)->nullable();
            $table->string('station', 10)->nullable();
        });
        if (DB::table('order_details')->whereRaw('unit_price <> trunc(unit_price) OR subtotal <> trunc(subtotal)')->exists()) throw new \RuntimeException('Order detail mengandung pecahan rupiah.');
        DB::statement('UPDATE order_details SET menu_name = menus.name, station = menus.station FROM menus WHERE order_details.menu_id = menus.id');
        DB::statement('ALTER TABLE order_details ALTER COLUMN unit_price TYPE BIGINT USING ROUND(unit_price)::bigint');
        DB::statement('ALTER TABLE order_details ALTER COLUMN subtotal TYPE BIGINT USING ROUND(subtotal)::bigint');
        DB::statement('ALTER TABLE order_details ALTER COLUMN qty TYPE SMALLINT USING qty::smallint');
        DB::statement('ALTER TABLE order_details ALTER COLUMN menu_name SET NOT NULL');
        DB::statement('ALTER TABLE order_details ALTER COLUMN station SET NOT NULL');
        DB::statement('ALTER TABLE order_details ADD CONSTRAINT order_details_qty_check CHECK (qty > 0)');
        DB::statement("ALTER TABLE order_details ADD CONSTRAINT order_details_station_check CHECK (station IN ('barista','kitchen'))");
    }

    private function prepareAuditLogs(): void
    {
        if (! Schema::hasTable('audit_logs') || Schema::hasColumn('audit_logs', 'subject_type')) return;
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->jsonb('changes')->nullable();
            $table->string('ip', 45)->nullable();
        });
        DB::statement("UPDATE audit_logs SET subject_type = table_name, subject_id = record_id, changes = jsonb_build_object('old', old_value, 'new', new_value), ip = ip_address");
    }

    private function assertKnownLegacyTablesOnly(): void
    {
        // Fail closed if one of the expected legacy mappings did not produce a target table.
        foreach (['menu_categories','recipes','order_details','orders','menus','ingredients','users','tables','attendances'] as $table) {
            if (! Schema::hasTable($table)) throw new \RuntimeException("Tabel target $table tidak tersedia setelah preflight.");
        }
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback rekonsiliasi ditolak untuk mencegah kehilangan atau pemetaan balik data yang ambigu.');
    }
};
