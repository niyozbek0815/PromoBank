<?php

use App\Http\Controllers\Admin\Company\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\Promo\PromotionController;
use App\Http\Controllers\Admin\User\UserController;
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
Route::middleware('checkadmin')->prefix('/admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/profile/update', [ProfileController::class, 'updateProfile'])->name('admin.profile.update');

    Route::prefix('/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/data', [UserController::class, 'data'])->name('admin.users.data');
        Route::get('{id}/edit', [UserController::class, 'edit']);
        Route::post('{id}/delete', [UserController::class, 'delete'])->name('admin.users.delete');
        Route::post('{id}/status', [UserController::class, 'changeStatus'])->name('admin.users.status');
        Route::put('{id}/update', [UserController::class, 'update'])->name('admin.users.update');
    });

    Route::get('region/{regionId}/districts', [UserController::class, 'getDistricts'])->name('admin.region.districts');

    Route::prefix('/company')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('admin.company.index');
        Route::get('/data', [CompanyController::class, 'data'])->name('admin.company.data');
        Route::get('{id}/edit', [CompanyController::class, 'edit']);
        Route::post('{id}/delete', [CompanyController::class, 'delete'])->name('admin.company.delete');
        Route::post('{id}/status', [CompanyController::class, 'changeStatus'])->name('admin.company.status');
        Route::put('{id}/update', [CompanyController::class, 'update'])->name('admin.company.update');
    });

    Route::prefix('/promotion')->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('admin.promotion.index');
        Route::get('/data', [PromotionController::class, 'data'])->name('admin.promotion.data');
        Route::get('{id}/edit', [PromotionController::class, 'edit']);
        Route::post('{id}/delete', [PromotionController::class, 'delete'])->name('admin.promotion.delete');
        Route::post('{id}/status', [PromotionController::class, 'changeStatus'])->name('admin.promotion.status');
        Route::put('{id}/update', [PromotionController::class, 'update'])->name('admin.promotion.update');
    });

});