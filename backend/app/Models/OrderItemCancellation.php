<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class OrderItemCancellation extends Model
{
    protected $fillable = ['order_id','order_detail_id','qty','qty_start','qty_end','unit_price','source','actor_type','actor_id','reason','idempotency_key'];
    protected function casts(): array { return ['qty'=>'integer','unit_price'=>'integer']; }
    public function detail() { return $this->belongsTo(OrderDetail::class, 'order_detail_id'); }
    public function refundItem() { return $this->hasOne(RefundRequestItem::class, 'cancellation_id'); }
    public function refundItems(): HasMany { return $this->hasMany(RefundRequestItem::class, 'cancellation_id'); }
}
