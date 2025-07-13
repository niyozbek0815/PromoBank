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
                $http = $http->asMultipart();

                // Form-data (text) tayyorlash
                $multipartData = collect($request->except($fileKeys))
                    ->flatMap(function ($value, $key) {
                        if (is_array($value)) {
                            return collect($value)->map(function ($subValue, $subKey) use ($key) {
                                return [
                                    'name'     => is_numeric($subKey) ? "{$key}[]" : "{$key}[{$subKey}]",
                                    'contents' => $subValue,
                                ];
                            })->values();
                        }
                        return [[
                            'name'     => $key,
                            'contents' => $value,
                        ]];
                    })->values();

                // Fayllarni qo‘shish (bir dona yoki array)
                foreach ($fileKeys as $fileKey) {
                    if ($request->hasFile($fileKey)) {
                        $files = $request->file($fileKey);

                        if ($files instanceof UploadedFile) {
                            // Bitta fayl
                            $multipartData->push([
                                'name'     => $fileKey,
                                'contents' => fopen($files->getPathname(), 'r'),
                                'filename' => $files->getClientOriginalName(),
                            ]);
                        } elseif (is_array($files)) {
                            // Ko‘p fayl
                            foreach ($files as $file) {
                                $multipartData->push([
                                    'name'     => "{$fileKey}[]",
                                    'contents' => fopen($file->getPathname(), 'r'),
                                    'filename' => $file->getClientOriginalName(),
                                ]);
                            }
                        }
                    }
                }

                // Agar method PUT bo‘lsa, _method ni berish kerak
                if ($method === 'PUT') {
                    $multipartData->push([
                        'name'     => '_method',
                        'contents' => 'PUT',
                    ]);
                    return $http->post($url, $multipartData->all());
                }

                return $http->post($url, $multipartData->all());
            }

            // Faylsiz GET/POST/PUT/DELETE
            return match ($method) {
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
