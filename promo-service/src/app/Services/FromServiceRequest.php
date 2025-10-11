<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class FromServiceRequest
{
    public function forward(
        string $method,
        string $baseUrl,
        string $path,
        Request|array $request,
        int $successCode = 200
    ): Response|JsonResponse {
        $url = Str::of($baseUrl)->finish('/')->append(ltrim($path, '/'))->toString();

        $headers = ['Accept' => 'application/json'];

        if ($request instanceof Request) {
            $headers['User-Ip'] = $request->ip() ?? '127.0.0.1';
            $headers['User-Agent'] = $request->userAgent() ?? 'Laravel-Forward-Client';
        }

        $http = Http::withHeaders($headers)
            ->timeout(5)
            ->retry(3, 200);

        $payload = $request instanceof Request ? $request->all() : $request;
        $query   = $request instanceof Request ? $request->query() : [];

        try {
            return match (strtoupper($method)) {
                'GET'    => $http->get($url, $query),
                'POST'   => $http->post($url, $payload),
                'PUT'    => $http->put($url, $payload),
                'DELETE' => $http->delete($url, $payload),
                default  => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
            };
        } catch (\Throwable $e) {
            Log::error('HTTP forward request failed', [
                'url'    => $url,
                'method' => $method,
                'error'  => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Internal error when calling external service.',
                'error'   => $e->getMessage(),
            ], 503);
        }
    }
}
