<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // WAMP's MySQL build caps index keys at 1000 bytes; 191 chars keeps
        // utf8mb4 unique string indexes within that limit.
        Schema::defaultStringLength(191);
    }
}
