<?php

namespace App\Providers;

use App\Models\Promotions;
use App\Observers\PromotionObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set('Asia/Tashkent');
        config(['app.timezone' => 'Asia/Tashkent']);
        Carbon::setLocale('uz');
    }
}