<?php

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SocialMediaController;
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
        Route::get('/data', [CompanyController::class, 'data'])->name('admin.company.data');
        Route::post('/store', [CompanyController::class, 'store'])->name('admin.company.store');
        Route::post('/{id}/edit', [CompanyController::class, 'edit']);
        Route::post('/{id}/delete', [CompanyController::class, 'delete']);
        Route::post('/{id}/status', [CompanyController::class, 'changeStatus']);
        Route::put('/{id}/update', [CompanyController::class, 'update']);
    });
    Route::controller(SocialMediaController::class)
        ->prefix('socialcompany')
        ->as('admin.socialcompany.')
        ->group(function () {
            Route::get('/{id}/data', 'data')->name('data');        // admin.socialcompany.data
            Route::post('/store', 'store')->name('store');         // admin.socialcompany.store
            Route::post('/{id}/delete', 'delete')->name('delete'); // admin.socialcompany.update
        });

    Route::controller(PromotionController::class)
        ->prefix('promotion')
        ->name('admin.promotion.')
        ->group(function () {
            Route::get('/{id}/data', 'companydata')->name('companydata'); // AJAX uchun server-side table
            Route::get('/data', 'data')->name('data');
            Route::get('/{id}/edit', 'edit')->name('edit'); // GET — edit form
            Route::post('/', 'store')->name('store');       // POST — create
            Route::match(['POST', 'PUT'], '/{id}', [PromotionController::class, 'update'])->name('update');
            Route::post('{id}/delete', 'delete')->name('delete');        // admin.socialcompany.delete
            Route::post('/{id}/status', 'changeStatus')->name('status'); // POST — toggle status
            Route::post('/{id}/public', 'changePublic')->name('public'); // POST — toggle status
            Route::get('/create', 'create')->name('create');
            Route::post('/{promotion}/participant-type/{participant_type}/update', 'updateParticipantType')->name('participant-type.update');
            Route::post('{promotion}/platform/{platform}/update', 'updatePlatform')->name('platform.update');
        });
    Route::controller(PromoCodeController::class)
        ->prefix('promocode')
        ->name('admin.promocode.')
        ->group(function () {
            Route::get('/create/{promotion_id?}', 'create')->name('create');
            Route::post('/{promotion}/generate', 'generatePromoCodes')->name('generate');
            Route::post('/{promotion}/import', 'importPromoCodes')->name('import');
            Route::get('{promotion}/promocode-settings', 'showPromocodeSettingsForm')->name('settings.form');
            Route::post('{promotion}/promocode-settings', "updatePromocodeSettings")->name('settings.update');
            Route::get('/{promotion}/generatedata', 'generateData')->name('generateData'); // AJAX uchun server-side table
            Route::get('/{generate}/showgenerate', 'generateShow')->name('generateshow');
            Route::get('/{promotion}/generate/promocodedata', 'generatePromocodeData')->name('generate.promocodedata'); // AJAX uchun server-side table
            Route::get('/{promotion}/promocodedata', 'promocodeData')->name('promocodedata'); // AJAX uchun server-side table
        });

});
