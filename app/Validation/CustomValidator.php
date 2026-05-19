<?php

namespace App\Validation;

use Illuminate\Validation\Validator;
use Illuminate\Http\UploadedFile;

class CustomValidator extends Validator
{
    /**
     * Override the core validateImage rule to gracefully handle missing php_fileinfo extension.
     */
    public function validateImage($attribute, $value)
    {
        if (!extension_loaded('fileinfo')) {
            if ($value instanceof UploadedFile) {
                $ext = strtolower($value->getClientOriginalExtension());
                return in_array($ext, ['jpeg', 'png', 'jpg', 'gif', 'svg', 'webp', 'bmp']);
            }
            return false;
        }

        return parent::validateImage($attribute, $value);
    }

    /**
     * Override the core validateMimes rule to gracefully handle missing php_fileinfo extension.
     */
    public function validateMimes($attribute, $value, $parameters)
    {
        if (!extension_loaded('fileinfo')) {
            if ($value instanceof UploadedFile) {
                $ext = strtolower($value->getClientOriginalExtension());
                return in_array($ext, $parameters);
            }
            return false;
        }

        return parent::validateMimes($attribute, $value, $parameters);
    }
}
