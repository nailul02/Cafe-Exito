<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;

/** Pemeriksaan kesehatan tanpa detail internal (disiapkan untuk tahap hosting). */
class HealthController extends Controller
{
    public function __invoke()
    {
        return ApiResponse::success(['status' => 'ok']);
    }
}
