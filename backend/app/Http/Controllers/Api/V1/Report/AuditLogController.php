<?php

namespace App\Http\Controllers\Api\V1\Report;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Pembacaan jejak audit (mis. "Audit Log Perubahan Menu" di Figma MG-02). */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $page = AuditLog::query()->with('user:id,name')
            ->when($request->query('subject_type'), fn ($q, $t) => $q->where('subject_type', $t))
            ->when($request->query('subject_id'), fn ($q, $i) => $q->where('subject_id', $i))
            ->orderByDesc('id')->paginate(min((int) $request->query('per_page', 30), 100));

        $rows = $page->getCollection()->map(fn (AuditLog $a) => [
            'id' => $a->id, 'action' => $a->action, 'subject_type' => $a->subject_type, 'subject_id' => $a->subject_id,
            'changes' => $a->changes, 'user' => $a->user?->name, 'created_at' => $a->created_at?->toIso8601String(),
        ])->values()->all();

        return ApiResponse::success($rows, 'OK', ['pagination' => [
            'page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage(),
        ]]);
    }
}
