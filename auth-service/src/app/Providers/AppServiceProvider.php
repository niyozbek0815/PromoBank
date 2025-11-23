<?php
namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Services\SmsSendService;
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

        Carbon::setLocale('uz');
        Carbon::now()->setTimezone('Asia/Tashkent');

        User::observe(UserObserver::class);
    }
}
