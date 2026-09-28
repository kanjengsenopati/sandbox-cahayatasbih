<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\AcademicYear;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditTagihanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('SUPER ADMIN');
        $hasAccess = $isSuperAdmin;

        // Sinkronisasi RBAC Dinamis dari tabel Pengaturan Module (SubMenuNavigation)
        if (!$hasAccess) {
            $menu = \App\Models\SubMenuNavigation::where('url', 'like', '%audit/tagihan-pembayaran%')->first();
            if ($menu && $menu->permission) {
                $permissions = explode(',', $menu->permission);
                foreach ($permissions as $perm) {
                    if ($user->can(trim($perm))) {
                        $hasAccess = true;
                        break;
                    }
                }
            }
        }

        // Blokir jika tidak berhak
        if (!$hasAccess) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses modul ini.');
        }

        if ($request->ajax()) {
            if ($request->tab === 'archive') {
                return $this->getArchiveTransactionData();
            }
        }

        // Fix Error 500: Mengambil data Schools & AcademicYears untuk dropdown import
        $schools = School::orderBy('name')->hasSchool()->get();
        $academicYears = AcademicYear::where(function($query) {
            $query->where('is_active', true)
                  ->orWhereHas('billTypes', function ($q) {
                      $q->where('is_visible', true);
                  });
        })->orderBy('start_year', 'desc')->get();

        return view('admins.audit.tagihan', compact('schools', 'academicYears'));
    }

    private function getArchiveTransactionData()
    {
        @set_time_limit(300);
        // Lepas session lock lebih awal agar request lain dari user yang sama
        // tidak terblokir selama query berat DataTables ini berjalan.
        session()->save();

        $transferMethodIds = PaymentMethod::where('type', PaymentMethod::TYPE_TRANSFER)->pluck('id')->toArray();

        $transactions = Transaction::select([
                'id', 'student_id', 'payment_method_id', 'pay_amount',
                'unique_payment', 'payment_code', 'status', 'type',
                'created_at', 'updated_at', 'deleted_at',
                'admin_id', 'is_deleted_from_archive',
            ])
            ->with([
                'student:id,name,nis',
                'paymentMethod:id,name,type',
                'activeProof.bank',
                'transactionProofs.bank',
                'admin:id,name',
                'transactionDetails.bill.billType.academicYear',
                'transactionDetails.bill.academicYear',
                'transactionDetails.saldoHistory',
                'transactionDetails.savingHistory',
                'transactionDetails.ppdbRegistration'
            ])
            ->whereIn('payment_method_id', $transferMethodIds)
            ->where('type', Transaction::TYPE_BILL)
            ->where('status', Transaction::STATUS_PAID)
            ->where('is_deleted_from_archive', false)
            ->hasSchool();

        if ($searchStudent = request()->search_student) {
            $transactions->whereHas('student', function ($q) use ($searchStudent) {
                $q->where('name', 'like', "%{$searchStudent}%")
                  ->orWhere('nis', 'like', "%{$searchStudent}%");
            });
        }
        if ($startDate = request()->start_date) {
            $transactions->whereDate('updated_at', '>=', $startDate);
        }
        if ($endDate = request()->end_date) {
            $transactions->whereDate('updated_at', '<=', $endDate);
        }

        $transactions->latest('updated_at');

        return DataTables::of($transactions)
            ->addIndexColumn()
            ->addColumn('student', function ($row) {
                $name = e($row->student->name ?? '-');
                $nis = e($row->student->nis ?? '-');
                return "<div class='d-flex flex-column'><span class='text-gray-800 fw-bolder'>{$name}</span><small class='text-muted fs-8'>NIS: {$nis}</small></div>";
            })
            ->editColumn('pay_amount', fn($transaction) => $this->formatPayAmountWithDetailsColumn($transaction))
            ->addColumn('kode_unik', function ($row) {
                if ($row->unique_payment) {
                    return '<span class="badge badge-light-primary fw-bold fs-7">' . number_format($row->unique_payment, 0, ',', '.') . '</span>';
                }
                return '<span class="text-muted fs-8">-</span>';
            })
            ->addColumn('bank_recipient', function ($transaction) {
                $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                $bank = $proof?->bank;
                if (!$bank) return '<span class="text-muted fs-8">-</span>';
                return "<strong>" . e($bank->name) . "</strong><br><small class='text-muted'>No. Rek: " . e($bank->account_number) . "</small><br><small class='text-muted'>A.N: " . e($bank->account_name) . "</small>";
            })
            ->addColumn('proof', fn($transaction) => $this->formatProofColumn($transaction))
            ->editColumn('status', fn($transaction) => $this->formatStatusColumn($transaction))
            ->addColumn('officer', function ($transaction) {
                return $transaction->admin?->name ?? '<span class="text-muted fs-8">-</span>';
            })
            ->addColumn('date', function ($transaction) {
                return $transaction->updated_at ? Carbon::parse($transaction->updated_at)->translatedFormat('d F Y H:i') : '-';
            })
            ->addColumn('action', fn($transaction) => $this->formatArchiveActionColumn($transaction))
            ->rawColumns(['student', 'pay_amount', 'kode_unik', 'bank_recipient', 'proof', 'status', 'officer', 'action'])
            ->make(true);
    }

    private function formatProofColumn($transaction)
    {
        $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
        $proofUrl = $proof?->proof_image_url ?? $proof?->proof_image;
        if (!$proofUrl) return '<span class="text-muted fs-8 fst-italic">-</span>';
        $fallbackSvg = "data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' viewBox=\\'0 0 100 100\\'%3E%3Crect width=\\'100\\' height=\\'100\\' fill=\\'%23f1f5f9\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' dominant-baseline=\\'middle\\' text-anchor=\\'middle\\' font-family=\\'sans-serif\\' font-size=\\'11\\' fill=\\'%2394a3b8\\'%3ETidak Ada%3C/text%3E%3C/svg%3E";
        return "<img src='{$proofUrl}' class='img-fluid img-thumbnail cursor-pointer view-proof-image shadow-sm' data-src='{$proofUrl}' onerror=\"this.onerror=null; this.src='{$fallbackSvg}';\" style='max-width: 80px; height: auto; border-radius: 8px;' alt='Bukti Transfer' title='Klik untuk melihat bukti'>";
    }

    private function formatStatusColumn($transaction)
    {
        $statusLabels = [
            Transaction::STATUS_PENDING => 'Belum Dibayar',
            Transaction::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
            Transaction::STATUS_PENDING_CONFIRMATION => 'Menunggu Verifikasi',
            Transaction::STATUS_PAID => 'Lunas',
            Transaction::STATUS_EXPIRED => 'Kedaluwarsa',
            Transaction::STATUS_CANCELLED => 'Dibatalkan',
            Transaction::STATUS_REJECTED => 'Ditolak'
        ];

        $statusClasses = [
            Transaction::STATUS_PENDING => 'badge badge-primary',
            Transaction::STATUS_PENDING_PAYMENT => 'badge badge-warning',
            Transaction::STATUS_PENDING_CONFIRMATION => 'badge badge-danger',
            Transaction::STATUS_PAID => 'badge badge-success',
            Transaction::STATUS_EXPIRED => 'badge badge-secondary',
            Transaction::STATUS_CANCELLED => 'badge badge-secondary',
            Transaction::STATUS_REJECTED => 'badge badge-danger'
        ];

        $status = $transaction->status;
        $label = $statusLabels[$status] ?? $status;
        $class = $statusClasses[$status] ?? 'badge badge-light';

        return "<span class='{$class}'>{$label}</span>";
    }

    private function formatPayAmountWithDetailsColumn($transaction)
    {
        $formattedTotal = 'Rp ' . number_format($transaction->pay_amount, 0, ',', '.');
        
        // Group details by BillType + AcademicYear
        $grouped = [];
        $totalItemCount = 0;

        foreach ($transaction->transactionDetails as $detail) {
            $bill = $detail->bill;
            if ($bill) {
                $billType = $bill->billType;
                $billTypeName = $billType?->name ?? 'Tagihan Siswa';
                
                $academicYear = $billType?->academicYear ?? $bill->academicYear;
                $academicYearName = $academicYear?->name ?? '';

                $monthName = $bill->translated_month ?? $bill->getTranslatedMonthAttribute();
                $periodLabel = $monthName ? $monthName : ($bill->year ? "Tahun {$bill->year}" : 'Sekali Bayar');
                
                $groupKey = $billTypeName . '___' . $academicYearName;

                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'bill_type_name' => $billTypeName,
                        'academic_year' => $academicYearName,
                        'months' => [],
                        'total_amount' => 0,
                    ];
                }
                
                $itemAmount = $detail->amount > 0 ? $detail->amount : ($bill->amount ?? 0);
                $grouped[$groupKey]['months'][] = $periodLabel;
                $grouped[$groupKey]['total_amount'] += $itemAmount;
                $totalItemCount++;
            } elseif ($detail->saldoHistory || $transaction->type === Transaction::TYPE_SALDO) {
                $groupKey = 'TOPUP_SALDO';
                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'bill_type_name' => 'Top Up Saldo Uang Saku',
                        'academic_year' => '',
                        'months' => ['Uang Saku PWA'],
                        'total_amount' => max(0, $transaction->pay_amount - ($transaction->unique_payment ?? 0)),
                    ];
                    $totalItemCount++;
                }
            } elseif ($detail->savingHistory || $transaction->type === Transaction::TYPE_SAVING) {
                $groupKey = 'SAVING';
                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'bill_type_name' => 'Setoran Tabungan Santri',
                        'academic_year' => '',
                        'months' => ['Tabungan'],
                        'total_amount' => max(0, $transaction->pay_amount - ($transaction->unique_payment ?? 0)),
                    ];
                    $totalItemCount++;
                }
            } elseif ($detail->ppdbRegistration || $transaction->type === Transaction::TYPE_PPDB) {
                $groupKey = 'PPDB';
                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'bill_type_name' => 'Biaya Pendaftaran PPDB',
                        'academic_year' => '',
                        'months' => ['Pendaftaran'],
                        'total_amount' => max(0, $transaction->pay_amount - ($transaction->unique_payment ?? 0)),
                    ];
                    $totalItemCount++;
                }
            } elseif ($detail->amount > 0) {
                $groupKey = 'DETAIL_MANUAL_' . $detail->id;
                $grouped[$groupKey] = [
                    'bill_type_name' => 'Tagihan Siswa (' . ($transaction->payment_code ?? 'Manual') . ')',
                    'academic_year' => '',
                    'months' => ['Biaya Pendidikan'],
                    'total_amount' => $detail->amount,
                ];
                $totalItemCount++;
            }
        }

        // Fallback if transactionDetails has no mapped items or empty (ensures 100% of items have full detail):
        if (empty($grouped)) {
            $typeLabel = match($transaction->type) {
                Transaction::TYPE_SALDO => 'Top Up Saldo Uang Saku',
                Transaction::TYPE_SAVING => 'Setoran Tabungan Santri',
                Transaction::TYPE_PPDB => 'Biaya Pendaftaran PPDB',
                default => 'Pembayaran Tagihan Siswa'
            };
            $periodLabel = $transaction->payment_code ? "Kode: {$transaction->payment_code}" : 'Pembayaran Transfer';
            $grouped['FALLBACK'] = [
                'bill_type_name' => $typeLabel,
                'academic_year' => '',
                'months' => [$periodLabel],
                'total_amount' => max(0, $transaction->pay_amount - ($transaction->unique_payment ?? 0)),
            ];
            $totalItemCount = 1;
        }

        $buttonText = "{$totalItemCount} Item Tagihan";
        if ($transaction->type === Transaction::TYPE_SALDO) {
            $buttonText = "Detail Top Up";
        } elseif ($transaction->type === Transaction::TYPE_SAVING) {
            $buttonText = "Detail Tabungan";
        } elseif ($transaction->type === Transaction::TYPE_PPDB) {
            $buttonText = "Detail PPDB";
        }

        $collapseId = 'collapse-bills-' . $transaction->id;

        $html = "<div class='d-flex flex-column align-items-start gap-1'>";
        $html .= "  <span class='text-emerald-600 fw-boldest fs-6'>{$formattedTotal}</span>";
        $html .= "  <button type='button' class='btn btn-xs btn-light-primary py-1 px-2.5 rounded-[12px] fs-8 fw-bold d-inline-flex align-items-center gap-1 mt-1' data-bs-toggle='collapse' data-bs-target='#{$collapseId}' aria-expanded='false'>";
        $html .= "    <i class='fas fa-list-ul fs-9'></i> {$buttonText} <i class='fas fa-chevron-down ms-1 fs-9'></i>";
        $html .= "  </button>";
        $html .= "</div>";

        // Wide Expandable Grouped Table Panel
        $html .= "<div class='collapse mt-3 text-start' id='{$collapseId}'>";
        $html .= "  <div class='card card-body p-4 rounded-[16px] border border-gray-200 bg-white shadow-lg' style='min-width: 650px; width: 100%; max-width: 850px;'>";
        $html .= "    <div class='table-responsive'>";
        $html .= "      <table class='table align-middle table-row-dashed fs-8 gy-3 mb-0'>";
        $html .= "        <thead>";
        $html .= "          <tr class='text-start text-gray-500 fw-bolder fs-8 text-uppercase gs-0 border-bottom border-gray-300'>";
        $html .= "            <th style='width: 5%'>NO</th>";
        $html .= "            <th style='width: 35%'>NAMA TAGIHAN & TAHUN AJARAN</th>";
        $html .= "            <th style='width: 40%'>BULAN TERBAYAR</th>";
        $html .= "            <th class='text-end' style='width: 20%'>NOMINAL TOTAL</th>";
        $html .= "          </tr>";
        $html .= "        </thead>";
        $html .= "        <tbody class='text-gray-700 fw-bold'>";

        $rowNo = 1;
        $grandTotal = 0;

        foreach ($grouped as $groupData) {
            $typeName = $groupData['bill_type_name'];
            $taName = $groupData['academic_year'];
            $months = $groupData['months'];
            $groupTotalFormatted = 'Rp ' . number_format($groupData['total_amount'], 0, ',', '.');
            $grandTotal += $groupData['total_amount'];

            $html .= "          <tr>";
            $html .= "            <td class='align-top pt-3'>{$rowNo}</td>";
            $html .= "            <td class='align-top pt-3'>";
            $html .= "              <span class='d-block text-dark fw-bolder fs-7 text-uppercase mb-1'>{$typeName}</span>";
            if ($taName) {
                $html .= "              <span class='badge badge-light-info text-info border border-info border-opacity-40 rounded-pill px-2.5 py-1 fs-9 fw-bold d-inline-flex align-items-center gap-1'>";
                $html .= "                <i class='fas fa-calendar-alt fs-9 text-info'></i> TA {$taName}";
                $html .= "              </span>";
            }
            $html .= "            </td>";
            $html .= "            <td class='align-top pt-3'>";
            $html .= "              <div class='d-flex flex-wrap gap-1.5'>";
            foreach ($months as $m) {
                $html .= "                <span class='badge bg-white text-info border border-info border-opacity-60 rounded-pill px-3 py-1.5 fs-8 fw-semibold shadow-xs'>{$m}</span>";
            }
            $html .= "              </div>";
            $html .= "            </td>";
            $html .= "            <td class='text-end align-top pt-3 text-dark fw-bolder fs-7'>{$groupTotalFormatted}</td>";
            $html .= "          </tr>";

            $rowNo++;
        }

        $grandTotalFormatted = 'Rp ' . number_format($grandTotal, 0, ',', '.');

        $html .= "        </tbody>";
        $html .= "        <tfoot>";
        $html .= "          <tr class='border-top border-gray-300 fw-boldest fs-7'>";
        $html .= "            <td colspan='3' class='text-end pt-4 text-dark'>Total Pembayaran Keseluruhan:</td>";
        $html .= "            <td class='text-end pt-4 text-emerald-600 fs-6'>{$grandTotalFormatted}</td>";
        $html .= "          </tr>";
        $html .= "        </tfoot>";
        $html .= "      </table>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "</div>";

        return $html;
    }

    private function formatArchiveActionColumn($transaction)
    {
        $user = Auth::user();
        if (!$user || (!$user->can('Edit Tagihan') && !$user->hasRole('Super Admin') && !$user->hasRole('SUPER ADMIN'))) {
            return '';
        }

        $daysDiff = $transaction->updated_at ? $transaction->updated_at->diffInDays(now()) : 0;
        $canDelete = $daysDiff > 30;

        if ($canDelete) {
            return "<button class='btn btn-danger btn-sm btn-delete-archive' data-id='{$transaction->id}'>
                        <i class='fas fa-trash me-1'></i> Hapus
                    </button>";
        } else {
            return "<button class='btn btn-secondary btn-sm' disabled title='Hapus dinonaktifkan karena usia arsip kurang dari 30 hari'>
                        <i class='fas fa-trash me-1'></i> Hapus
                    </button>";
        }
    }

    public function hideArchive($id)
    {
        $user = Auth::user();
        if (!$user || (!$user->can('Edit Tagihan') && !$user->hasRole('Super Admin') && !$user->hasRole('SUPER ADMIN'))) {
            return response()->json([
                'code' => '403',
                'message' => 'Anda tidak memiliki hak akses untuk menghapus arsip.'
            ], 403);
        }

        $transaction = Transaction::findOrFail($id);

        $daysDiff = $transaction->updated_at ? $transaction->updated_at->diffInDays(now()) : 0;
        if ($daysDiff <= 30) {
            return response()->json([
                'code' => '400',
                'message' => 'Gagal menghapus: Arsip hanya dapat dihapus jika usianya sudah lebih dari 30 hari.'
            ], 400);
        }

        $transaction->update([
            'is_deleted_from_archive' => true
        ]);

        return response()->json([
            'code' => '200',
            'message' => 'Arsip riwayat pembayaran berhasil disembunyikan.'
        ]);
    }
}