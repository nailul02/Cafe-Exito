<?php

namespace App\Models;

use App\Enums\StockLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'category', 'unit', 'stock', 'min_stock', 'is_active'];

    protected function casts(): array
    {
        return ['stock' => 'decimal:3', 'min_stock' => 'decimal:3', 'is_active' => 'boolean'];
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function level(): StockLevel
    {
        return StockLevel::calculate($this->stock, $this->min_stock, (float) config('cafe.stock_levels.critical_ratio', 0.5));
    }

    /** Persentase stok terhadap minimum (Figma: "KRITIS (24%)", "Aman (190%)"). */
    public function stockPercent(): int
    {
        $min = (float) $this->min_stock;

        return $min > 0 ? (int) round(((float) $this->stock / $min) * 100) : 100;
    }
}
