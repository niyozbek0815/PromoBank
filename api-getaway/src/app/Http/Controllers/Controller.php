<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;

abstract class Controller
{
    use ApiResponse;
    protected function forwardRequest(string $method, string $baseUrl, string $path,  $request, int $successCode = 200)
    {
        try {
            $headers = [
                'Accept' => 'application/json',
                'User-Ip' => $request->ip(),
                'User-Agent' => $request->userAgent(),
            ];

            $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
            $http = Http::withHeaders($headers);

            $response = match (strtoupper($method)) {
                'GET'    => $http->get($url, $request->query()),
                'POST'   => $http->post($url, $request->all()),
                'PUT'    => $http->put($url, $request->all()),
                'DELETE' => $http->delete($url, $request->all()),
                default  => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
            };

            return $response;
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Internal error from external service',
                $e->getMessage(),
                503
            );
        }
    }
}
