<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Support\ApiResponse;

/** Supplier sederhana: nama, kontak, status (K-B: pengelolaan supplier kompleks tetap di luar MVP). */
class SupplierController extends Controller
{
    public function index()
    {
        return ApiResponse::success(SupplierResource::collection(Supplier::query()->where('is_active', true)->orderBy('name')->get()));
    }

    public function store(SaveSupplierRequest $request)
    {
        return ApiResponse::created(SupplierResource::make(Supplier::create($request->validated())), 'Supplier ditambahkan');
    }

    public function update(SaveSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return ApiResponse::success(SupplierResource::make($supplier), 'Supplier diperbarui');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->update(['is_active' => false]);

        return ApiResponse::success(null, 'Supplier dinonaktifkan');
    }
}
