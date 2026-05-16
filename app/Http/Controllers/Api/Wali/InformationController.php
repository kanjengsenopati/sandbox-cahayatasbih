<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Information;
use Illuminate\Http\Request;

class InformationController extends BaseWaliApiController
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 3);

        $informations = Information::with('informationCategory')
            ->where('is_active', true)
            ->latest()
            ->paginate($perPage);
            
        return response()->json($informations);
    }

    public function show($id)
    {
        $information = Information::with('informationCategory')->findOrFail($id);

        return response()->json(['data' => $information]);
    }
}
