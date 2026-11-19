<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyDataIntegrityCommand extends Command
{
    protected $signature = 'app:verify-data-integrity';
    protected $description = 'Memverifikasi keutuhan seluruh data bisnis dan struktur tabel pasca-rekonsiliasi';

    public function handle(): int
    {
        $this->info('=== MEMULAI VERIFIKASI KEUTUHAN DATA & SKEMA ===');
        $passed = true;

        // 1. Kategori
        $catCount = Schema::hasTable('menu_categories') ? DB::table('menu_categories')->count() : 0;
        if ($catCount === 3) {
            $this->info("✓ Kategori: $catCount baris (Lolos)");
        } else {
            $this->error("✗ Kategori: $catCount baris (Harus 3)");
            $passed = false;
        }

        // 2. Menu
        $menuCount = Schema::hasTable('menus') ? DB::table('menus')->count() : 0;
        if ($menuCount === 10) {
            $this->info("✓ Menu: $menuCount baris (Lolos)");
        } else {
            $this->error("✗ Menu: $menuCount baris (Harus 10)");
            $passed = false;
        }

        // 3. Meja
        $tableCount = Schema::hasTable('tables') ? DB::table('tables')->count() : 0;
        if ($tableCount === 5) {
            $this->info("✓ Meja: $tableCount baris (Lolos)");
        } else {
            $this->error("✗ Meja: $tableCount baris (Harus 5)");
            $passed = false;
        }

        // 4. Orders
        $orderCount = Schema::hasTable('orders') ? DB::table('orders')->count() : 0;
        if ($orderCount === 14) {
            $this->info("✓ Orders: $orderCount baris (Lolos)");
        } else {
            $this->error("✗ Orders: $orderCount baris (Harus 14)");
            $passed = false;
        }

        // 5. Order Details
        $detailCount = Schema::hasTable('order_details') ? DB::table('order_details')->count() : 0;
        if ($detailCount === 15) {
            $this->info("✓ Order Details: $detailCount baris (Lolos)");
        } else {
            $this->error("✗ Order Details: $detailCount baris (Harus 15)");
            $passed = false;
        }

        // 6. Daily Counters
        $counterCount = Schema::hasTable('daily_counters') ? DB::table('daily_counters')->count() : 0;
        if ($counterCount === 1) {
            $this->info("✓ Daily Counters: $counterCount baris (Lolos)");
        } else {
            $this->error("✗ Daily Counters: $counterCount baris (Harus 1)");
            $passed = false;
        }

        // 7. Tabel Baru yang Wajib Ada
        $newTables = [
            'user_permissions', 'app_settings', 'order_tickets', 'payments',
            'order_status_histories', 'suppliers', 'purchases', 'purchase_details',
            'goods_receipts', 'goods_receipt_details', 'stock_movements',
            'attendances', 'attendance_events'
        ];
        foreach ($newTables as $tbl) {
            if (Schema::hasTable($tbl)) {
                $this->info("✓ Tabel [$tbl] tersedia");
            } else {
                $this->error("✗ Tabel [$tbl] TIDAK ditemukan!");
                $passed = false;
            }
        }

        // 8. Tipe data kolom price pada menus
        $priceType = DB::selectOne("
            SELECT data_type FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = 'menus' AND column_name = 'price'
        ")?->data_type;
        if ($priceType === 'bigint') {
            $this->info("✓ Tipe data menus.price adalah bigint (Lolos)");
        } else {
            $this->warn("! Tipe data menus.price adalah $priceType");
        }

        if ($passed) {
            $this->info("\n=== SELURUH DATA & SKEMA BERHASIL DIVERIFIKASI DENGAN NILAI SEMPURNA 100%! ===");
            return Command::SUCCESS;
        } else {
            $this->error("\n=== TERDAPAT KETIDAKSESUAIAN DALAM VERIFIKASI ===");
            return Command::FAILURE;
        }
    }
}
