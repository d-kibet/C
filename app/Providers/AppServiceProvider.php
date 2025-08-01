<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\RobermsService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Make RobermsService a singleton
    $this->app->singleton(RobermsService::class, function($app) {
        return new RobermsService();
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
