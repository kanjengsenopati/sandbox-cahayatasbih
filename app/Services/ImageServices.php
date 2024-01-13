<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;



class ImageServices
{
    public static function compress($image, $path, $width = 1500)
    {
        // get image size in bytes
        $size = $image->getSize();
        
        if ($size < 1000000) {
            $quality = 50;
        }
        // if image size is greater than 1MB and less than 2MB $quality = 40
        if ($size > 1000000 && $size < 2000000) {
            $quality = 30;
        }
        // if image size is greater than 2MB and less than 3MB $quality = 30
        if ($size > 2000000 && $size < 3000000) {
            $quality = 20;
        }
        // if image size is greater than 3MB and less than 4MB $quality = 20
        if ($size > 3000000 && $size < 4000000) {
            $quality = 15;
        }
        // if image size is greater than 4MB and less than 5MB $quality = 10
        if ($size > 4000000 && $size < 5000000) {
            $quality = 10;
        }
        // if image size is greater than 5MB $quality = 5
        if ($size > 5000000) {
            $quality = 5;
        }

        // resize image use intervention image 1200 x 630 pixels
        $image = Image::make($image->getRealPath())->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $name = time() . '.webp';

        // check if directory not exist, if not create directory
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0777, true);
        }
        $image->encode('webp', $quality)->save(public_path($path . $name));

        if ($quality <= 8) {
            // compress again with intervention image optimizer
            Image::make(public_path($path . $name))->encode('webp', 8)->save(public_path($path . $name));
        }

        return $path . $name;
    }
}