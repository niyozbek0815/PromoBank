<?php

namespace App\Providers;

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
         if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
    }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    //   DB::statement("SET TIMEZONE TO 'Asia/Tashkent'");

    // // Carbon uchun ham global timezone
    // config(['app.timezone' => 'Asia/Tashkent']);
    // date_default_timezone_set('Asia/Tashkent');
    // Carbon::setLocale('uz');
    //        Carbon::now()->setTimezone('Asia/Tashkent');

    }
}

