<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\RefundExecuteRequest;
use App\Models\AppNotification;
use App\Models\RefundConflictNote;
use App\Models\RefundRequest;
use App\Models\Payment;
use App\Services\RefundService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RefundController extends Controller
{
 public function index(Request $request){$q=RefundRequest::with(['order','items.cancellation.detail','execution','events','conflictNotes.recorder','conflictNotes.verifier'])->orderByDesc('id');if($request->user()->role->value==='kasir')$q->whereIn('status',['requested','approved','completed','rejected','withdrawn','approval_revoked']);return ApiResponse::success($q->paginate(min((int)$request->query('per_page',30),100)));}
 public function decide(Request $request,RefundRequest $refund,RefundService $service){$data=$request->validate(['decision'=>['required','in:approved,rejected'],'reason'=>['nullable','string','max:500','regex:/\S/','required_if:decision,rejected']]);return ApiResponse::success($service->decide($refund,$request->user(),$data['decision'],$data['reason']??null));}
 public function createManual(Request $request,RefundService $service){$data=$request->validate(['payment_id'=>['required','integer','exists:payments,id'],'amount'=>['required','integer','min:1'],'reason'=>['required','string','max:500','regex:/\S/']]);return ApiResponse::created($service->manualRequest(Payment::findOrFail($data['payment_id']),$request->user(),$data['amount'],$data['reason']));}
 public function withdraw(Request $request,RefundRequest $refund,RefundService $service){$data=$request->validate(['reason'=>['required','string','max:500','regex:/\S/']]);return ApiResponse::success($service->withdraw($refund,$request->user(),$data['reason']));}
 public function revoke(Request $request,RefundRequest $refund,RefundService $service){$data=$request->validate(['reason'=>['required','string','max:500','regex:/\S/']]);return ApiResponse::success($service->revoke($refund,$request->user(),$data['reason']));}
 public function execute(RefundExecuteRequest $request,RefundRequest $refund,RefundService $service){return ApiResponse::success($service->execute($refund,$request->user(),$request->validated()));}
 public function notifications(Request $request){return ApiResponse::success(AppNotification::where('user_id',$request->user()->id)->orderByDesc('id')->paginate(50));}
 public function readNotification(Request $request,AppNotification $notification){abort_unless($notification->user_id===$request->user()->id,404);$notification->update(['read_at'=>now()]);return ApiResponse::success($notification);}
 public function proof(Request $request,RefundRequest $refund){abort_unless(in_array($request->user()->role->value,['owner','kasir'],true),403);$path=$refund->execution?->proof_path;if(!$path||!Storage::disk('private')->exists($path))abort(404);return Storage::disk('private')->download($path);}
 public function conflictProof(Request $request,RefundConflictNote $note){abort_unless(in_array($request->user()->role->value,['owner','manager'],true),403);if(!$note->proof_path||!Storage::disk('private')->exists($note->proof_path))abort(404);return Storage::disk('private')->download($note->proof_path);}
 public function conflictNote(Request $request,RefundRequest $refund,RefundService $service){$data=$request->validate(['handled_by'=>['required','string','max:160'],'resolved_on'=>['required','date'],'outcome'=>['required','string','max:500'],'reason'=>['required','string','max:500'],'amount'=>['required','integer','min:0'],'proof'=>['nullable','file','mimes:jpg,jpeg,png,pdf','max:5120']]);return ApiResponse::created($service->recordConflict($refund,$request->user(),$data));}
 public function conflictNotes(RefundRequest $refund){return ApiResponse::success($refund->conflictNotes()->with(['recorder:id,name','verifier:id,name'])->get());}
 public function verifyConflict(Request $request,RefundConflictNote $note,RefundService $service){return ApiResponse::success($service->verifyConflict($note,$request->user()));}
}
