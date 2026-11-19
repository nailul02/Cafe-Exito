<?php

namespace App\Services;

use Fruitcake\Cors\CorsService as BaseCorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Layanan CORS khusus: memastikan origin tidak dikenal (unauthorized)
 * TIDAK mendapatkan header Access-Control-Allow-Origin, meskipun hanya
 * satu origin yang terdaftar di konfigurasi (mencegah fallback otomatis Fruitcake).
 */
class CorsService extends BaseCorsService
{
    public function addPreflightRequestHeaders(Response $response, Request $request): Response
    {
        if (! $this->isOriginAllowed($request)) {
            return $response;
        }

        return parent::addPreflightRequestHeaders($response, $request);
    }

    public function addActualRequestHeaders(Response $response, Request $request): Response
    {
        if (! $this->isOriginAllowed($request)) {
            return $response;
        }

        return parent::addActualRequestHeaders($response, $request);
    }
}
