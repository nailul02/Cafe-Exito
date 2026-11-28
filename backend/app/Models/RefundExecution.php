<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RefundExecution extends Model
{
    protected $fillable=['refund_request_id','cashier_id','executed_by','method','amount','reference_number','proof_path','executed_at'];
    protected function casts(): array { return ['amount'=>'integer','executed_at'=>'datetime']; }
    public function executor() { return $this->belongsTo(User::class, 'executed_by'); }
}
