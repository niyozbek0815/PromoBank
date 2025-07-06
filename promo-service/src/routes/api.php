<?php

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Mobil\PromoController;
use App\Http\Controllers\Mobil\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::controller(PromoController::class)->prefix('promotions')->group(function () {
    // Route::get('/', 'index');
    Route::get('/', 'index');
    Route::post('/{promotion}/participate/promocode', 'viaPromocode');
    Route::post('/{promotion}/participate/receipt', 'viaReceipt');
    Route::post('/{promotion}/participations', 'listParticipationHistory');
});
Route::controller(ReceiptController::class)->prefix('receipt')->group(function () {
    Route::post('/', 'index');
    Route::post('/user_points', 'points');
});

Route::prefix('front')->group(function () {

    Route::controller(CompanyController::class)->prefix('/company')->group(function () {
        Route::get('/data', [CompanyController::class, 'data'])->name('admin.users.data');
        Route::post('/{id}/edit', [CompanyController::class, 'edit']);
        Route::post('/{id}/delete', [CompanyController::class, 'delete']);
        Route::post('/{id}/status', [CompanyController::class, 'changeStatus']);
        Route::put('/{id}/update', [CompanyController::class, 'update']);
    });

});
