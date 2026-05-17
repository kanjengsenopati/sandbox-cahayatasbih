<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Officer;

class OfficerController extends BaseWaliApiController
{
    /**
     * Display a listing of active officers.
     */
    public function index()
    {
        $student = $this->resolveActiveStudent();
        $officers = Officer::where('is_active', true)->latest()->get();

        return response()->json([
            'student_name' => $student ? $student->name : 'Santri',
            'data' => $officers,
        ]);
    }
}
