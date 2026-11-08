<?php

namespace App\Http\Controllers\Api\V1\Station;

use App\Enums\Station;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\OrderTicket;
use App\Services\DashboardService;
use App\Services\TicketService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Antrean Barista/Kitchen. Parameter {station} dicantumkan di setiap metode karena Laravel menyuntikkan parameter rute secara
 * berurutan; nilai sebenarnya dibaca dari atribut request yang diisi EnsureStationAccess (enum Station).
 * Akses stasiun ditegakkan middleware EnsureStationAccess; setiap query difilter stasiun,
 * sehingga tiket stasiun lain menghasilkan 404 (bukan 403).
 */
class StationController extends Controller
{
    private const WITH = ['order.table', 'order.details', 'handler'];

    public function __construct(private readonly TicketService $tickets) {}

    public function dashboard(Request $request, string $station, DashboardService $dashboard)
    {
        return ApiResponse::success($dashboard->station($request->attributes->get('station')));
    }

    public function index(Request $request, string $station)
    {
        /** @var Station $station */
        $station = $request->attributes->get('station');
        $status = data_get($request->query(), 'filter.status');
        $since = $request->query('updated_since') ? Carbon::parse($request->query('updated_since')) : null;

        return ApiResponse::success(TicketResource::collection($this->tickets->queue($station, $status, $since)->get()));
    }

    public function show(Request $request, string $station, int $ticket)
    {
        return ApiResponse::success(TicketResource::make($this->find($request, $ticket)));
    }

    public function start(Request $request, string $station, int $ticket)
    {
        $this->tickets->start($this->find($request, $ticket), $request->user());

        return ApiResponse::success(TicketResource::make($this->find($request, $ticket)), 'Pesanan mulai diproses');
    }

    public function complete(Request $request, string $station, int $ticket)
    {
        $this->tickets->complete($this->find($request, $ticket), $request->user());

        return ApiResponse::success(TicketResource::make($this->find($request, $ticket)), 'Pesanan selesai');
    }

    private function find(Request $request, int $id): OrderTicket
    {
        return OrderTicket::query()->with(self::WITH)->where('station', $request->attributes->get('station')->value)->findOrFail($id);
    }
}
