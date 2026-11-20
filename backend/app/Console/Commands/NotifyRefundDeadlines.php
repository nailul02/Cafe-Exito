<?php
namespace App\Console\Commands;
use App\Models\RefundRequest;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class NotifyRefundDeadlines extends Command
{
 protected $signature='refunds:notify-deadlines';
 protected $description='Kirim notifikasi in-app untuk tenggat keputusan dan pelaksanaan refund';
 public function handle(InAppNotificationService $notifications): int
 {
  $decisionIds=RefundRequest::query()->where('status','requested')->where('created_at','<=',now()->subHours(24))->whereNull('decision_late_notified_at')->pluck('id');
  foreach($decisionIds as $id) DB::transaction(function()use($id,$notifications){
   $r=RefundRequest::query()->whereKey($id)->lockForUpdate()->first();
   if(!$r||$r->status!=='requested'||$r->decision_late_notified_at||$r->created_at->gt(now()->subHours(24)))return;
   if($r->requested_by&&$u=User::find($r->requested_by))$notifications->send($u,'refund.decision_overdue',['refund_request_id'=>$r->id]);
   else User::where('role','kasir')->where('is_active',true)->each(fn($u)=>$notifications->send($u,'refund.decision_overdue',['refund_request_id'=>$r->id]));
   $r->update(['decision_late_notified_at'=>now()]);
  });
  $executionIds=RefundRequest::query()->where('status','approved')->where('decided_at','<=',now()->subHours(48))->whereNull('execution_late_notified_at')->pluck('id');
  foreach($executionIds as $id) DB::transaction(function()use($id,$notifications){
   $r=RefundRequest::query()->whereKey($id)->lockForUpdate()->first();
   if(!$r||$r->status!=='approved'||$r->execution_late_notified_at||!$r->decided_at||$r->decided_at->gt(now()->subHours(48)))return;
   $notifications->sendRole('owner','refund.execution_overdue',['refund_request_id'=>$r->id]);
   User::where('role','kasir')->where('is_active',true)->each(fn($u)=>$notifications->send($u,'refund.execution_overdue',['refund_request_id'=>$r->id]));
   $r->update(['execution_late_notified_at'=>now()]);
  });
  return self::SUCCESS;
 }
}
