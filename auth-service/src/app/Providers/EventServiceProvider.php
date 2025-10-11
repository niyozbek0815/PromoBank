<?php
namespace App\Providers;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // misol:
        // 'eloquent.updated: App\Models\User' => [
        //     \App\Listeners\UserChangedListener::class,
        // ],
    ];

    public function boot(): void
    {
        User::observe(UserObserver::class);

    }
}
