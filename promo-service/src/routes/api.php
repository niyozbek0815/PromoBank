<?php

use App\Http\Controllers\Admin\BannersController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\MessagesController;
use App\Http\Controllers\Admin\PlatformPromoSettingsController;
use App\Http\Controllers\Admin\PrizeCategoryController;
use App\Http\Controllers\Admin\PrizeController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\PromotionProductController;
use App\Http\Controllers\Admin\PromotionShopController;
use App\Http\Controllers\Admin\SelesReceiptController;
use App\Http\Controllers\Admin\SocialMediaController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PromotionController as FrontendPromotionController;
use App\Http\Controllers\Mobil\BannerController;
use App\Http\Controllers\Mobil\PromoballControlller;
use App\Http\Controllers\Mobil\PromoController;
use App\Http\Controllers\Mobil\ReceiptController;
use App\Http\Controllers\WebApp\PlatformPromoSettingsController as WebAppPlatformPromoSettingsController;
use App\Http\Controllers\WebApp\PromotionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('frontend')->name('frontend.')->group(function () {
    Route::post('/', [HomeController::class, 'index'])->name('home');

    Route::controller(FrontendPromotionController::class)
        ->group(function () {
            Route::post('/promotion/{id}', 'show');
        });

    Route::controller(PromotionsController::class)->prefix('promotion')->name('promotions.')->group(function () {
        Route::post('{promotion}/promocode', 'viaPromocode')->name('viaPromocode');
        Route::post('{promotion}/receipt', 'viaReceipt')->name('viaReceipt');
    });
});

