<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
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
            'Content-Type' => $response->header('Content-Type') ?: 'application/octet-stream', // MIME tipini tekshirib ko'rish
        ]);
    }

    // Agar fayl topilmasa, 404 xatosi
    abort(404, 'File not found.');
});
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login']);
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
Route::middleware('checkadmin')->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    // boshqa admin route’lar
});
