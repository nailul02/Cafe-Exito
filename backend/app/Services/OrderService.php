<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Exceptions\DomainError;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderItemCancellation;
use App\Models\Payment;
use App\Models\Table;
use App\Models\TableSession;
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
        private readonly RefundService $refunds,
        private readonly InAppNotificationService $notifications,
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
    public function create(array $data, ?Table $tokenTable = null, ?TableSession $session = null): Order
    {
        return DB::transaction(function () use ($data, $tokenTable, $session) {
            if ($session) {
                $session = TableSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
                $session->loadMissing('table');
                if ($session->status !== 'active' || $session->table->status !== 'active') {
                    throw new DomainError('TABLE_SESSION_CLOSED', 'Sesi meja sudah ditutup.', 409);
                }
                if ($tokenTable && (int) $tokenTable->id !== (int) $session->table_id) {
                    throw DomainError::tableInvalid('Sesi tidak sesuai dengan QR meja.');
                }
                $tokenTable = $session->table;
            }
            $serviceType = $data['service_type'] ?? 'dine_in';
            if ($serviceType === 'dine_in' && $tokenTable && ! empty($data['table_number'])
                && (string) $data['table_number'] !== (string) $tokenTable->table_number) {
                throw DomainError::tableInvalid('Nomor meja tidak sesuai dengan QR yang dipindai.');
            }
            if (! empty($data['client_request_id'])) {
                if ($session) {
                    $requestHash = hash('sha256', json_encode([
                        'items' => $this->merge($data['items']), 'payment_method' => $data['payment_method'],
                    ], JSON_THROW_ON_ERROR));
                    $recorded = DB::table('table_session_order_requests')->where('table_session_id', $session->id)
                        ->where('client_request_id', $data['client_request_id'])->lockForUpdate()->first();
                    if ($recorded) {
                        if (! hash_equals($recorded->payload_hash, $requestHash)) {
                            throw new DomainError('IDEMPOTENCY_KEY_REUSED', 'ID checkout sudah digunakan untuk isi keranjang berbeda.', 409);
                        }
                        return Order::query()->findOrFail($recorded->order_id);
                    }
                    $session = TableSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
                    if ($session->status !== 'active') throw new DomainError('TABLE_SESSION_CLOSED', 'Sesi meja sudah ditutup.', 409);
                }
                $existing = Order::query()->where('client_request_id', $data['client_request_id'])->first();
                if ($existing) {
                    if ($session) {
                        // Customer retries are mapped atomically to their own session
                        // above. A global request ID without that mapping is either
                        // reused from another session or signals inconsistent data.
                        throw new DomainError('IDEMPOTENCY_KEY_REUSED', 'ID checkout sudah digunakan oleh pesanan lain.', 409);
                    }
                    if ($tokenTable && (int) $existing->table_id !== (int) $tokenTable->id) {
                        throw DomainError::tableInvalid('Permintaan ini sudah terikat ke meja QR lain.');
                    }
                    return $existing; // permintaan ulang yang sama: kembalikan pesanan yang sama
                }
            }
            $table = null;
            $requestedTableNumber = $serviceType === 'dine_in'
                ? ($tokenTable?->table_number ?? ($data['table_number'] ?? null))
                : null;
            if ($serviceType === 'dine_in' && empty($requestedTableNumber)) {
                throw DomainError::tableInvalid('Pesanan dine-in wajib memiliki meja aktif.');
            }
            if ($requestedTableNumber !== null) {
                $table = Table::query()->numbered()->where('table_number', (string) $requestedTableNumber)->where('status', 'active')->first();
                if (! $table) {
                    throw DomainError::tableInvalid();
                }
                if ($tokenTable && $tokenTable->id !== $table->id) {
                    throw DomainError::tableInvalid('Nomor meja tidak sesuai dengan QR yang dipindai.');
                }
            }

            $merged = $this->merge($data['items']);
            // Keep availability stable until order and payment rows are committed.
            $menus = $this->availability->load(Menu::query()->whereIn('id', array_keys($merged))->orderBy('id')->lockForUpdate()->get())->keyBy('id');
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

            if ($session) {
                $inProgress = $session->orders()->where('status', OrderStatus::Diproses->value)->orderByDesc('id')->lockForUpdate()->first();
                if ($inProgress) {
                    $pending = $inProgress->payments()->where('status', PaymentStatus::Pending->value)->lockForUpdate()->first();
                    if ($pending && $pending->method->value !== $data['payment_method']) {
                        throw new DomainError('INSTALLMENT_METHOD_MISMATCH', 'Selesaikan atau gunakan metode pembayaran tertunda yang sama sebelum menambah pesanan.', 409);
                    }
                    if ($pending && $pending->proofs()->where('status', 'pending')->exists()) {
                        throw new DomainError('PAYMENT_PROOF_PENDING', 'Tunggu verifikasi bukti pembayaran sebelum menambah item pada pesanan ini.', 409);
                    }
                    $added = $inProgress->details()->createMany($rows);
                    $inProgress->update(['total' => (int) $inProgress->total + $total]);
                    $payment = $pending ?: $inProgress->payments()->create([
                        'method' => $data['payment_method'], 'status' => PaymentStatus::Pending->value, 'amount' => $total,
                    ]);
                    if ($pending) $payment->update(['amount' => (int) $payment->amount + $total]);
                    PaymentAllocationService::attach($payment, $added->mapWithKeys(fn (OrderDetail $detail) => [$detail->id => (int) $detail->subtotal])->all());
                    if ($data['payment_method'] === 'tunai') $this->tickets->createForDetails($inProgress, $added->all());
                    if (! empty($data['client_request_id'])) {
                        DB::table('table_session_order_requests')->insert([
                            'table_session_id' => $session->id, 'order_id' => $inProgress->id,
                            'client_request_id' => $data['client_request_id'],
                            'payload_hash' => $requestHash, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                    $this->audit->record(null, 'order.items_added_to_session_order', 'order', $inProgress->id, ['detail_ids' => $added->pluck('id')->all(), 'total_delta' => $total]);
                    return $inProgress->fresh();
                }
            }

            $order = Order::create([
                'public_token' => (string) Str::ulid(),
                'order_number' => NumberGenerator::next('EX', 'orders', 'order_number'),
                'client_request_id' => $data['client_request_id'] ?? null,
                'table_id' => $table?->id,
                'table_session_id' => $session?->id,
                'service_type' => $data['service_type'] ?? 'dine_in',
                'order_source' => $data['order_source'] ?? 'qr_customer',
                'customer_name' => $session?->customer_name ?? ($data['customer_name'] ?? null),
                'status' => OrderStatus::MenungguPembayaran->value,
                'ordered_at' => now(),
                'total' => $total,
            ]);
            $details = $order->details()->createMany($rows);
            $payment = $order->payments()->create(['method' => $data['payment_method'], 'status' => PaymentStatus::Pending->value, 'amount' => $total]);
            PaymentAllocationService::attach($payment, $details->mapWithKeys(fn (OrderDetail $detail) => [$detail->id => (int) $detail->subtotal])->all());
            if ($data['payment_method'] === 'tunai') {
                $this->tickets->createForDetails($order, $details->all());
            }
            if ($session && ! empty($data['client_request_id'])) {
                DB::table('table_session_order_requests')->insert([
                    'table_session_id' => $session->id, 'order_id' => $order->id,
                    'client_request_id' => $data['client_request_id'], 'payload_hash' => $requestHash,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $this->log->order($order, null, OrderStatus::MenungguPembayaran->value, null, 'Pesanan dibuat pelanggan');

            return $order;
        });
    }

    public function cancelItemByCustomer(Order $order, int $detailId, int $qty, string $reason, ?string $key = null): Order
    {
        return $this->cancelItem($order, $detailId, $qty, $reason, 'customer_cancellation', null, $key);
    }

    public function cancelItemByOwner(Order $order, int $detailId, int $qty, string $reason, User $owner, ?string $key = null): Order
    {
        return $this->cancelItem($order, $detailId, $qty, $reason, 'owner_cancellation', $owner, $key);
    }

    private function cancelItem(Order $order, int $detailId, int $qty, string $reason, string $source, ?User $actor, ?string $key): Order
    {
        return DB::transaction(function () use ($order, $detailId, $qty, $reason, $source, $actor, $key) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($key && $existing = OrderItemCancellation::query()->where('order_id', $order->id)->where('idempotency_key', $key)->first()) {
                $samePayload = (int) $existing->order_detail_id === $detailId
                    && (int) $existing->qty === $qty
                    && trim($existing->reason) === trim($reason)
                    && $existing->source === $source
                    && (int) ($existing->actor_id ?? 0) === (int) ($actor?->id ?? 0);
                if (! $samePayload) {
                    throw new DomainError('IDEMPOTENCY_KEY_REUSED', 'Idempotency-Key ini sudah digunakan untuk pembatalan dengan payload berbeda.', 409);
                }

                return $order->fresh();
            }
            if ($order->status === OrderStatus::Dibatalkan || $order->status === OrderStatus::Selesai) {
                throw DomainError::orderNotCancellable();
            }
            $detail = OrderDetail::query()->where('order_id', $order->id)->whereKey($detailId)->lockForUpdate()->firstOrFail();
            if ($detail->started_at) {
                throw new DomainError('ORDER_NOT_CANCELLABLE', 'Item sudah mulai diproses.', 409);
            }
            $remaining = $detail->qty - (int) $detail->cancellations()->sum('qty');
            if ($qty < 1 || $qty > $remaining) {
                throw new DomainError('INVALID_CANCELLATION_QUANTITY', 'Kuantitas melebihi jumlah yang belum dibatalkan.', 422, ['remaining' => $remaining]);
            }
            $qtyStart = (int) $detail->cancellations()->max('qty_end') + 1;
            $qtyEnd = $qtyStart + $qty - 1;
            $cancellation = OrderItemCancellation::create(['order_id' => $order->id, 'order_detail_id' => $detail->id, 'qty' => $qty, 'qty_start' => $qtyStart, 'qty_end' => $qtyStart + $qty - 1, 'unit_price' => $detail->unit_price, 'source' => $source, 'actor_type' => $actor ? 'owner' : 'customer', 'actor_id' => $actor?->id, 'reason' => trim($reason), 'idempotency_key' => $key]);
            $payments = Payment::query()->where('order_id', $order->id)
                ->whereHas('orderDetails', fn ($q) => $q->where('order_details.id', $detail->id))
                ->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Verified->value])
                ->orderBy('id')->lockForUpdate()->get();
            $order->update(['total' => max(0, $order->total - $detail->unit_price * $qty)]);
            foreach ($payments as $payment) {
                $allocation = (int) DB::table('payment_order_details')->where('payment_id', $payment->id)
                    ->where('order_detail_id', $detail->id)->value('amount');
                $cancelledAmount = PaymentAllocationService::amountForRange($allocation, (int) $detail->qty, $qtyStart, $qtyEnd);
                if ($cancelledAmount < 1) continue;

                if ($payment->status === PaymentStatus::Pending) {
                    $newAmount = max(0, (int) $payment->amount - $cancelledAmount);
                    if ($allocation === $cancelledAmount) {
                        DB::table('payment_order_details')->where('payment_id', $payment->id)->where('order_detail_id', $detail->id)->delete();
                    } else {
                        DB::table('payment_order_details')->where('payment_id', $payment->id)->where('order_detail_id', $detail->id)
                            ->update(['amount' => $allocation - $cancelledAmount, 'updated_at' => now()]);
                    }
                    $payment->update(['amount' => $newAmount, 'status' => $newAmount === 0 ? PaymentStatus::Cancelled->value : PaymentStatus::Pending->value]);
                } else {
                    $this->refunds->requestForCancellation($cancellation, $payment, $cancelledAmount);
                }
            }
            $allCancelled = $order->details()->get()->every(fn ($d) => $d->qty <= (int) $d->cancellations()->sum('qty'));
            if ($allCancelled) {
                $from = $order->status->value;
                $order->update(['status' => OrderStatus::Dibatalkan->value, 'cancelled_at' => now(), 'cancel_reason' => trim($reason), 'cancelled_by' => $actor?->id]);
                $this->log->order($order, $from, OrderStatus::Dibatalkan->value, $actor, trim($reason));
                $pendingIds = $order->payments()->where('status', PaymentStatus::Pending->value)->pluck('id');
                if ($pendingIds->isNotEmpty()) DB::table('payment_order_details')->whereIn('payment_id', $pendingIds)->delete();
                $order->payments()->where('status', PaymentStatus::Pending->value)->update(['status' => PaymentStatus::Cancelled->value, 'amount' => 0]);
                foreach ($order->tickets()->whereNull('started_at')->get() as $ticket) {
                    $ticket->update(['status' => TicketStatus::Dibatalkan->value, 'cancelled_at' => now()]);
                }
            }
            $this->audit->record($actor, 'order.item_cancelled', 'order_detail', $detail->id, ['order_id' => $order->id, 'qty' => $qty, 'reason' => trim($reason), 'source' => $source, 'cancellation_id' => $cancellation->id]);
            $notice = ['order_id' => $order->id, 'order_number' => $order->order_number, 'detail_id' => $detail->id, 'qty' => $qty, 'reason' => trim($reason), 'source' => $source];
            $this->notifications->sendRole('kasir', 'order.item_cancelled', $notice);
            $this->notifications->sendStation($detail->station->value, 'order.item_cancelled', $notice);
            $stationItems = $order->details()->where('station', $detail->station->value)->with('cancellations')->get();
            if ($stationItems->every(fn ($d) => $d->qty <= (int) $d->cancellations->sum('qty'))) {
                foreach ($order->tickets()->where('station', $detail->station->value)->lockForUpdate()->get() as $ticket) {
                    if ($ticket->status->isOpen()) {
                        $before = $ticket->status->value;
                        $ticket->update(['status' => TicketStatus::Dibatalkan->value, 'cancelled_at' => now()]);
                        $this->log->ticket($order, $ticket, $before, TicketStatus::Dibatalkan->value, $actor, 'Seluruh item stasiun dibatalkan');
                    }
                }
            }

            return $order->fresh();
        });
    }

    /** Menandai order batal setelah pembayaran ditolak; tidak mengubah stok atau refund. */
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
