<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Dasar semua FormRequest API. Otorisasi ditegakkan middleware permission/stasiun + Policy, bukan di sini. */
abstract class ApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
