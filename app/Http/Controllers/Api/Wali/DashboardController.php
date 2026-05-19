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

        // Resolve student's school and class level for menu scoping
        $studentSchoolId = $activeStudent?->school_id;
        $studentClassLevel = null;
        if ($activeStudent?->classroom) {
            // Ekstrak jenjang dari nama kelas: "VII-A" → "VII", "IX-B" → "IX", "XII-IPA1" → "XII"
            $classroomName = $activeStudent->classroom->name ?? '';
            if (preg_match('/^(VII|VIII|IX|X{1,2}I{0,2}|I{1,3}V?|[0-9]+)/', strtoupper($classroomName), $matches)) {
                $studentClassLevel = $matches[1];
            }
        }

        $menus = \App\Models\ApplicationMenu::where('status', true)
            ->where(function ($query) use ($studentSchoolId, $studentClassLevel) {
                // Menu global (tanpa scope sama sekali)
                $query->whereDoesntHave('scopes')
                // ATAU menu yang scope-nya cocok dengan santri aktif (logika AND)
                ->orWhereHas('scopes', function ($q) use ($studentSchoolId, $studentClassLevel) {
                    $q->where('school_id', $studentSchoolId)
                      ->where(function ($sq) use ($studentClassLevel) {
                          // Scope tanpa class_level = semua jenjang di unit tsb
                          $sq->whereNull('class_level')
                          // ATAU scope dengan class_level yang cocok
                             ->orWhere('class_level', $studentClassLevel);
                      });
                });
            })
            ->get();

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
            'menus' => $menus,
        ]);
    }
}
