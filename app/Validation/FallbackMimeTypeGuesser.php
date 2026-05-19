<?php

namespace App\Validation;

use Symfony\Component\Mime\MimeTypeGuesserInterface;

class FallbackMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * Returns true if this guesser is supported.
     */
    public function isSupported(): bool
    {
        return true;
    }

    /**
     * Guesses the MIME type of the file with the given path using magic bytes.
     */
    public function guessMimeType(string $path): ?string
    {
        if (!is_file($path) || !is_readable($path)) {
            return 'application/octet-stream';
        }

        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return 'application/octet-stream';
        }
        $bytes = @fread($handle, 12);
        @fclose($handle);

        if ($bytes === false || strlen($bytes) < 4) {
            return 'application/octet-stream';
        }

        // Check PNG: 89 50 4E 47
        if (str_starts_with($bytes, "\x89PNG")) {
            return 'image/png';
        }

        // Check JPEG: FF D8 FF
        if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return 'image/jpeg';
        }

        // Check GIF: 47 49 46 38
        if (str_starts_with($bytes, "GIF8")) {
            return 'image/gif';
        }

        // Check PDF: 25 50 44 46
        if (str_starts_with($bytes, "%PDF")) {
            return 'application/pdf';
        }

        // Check ZIP / Office Docs: 50 4B 03 04
        if (str_starts_with($bytes, "PK\x03\x04")) {
            return 'application/zip';
        }

        // Check WebP: RIFFxxxxWEBP
        if (str_starts_with($bytes, "RIFF") && substr($bytes, 8, 4) === "WEBP") {
            return 'image/webp';
        }

        return 'application/octet-stream';
    }
}
