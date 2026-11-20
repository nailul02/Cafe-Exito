<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RefundRequest extends Model
{
    protected $fillable = ['order_id','payment_id','source','status','amount','reason','requested_by','decided_at','decided_by','decision_reason','withdrawn_at','withdrawn_by','withdraw_reason','approval_revoked_at','approval_revoked_by','approval_revoke_reason','conflict_of_interest_at','completed_at','decision_late_notified_at','execution_late_notified_at'];
    protected function casts(): array { return ['amount'=>'integer','decided_at'=>'datetime','withdrawn_at'=>'datetime','approval_revoked_at'=>'datetime','conflict_of_interest_at'=>'datetime','completed_at'=>'datetime']; }
    public function order() { return $this->belongsTo(Order::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function items() { return $this->hasMany(RefundRequestItem::class); }
    public function events() { return $this->hasMany(RefundEvent::class); }
    public function execution() { return $this->hasOne(RefundExecution::class); }
    public function conflictNotes() { return $this->hasMany(RefundConflictNote::class); }
}
