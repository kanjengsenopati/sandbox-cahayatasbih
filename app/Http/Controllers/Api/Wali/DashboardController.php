<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Information;
use App\Models\Student;
use App\Models\Tahfidz;
use App\Models\StudyGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseWaliApiController
{
    public function index()
    {
        $user = Auth::guard('wali')->user();
        
        $informations = \Illuminate\Support\Facades\Cache::remember('wali_dashboard_informations', 1800, function() {
            return Information::with('informationCategory')
                ->where('is_active', true)
                ->latest()
                ->take(5)
                ->get();
        });
            
        $students = Student::where('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get();
            
        $activeStudent = $this->resolveActiveStudent();

        $recentTransactions = collect();
        $todaySummary = ['count' => 0, 'in' => 0, 'out' => 0];
        $tahfidzCount = 0;
        $studyCount = 0;
        if ($activeStudent) {
            $tahfidzCount = Tahfidz::where('student_id', $activeStudent->id)->sum('number_of_pages');
            $studyCount = StudyGrade::where('student_id', $activeStudent->id)->distinct('study_id')->count();

            $saldoHistories = \App\Models\SaldoHistory::with('transaction_details')->where('student_id', $activeStudent->id)
                ->where(function ($q) {
                    $q->whereNotIn('usage', [\App\Models\SaldoHistory::USAGE_POS, \App\Models\SaldoHistory::USAGE_BILL])
                      ->orWhereDoesntHave('pointOfSaleTransaction');
                })
                ->whereNotIn('status', [\App\Models\SaldoHistory::STATUS_FAILED])
                ->where('created_at', '>=', now()->startOfDay())
                ->latest()
                ->get()
                ->reject(function($item) {
                    // Lewati SaldoHistory Kode Unik yang orphaned (tidak punya transaction_detail).
                    // Proses cleanup dan pengembalian saldo dilakukan oleh scheduled command:
                    // php artisan saldo:cleanup-orphaned-kode-unik (berjalan harian)
                    // Ini menjaga idempotency GET request dan mencegah race condition.
                    if ($item->status === \App\Models\SaldoHistory::STATUS_SUCCESS
                        && stripos($item->description, 'Kode Unik') !== false
                        && $item->transaction_details->isEmpty()
                    ) {
                        return true; // reject dari collection, tapi TIDAK mengubah data
                    }
                    return false;
                })
                ->map(function($item) {
                    return [
                        'id' => $item->transaction_details->first()?->transaction_id ?? $item->id,
                        'type' => $item->type === 'IN' ? 'IN' : 'OUT',
                        'amount' => $item->amount,
                        'note' => $item->description ?? ($item->type === 'IN' ? 'Topup Saldo' : 'Pengeluaran Saldo'),
                        'status' => $item->status,
                        'created_at' => $item->created_at,
                        'category' => $item->type === 'IN' ? 'TOPUP' : ($item->type === 'WITHDRAW' ? 'WITHDRAW' : 'SALDO')
                    ];
                });

            $posTransactions = \App\Models\PointOfSaleTransaction::with(['pointOfSaleTransactionDetails.item', 'admins'])
                ->where('student_id', $activeStudent->id)
                ->where('status', 'SUCCESS')
                ->where('created_at', '>=', now()->startOfDay())
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
                        'created_at' => $item->paid_at ?? $item->created_at,
                        'category' => 'POS',
                        'status' => 'SUCCESS'
                    ];
                });

            $billTransactions = \App\Models\Transaction::with(['paymentMethod', 'transactionDetails.bill.billType'])
                ->where('student_id', $activeStudent->id)
                ->where('type', \App\Models\Transaction::TYPE_BILL)
                ->whereNotIn('status', [
                    \App\Models\Transaction::STATUS_CANCELLED,
                    \App\Models\Transaction::STATUS_REJECTED,
                    \App\Models\Transaction::STATUS_EXPIRED
                ])
                ->whereHas('transactionDetails', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->where('created_at', '>=', now()->startOfDay())
                ->latest()
                ->get()
                ->map(function($item) {
                    $billNames = $item->transactionDetails
                        ->filter(fn($d) => is_null($d->deleted_at))
                        ->map(fn($d) => $d->bill->billType->name ?? 'Tagihan')
                        ->unique()
                        ->join(', ');

                    return [
                        'id' => $item->id,
                        'type' => 'OUT',
                        'amount' => $item->pay_amount,
                        'note' => $billNames ?: 'Pembayaran Tagihan',
                        'merchant' => $item->getTranslatedPaymentMethod(),
                        'created_at' => $item->paid_at ?? $item->created_at,
                        'category' => 'BILL',
                        'status' => $item->status
                    ];
                });

            $showSaldo = $activeStudent->isPwaSaldoVisible();
            if ($showSaldo) {
                $recentTransactions = $saldoHistories->concat($posTransactions)->concat($billTransactions)->sortByDesc('created_at')->values();
                $todaySummary = [
                    'count' => $recentTransactions->count(),
                    'in' => $saldoHistories->where('type', 'IN')->where('status', \App\Models\SaldoHistory::STATUS_SUCCESS)->sum('amount'),
                    'out' => $saldoHistories->where('type', 'OUT')->where('status', \App\Models\SaldoHistory::STATUS_SUCCESS)->sum('amount') 
                        + $posTransactions->sum('amount') 
                        + $billTransactions->where('status', \App\Models\Transaction::STATUS_PAID)->sum('amount'),
                ];
            } else {
                $recentTransactions = $billTransactions->sortByDesc('created_at')->values();
                $todaySummary = [
                    'count' => $billTransactions->count(),
                    'in' => 0,
                    'out' => $billTransactions->where('status', \App\Models\Transaction::STATUS_PAID)->sum('amount'),
                ];
            }
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
                    $driver = DB::connection()->getDriverName();
                    $likeExpr = $driver === 'sqlite'
                        ? "? LIKE ('%' || eligible_class_level || '%')"
                        : "? LIKE CONCAT('%', eligible_class_level, '%')";

                    $query->whereNull('eligible_class_level')
                          ->orWhere('eligible_class_level', '')
                          ->orWhereRaw($likeExpr, [$classroomName]);
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

        $version = \Illuminate\Support\Facades\Cache::rememberForever('wali_menus_version', function() {
            return time();
        });
        $menuCacheKey = "wali_menus_school_{$studentSchoolId}_level_{$studentClassLevel}_v{$version}";
        
        $menus = \Illuminate\Support\Facades\Cache::remember($menuCacheKey, 3600, function() use ($studentSchoolId, $studentClassLevel) {
            return \App\Models\ApplicationMenu::where('status', true)
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
        });

        $hasUnpaidBills = false;
        if ($activeStudent) {
            $currentMonth = (int) date('n');
            $currentYear = (int) date('Y');
            $hasUnpaidBills = \App\Models\Bill::where('student_id', $activeStudent->id)
                ->whereHas('billType', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->where('status', \App\Models\Bill::STATUS_UNPAID)
                ->where('amount', '>', 0)
                ->where(function ($q) use ($currentMonth, $currentYear) {
                    $q->where('year', '<', $currentYear)
                        ->orWhere(function ($q2) use ($currentMonth, $currentYear) {
                            $q2->where('year', $currentYear)
                                ->where('month', '<=', $currentMonth);
                        });
                })->exists();
        }

        $pwaPermissions = $activeStudent ? $activeStudent->getPwaPermissions() : [
            'allow_pwa_login' => true,
            'show_pwa_saldo' => true,
            'allow_pwa_saldo_payment' => true,
        ];

        if ($activeStudent) {
            $activeStudent->show_pwa_saldo = $pwaPermissions['show_pwa_saldo'];
            $activeStudent->allow_pwa_login = $pwaPermissions['allow_pwa_login'];
            $activeStudent->allow_pwa_saldo_payment = $pwaPermissions['allow_pwa_saldo_payment'];
            $activeStudent->setAttribute('effective_daily_limit', $activeStudent->getEffectiveDailyLimit());
            $activeStudent->setAttribute('is_custom_limit', $activeStudent->daily_limit > 0);
        }

        $students->each(function ($st) {
            $st->show_pwa_saldo = $st->isPwaSaldoVisible();
            $st->allow_pwa_login = $st->isPwaLoginAllowed();
            $st->allow_pwa_saldo_payment = $st->isPwaSaldoPaymentAllowed();
            $st->setAttribute('effective_daily_limit', $st->getEffectiveDailyLimit());
            $st->setAttribute('is_custom_limit', $st->daily_limit > 0);
        });

        $appSetting = \App\Models\ApplicationSetting::first();
        $heroSaldoOffMessage = !empty($appSetting->pwa_hero_saldo_off_message)
            ? $appSetting->pwa_hero_saldo_off_message
            : 'Layanan uang saku & belanja santri dikelola melalui sistem kartu utama / aplikasi lama.';

        return response()->json([
            'user' => $user,
            'informations' => $informations,
            'students' => $students,
            'activeStudent' => $activeStudent,
            'pwa_permissions' => $pwaPermissions,
            'hero_saldo_off_message' => $heroSaldoOffMessage,
            'tahfidzCount' => (int) $tahfidzCount,
            'studyCount' => (int) $studyCount,
            'recentTransactions' => $recentTransactions,
            'todaySummary' => $todaySummary,
            'unit_transfer' => $unitTransfer,
            'menus' => $menus,
            'has_unpaid_bills' => $hasUnpaidBills,
        ]);
    }
}
