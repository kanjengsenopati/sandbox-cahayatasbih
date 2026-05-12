<?php

namespace App\Providers;

use Carbon\Carbon;
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
        // Hide deprecation warnings from output (PHP 8.3+)
        error_reporting(E_ALL & ~E_DEPRECATED);

        // Mengatur lokal ke bahasa Indonesia
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID');
    }
}
