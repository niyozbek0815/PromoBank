<?php
namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

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
                'X-Internal-Secret' => env('INTERNAL_SECRET'),
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

    protected function forwardRequestMedias(
        string $method,
        string $baseUrl,
        string $path,
        Request $request,
        array $fileKeys = ['image', 'media'],
        int $successCode = 200
    ) {
        try {
            $headers = [
                'Accept'     => 'application/json',
                'User-Ip'    => $request->ip(),
                'User-Agent' => $request->userAgent(),
            ];

            $url    = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
            $http   = Http::withHeaders($headers);
            $method = strtoupper($method);

            $hasFiles = collect($fileKeys)->some(fn($key) => $request->hasFile($key));

            if (in_array($method, ['POST', 'PUT']) && $hasFiles) {
                $multipartData = collect();

                // 1️⃣ Oddiy fieldlar
                foreach ($request->except($fileKeys) as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $subKey => $subVal) {
                            $fieldName = is_numeric($subKey) ? "{$key}[]" : "{$key}[{$subKey}]";
                            $multipartData->push([
                                'name'     => $fieldName,
                                // array bo'lsa JSON encode qilamiz
                                'contents' => is_array($subVal) ? json_encode($subVal, JSON_UNESCAPED_UNICODE) : $subVal,
                            ]);
                        }
                    } else {
                        $multipartData->push([
                            'name'     => $key,
                            'contents' => $value,
                        ]);
                    }
                }

                // 2️⃣ Fayllar
                foreach ($fileKeys as $fileKey) {
                    if ($request->hasFile($fileKey)) {
                        $files = $request->file($fileKey);

                        if ($files instanceof UploadedFile) {
                            $multipartData->push([
                                'name'     => $fileKey,
                                'contents' => fopen($files->getPathname(), 'r'),
                                'filename' => $files->getClientOriginalName(),
                            ]);
                        } elseif (is_array($files)) {
                            foreach ($files as $langKey => $file) {
                                if ($file instanceof UploadedFile) {
                                    $fieldName = is_numeric($langKey)
                                        ? "{$fileKey}[]"
                                        : "{$fileKey}[{$langKey}]";

                                    $multipartData->push([
                                        'name'     => $fieldName,
                                        'contents' => fopen($file->getPathname(), 'r'),
                                        'filename' => $file->getClientOriginalName(),
                                    ]);
                                }
                            }
                        }
                    }
                }

                // 3️⃣ PUT bo‘lsa _method=PUT
                if ($method === 'PUT') {
                    $multipartData->push([
                        'name'     => '_method',
                        'contents' => 'PUT',
                    ]);
                }

                // 4️⃣ So‘rovni yuborish
                return $http->send('POST', $url, [
                    'multipart' => $multipartData->toArray(),
                ]);
            }

            // 5️⃣ Faylsiz oddiy so‘rovlar
            return match ($method) {
                'GET' => $http->get($url, $request->query()),
                'POST' => $http->post($url, $request->all()),
                'PUT' => $http->put($url, $request->all()),
                'DELETE' => $http->delete($url, $request->all()),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
            };
        } catch (Throwable $e) {
            Log::error('❌ ForwardRequestMedias error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal error from external service',
                'errors'  => $e->getMessage(),
            ], 503);
        }
    }
}
