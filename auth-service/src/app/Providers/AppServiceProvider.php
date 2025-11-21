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
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
            $this->app->singleton(SmsSendService::class, function ($app) {
                return new SmsSendService();
            });
        }
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
