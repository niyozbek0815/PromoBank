<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Http;

abstract class Controller
{
    use ApiResponse;
    protected function forwardRequest(string $baseUrl, string $path, $request, int $successCode = 200)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'User-Ip' => $request->ip(),
                'User-Agent' => $request->userAgent(),
            ])->post("{$baseUrl}{$path}", $request->all());
            if ($response->successful()) {
                return $response;
            }
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
