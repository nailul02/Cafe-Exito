<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceListRequest;
use App\Http\Requests\AttendanceEventRequest;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Support\ApiResponse;

class AttendanceController extends Controller
{
    public function todayStatus(AttendanceListRequest $request, AttendanceService $attendance)
    {
        return ApiResponse::success($attendance->today($request->user()));
    }

    public function checkIn(AttendanceEventRequest $request, AttendanceService $attendance)
    {
        return ApiResponse::success(AttendanceResource::make($attendance->checkIn($request->user(), $request->validated())), 'Check-in berhasil dicatat');
    }

    public function checkOut(AttendanceEventRequest $request, AttendanceService $attendance)
    {
        return ApiResponse::success(AttendanceResource::make($attendance->checkOut($request->user(), $request->validated())), 'Check-out berhasil dicatat');
    }

    public function myHistory(AttendanceListRequest $request, AttendanceService $attendance)
    {
        return ApiResponse::paginated($attendance->myHistory($request->user(), $request->validated()), AttendanceResource::class);
    }

    public function list(AttendanceListRequest $request, AttendanceService $attendance)
    {
        return ApiResponse::paginated($attendance->all($request->validated()), AttendanceResource::class);
    }

    public function correct(AttendanceCorrectionRequest $request, Attendance $attendance, AttendanceService $service)
    {
        return ApiResponse::success(AttendanceResource::make($service->correct($attendance, $request->validated(), $request->user())), 'Koreksi absensi tersimpan');
    }
}
