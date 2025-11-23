<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

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
        // DB timezone statement
        DB::statement("SET TIMEZONE TO 'Asia/Tashkent'");

        // PHP Carbon timezone
        config(['app.timezone' => 'Asia/Tashkent']);
        date_default_timezone_set('Asia/Tashkent');
        Carbon::setLocale('uz');

        // Butun modeli datetime ustunlari uchun avtomatik timezone
        Model::retrieved(function ($model) {
            foreach ($model->getDates() as $dateField) {
                if ($model->{$dateField}) {
                    $model->{$dateField} = $model->{$dateField}->setTimezone('Asia/Tashkent');
                }
            }
        });
    }
}
