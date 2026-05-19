<?php

namespace {
    if (!class_exists('finfo')) {
        class finfo {
            public function __construct($options = null, $arg = null) {}
            public function file($filename, $options = null, $context = null) {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                return match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'svg' => 'image/svg+xml',
                    'pdf' => 'application/pdf',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'txt' => 'text/plain',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed',
                    default => 'application/octet-stream',
                };
            }
            public function buffer($string, $options = null, $context = null) {
                return 'application/octet-stream';
            }
        }
    }
}

namespace App\Providers {
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

            // Register Symfony Mime Fallback Guesser to prevent LogicException when php_fileinfo is not enabled
            if (class_exists(\Symfony\Component\Mime\MimeTypes::class)) {
                \Symfony\Component\Mime\MimeTypes::getDefault()->registerGuesser(new \App\Validation\FallbackMimeTypeGuesser());
            }
        }
    }
}
