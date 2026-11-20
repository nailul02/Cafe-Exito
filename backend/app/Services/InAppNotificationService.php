<?php
namespace App\Services;
use App\Models\AppNotification;
use App\Models\User;
class InAppNotificationService
{
    public function send(User $user, string $type, array $data): void { AppNotification::create(['user_id'=>$user->id,'type'=>$type,'data'=>$data]); }
    public function sendRole(string $role, string $type, array $data): void
    { User::query()->where('role',$role)->where('is_active',true)->each(fn(User $u)=>$this->send($u,$type,$data)); }
    public function sendStation(string $station,string $type,array $data):void
    { User::query()->where('role','barista_kitchen')->where('station',$station)->where('is_active',true)->each(fn(User $u)=>$this->send($u,$type,$data)); }
}
