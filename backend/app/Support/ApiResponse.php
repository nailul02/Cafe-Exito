<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Envelope respons seragam untuk seluruh API:
 *   sukses: { success: true,  message, data, meta: { request_id, server_time, [pagination] } }
 *   gagal : { success: false, message, error: { code, details }, errors?, meta }
 */
class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK', array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => self::normalize($data),
            'meta' => self::meta($meta),
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Berhasil dibuat'): JsonResponse
    {
        return self::success($data, $message, [], 201);
    }

    /** @param  class-string<JsonResource>  $resource */
    public static function paginated(LengthAwarePaginator $page, string $resource, string $message = 'OK'): JsonResponse
    {
        return self::success($resource::collection($page->getCollection()), $message, ['pagination' => [
            'page' => $page->currentPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
            'last_page' => $page->lastPage(),
        ]]);
    }

    public static function error(string $code, string $message, int $status, ?array $details = null, ?array $errors = null): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
            'error' => ['code' => $code, 'details' => $details],
        ];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        $body['meta'] = self::meta();

        return response()->json($body, $status);
    }

    public static function meta(array $extra = []): array
    {
        return array_merge([
            'request_id' => request()->attributes->get('request_id'),
            'server_time' => now()->toIso8601String(),
        ], $extra);
    }

    private static function normalize(mixed $data): mixed
    {
        if ($data instanceof JsonResource) {
            return $data->resolve(request());
        }

        return $data;
    }
}
