<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Exceptions\DomainError;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\User;
use App\Support\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Pembuatan dan pembatalan pesanan. Harga SELALU diambil dari database, tidak pernah dari klien. */
class OrderService
{
    public function __construct(
        private readonly MenuAvailabilityService $availability,
        private readonly TicketService $tickets,
        private readonly StatusLogger $log,
        private readonly AuditService $audit,
    ) {}

    /**
     * Pratinjau keranjang: harga dan masalah per item (tidak menyimpan apa pun).
     *
     * @param  list<array{menu_id:int, qty:int}>  $items
     * @return array{items: list<array>, total: int, issues: list<array>}
     */
    public function preview(array $items): array
    {
        $merged = $this->merge($items);
        $menus = $this->availability->load(Menu::query()->whereIn('id', array_keys($merged))->get())->keyBy('id');
        $lines = [];
        $issues = [];
        $total = 0;
        foreach ($merged as $menuId => $qty) {
            $menu = $menus->get($menuId);
            if (! $menu) {
                $issues[] = ['menu_id' => $menuId, 'code' => 'MENU_UNAVAILABLE', 'message' => 'Menu tidak ditemukan.'];

                continue;
            }
            $state = $this->availability->evaluate($menu);
            if (! $state['orderable']) {
                $issues[] = ['menu_id' => $menuId, 'code' => 'MENU_UNAVAILABLE', 'message' => "{$menu->name}: {$state['reason']}"];
            }
            $subtotal = $menu->price * $qty;
            $total += $subtotal;
            $lines[] = ['menu_id' => $menuId, 'name' => $menu->name, 'qty' => $qty, 'unit_price' => $menu->price, 'subtotal' => $subtotal];
        }

        return ['items' => $lines, 'total' => $total, 'issues' => $issues];
    }

    /**
     * @param  array{table_number?:string, payment_method:string, service_type?:string, order_source?:string, customer_name?:string, items:list<array{menu_id:int, qty:int}>, client_request_id?:?string}  $data
     */
    public function create(array $data, ?Table $tokenTable = null): Order
    {
        return DB::transaction(function () use ($data, $tokenTable) {
            if (! empty($data['client_request_id'])) {
                $existing = Order::query()->where('client_request_id', $data['client_request_id'])->first();
                if ($existing) {
                    return $existing; // permintaan ulang yang sama: kembalikan pesanan yang sama
                }
            }
            $serviceType = $data['service_type'] ?? 'dine_in';
            $table = null;
            if (! empty($data['table_number'])) {
                $table = Table::query()->where('table_number', (string) $data['table_number'])->where('status', 'active')->first();
                if (! $table) {
                    throw DomainError::tableInvalid();
                }
                if ($tokenTable && $tokenTable->id !== $table->id) {
                    throw DomainError::tableInvalid('Nomor meja tidak sesuai dengan QR yang dipindai.');
                }
            }

            $merged = $this->merge($data['items']);
            $menus = $this->availability->load(Menu::query()->whereIn('id', array_keys($merged))->get())->keyBy('id');
            $total = 0;
            $rows = [];
            foreach ($merged as $menuId => $qty) {
                $menu = $menus->get($menuId);
                $state = $menu ? $this->availability->evaluate($menu) : ['orderable' => false, 'reason' => 'tidak ditemukan'];
                if (! $menu || ! $state['orderable']) {
                    throw DomainError::menuUnavailable($menu?->name ?? "#$menuId", $state['reason']);
                }
                $subtotal = $menu->price * $qty;
                $total += $subtotal;
                $rows[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'qty' => $qty,
                    'unit_price' => $menu->price,
                    'subtotal' => $subtotal,
                    'station' => $menu->station->value,
                    'item_type' => $menu->station->value === 'barista' ? 'drink' : 'food',
                ];
            }

            $order = Order::create([
                'public_token' => (string) Str::ulid(),
                'order_number' => NumberGenerator::next('EX', 'orders', 'order_number'),
                'client_request_id' => $data['client_request_id'] ?? null,
                'table_id' => $table?->id,
                'service_type' => $data['service_type'] ?? 'dine_in',
                'order_source' => $data['order_source'] ?? 'qr_customer',
                'customer_name' => $data['customer_name'] ?? null,
                'status' => OrderStatus::MenungguPembayaran->value,
                'ordered_at' => now(),
                'total' => $total,
            ]);
            $order->details()->createMany($rows);
            $order->payments()->create(['method' => $data['payment_method'], 'status' => PaymentStatus::Pending->value, 'amount' => $total]);
            if ($data['payment_method'] === 'tunai') {
                $this->tickets->createForOrder($order);
            }
            $this->log->order($order, null, OrderStatus::MenungguPembayaran->value, null, 'Pesanan dibuat pelanggan');

            return $order;
        });
    }

    /** Pelanggan hanya boleh membatalkan selama menunggu pembayaran. */
    public function cancelByCustomer(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status !== OrderStatus::MenungguPembayaran) {
                throw DomainError::orderNotCancellable();
            }
            $order->payments()->where('status', PaymentStatus::Pending->value)->update(['status' => PaymentStatus::Cancelled->value]);
            $this->markCancelled($order, 'Dibatalkan pelanggan', null);

            return $order;
        });
    }

    /**
     * Pembatalan oleh Owner (pesanan sudah dibayar/diproses). Tiket terbuka dibatalkan.
     * Persetujuan refund merupakan alur terpisah dan belum tersedia.
     */
    public function cancelByAdmin(Order $order, string $reason, User $user): Order
    {
        return DB::transaction(function () use ($order, $reason, $user) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! $order->status->canTransitionTo(OrderStatus::Dibatalkan) || $order->status === OrderStatus::MenungguPembayaran) {
                throw DomainError::orderNotCancellable();
            }
            foreach ($order->tickets()->orderBy('id')->lockForUpdate()->get() as $ticket) {
                if (! $ticket->status->isOpen()) {
                    continue;
                }
                $from = $ticket->status->value;
                $ticket->update(['status' => TicketStatus::Dibatalkan->value, 'cancelled_at' => now()]);
                $this->log->ticket($order, $ticket, $from, TicketStatus::Dibatalkan->value, $user, $reason);
            }
            $this->markCancelled($order, $reason, $user);
            $this->audit->record($user, 'order.cancelled', 'order', $order->id, ['reason' => $reason, 'refund_approved' => false]);

            return $order;
        });
    }

    /** Helper pembatalan eksplisit pelanggan/Owner; penolakan payment tidak memanggil method ini. */
    public function markCancelled(Order $order, string $reason, ?User $user): void
    {
        $from = $order->status->value;
        $order->update(['status' => OrderStatus::Dibatalkan->value, 'cancelled_at' => now(), 'cancel_reason' => $reason, 'cancelled_by' => $user?->id]);
        $this->log->order($order, $from, OrderStatus::Dibatalkan->value, $user, $reason);
    }

    /**
     * Menggabungkan baris menu ganda dan memvalidasi batas keranjang.
     *
     * @param  list<array{menu_id:int, qty:int}>  $items
     * @return array<int, int> menu_id => qty
     */
    private function merge(array $items): array
    {
        $merged = [];
        foreach ($items as $item) {
            $merged[(int) $item['menu_id']] = ($merged[(int) $item['menu_id']] ?? 0) + (int) $item['qty'];
        }
        $max = (int) config('cafe.cart.max_qty', 99);
        foreach ($merged as $menuId => $qty) {
            $merged[$menuId] = min($qty, $max);
        }

        return $merged;
    }
}
