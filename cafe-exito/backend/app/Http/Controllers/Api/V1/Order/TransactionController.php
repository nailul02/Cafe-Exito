<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Riwayat transaksi (pembayaran terverifikasi): tabel transaksi di layar Laporan Owner (Figma OW-02). */
class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $method = $request->query('method');
        $page = Payment::query()->with(['order.table', 'order.details', 'order.tickets', 'order.payment.verifier', 'verifier'])
            ->where('status', 'verified')
            ->when($method, fn ($q) => $q->where('method', $method))
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $w->where('transaction_number', 'ilike', $like)->orWhereHas('order.details', fn ($d) => $d->where('menu_name', 'ilike', $like));
            }))
            ->orderByDesc('paid_at')->paginate(min((int) $request->query('per_page', 25), 100));

        $rows = $page->getCollection()->map(fn (Payment $p) => [
            'order' => OrderResource::make($p->order)->resolve($request),
            'payment' => PaymentResource::make($p)->resolve($request),
        ])->values()->all();

        return ApiResponse::success($rows, 'OK', ['pagination' => [
            'page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage(),
        ]]);
    }
}
