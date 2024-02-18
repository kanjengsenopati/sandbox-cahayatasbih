<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tahfidz;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TahfidzController extends Controller
{
    public function index(Request $request)
    {
        $tahfidzs = Tahfidz::where('student_id', $request->student_id)->latest()->paginate(10);

        return $this->postSuccessResponse("Berhasil Mengambil Data", $tahfidzs);
    }
}
