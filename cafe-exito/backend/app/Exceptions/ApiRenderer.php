<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/** Memetakan semua exception menjadi respons JSON seragam. Tanpa redirect dan tanpa halaman HTML. */
final class ApiRenderer
{
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        return match (true) {
            $e instanceof DomainError => $e->render(),
            $e instanceof ValidationException => ApiResponse::error('VALIDATION_FAILED', 'Data yang dikirim tidak valid.', 422, null, $e->errors()),
            $e instanceof AuthenticationException => ApiResponse::error('UNAUTHENTICATED', 'Silakan login terlebih dahulu.', 401),
            $e instanceof AuthorizationException, $e instanceof AccessDeniedHttpException => ApiResponse::error('FORBIDDEN', 'Anda tidak memiliki izin untuk aksi ini.', 403),
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => ApiResponse::error('NOT_FOUND', 'Data tidak ditemukan.', 404),
            $e instanceof MethodNotAllowedHttpException => ApiResponse::error('METHOD_NOT_ALLOWED', 'Metode HTTP tidak diizinkan untuk alamat ini.', 405),
            $e instanceof TooManyRequestsHttpException => self::withHeaders(ApiResponse::error('TOO_MANY_REQUESTS', 'Terlalu banyak permintaan. Coba lagi sebentar lagi.', 429), $e),
            $e instanceof HttpExceptionInterface => ApiResponse::error('HTTP_'.$e->getStatusCode(), $e->getMessage() ?: 'Permintaan tidak dapat diproses.', $e->getStatusCode()),
            // Mode debug: biarkan Laravel menampilkan JSON debug lengkap. Produksi: pesan generik tanpa detail internal.
            config('app.debug') => null,
            default => ApiResponse::error('SERVER_ERROR', 'Terjadi kesalahan pada server.', 500),
        };
    }

    private static function withHeaders(JsonResponse $response, HttpExceptionInterface $e): JsonResponse
    {
        foreach ($e->getHeaders() as $key => $value) {
            $response->headers->set($key, (string) $value);
        }

        return $response;
    }
}
