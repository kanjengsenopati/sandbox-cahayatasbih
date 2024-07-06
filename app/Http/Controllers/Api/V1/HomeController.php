<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Information;
use App\Models\Student;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Retrieve the student with the related classroom and school
            $student = Student::with(['classroom', 'school'])->findOrFail($request->input('student_id'));

            // Retrieve active information categories with a limit of 5
            $informations = Information::with('informationCategory')
                ->where('is_active', true)
                ->latest()
                ->limit(5)
                ->get();

            // Retrieve the latest saldo histories for the student with a limit of 5
            $saldoHistories = $student->saldoHistories()
                ->latest()
                ->limit(5)
                ->get();

            // Return a successful response with the retrieved data
            return $this->postSuccessResponse('Berhasil Mengambil Data', [
                'student' => $student,
                'informations' => $informations,
                'saldo_histories' => $saldoHistories,
            ]);
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function information()
    {
        $informations = Information::with('informationCategory')->whereIsActive(true)->latest()->paginate(10);

        return $this->postSuccessResponse("Berhasil Mengambil Data", $informations);
    }

    public function listStudent()
    {
        $students = Student::with('classroom', 'school')->where('user_id', auth()->user()->id)->latest()->get();

        return $this->postSuccessResponse("Berhasil Mengambil Data", $students);
    }
}
