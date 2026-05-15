<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Information;
use App\Models\Student;
use App\Models\Tahfidz;
use App\Models\StudyGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseWaliApiController
{
    public function index()
    {
        $user = Auth::guard('wali')->user();
        
        $informations = Information::with('informationCategory')
            ->where('is_active', true)
            ->latest()
            ->take(5)
            ->get();
            
        $students = Student::where('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get();
            
        $activeStudent = $this->resolveActiveStudent();

        $recentTransactions = collect();
        if ($activeStudent) {
            $tahfidzCount = Tahfidz::where('student_id', $activeStudent->id)->sum('number_of_pages');
            $studyCount = StudyGrade::where('student_id', $activeStudent->id)->distinct('study_id')->count();

            $saldoHistories = \App\Models\SaldoHistory::where('student_id', $activeStudent->id)
                ->where('status', 'SUCCESS')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'type' => $item->type === 'IN' ? 'IN' : 'OUT',
                        'amount' => $item->amount,
                        'note' => $item->description ?? 'Topup Saldo',
                        'created_at' => $item->created_at
                    ];
                });

            $posTransactions = \App\Models\PointOfSaleTransaction::where('student_id', $activeStudent->id)
                ->where('status', 'SUCCESS')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'type' => 'OUT',
                        'amount' => $item->pay_amount,
                        'note' => 'Belanja Kantin',
                        'created_at' => $item->paid_at ?? $item->created_at
                    ];
                });

            $recentTransactions = $saldoHistories->concat($posTransactions)->sortByDesc('created_at')->take(5)->values();
        }

        // Check for Unit Transfer Availability
        $unitTransfer = null;
        if ($activeStudent) {
            $unitTransfer = \App\Models\UnitTransferConfig::with(['fromSchool', 'toSchool', 'toClassroom', 'billType.billItem'])
                ->where('from_school_id', $activeStudent->school_id)
                ->where('is_active', true)
                ->first();
        }

        return response()->json([
            'user' => $user,
            'informations' => $informations,
            'students' => $students,
            'activeStudent' => $activeStudent,
            'tahfidzCount' => (int) $tahfidzCount,
            'studyCount' => (int) $studyCount,
            'recentTransactions' => $recentTransactions,
            'unit_transfer' => $unitTransfer,
        ]);
    }
}
