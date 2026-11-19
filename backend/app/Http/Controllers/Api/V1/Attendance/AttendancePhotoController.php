<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEvent;
use App\Services\PermissionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendancePhotoController extends Controller
{
    public function __invoke(Request $request, AttendanceEvent $event, PermissionService $permissions): StreamedResponse
    {
        $viewer = $request->user();
        $mayViewFullReport = $permissions->has($viewer, 'attendance.report');
        if (! $mayViewFullReport && $event->user_id !== $viewer->id) {
            throw new AuthorizationException;
        }
        abort_unless(Storage::disk('local')->exists($event->photo_path), 404);

        return Storage::disk('local')->response($event->photo_path);
    }
}
