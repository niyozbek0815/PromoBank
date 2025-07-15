<?php
namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    protected function forwardRequestMedias(
        string $method,
        string $baseUrl,
        string $path,
        Request $request,
        array $fileKeys = ['image'],
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

                // 🔹 1. Oddiy fieldlar (text, array, translations, etc.)
                foreach ($request->except($fileKeys) as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $subKey => $subVal) {
                            $fieldName = is_numeric($subKey) ? "{$key}[]" : "{$key}[{$subKey}]";
                            $multipartData->push([
                                'name'     => $fieldName,
                                'contents' => $subVal,
                            ]);
                        }
                    } else {
                        $multipartData->push([
                            'name'     => $key,
                            'contents' => $value,
                        ]);
                    }
                }

                // 🔹 2. Fayllar
                foreach ($fileKeys as $fileKey) {
                    if ($request->hasFile($fileKey)) {
                        $files = $request->file($fileKey);

                        if ($files instanceof UploadedFile) {
                            Log::info("media_name=" . $files->getClientOriginalName());

                            // Bitta fayl
                            $multipartData->push([
                                'name'     => $fileKey,
                                'contents' => fopen($files->getPathname(), 'r'),
                                'filename' => $files->getClientOriginalName(),
                            ]);
                        } elseif (is_array($files)) {
                            // Ko‘p fayl
                            foreach ($files as $file) {
                                if ($file instanceof UploadedFile) {
                                    $multipartData->push([
                                        'name'     => "{$fileKey}[]", // ❗️❗️ NOT "{$fileKey}[]"!
                                        'contents' => fopen($file->getPathname(), 'r'),
                                        'filename' => $file->getClientOriginalName(),
                                    ]);
                                }
                            }

                        }
                    }
                }

                // 🔹 3. PUT bo‘lsa _method=PUT kerak
                if ($method === 'PUT') {
                    $multipartData->push([
                        'name'     => '_method',
                        'contents' => 'PUT',
                    ]);
                }

                // 🔹 4. So‘rovni yuborish
                return $http->send('POST', $url, [
                    'multipart' => $multipartData->toArray(),
                ]);
            }

            // 🔹 5. Faylsiz oddiy so‘rovlar
            return match ($method) {
                'GET' => $http->get($url, $request->query()),
                'POST' => $http->post($url, $request->all()),
                'PUT' => $http->put($url, $request->all()),
                'DELETE' => $http->delete($url, $request->all()),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
            };

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal error from external service',
                'errors'  => $e->getMessage(),
            ], 503);
        }
    }
}