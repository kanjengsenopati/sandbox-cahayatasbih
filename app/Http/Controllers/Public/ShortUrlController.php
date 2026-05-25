<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ShortenedUrl;
use Illuminate\Http\Request;

class ShortUrlController extends Controller
{
    public function show($code)
    {
        $shortUrl = ShortenedUrl::where('code', $code)->first();

        if (!$shortUrl) {
            abort(404, 'URL tidak ditemukan');
        }

        return redirect()->away($shortUrl->original_url);
    }
}
