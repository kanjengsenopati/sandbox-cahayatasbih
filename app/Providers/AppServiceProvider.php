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
        if (!extension_loaded('fileinfo')) {
            Validator::extend('image', function ($attribute, $value, $parameters, $validator) {
                if ($value instanceof UploadedFile) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    return in_array($ext, ['jpeg', 'png', 'jpg', 'gif', 'svg', 'webp', 'bmp']);
                }
                return false;
            });

            Validator::extend('mimes', function ($attribute, $value, $parameters, $validator) {
                if ($value instanceof UploadedFile) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    return in_array($ext, $parameters);
                }
                return false;
            });
        }
    }
}
