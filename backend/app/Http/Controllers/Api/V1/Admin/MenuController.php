<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Services\AuditService;
use App\Services\MenuAvailabilityService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function __construct(private readonly MenuAvailabilityService $availability, private readonly AuditService $audit) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $menus = Menu::query()->when($search !== '', function ($q) use ($search) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $q->where(fn ($w) => $w->where('name', 'ilike', $like)->orWhere('sku', 'ilike', $like));
        })->orderBy('name')->get();

        return ApiResponse::success(MenuResource::collection($this->availability->load($menus)));
    }

    public function show(Menu $menu)
    {
        return ApiResponse::success(MenuResource::make($this->availability->load($menu->newCollection([$menu]))->first()));
    }

    public function store(SaveMenuRequest $request)
    {
        $data = $this->payload($request);
        $menu = Menu::create($data);
        $this->audit->record($request->user(), 'menu.created', 'menu', $menu->id, ['price' => $menu->price]);

        return ApiResponse::created(MenuResource::make($this->fresh($menu)), 'Menu dibuat');
    }

    public function update(SaveMenuRequest $request, Menu $menu)
    {
        $oldPrice = $menu->price;
        $menu->update($this->payload($request, $menu));
        if ($oldPrice !== $menu->price) {
            $this->audit->record($request->user(), 'menu.price_changed', 'menu', $menu->id, ['from' => $oldPrice, 'to' => $menu->price]);
        }

        return ApiResponse::success(MenuResource::make($this->fresh($menu)), 'Menu diperbarui');
    }

    public function destroy(Request $request, Menu $menu)
    {
        $menu->delete(); // soft delete: riwayat pesanan tetap utuh
        $this->audit->record($request->user(), 'menu.deleted', 'menu', $menu->id);

        return ApiResponse::success(null, 'Menu dihapus');
    }

    /** Saklar manual. Ketersediaan efektif tetap mengikuti stok (dihitung dinamis). */
    public function availability(Request $request, Menu $menu)
    {
        $data = $request->validate(['is_available' => ['required', 'boolean']]);
        $menu->update($data);
        $this->audit->record($request->user(), 'menu.availability_changed', 'menu', $menu->id, $data);

        return ApiResponse::success(MenuResource::make($this->fresh($menu)));
    }

    public function categories()
    {
        return ApiResponse::success(MenuCategory::query()->orderBy('sort_order')->orderBy('name')->get()->map(fn ($c) => [
            'id' => $c->id, 'name' => $c->name, 'display_group' => $c->display_group->value, 'station' => $c->station->value,
        ])->all());
    }

    private function payload(SaveMenuRequest $request, ?Menu $existing = null): array
    {
        $data = $request->safe()->except(['image']);
        // Stasiun bawaan mengikuti kategori bila tidak diisi.
        $data['station'] = $data['station'] ?? $existing?->station?->value ?? MenuCategory::findOrFail($data['category_id'])->station->value;
        if ($request->hasFile('image')) {
            if ($existing?->image_path) {
                Storage::disk('public')->delete($existing->image_path);
            }
            $data['image_path'] = $request->file('image')->store('menus', 'public');
        }

        return $data;
    }

    private function fresh(Menu $menu): Menu
    {
        return $this->availability->load($menu->refresh()->newCollection([$menu]))->first();
    }
}
