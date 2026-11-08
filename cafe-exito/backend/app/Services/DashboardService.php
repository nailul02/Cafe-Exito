<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PurchaseStatus;
use App\Enums\Station;
use App\Enums\TicketStatus;
use App\Http\Resources\IngredientResource;
use App\Http\Resources\OrderResource;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderTicket;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Support\Collection;

/** Ringkasan dashboard per role. Hanya membaca; tidak ada logika bisnis tulis di sini. */
class DashboardService
{
    public function __construct(private readonly ReportService $reports, private readonly MenuAvailabilityService $availability) {}

    public function owner(): array
    {
        $today = $this->reports->sales('daily');
        $y = now()->subDay()->toDateString();
        $yesterday = $this->reports->sales('daily', $y, $y);
        $yRevenue = $yesterday['totals']['revenue'];
        $low = $this->lowStock();

        return [
            'today' => [
                'revenue' => $today['totals']['revenue'],
                'orders' => $today['totals']['orders'],
                'average_order' => $today['totals']['average_order'],
                'vs_yesterday_percent' => $yRevenue > 0 ? round(($today['totals']['revenue'] - $yRevenue) / $yRevenue * 100, 1) : null,
            ],
            'series' => $today['series'],
            'top_menus' => $today['top_menus'],
            'by_method' => $today['by_method'],
            'low_stock' => IngredientResource::collection($low)->resolve(request()),
            'critical_count' => $low->filter(fn ($i) => in_array($i->level()->value, ['kritis', 'habis'], true))->count(),
            'pending_purchases' => $this->pendingPurchases(),
        ];
    }

    public function manager(): array
    {
        $sales = $this->reports->sales('daily');
        $startOfDay = now()->startOfDay();
        $active = Order::query()->where('ordered_at', '>=', $startOfDay)->whereIn('status', [OrderStatus::Dibayar->value, OrderStatus::Diproses->value])->count();
        $recent = Order::query()->with(['table', 'details', 'payment.verifier', 'tickets'])
            ->where('ordered_at', '>=', $startOfDay)->orderByDesc('id')->limit(8)->get();

        $unavailable = $this->availability->load(Menu::query()->get())
            ->map(fn (Menu $m) => ['menu' => $m, 'state' => $this->availability->evaluate($m)])
            ->reject(fn ($x) => $x['state']['orderable'])
            ->map(fn ($x) => ['id' => $x['menu']->id, 'name' => $x['menu']->name, 'reason' => $x['state']['reason']])->values()->all();

        return [
            'revenue_today' => $sales['totals']['revenue'],
            'active_orders' => [
                'total' => $active,
                'queued' => OrderTicket::query()->where('status', TicketStatus::Antre->value)->count(),
                'in_progress' => OrderTicket::query()->where('status', TicketStatus::Diproses->value)->count(),
            ],
            'orders' => OrderResource::collection($recent)->resolve(request()),
            'low_stock' => IngredientResource::collection($this->lowStock())->resolve(request()),
            'unavailable_menus' => $unavailable,
            'pending_receipts' => $this->pendingPurchases(),
        ];
    }

    public function cashier(): array
    {
        $start = now()->startOfDay();
        $today = Order::query()->where('ordered_at', '>=', $start);

        return [
            'new_orders' => (clone $today)->whereIn('status', [OrderStatus::MenungguPembayaran->value, OrderStatus::Dibayar->value])->count(),
            'awaiting_payment' => Order::query()->where('status', OrderStatus::MenungguPembayaran->value)->count(),
            'orders_today' => (clone $today)->count(),
            'revenue_today' => (int) Payment::query()->where('status', 'verified')->where('paid_at', '>=', $start)->sum('amount'),
        ];
    }

    public function station(Station $station): array
    {
        $tickets = OrderTicket::query()->where('station', $station->value);

        return [
            'queued' => (clone $tickets)->where('status', TicketStatus::Antre->value)->count(),
            'in_progress' => (clone $tickets)->where('status', TicketStatus::Diproses->value)->count(),
            'completed_today' => (clone $tickets)->where('status', TicketStatus::Selesai->value)->where('ready_at', '>=', now()->startOfDay())->count(),
        ];
    }

    /** @return Collection<int, Ingredient> */
    public function lowStock(): Collection
    {
        return Ingredient::query()->where('is_active', true)->orderBy('name')->get()->filter(fn (Ingredient $i) => $i->level()->isLow())->values();
    }

    private function pendingPurchases(): int
    {
        return Purchase::query()->whereIn('status', [PurchaseStatus::Dipesan->value, PurchaseStatus::DiterimaSebagian->value])->count();
    }
}
