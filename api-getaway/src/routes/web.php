<?php

use App\Http\Controllers\Admin\Company\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\Promo\PromotionController;
use App\Http\Controllers\Admin\SocialMediaController;
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

    Route::prefix('company')->name('admin.company.')->controller(CompanyController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}/update', 'update')->name('update');
        Route::post('/{id}/delete', 'delete')->name('delete');
        Route::post('/{id}/status', 'changeStatus')->name('status');
    });
    Route::prefix('socialcompany')
        ->controller(SocialMediaController::class)
        ->as('admin.socialcompany.')
        ->group(function () {
            Route::get('/{id}/data', 'data')->name('data');       // admin.socialcompany.data
            Route::post('/{id}', 'store')->name('store');         // admin.socialcompany.store
            Route::post('{id}/delete', 'delete')->name('delete'); // admin.socialcompany.delete
        });

    // PromotionController uchun to'liq RESTful API route'lari
    Route::prefix('promotion')->name('admin.promotion.')->controller(PromotionController::class)->group(function () {
        Route::get('/{id}/data', 'companydata')->name('companydata'); // AJAX uchun server-side table
        Route::get('/', 'index')->name('index');                      // GET /promotion
        Route::get('/{id}/edit', 'edit')->name('edit');               // GET /promotion/{id}/edit
        Route::post('{id}/delete', 'delete')->name('delete');         // admin.socialcompany.delete
        Route::post('/{id}/status', 'changeStatus')->name('status');  // Status toggle
        Route::post('/{id}/public', 'changePublic')->name('public');
        Route::get('/data', 'data')->name('data');                     // AJAX uchun server-side table
        Route::get('/create/{company_id?}', 'create')->name('create'); // GET /promotion/create
        Route::post('/', 'store')->name('store');                      // POST /promotion                                                 // Route::get('/{id}', 'show')->name('show');                    // GET /promotion/{id}
        Route::put('/{id}', 'update')->name('update');                 // PUT /promotion/{id}
    });

});