Route::controller(PromoController::class)->prefix('promotions')->group(function () {
    // Route::get('/', 'index');
    Route::get('/', 'index');
    Route::post('/{promotion}/participate/promocode', 'viaPromocode');
    // Route::post('/{promotion}/participate/receipt', 'viaReceipt');
    Route::post('/{promotion}/participations', 'listParticipationHistory');
});
Route::controller(ReceiptController::class)->prefix('receipt')->group(function () {
    Route::post('/', 'index');
    Route::post('/user_points', 'points');
});
Route::controller(BannerController::class)->prefix('banners')->group(function () {
    Route::get('/', 'index');
});
Route::prefix('webapp')->name('webapp.')->group(function () {
    Route::get('/platform-promo-settings', [WebAppPlatformPromoSettingsController::class, 'index'])
        ->name('platform-promo-settings.index');
    Route::post('/add-points-to-user', [WebAppPlatformPromoSettingsController::class, 'addPointsToUser'])
        ->name('platform-promo-settings.add-points');
    Route::post('/add-points-to-user_register', [WebAppPlatformPromoSettingsController::class, 'addPointsToUserRegister'])
        ->name('platform-promo-settings.add-points_register');
});
Route::controller(PromoballControlller::class)->prefix('promoball')->group(function () {
    // Route::get('/', 'index');
    Route::post('/game-rating', 'gameRating');
    Route::post('/my-game-points', 'myGamePoints');
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
            Route::get("/gettypes", 'getTypes')->name('gettypes');
        });
    Route::controller(PromoCodeController::class)
        ->prefix('promocode')
        ->name('admin.promocode.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data'); // GET /promocode/data
            Route::get('/create/{promotion_id?}', 'create')->name('create');
            Route::get('/{promotion}/show', 'show')->name('show');
            Route::post('/{promotion}/generate', 'generatePromoCodes')->name('generate');
            Route::post('/{promotion}/import', 'importPromoCodes')->name('import');
            Route::post('/{promotion}/store', 'storePromoCodes')->name('store');
            Route::get('{promotion}/promocode-settings', 'showPromocodeSettingsForm')->name('settings.form');
            Route::post('{promotion}/promocode-settings', "updatePromocodeSettings")->name('settings.update');
            Route::get('/{promotion}/generatedata', 'generateData')->name('generateData'); // AJAX uchun server-side table
            Route::get('/{generate}/showgenerate', 'generateShow')->name('generateshow');
            Route::get('/{promotion}/generate/promocodedata', 'generatePromocodeData')->name('generate.promocodedata'); // AJAX uchun server-side table
            Route::get('/{promotion}/promocodedata', 'promocodeData')->name('promocodedata');                           // AJAX uchun server-side table
            Route::get('/{promotion}/prizedata', 'prizeData')->name('prizedata');
            Route::get('/{prize}/autobinddata', 'autobindData')->name('autobindData'); // AJAX uchun server-side table
            Route::get('/{promotion}/search', 'searchPromocodes')->name('search');
        });
    Route::prefix('prize-category')
        ->name('admin.prize-category.')
        ->controller(PrizeCategoryController::class)
        ->where(['type' => 'manual|smart_random|auto_bind|weighted_random'])
        ->group(function () {
            Route::get('{promotion}/type/{type}', 'show')->name('show');
            Route::get('{promotion}/type/{type}/data', 'data')->name('data');
        });
    Route::prefix('prize')
        ->name('admin.prize.')
        ->controller(PrizeController::class)
        ->group(function () {
            // Route::get('/', 'index')->name('index');
            Route::get('/data', 'prizeData')->name('data');
            Route::get('/{prize}/status', 'changeStatus')->name('status');
         Route::get('/{prize}/edit', 'edit')->name('edit');
            Route::match(['POST', 'PUT'], '/{prize}/update', 'update')->name('update');
            Route::post('/{prize}/delete', 'delete')->name('delete');
            Route::post('/{prize}/message', 'storeMessage')->name('message.store');
            Route::post('/{prize}/smartrules', 'storeRules')->name('smartrules.updateOrCreate');
            Route::post('/{prize}/autobind', 'autobind')->name('attachPromocodes');
            Route::post('/{prize}/autobind/{promocodeId}', 'autobindDelete')->name('detachPromocodes');
            Route::get('/{prize}/actions-data', 'actionsData')->name('actionsData');
            Route::prefix('category/{category}/promotion/{promotion}')
                ->group(function () {
                    Route::get('/create', 'createByCategory')->name('createByCategory');
                    Route::post('/store', 'storeByCategory')->name('storeByCategory');
                    Route::post('/import', 'importByCategory')->name('importByCategory');
                });
        });
    Route::prefix('prize_messages')
        ->name('prize_messages.')
        ->controller(MessagesController::class)
        ->group(function () {
            Route::get('/data/{id}', 'prizeMessagesData')->name('data');
            Route::get('/{id}/generate', 'prizeGenerate')->name('generate');
        });
    Route::prefix('promotion_shops')
        ->name('admin.promotion_shops.')
        ->controller(PromotionShopController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get("/data", 'data')->name('data');
            Route::get('/create/{promotion_id?}', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::get("/{promotion_id}/promotion_data", 'promotiondata')->name('promotion_data');
        });
    Route::prefix('promotion_products')
        ->name('admin.promotion_products.')
        ->controller(PromotionProductController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create/{shop_id?}', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::get("/{shop_id}/promotion_data", 'promotiondata')->name('promotion_data');
            Route::post('/{id}/change_status', 'changeStatus')->name('change_status');
        });
    Route::prefix('sales-receipts')
        ->name('admin.sales_receipts.')
        ->controller(SelesReceiptController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/{promotion_id}/winning', 'winningByPromotion')
                ->name('winning_by_promotion');
            Route::get('/show/{id}', 'show')->name('show');
        });
    Route::prefix('banners')->name('admin.banners.')
        ->controller(BannersController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::post('/{banner}/status', 'changeStatus')->name('status');
            Route::post('/{banner}/delete', 'destroy')->name('delete');
            Route::get('/{banner}/edit', 'edit')->name('edit');
            Route::put('/{banner}', 'update')->name('update');
        });

    Route::prefix('promotion_messages')
        ->name('promotion_messages.')
        ->controller(MessagesController::class)
        ->group(function () {
            Route::get('/data/{id}', 'promotionMessagesData')->name('data');
            Route::get('/{id}/generate', 'promotionGenerate')->name('generate');
        });
    Route::prefix('settings')
        ->name('settings.')
        ->group(function () {
            Route::prefix('messages')
                ->name('messages.')
                ->controller(MessagesController::class)
                ->group(function () {
                    Route::get('/data', 'data')->name('data');
                    Route::get('/{id}/edit', 'edit')->name('edit');
                    Route::match(['put', 'patch'], '/{id}', 'update')->name('update');
                });

            Route::controller(PlatformPromoSettingsController::class)
                ->prefix('platform-promoball')
                ->name('platform-promoball.')
                ->group(function () {
                    Route::get('edit', 'edit')->name('edit');
                    Route::put('{id}', 'update')->name('update');
                });
        });
});
