<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RefundConflictNote extends Model
{
    protected $fillable=['refund_request_id','recorded_by','verified_by','handled_by','resolved_on','outcome','reason','amount','proof_path','verified_at'];
    protected function casts(): array { return ['resolved_on'=>'date','verified_at'=>'datetime','amount'=>'integer']; }
    public function recorder(){return $this->belongsTo(User::class,'recorded_by');}
    public function verifier(){return $this->belongsTo(User::class,'verified_by');}
}
