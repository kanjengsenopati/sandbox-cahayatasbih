<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;

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

        // Global fallback validation rules if php_fileinfo extension is not enabled
        Validator::resolver(function($translator, $data, $rules, $messages, $customAttributes) {
            return new \App\Validation\CustomValidator($translator, $data, $rules, $messages, $customAttributes);
        });
    }
}
