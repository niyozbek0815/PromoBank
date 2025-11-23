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
        // faqat local muhitda Telescope’ni ro‘yxatga olish
 
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
   

        Carbon::setLocale('uz');
        Carbon::now()->setTimezone('Asia/Tashkent');

    }
}
