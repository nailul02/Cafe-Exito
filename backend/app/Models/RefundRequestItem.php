<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RefundRequestItem extends Model
{
    protected $fillable=['refund_request_id','cancellation_id','qty','amount'];
    protected function casts(): array { return ['qty'=>'integer','amount'=>'integer']; }
    public function cancellation() { return $this->belongsTo(OrderItemCancellation::class, 'cancellation_id'); }
    public function refundRequest() { return $this->belongsTo(RefundRequest::class); }
}
