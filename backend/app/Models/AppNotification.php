<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AppNotification extends Model
{
    protected $fillable=['user_id','type','data','read_at'];
    protected function casts(): array { return ['data'=>'array','read_at'=>'datetime']; }
}
