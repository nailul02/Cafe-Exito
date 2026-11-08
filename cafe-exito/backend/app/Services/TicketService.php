<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\Station;
use App\Enums\TicketStatus;
use App\Exceptions\DomainError;
use App\Models\Order;
use App\Models\OrderTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Tiket per stasiun (Barista/Kitchen). Satu pesanan campuran menghasilkan dua tiket.
 * Stok dikurangi saat tiket DIMULAI (K: sesuai activity diagram laporan), sesuai resep item stasiun itu.
 */
class TicketService
{
    public function __construct(private readonly StockService $stock, private readonly StatusLogger $log) {}

    public function createForOrder(Order $order, ?User $user = null): void
    {
        foreach ($order->details()->get()->groupBy(fn ($d) => $d->station->value) as $station => $lines) {
            $ticket = $order->tickets()->create(['station' => $station, 'status' => TicketStatus::Antre->value]);
            $this->log->ticket($order, $ticket, null, TicketStatus::Antre->value, $user);
        }
    }

    public function start(OrderTicket $ticket, User $user): OrderTicket
    {
        return DB::transaction(function () use ($ticket, $user) {
            $ticket = OrderTicket::query()->whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            $order = Order::query()->whereKey($ticket->order_id)->lockForUpdate()->firstOrFail();
            if (! $ticket->status->canTransitionTo(TicketStatus::Diproses)) {
                throw DomainError::invalidTransition('Tiket', $ticket->status->value, TicketStatus::Diproses->value);
            }

            $this->stock->consume($ticket, $this->requirements($order, $ticket->station), $user);

            $ticket->update(['status' => TicketStatus::Diproses->value, 'started_at' => now(), 'handled_by' => $user->id]);
            $this->log->ticket($order, $ticket, TicketStatus::Antre->value, TicketStatus::Diproses->value, $user);
            if ($order->status === OrderStatus::Dibayar) {
                $order->update(['status' => OrderStatus::Diproses->value]);
                $this->log->order($order, OrderStatus::Dibayar->value, OrderStatus::Diproses->value, $user);
            }

            return $ticket;
        });
    }

    public function complete(OrderTicket $ticket, User $user): OrderTicket
    {
        return DB::transaction(function () use ($ticket, $user) {
            $ticket = OrderTicket::query()->whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            $order = Order::query()->whereKey($ticket->order_id)->lockForUpdate()->firstOrFail();
            if (! $ticket->status->canTransitionTo(TicketStatus::Selesai)) {
                throw DomainError::invalidTransition('Tiket', $ticket->status->value, TicketStatus::Selesai->value);
            }
            $ticket->update(['status' => TicketStatus::Selesai->value, 'ready_at' => now(), 'handled_by' => $ticket->handled_by ?? $user->id]);
            $this->log->ticket($order, $ticket, TicketStatus::Diproses->value, TicketStatus::Selesai->value, $user);

            $stillOpen = $order->tickets()->whereIn('status', [TicketStatus::Antre->value, TicketStatus::Diproses->value])->exists();
            if (! $stillOpen && $order->status->canTransitionTo(OrderStatus::Selesai)) {
                $from = $order->status->value;
                $order->update(['status' => OrderStatus::Selesai->value, 'completed_at' => now()]);
                $this->log->order($order, $from, OrderStatus::Selesai->value, $user);
            }

            return $ticket;
        });
    }

    /**
     * Antrean stasiun, FIFO menurut waktu pesan (sesuai sequence diagram laporan; K-... ASUMSI bila ingin waktu bayar).
     * Menampilkan tiket terbuka (apa pun harinya) + tiket yang berubah hari ini.
     */
    public function queue(Station $station, ?string $status = null, ?Carbon $updatedSince = null): Builder
    {
        $startOfDay = now()->startOfDay();

        return OrderTicket::query()
            ->select('order_tickets.*')
            ->join('orders', 'orders.id', '=', 'order_tickets.order_id')
            ->where('order_tickets.station', $station->value)
            ->where(function (Builder $q) use ($startOfDay) {
                $q->whereIn('order_tickets.status', [TicketStatus::Antre->value, TicketStatus::Diproses->value])
                    ->orWhere('order_tickets.updated_at', '>=', $startOfDay);
            })
            ->when($status && $status !== 'semua', fn (Builder $q) => $q->where('order_tickets.status', $status))
            ->when($updatedSince, fn (Builder $q) => $q->where('order_tickets.updated_at', '>', $updatedSince))
            ->orderBy('orders.ordered_at')
            ->orderBy('order_tickets.id')
            ->with(['order.table', 'order.details', 'handler']);
    }

    /**
     * Kebutuhan bahan tiket: jumlah resep × qty untuk item yang ditangani stasiun ini.
     *
     * @return array<int, float> ingredient_id => jumlah
     */
    public function requirements(Order $order, Station $station): array
    {
        $need = [];
        $details = $order->details()->where('station', $station->value)->with('menu.recipes')->get();
        foreach ($details as $detail) {
            foreach ($detail->menu->recipes as $recipe) {
                $need[$recipe->ingredient_id] = round(($need[$recipe->ingredient_id] ?? 0) + (float) $recipe->quantity * $detail->qty, 3);
            }
        }

        return $need;
    }
}
