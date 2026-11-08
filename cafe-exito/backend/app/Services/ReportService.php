<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Laporan penjualan. Definisi (ASUMSI, perlu konfirmasi):
 *  - Penjualan = pembayaran berstatus verified; pesanan dibatalkan/refund tidak dihitung.
 *  - Tanggal/jam dikelompokkan menurut zona waktu aplikasi (Asia/Jakarta), berdasarkan paid_at.
 *  - Periode: daily = hari ini; weekly = 7 hari terakhir; monthly = 30 hari terakhir (jendela bergulir).
 *  - Tanpa HPP/laba bersih/pengeluaran/EDC (K-B3, K-B8 PENDING).
 */
class ReportService
{
    /** @return array{0: Carbon, 1: Carbon} */
    public function range(string $period = 'daily', ?string $from = null, ?string $to = null): array
    {
        $tz = config('app.timezone');
        if ($from) {
            $start = Carbon::parse($from, $tz)->startOfDay();
            $end = $to ? Carbon::parse($to, $tz)->endOfDay() : $start->copy()->endOfDay();

            return [$start, $end];
        }
        $end = now()->endOfDay();
        $start = match ($period) {
            'weekly' => now()->subDays(6)->startOfDay(),
            'monthly' => now()->subDays(29)->startOfDay(),
            default => now()->startOfDay(),
        };

        return [$start, $end];
    }

    public function sales(string $period = 'daily', ?string $from = null, ?string $to = null, ?string $granularity = null): array
    {
        [$start, $end] = $this->range($period, $from, $to);
        $tz = config('app.timezone');
        $hourly = ($granularity ?? ($period === 'daily' && ! $from ? 'hour' : 'day')) === 'hour';

        $totals = $this->payments($start, $end)->selectRaw('COUNT(*) AS orders, COALESCE(SUM(payments.amount), 0) AS revenue')->first();
        $revenue = (int) $totals->revenue;
        $orders = (int) $totals->orders;
        $items = (int) $this->details($start, $end)->sum('order_details.qty');

        $byMethod = [];
        foreach ($this->payments($start, $end)->selectRaw('payments.method AS method, COUNT(*) AS orders, SUM(payments.amount) AS amount')->groupBy('payments.method')->get()->keyBy('method') as $row) {
            $byMethod[$row->method] = $row;
        }
        $methods = [];
        foreach (['qris', 'tunai'] as $m) {
            $row = $byMethod[$m] ?? null;
            $amount = (int) ($row->amount ?? 0);
            $methods[] = ['method' => $m, 'orders' => (int) ($row->orders ?? 0), 'amount' => $amount, 'percent' => $this->percent($amount, $revenue)];
        }

        $categories = $this->details($start, $end)
            ->join('menus', 'menus.id', '=', 'order_details.menu_id')
            ->join('menu_categories', 'menu_categories.id', '=', 'menus.category_id')
            ->selectRaw('menu_categories.name AS category, SUM(order_details.qty) AS qty, SUM(order_details.subtotal) AS amount')
            ->groupBy('menu_categories.name')->orderByDesc('amount')->get()
            ->map(fn ($r) => ['category' => $r->category, 'qty' => (int) $r->qty, 'amount' => (int) $r->amount, 'percent' => $this->percent((int) $r->amount, $revenue)])->all();

        $top = $this->details($start, $end)
            ->selectRaw('order_details.menu_name AS name, SUM(order_details.qty) AS qty, SUM(order_details.subtotal) AS amount')
            ->groupBy('order_details.menu_name')->orderByDesc('qty')->limit(5)->get()
            ->map(fn ($r) => ['name' => $r->name, 'qty' => (int) $r->qty, 'amount' => (int) $r->amount])->all();

        $cashiers = $this->payments($start, $end)->leftJoin('users', 'users.id', '=', 'payments.verified_by')
            ->selectRaw("COALESCE(users.name, '-') AS cashier, COUNT(*) AS orders, SUM(payments.amount) AS amount")
            ->groupBy('users.name')->orderByDesc('amount')->get()
            ->map(fn ($r) => ['cashier' => $r->cashier, 'orders' => (int) $r->orders, 'amount' => (int) $r->amount])->all();

        $avg = DB::table('order_tickets')
            ->join('orders', 'orders.id', '=', 'order_tickets.order_id')
            ->join('payments', function ($j) {
                $j->on('payments.order_id', '=', 'orders.id')->where('payments.status', '=', 'verified');
            })
            ->whereBetween('payments.paid_at', [$start, $end])->whereNotNull('order_tickets.ready_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (order_tickets.ready_at - orders.ordered_at))) AS seconds')->value('seconds');

        return [
            'period' => $period,
            'from' => $start->toIso8601String(),
            'to' => $end->toIso8601String(),
            'totals' => ['orders' => $orders, 'revenue' => $revenue, 'average_order' => $orders ? (int) round($revenue / $orders) : 0, 'items' => $items],
            'by_method' => $methods,
            'by_category' => $categories,
            'series' => $this->series($start, $end, $hourly, $tz),
            'top_menus' => $top,
            'cashier_recap' => $cashiers,
            'avg_service_seconds' => $avg !== null ? (int) round((float) $avg) : null,
        ];
    }

