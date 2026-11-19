<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasTable('payments') || ! Schema::hasColumn('orders', 'payment_status')) {
            return;
        }

        $unknown = DB::table('orders')->whereNotIn('payment_status', ['pending'])->exists();
        if ($unknown) {
            throw new \RuntimeException('Terdapat status pembayaran lama selain pending; migrasi tidak akan menebak status payment baru.');
        }

        $unmappedMethod = DB::table('orders')->whereNull('payment_method')->orWhereNotIn('payment_method', ['cash', 'qris'])->exists();
        if ($unmappedMethod) {
            throw new \RuntimeException('Ada payment_method lama yang tidak dikenal.');
        }

        $orders = DB::table('orders')->where('payment_status', 'pending')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('payments')->whereColumn('payments.order_id', 'orders.id'))
            ->orderBy('id')->get(['id', 'payment_method', 'total', 'created_at', 'updated_at']);

        foreach ($orders as $order) {
            DB::table('payments')->insert([
                'order_id' => $order->id,
                'method' => $order->payment_method === 'cash' ? 'tunai' : 'qris',
                'status' => 'pending',
                'amount' => $order->total,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback ditolak karena payment rows berasal dari rekonsiliasi dan tidak boleh dihapus otomatis.');
    }
};
