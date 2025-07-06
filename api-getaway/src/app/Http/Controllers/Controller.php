<?php
namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

abstract class Controller
{
    use ApiResponse;
    // protected function forwardRequest(string $method, string $baseUrl, string $path, $request, int $successCode = 200)
    // {
    //     try {
    //         $headers = [
    //             'Accept'     => 'application/json',
    //             'User-Ip'    => $request->ip(),
    //             'User-Agent' => $request->userAgent(),
    //         ];

    //         $url  = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    //         $http = Http::withHeaders($headers);

    //         return match (strtoupper($method)) {
    //             'GET' => $http->get($url, $request->query()),
    //             'POST' => $http->post($url, $request->all()),
    //             'PUT' => $http->put($url, $request->all()),
    //             'DELETE' => $http->delete($url, $request->all()),
    //             default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
    //         };
    //     } catch (\Exception $e) {
    //         return $this->errorResponse(
    //             'Internal error from external service',
    //             $e->getMessage(),
    //             503
    //         );
    //     }
    // }
    protected function forwardRequest(string $method, string $baseUrl, string $path, Request $request, int $successCode = 200)
    {
        try {
            $headers = [
                'Accept'     => 'application/json',
                'User-Ip'    => $request->ip(),
                'User-Agent' => $request->userAgent(),
            ];

            $url  = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
            $http = Http::withHeaders($headers);

            $hasFile = $request->hasFile('image');

            // Fayl bo‘lsa, multipart formdata bilan POST qilamiz, methodni ichida uzatamiz
            if (strtoupper($method) === 'PUT' && $hasFile) {
                $http = $http->asMultipart();

                $multipartData = collect($request->except('image'))
                    ->map(fn($value, $key) => ['name' => $key, 'contents' => $value])
                    ->values()
                    ->toArray();

                // Faylni qo‘shamiz
                if ($request->file('image') instanceof UploadedFile) {
                    $multipartData[] = [
                        'name'     => 'image',
                        'contents' => fopen($request->file('image')->getPathname(), 'r'),
                        'filename' => $request->file('image')->getClientOriginalName(),
                    ];
                }

                // Laravel Http clientda put multipart to‘g‘ridan ishlamaydi, shuning uchun POST + _method
                $multipartData[] = [
                    'name'     => '_method',
                    'contents' => 'PUT',
                ];

                return $http->post($url, $multipartData);
            }

            // Fayl yo‘q, oddiy PUT, POST, GET, DELETE
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