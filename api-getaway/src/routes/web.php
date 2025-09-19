<?php

use App\Http\Controllers\Admin\AboutsController;
use App\Http\Controllers\Admin\BannersController;
use App\Http\Controllers\Admin\BenefitController;
use App\Http\Controllers\Admin\Company\CompanyController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\ForSponsorsController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\PrizeCategoryController;
use App\Http\Controllers\Admin\PrizeController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\PromotionProductController;
use App\Http\Controllers\Admin\PromotionShopController;
use App\Http\Controllers\Admin\Promo\PromotionController;
use App\Http\Controllers\Admin\SelesReceiptController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SocialMediaController;
use App\Http\Controllers\Admin\SocialsController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PromotionController as FrontendPromotionController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::prefix('promotion')->name('promotion.')->group(function () {
    Route::get('/{promotion}', [FrontendPromotionController::class, 'show'])->name('show');
});
Route::get('/lang/{locale}', function ($locale) {
    $available = ['uz', 'ru', 'kr']; // mavjud tillar
    if (in_array($locale, $available)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return back(); // o‘sha sahifaga qaytadi
})->name('changeLang');
Route::get('/media/uploads/{context}/{fileName}', function ($context, $fileName) {
    $mediaServiceUrl = config('services.urls.media_service') . "/uploads/{$context}/{$fileName}";

    $response = Http::timeout(10)->get($mediaServiceUrl);

    if ($response->ok()) {
        return Response::make($response->body(), 200, [
            'Content-Type'        => $response->header('Content-Type') ?? 'application/octet-stream',
            'Content-Disposition' => $response->header('Content-Disposition') ?? 'inline',
        ]);
    }

    abort(404, 'File not found.');
})->name('media.proxy');

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
        Route::put('/{id}', 'update')->name('update');
        Route::post('{promotion}/participant-type/{participant_type}/update', 'updateParticipantType')->name('participant-type.update');
        Route::post('{promotion}/platform/{platform}/update', 'updatePlatform')->name('platform.update');
    });
    Route::prefix('promocode')->name('admin.promocode.')->controller(PromoCodeController::class)->group(function () {
        Route::get('/', 'index')->name('index');                         // GET /promocode
        Route::get('/data', 'data')->name('data');                       // GET /promocode/data
        Route::get('/create/{promotion_id?}', 'create')->name('create'); // GET /promocode/create
        Route::post('/{promotion}/generate', 'generatePromoCodes')->name('generate');
        Route::post('/{promotion}/import', 'importPromoCodes')->name('import');
        Route::get('/{promotion}/promocode-settings', 'showPromocodeSettingsForm')->name('settings.form');
        Route::post('/{promotion}/promocode-settings', "updatePromocodeSettings")->name('settings.update');
        Route::get('/{promotion}/generatedata', 'generateData')->name('generatedata'); // AJAX uchun server-side table
        Route::get('/{generate}/showgenerate', 'generateShow')->name('generateshow');
        Route::get('/{promotion}/generate/promocodedata', 'generatePromocodeData')->name('generate.promocodedata'); // AJAX uchun server-side table
        Route::get('/{promotion}/promocodedata', 'promocodeData')->name('promocodedata');
        Route::get('/{prize}/prizedata', 'prizeData')->name('prizedata');
        Route::get('/{prize}/autobinddata', 'autobindData')->name('autobindData');
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
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/{prize}/status', 'changeStatus')->name('status');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{prize}/edit', 'edit')->name('edit');
            Route::put('/{prize}', 'update')->name('update');
            Route::get('/{prize}/delete', 'delete')->name('delete');
            Route::post('/{prize}/message', 'storeMessage')->name('message.store');
            Route::post('/{prize}/smartrules', 'storeRules')->name('smartrules.updateOrCreate');
            Route::post('/{prize}/smartrules/{rule}/delete', 'deleteRule')->name('smartrules.delete');
            Route::post('/{prize}/autobind', 'autobind')->name('attachPromocodes');
            Route::post('/{prize}/autobind/{promocodeId}', 'autobindDelete')->name('detachPromocodes');
        });
    Route::prefix('promotion_shops')
        ->name('admin.promotion_shops.')
        ->controller(PromotionShopController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
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
    Route::prefix('seles_receipts')->name('admin.seles_receipts.')
        ->controller(SelesReceiptController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/{promotion_id}/promotion_receipt', 'wonPromotionSelesReceipts')->name('won_seles_receipts');
        });
    Route::prefix('banners')->name('admin.banners.')
        ->controller(BannersController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{type}/urls', 'getUrls')->name('getUrls');
            Route::post('/{banner}/status', 'changeStatus')->name('status');
            Route::post('/{banner}/delete', 'destroy')->name('delete');
            Route::get('/{banner}/edit', 'edit')->name('edit');
            Route::put('/{banner}', 'update')->name('update');
        });
    Route::prefix('notifications')->name('admin.notifications.')
        ->controller(NotificationsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{banner}/edit', 'edit')->name('edit');
            Route::put('/{banner}', 'update')->name('update');
            Route::post('/{banner}/delete', 'destroy')->name('delete');
            Route::get('/{notification}/resend', 'resent')->name('resend');
            Route::get('/{type}/urls', 'getUrls')->name('getUrls');
            Route::get('/users', 'getUsers')->name('getUsers');
        });



    // web sayt routes

    Route::prefix('sponsors')
        ->name('admin.sponsors.')
        ->controller(SponsorController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{sponsor}/edit', 'edit')->name('edit');
            Route::put('/{sponsor}', 'update')->name('update');
            Route::post('/{sponsor}/delete', 'destroy')->name('delete');
            Route::post('/{sponsor}/status', 'changeStatus')->name('status');
        });
    Route::prefix('benefits')
        ->name('admin.benefits.')
        ->controller(BenefitController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{benefit}/edit', 'edit')->name('edit');
            Route::put('/{benefit}', 'update')->name('update');
            Route::post('/{benefit}/delete', 'destroy')->name('delete');
            Route::post('/{benefit}/status', 'changeStatus')->name('status');
        });
    Route::prefix('portfolio')
        ->name('admin.portfolio.')
        ->controller(PortfolioController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/delete', 'destroy')->name('delete');
            Route::post('/{id}/status', 'changeStatus')->name('status');
        });
    Route::prefix('forsponsor')
        ->name('admin.forsponsor.')
        ->controller(ForSponsorsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/delete', 'destroy')->name('delete');
            Route::post('/{id}/status', 'changeStatus')->name('status');
        });
    Route::prefix('socials')
        ->name('admin.socials.')
        ->controller(SocialsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/delete', 'destroy')->name('delete');
            Route::post('/{id}/status', 'changeStatus')->name('status');
        });
    Route::prefix('contacts')
        ->name('admin.contacts.')
        ->controller(ContactController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/delete', 'destroy')->name('delete');
            Route::post('/{id}/status', 'changeStatus')->name('status');
        });
    Route::prefix('settings')
        ->name('admin.settings.')
        ->controller(SettingsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit', 'edit')->name('edit');
        Route::put('/update', 'update')->name('update');
    });
    Route::prefix('downloads')
        ->name('admin.downloads.')
        ->controller(DownloadController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit', 'edit')->name('edit');
            Route::put('/update', 'update')->name('update');
        });
    Route::prefix('abouts')
        ->name('admin.abouts.')
        ->controller(AboutsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit', 'edit')->name('edit');
            Route::put('/update', 'update')->name('update');
        });
});
Route::get('/fcm-test', function () {
    return view('fcm-test');
})->name('fcm-test');
