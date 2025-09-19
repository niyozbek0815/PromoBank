<?php

use App\Http\Controllers\Admin\AboutsController;
use App\Http\Controllers\Admin\BenefitController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\ForSponsorsController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SocialsController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Telegram\TelegramController as TelegramTelegramController;
use Illuminate\Support\Facades\Route;

Route::prefix('frontend')->name('frontend.')
    ->controller(HomeController::class)
    ->group(function () {
        Route::post('/', 'index')->name('home');
        Route::post('/pages', 'pages')->name('pages');
    });

Route::prefix('/admin')->name('admin.')->group(
    function () {
        Route::prefix('sponsors')
            ->name('sponsors.')
            ->controller(SponsorController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::post('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::post('/{id}/delete', 'destroy')->name('destroy');
                Route::post('/{id}/status', 'changeStatus')->name('status');
            });
        Route::prefix('benefits')
            ->name('benefits.')
            ->controller(BenefitController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::post('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::post('/{id}/delete', 'destroy')->name('destroy');
                Route::post('/{id}/status', 'changeStatus')->name('status');
            });
        Route::prefix('portfolio')
            ->name('portfolio.')
            ->controller(PortfolioController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::post('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::post('/{id}/delete', 'destroy')->name('destroy');
                Route::post('/{id}/status', 'changeStatus')->name('status');
            });
        Route::prefix('forsponsor')
            ->name('forsponsor.')
            ->controller(ForSponsorsController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::post('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::post('/{id}/delete', 'destroy')->name('destroy');
                Route::post('/{id}/status', 'changeStatus')->name('status');
            });
        Route::prefix('socials')
            ->name('socials.')
            ->controller(SocialsController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::post('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::post('/{id}/delete', 'destroy')->name('destroy');
                Route::post('/{id}/status', 'changeStatus')->name('status');
            });
        Route::prefix('contacts')
            ->name('contacts.')
            ->controller(ContactController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::post('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::post('/{id}/delete', 'destroy')->name('destroy');
                Route::post('/{id}/status', 'changeStatus')->name('status');
            });
        Route::prefix('settings')
            ->name('settings.')
            ->controller(SettingsController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/edit', 'edit')->name('edit');
                Route::put('/update', 'update')->name('update');
            });
        Route::prefix('downloads')
            ->name('downloads.')
            ->controller(DownloadController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/edit', 'edit')->name('edit');
                Route::put('/update', 'update')->name('update');
            });
        Route::prefix('abouts')
            ->name('abouts.')
            ->controller(AboutsController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/edit', 'edit')->name('edit');
                Route::put('/update', 'update')->name('update');
            });
    }
);
Route::prefix('/telegram')->name('telegram.')
    ->controller(TelegramTelegramController::class)
    ->group(function () {
        Route::post('/social_links', 'index')->name('socials');
    });
