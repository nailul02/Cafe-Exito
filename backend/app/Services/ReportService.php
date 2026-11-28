<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Laporan penjualan hanya memasukkan pesanan yang lunas; refund terealisasi ditampilkan terpisah. */
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

        $paid = $this->paidOrders($start, $end);
        $totals = (clone $paid)->leftJoinSub($this->manualRefundsByOrder(), 'manual_refunds', 'manual_refunds.order_id', '=', 'orders.id')
            ->selectRaw('COUNT(*) AS orders, COALESCE(SUM(GREATEST(orders.total - COALESCE(manual_refunds.amount,0),0)),0) AS revenue')->first();
        $revenue = (int) $totals->revenue;
        $orders = (int) $totals->orders;
        $items = (int) $this->details($start, $end)->sum('qty');

        $byMethod = [];
        foreach ($this->payments($start, $end)->selectRaw('payments.method AS method, COUNT(DISTINCT orders.id) AS orders, SUM(payments.amount - COALESCE(refund_totals.amount,0)) AS amount')
            ->leftJoinSub($this->reportableRefundsByPayment(), 'refund_totals', 'refund_totals.payment_id', '=', 'payments.id')
            ->groupBy('payments.method')->get()->keyBy('method') as $row) {
            $byMethod[$row->method] = $row;
        }
        $methods = [];
        foreach (['qris', 'tunai'] as $m) {
            $row = $byMethod[$m] ?? null;
            $amount = (int) ($row->amount ?? 0);
            $methods[] = ['method' => $m, 'orders' => (int) ($row->orders ?? 0), 'amount' => $amount, 'percent' => $this->percent($amount, $revenue)];
        }

        $categories = $this->details($start, $end)
            ->join('menus', 'menus.id', '=', 'report_details.menu_id')
            ->join('menu_categories', 'menu_categories.id', '=', 'menus.category_id')
            ->selectRaw('menu_categories.name AS category, SUM(report_details.qty) AS qty, SUM(report_details.amount) AS amount')
            ->groupBy('menu_categories.name')->orderByDesc('amount')->get()
            ->map(fn ($r) => ['category' => $r->category, 'qty' => (int) $r->qty, 'amount' => (int) $r->amount, 'percent' => $this->percent((int) $r->amount, $revenue)])->all();

        $top = $this->details($start, $end)
            ->selectRaw('report_details.menu_name AS name, SUM(report_details.qty) AS qty, SUM(report_details.amount) AS amount')
            ->groupBy('report_details.menu_name')->orderByDesc('qty')->limit(5)->get()
            ->map(fn ($r) => ['name' => $r->name, 'qty' => (int) $r->qty, 'amount' => (int) $r->amount])->all();

        $cashiers = $this->payments($start, $end)->leftJoin('users', 'users.id', '=', 'payments.verified_by')
            ->leftJoinSub($this->reportableRefundsByPayment(), 'refund_totals', 'refund_totals.payment_id', '=', 'payments.id')
            ->selectRaw("COALESCE(users.name, '-') AS cashier, COUNT(DISTINCT orders.id) AS orders, SUM(payments.amount - COALESCE(refund_totals.amount,0)) AS amount")
            ->groupBy('users.id', 'users.name')->orderByDesc('amount')->get()
            ->map(fn ($r) => ['cashier' => $r->cashier, 'orders' => (int) $r->orders, 'amount' => (int) $r->amount])->all();

        $avg = DB::table('order_tickets')
            ->join('orders', 'orders.id', '=', 'order_tickets.order_id')
            ->whereIn('orders.id', $this->paidOrders($start, $end)->select('orders.id'))
            ->whereNotNull('order_tickets.ready_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (order_tickets.ready_at - orders.ordered_at))) AS seconds')->value('seconds');

        return [
            'period' => $period,
            'from' => $start->toIso8601String(),
            'to' => $end->toIso8601String(),
            'totals' => ['orders' => $orders, 'revenue' => $revenue, 'average_order' => $orders ? (int) round($revenue / $orders) : 0, 'items' => $items,
                'refunds_approved' => (int) DB::table('refund_requests')->whereIn('status', ['approved', 'completed'])->whereBetween('decided_at', [$start, $end])->sum('amount'),
                'refunds_completed' => (int) DB::table('refund_requests')->join('refund_executions', 'refund_executions.refund_request_id', '=', 'refund_requests.id')
                    ->where('refund_requests.status', 'completed')->whereBetween('refund_executions.executed_at', [$start, $end])->sum('refund_executions.amount')],
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

    private function paidOrders(Carbon $start, Carbon $end): Builder
    {
        $verified = DB::table('payments')->selectRaw('order_id, SUM(amount) AS amount')->whereIn('status', ['verified', 'refunded'])->groupBy('order_id');
        return DB::table('orders')->leftJoinSub($verified, 'verified_payments', 'verified_payments.order_id', '=', 'orders.id')
            ->where('orders.status', '<>', 'dibatalkan')->whereNotNull('orders.paid_at')
            ->whereBetween('orders.paid_at', [$start, $end])
            ->whereRaw('COALESCE(verified_payments.amount,0) >= orders.total');
    }

    private function reportableRefundsByPayment(): Builder
    {
        // Item cancellations reduce recognized sales; manual refunds reduce sales only after execution.
        return DB::table('refund_requests')->leftJoin('refund_executions', 'refund_executions.refund_request_id', '=', 'refund_requests.id')
            ->selectRaw('refund_requests.payment_id, SUM(CASE WHEN refund_requests.source = \'manual\' THEN refund_executions.amount ELSE refund_requests.amount END) AS amount')
            ->where(function ($q) { $q->where('refund_requests.source', '<>', 'manual')->orWhereNotNull('refund_executions.id'); })
            ->groupBy('refund_requests.payment_id');
    }

    private function manualRefundsByOrder(): Builder
    {
        return DB::table('refund_requests')->join('refund_executions', 'refund_executions.refund_request_id', '=', 'refund_requests.id')
            ->selectRaw('refund_requests.order_id, SUM(refund_executions.amount) AS amount')
            ->where('refund_requests.status', 'completed')->where('refund_requests.source', 'manual')->groupBy('refund_requests.order_id');
    }

    private function payments(Carbon $start, Carbon $end): Builder
    {
        return DB::table('payments')->join('orders', 'orders.id', '=', 'payments.order_id')
            ->whereIn('orders.id', $this->paidOrders($start, $end)->select('orders.id'))
            ->whereIn('payments.status', ['verified', 'refunded'])->whereNotNull('payments.paid_at')
            ->whereBetween('orders.paid_at', [$start, $end]);
    }

    private function details(Carbon $start, Carbon $end): Builder
    {
        $cancelled = DB::table('order_item_cancellations')->selectRaw('order_detail_id, SUM(qty) AS qty')->groupBy('order_detail_id');
        $lineAmount = 'GREATEST(order_details.qty - COALESCE(cancelled.qty,0),0) * order_details.unit_price';
        $lines = DB::table('order_details')->leftJoinSub($cancelled, 'cancelled', 'cancelled.order_detail_id', '=', 'order_details.id')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoinSub($this->manualRefundsByOrder(), 'manual_refunds', 'manual_refunds.order_id', '=', 'orders.id')
            ->whereIn('orders.id', $this->paidOrders($start, $end)->select('orders.id'))
            ->selectRaw('order_details.id AS detail_id, order_details.order_id, order_details.menu_id, order_details.menu_name')
            ->selectRaw('GREATEST(order_details.qty - COALESCE(cancelled.qty,0),0) AS qty')
            ->selectRaw("$lineAmount AS gross_amount, LEAST(COALESCE(manual_refunds.amount,0), GREATEST(orders.total,0)) AS manual_refund, GREATEST(orders.total,0) AS order_total")
            ->selectRaw("SUM($lineAmount) OVER (PARTITION BY orders.id) AS items_total")
            ->selectRaw("SUM($lineAmount) OVER (PARTITION BY orders.id ORDER BY order_details.id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS cumulative_amount");

        $allocation = 'FLOOR(manual_refund::numeric * cumulative_amount / NULLIF(items_total,0)) - FLOOR(manual_refund::numeric * (cumulative_amount - gross_amount) / NULLIF(items_total,0))';

        $allocated = DB::query()->fromSub($lines, 'line_values')
            ->select(['detail_id', 'order_id', 'menu_id', 'menu_name', 'qty'])
            ->selectRaw("GREATEST(gross_amount - CASE WHEN items_total > 0 THEN $allocation ELSE 0 END,0) AS amount");

        return DB::query()->fromSub($allocated, 'report_details');
    }

    /** @return list<array{label:string, amount:int, orders:int}> */
    private function series(Carbon $start, Carbon $end, bool $hourly, string $tz): array
    {
        $fmt = $hourly ? 'HH24' : 'YYYY-MM-DD';
        $rows = $this->paidOrders($start, $end)
            ->leftJoinSub($this->manualRefundsByOrder(), 'manual_refunds', 'manual_refunds.order_id', '=', 'orders.id')
            ->selectRaw("to_char(orders.paid_at AT TIME ZONE ?, '$fmt') AS bucket, SUM(GREATEST(orders.total - COALESCE(manual_refunds.amount,0),0)) AS amount, COUNT(orders.id) AS orders", [$tz])
            ->groupBy('bucket')->get()->keyBy('bucket');

        $out = [];
        if ($hourly) {
            for ($h = 0; $h <= 23; $h++) {
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
