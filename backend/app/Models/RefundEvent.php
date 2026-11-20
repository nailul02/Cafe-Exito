<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RefundEvent extends Model
{
    protected $fillable=['refund_request_id','actor_id','actor_type','event','details'];
    protected function casts(): array { return ['details'=>'array']; }
}
