<?php

use App\Jobs\TestRabbitMQJob;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/media/uploads/{context}/{fileName}', function ($context, $fileName) {
    // Media-service URL'ini shakllantirish
    $mediaServiceUrl = config('services.urls.media_service') . "/uploads/{$context}/{$fileName}";

    // Media-service'dan faylni olish
    $response = Http::get($mediaServiceUrl);

    // Agar faylni olish muvaffaqiyatli bo'lsa
    if ($response->ok()) {
        // Faylni qaytarish
        return Response::make($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type') ?: 'application/octet-stream' // MIME tipini tekshirib ko'rish
        ]);
    }

    // Agar fayl topilmasa, 404 xatosi
    abort(404, 'File not found.');
});
