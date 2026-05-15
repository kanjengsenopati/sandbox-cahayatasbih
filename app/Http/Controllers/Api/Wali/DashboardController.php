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
        $todaySummary = ['count' => 0, 'in' => 0, 'out' => 0];
        if ($activeStudent) {
            $tahfidzCount = Tahfidz::where('student_id', $activeStudent->id)->sum('number_of_pages');
            $studyCount = StudyGrade::where('student_id', $activeStudent->id)->distinct('study_id')->count();

            $saldoHistories = \App\Models\SaldoHistory::where('student_id', $activeStudent->id)
                ->whereDate('created_at', now()->toDateString())
                ->latest()
                ->get()
                ->map(function($item) {
                    return [
                        'type' => $item->type === 'IN' ? 'IN' : 'OUT',
                        'amount' => $item->amount,
                        'note' => $item->description ?? ($item->type === 'IN' ? 'Topup Saldo' : 'Pengeluaran Saldo'),
                        'status' => $item->status,
                        'created_at' => $item->created_at
                    ];
                });

            $posTransactions = \App\Models\PointOfSaleTransaction::with(['pointOfSaleTransactionDetails.item', 'admins'])
                ->where('student_id', $activeStudent->id)
                ->where('status', 'SUCCESS')
                ->whereDate('created_at', now()->toDateString())
                ->latest()
                ->get()
                ->map(function($item) {
                    // Build item names from details
                    $itemNames = $item->pointOfSaleTransactionDetails
                        ->map(fn($d) => $d->item->name ?? 'Item')
                        ->take(2)
                        ->join(', ');
                    $totalItems = $item->pointOfSaleTransactionDetails->count();
                    if ($totalItems > 2) {
                        $itemNames .= ' +' . ($totalItems - 2) . ' lainnya';
                    }

                    return [
                        'type' => 'OUT',
                        'amount' => $item->pay_amount,
                        'note' => $itemNames ?: 'Belanja Kantin',
                        'merchant' => $item->admins->name ?? null,
                        'items_count' => $totalItems,
                        'created_at' => $item->paid_at ?? $item->created_at
                    ];
                });

            $recentTransactions = $saldoHistories->concat($posTransactions)->sortByDesc('created_at')->values();
            
            $todaySummary = [
                'count' => $recentTransactions->count(),
                'in' => $saldoHistories->where('type', 'IN')->sum('amount'),
                'out' => $saldoHistories->where('type', 'OUT')->sum('amount') + $posTransactions->sum('amount'),
            ];
        }

        // Check for Unit Transfer Availability
        // Hanya tampilkan jika siswa berada di kelas yang sesuai (misal IX, XII)
        $unitTransfer = null;
        if ($activeStudent && $activeStudent->classroom) {
            $classroomName = $activeStudent->classroom->name ?? '';
            
            $unitTransfer = \App\Models\UnitTransferConfig::with(['fromSchool', 'toSchool', 'toClassroom', 'billType'])
                ->where('from_school_id', $activeStudent->school_id)
                ->where('is_active', true)
                ->where(function ($query) use ($classroomName) {
                    $query->whereNull('eligible_class_level')
                          ->orWhere('eligible_class_level', '')
                          ->orWhereRaw('? LIKE CONCAT(\'%\', eligible_class_level, \'%\')', [$classroomName]);
                })
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
            'todaySummary' => $todaySummary,
            'unit_transfer' => $unitTransfer,
        ]);
    }
}
