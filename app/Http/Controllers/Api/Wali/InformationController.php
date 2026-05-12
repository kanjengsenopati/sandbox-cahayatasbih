<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Information;
use Illuminate\Http\Request;

class InformationController extends BaseWaliApiController
{
    public function index()
    {
        $informations = Information::with('informationCategory')
            ->where('is_active', true)
            ->latest()
            ->paginate(10);
            
        return response()->json($informations);
    }
}
