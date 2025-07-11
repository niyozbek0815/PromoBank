<?php
namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

abstract class Controller
{
    use ApiResponse;

    protected function forwardRequest(string $method, string $baseUrl, string $path, Request $request, $imageFeilsName = 'image', int $successCode = 200)
    {
        try {
            $headers = [
                'Accept'     => 'application/json',
                'User-Ip'    => $request->ip(),
                'User-Agent' => $request->userAgent(),
            ];

            $url  = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
            $http = Http::withHeaders($headers);

            $hasFile = $request->hasFile($imageFeilsName);

            if (in_array(strtoupper($method), ['POST', 'PUT']) && $hasFile) {
                $http = $http->asMultipart();

                $multipartData = collect($request->except($imageFeilsName))
                    ->flatMap(function ($value, $key) {
                        if (is_array($value)) {
                            return collect($value)->map(function ($subValue, $subKey) use ($key) {
                                return [
                                    'name'     => "{$key}[{$subKey}]",
                                    'contents' => $subValue,
                                ];
                            })->values();
                        }
                        return [[
                            'name'     => $key,
                            'contents' => $value,
                        ]];
                    })->values()->toArray();

                if ($request->file($imageFeilsName) instanceof UploadedFile) {
                    $multipartData[] = [
                        'name'     => $imageFeilsName,
                        'contents' => fopen($request->file($imageFeilsName)->getPathname(), 'r'),
                        'filename' => $request->file($imageFeilsName)->getClientOriginalName(),
                    ];
                }

                if (strtoupper($method) === 'PUT') {
                    $multipartData[] = [
                        'name'     => '_method',
                        'contents' => 'PUT',
                    ];
                    return $http->post($url, $multipartData);
                }

                return $http->post($url, $multipartData);
            }

            return match (strtoupper($method)) {
                'GET' => $http->get($url, $request->query()),
                'POST' => $http->post($url, $request->all()),
                'PUT' => $http->put($url, $request->all()),
                'DELETE' => $http->delete($url, $request->all()),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
            };

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Internal error from external service',
                $e->getMessage(),
                503
            );
        }
    }
}
