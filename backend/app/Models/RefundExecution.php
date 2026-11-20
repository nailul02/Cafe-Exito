<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RefundExecution extends Model
{
    protected $fillable=['refund_request_id','cashier_id','method','amount','reference_number','proof_path','executed_at'];
    protected function casts(): array { return ['amount'=>'integer','executed_at'=>'datetime']; }
}