    /** Pemakaian bahan dari pesanan (laporan Stok & Mutasi). @return list<array{ingredient:string, unit:string, used:float}> */
    public function stockUsage(?Carbon $from = null, ?Carbon $to = null): array
    {
        return DB::table('stock_movements')
            ->join('ingredients', 'ingredients.id', '=', 'stock_movements.ingredient_id')
            ->where('stock_movements.type', 'pemakaian_pesanan')
            ->when($from, fn ($q) => $q->where('stock_movements.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('stock_movements.created_at', '<=', $to))
            ->selectRaw('ingredients.name AS ingredient, ingredients.unit AS unit, SUM(-stock_movements.quantity_change) AS used')
            ->groupBy('ingredients.id', 'ingredients.name', 'ingredients.unit')->orderByDesc('used')->get()
            ->map(fn ($r) => ['ingredient' => $r->ingredient, 'unit' => $r->unit, 'used' => (float) $r->used])->all();
    }

    private function payments(Carbon $start, Carbon $end): Builder
    {
        return DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('payments.status', 'verified')
            ->whereBetween('payments.paid_at', [$start, $end]);
    }

    private function details(Carbon $start, Carbon $end): Builder
    {
        return DB::table('order_details')
            ->join('payments', function ($j) {
                $j->on('payments.order_id', '=', 'order_details.order_id')->where('payments.status', '=', 'verified');
            })
            ->whereBetween('payments.paid_at', [$start, $end]);
    }

    /** @return list<array{label:string, amount:int, orders:int}> */
    private function series(Carbon $start, Carbon $end, bool $hourly, string $tz): array
    {
        $fmt = $hourly ? 'HH24' : 'YYYY-MM-DD';
        $rows = $this->payments($start, $end)
            ->selectRaw("to_char(payments.paid_at AT TIME ZONE ?, '$fmt') AS bucket, SUM(payments.amount) AS amount, COUNT(*) AS orders", [$tz])
            ->groupBy('bucket')->get()->keyBy('bucket');

        $out = [];
        if ($hourly) {
            for ($h = 8; $h <= 22; $h++) {
                $key = sprintf('%02d', $h);
                $out[] = ['label' => $key.':00', 'amount' => (int) ($rows[$key]->amount ?? 0), 'orders' => (int) ($rows[$key]->orders ?? 0)];
            }

            return $out;
        }
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $out[] = ['label' => $d->format('d/m'), 'amount' => (int) ($rows[$key]->amount ?? 0), 'orders' => (int) ($rows[$key]->orders ?? 0)];
        }

        return $out;
    }

    private function percent(int $part, int $total): float
    {
        return $total > 0 ? round($part / $total * 100, 1) : 0.0;
    }
}
