<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTableRequest;
use App\Http\Resources\TableResource;
use App\Models\Table;
use App\Services\AuditService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Meja dan QR. Gambar QR dirender frontend dari qr_url (K-T2/K-T4 PENDING). */
class TableController extends Controller
{
    public function index()
    {
        return ApiResponse::success(TableResource::collection(Table::query()->orderByRaw('LENGTH(table_number), table_number')->get()));
    }

    public function store(SaveTableRequest $request)
    {
        $table = Table::create([...$request->validated(), 'status' => $request->validated('status', 'active'), 'qr_token' => Str::lower(Str::random(24))]);

        return ApiResponse::created(TableResource::make($table), 'Meja dibuat');
    }

    public function update(SaveTableRequest $request, Table $table)
    {
        $table->update($request->validated());

        return ApiResponse::success(TableResource::make($table), 'Meja diperbarui');
    }

    /** Menonaktifkan (bukan menghapus) agar riwayat pesanan tetap utuh. */
    public function destroy(Table $table)
    {
        $table->update(['status' => 'inactive']);

        return ApiResponse::success(TableResource::make($table), 'Meja dinonaktifkan');
    }

    /** Token baru; QR lama otomatis tidak berlaku. */
    public function regenerateQr(Request $request, Table $table, AuditService $audit)
    {
        $table->update(['qr_token' => Str::lower(Str::random(24))]);
        $audit->record($request->user(), 'table.qr_regenerated', 'table', $table->id);

        return ApiResponse::success(TableResource::make($table), 'QR dibuat ulang; QR lama tidak berlaku');
    }
}
