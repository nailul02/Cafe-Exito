<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCartRequest;
use App\Http\Resources\CustomerMenuResource;
use App\Models\Menu;
use App\Models\Table;
use App\Services\MenuAvailabilityService;
use App\Services\OrderService;
use App\Services\SettingsService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Endpoint pelanggan tanpa login: meja, katalog, pratinjau keranjang, petunjuk bayar. */
class CatalogController extends Controller
{
    public function table(string $qrToken)
    {
        $table = Table::query()->where('qr_token', $qrToken)->where('status', 'active')->first();
        if (! $table) {
            throw new NotFoundHttpException;
        }

        return ApiResponse::success(['id' => $table->id, 'number' => $table->table_number]);
    }

    public function menus(MenuAvailabilityService $availability)
    {
        $menus = Menu::query()->join('menu_categories', 'menu_categories.id', '=', 'menus.category_id')
            ->select('menus.*')->orderBy('menu_categories.sort_order')->orderBy('menus.name')->get();

        return ApiResponse::success(CustomerMenuResource::collection($availability->load($menus)));
    }

    public function validateCart(ValidateCartRequest $request, OrderService $orders)
    {
        return ApiResponse::success($orders->preview($request->validated('items')));
    }

    /** Petunjuk pembayaran: QRIS statis (gambar dari pengaturan) atau tunai di kasir. */
    public function paymentInfo(Request $request, SettingsService $settings)
    {
        return ApiResponse::success([
            'cafe_name' => $settings->get('cafe_name', 'Cafe Exito'),
            'qris_image_url' => $settings->get('qris_image_url'),
            'instructions' => [
                'qris' => 'Scan QRIS Cafe Exito lalu tunjukkan bukti ke kasir. Pesanan diproses setelah kasir memverifikasi.',
                'tunai' => 'Bayar tunai di kasir. Pesanan diproses setelah kasir memverifikasi.',
            ],
        ]);
    }
}
