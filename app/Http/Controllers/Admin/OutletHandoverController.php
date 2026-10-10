<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Outlet;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Models\OutletHandover;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PointOfSaleTransaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OutletHandoverController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Pos Kasir') && !Auth::user()->can('Manage Laporan Rugi Laba')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->ajax()) {
            $query = OutletHandover::with(['outlet', 'recipientOutlet', 'recipient', 'creator', 'cashier'])
                ->when($request->filled('handover_type'), function ($q) use ($request) {
                    $q->where('handover_type', $request->handover_type);
                })
                ->when(auth()->user()->outlet_id, function ($q) {
                    $outletId = auth()->user()->outlet_id;
                    $q->where(function ($sub) use ($outletId) {
                        $sub->where('outlet_id', $outletId)
                            ->orWhere('recipient_outlet_id', $outletId);
                    });
                })
                ->when(!auth()->user()->outlet_id && $request->filled('outlet_id'), function ($q) use ($request) {
                    $outletId = $request->outlet_id;
                    $q->where(function ($sub) use ($outletId) {
                        $sub->where('outlet_id', $outletId)
                            ->orWhere('recipient_outlet_id', $outletId);
                    });
                })
                ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                    $q->whereBetween('handover_date', [$request->start_date, $request->end_date]);
                })
                ->latest('handover_date');

            return DataTables::of($query)
                ->addColumn('date', function ($data) {
                    return Carbon::parse($data->handover_date)->translatedFormat('d F Y');
                })
                ->addColumn('type_badge', function ($data) {
                    if ($data->handover_type === OutletHandover::TYPE_CASHIER_TO_MANAGEMENT) {
                        return '<span class="badge badge-light-warning fw-bold px-3 py-2 text-warning" style="border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px;">
                            <i class="fas fa-cash-register me-1 fs-8 text-warning"></i> Setor Kasir Tunai
                        </span>';
                    }
                    return '<span class="badge badge-light-primary fw-bold px-3 py-2 text-primary" style="border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px;">
                        <i class="fas fa-university me-1 fs-8 text-primary"></i> Saldo Koperasi ➔ Outlet
                    </span>';
                })
                ->addColumn('outlet', function ($data) {
                    return $data->outlet?->name ?? '-';
                })
                ->addColumn('cashier_name', function ($data) {
                    return $data->cashier?->name ?? '-';
                })
                ->addColumn('recipient', function ($data) {
                    $recipientPerson = $data->recipient?->name ?? $data->recipient_name ?? '-';
                    $recipientOutlet = $data->recipientOutlet?->name ?? '-';
                    return "$recipientPerson <br><small class='text-muted'>($recipientOutlet)</small>";
                })
                ->addColumn('system_amount', function ($data) {
                    return 'Rp ' . number_format($data->system_amount ?: $data->amount, 0, ',', '.');
                })
                ->addColumn('amount', function ($data) {
                    return '<strong class="text-success">Rp ' . number_format($data->amount, 0, ',', '.') . '</strong>';
                })
                ->addColumn('discrepancy', function ($data) {
                    $diff = $data->discrepancy ?? 0;
                    if ($diff > 0) {
                        return '<span class="badge badge-light-success text-success fw-bold">+Rp ' . number_format($diff, 0, ',', '.') . '</span>';
                    } elseif ($diff < 0) {
                        return '<span class="badge badge-light-danger text-danger fw-bold">-Rp ' . number_format(abs($diff), 0, ',', '.') . '</span>';
                    }
                    return '<span class="text-muted fs-8">Sesuai (Rp 0)</span>';
                })
                ->addColumn('evidence', function ($data) {
                    if ($data->evidence_path) {
                        $url = asset($data->evidence_path);
                        return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-light-primary d-inline-flex align-items-center gap-1 px-3 py-1" style="border-radius: 8px;" title="Lihat Bukti">
                            <i class="fas fa-file-image fs-7"></i> Lihat Bukti
                        </a>';
                    }
                    return '<span class="text-muted fst-italic fs-8">Tidak ada bukti</span>';
                })
                ->addColumn('creator', function ($data) {
                    return $data->creator?->name ?? '-';
                })
                ->addColumn('action', function ($data) {
                    if (auth()->user()->can('Delete Laporan Pos Multi Outlet') || auth()->user()->hasRole('Super Admin')) {
                        $actionDelete = route('outlet-handover.destroy', $data->id);
                        return "<div class='d-flex justify-content-center'>" .
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Serah Terima Dana']) .
                            "</div>";
                    }
                    return '-';
                })
                ->rawColumns(['type_badge', 'recipient', 'amount', 'discrepancy', 'evidence', 'action'])
                ->make(true);
        }

        return redirect()->route('report-profit-loss.index');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Pos Kasir') && !Auth::user()->can('Manage Laporan Rugi Laba')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mencatat serah terima dana');
        }

        $handoverType = $request->input('handover_type', OutletHandover::TYPE_KOPERASI_TO_OUTLET);

        $rules = [
            'handover_type' => 'required|in:' . OutletHandover::TYPE_KOPERASI_TO_OUTLET . ',' . OutletHandover::TYPE_CASHIER_TO_MANAGEMENT,
            'outlet_id' => 'required|exists:outlets,id',
            'recipient_id' => 'required|exists:admins,id',
            'amount' => 'required',
            'handover_date' => 'required|date',
            'evidence' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:3072',
            'notes' => 'nullable|string',
        ];

        if ($handoverType === OutletHandover::TYPE_KOPERASI_TO_OUTLET) {
            $rules['recipient_outlet_id'] = 'required|exists:outlets,id|different:outlet_id';
        } else {
            $rules['cashier_id'] = 'required|exists:admins,id';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $adminRecipient = Admin::findOrFail($request->recipient_id);
            $amount = (float) preg_replace('/\D/', '', $request->amount);

            $systemAmount = $request->filled('system_amount')
                ? (float) preg_replace('/\D/', '', $request->system_amount)
                : $amount;

            $discrepancy = $amount - $systemAmount;

            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $file = $request->file('evidence');
                $path = $file->store('images/handovers', ['disk' => 'public']);
                $evidencePath = 'storage/' . $path;
            }

            $recipientOutletId = $handoverType === OutletHandover::TYPE_KOPERASI_TO_OUTLET
                ? $request->recipient_outlet_id
                : $request->outlet_id;

            $handover = OutletHandover::create([
                'handover_type' => $handoverType,
                'outlet_id' => $request->outlet_id,
                'recipient_outlet_id' => $recipientOutletId,
                'recipient_id' => $request->recipient_id,
                'recipient_name' => $adminRecipient->name,
                'cashier_id' => $handoverType === OutletHandover::TYPE_CASHIER_TO_MANAGEMENT ? $request->cashier_id : null,
                'amount' => $amount,
                'system_amount' => $systemAmount,
                'discrepancy' => $discrepancy,
                'status' => OutletHandover::STATUS_APPROVED,
                'handover_date' => $request->handover_date,
                'evidence_path' => $evidencePath,
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            // Kategori dan deskripsi CashFlow sesuai jenis handover
            if ($handoverType === OutletHandover::TYPE_KOPERASI_TO_OUTLET) {
                $categoryName = 'Serah Terima Dana';
                $categoryDesc = 'Pemasukan dari Serah Terima Dana / Penarikan Koperasi ke Outlet';
                $flowDesc = "Penerimaan Serah Terima Saldo dari Koperasi. Catatan: " . ($request->notes ?? '-') . " [Handover ID: {$handover->id}]";
                $flowPaymentMethod = 'Transfer';
            } else {
                $categoryName = 'Setoran Kasir Tunai';
                $categoryDesc = 'Pemasukan dari Setoran Kasir Tunai (Shift Closing) ke Manajemen';
                $cashierAdmin = Admin::find($request->cashier_id);
                $cashierName = $cashierAdmin ? $cashierAdmin->name : 'Kasir';
                $flowDesc = "Penerimaan Setoran Kasir Tunai ({$cashierName}). Catatan: " . ($request->notes ?? '-') . " [Handover ID: {$handover->id}]";
                $flowPaymentMethod = 'CASH';
            }

            $category = \App\Models\CashFlowCategory::firstOrCreate(
                ['name' => $categoryName],
                ['description' => $categoryDesc]
            );

            // Generate payment code for CashFlow
            $cashflowCount = \App\Models\CashFlow::whereDate('created_at', now())->count();
            $paymentCode = 'INC-HDV-' . now()->format('Ymd') . str_pad($cashflowCount + 1, 3, '0', STR_PAD_LEFT);

            // Catat Pemasukan Arus Kas secara otomatis untuk Outlet Penerima
            \App\Models\CashFlow::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $request->recipient_id,
                'outlet_id' => $recipientOutletId,
                'cash_flow_category_id' => $category->id,
                'payment_code' => $paymentCode,
                'type' => \App\Models\CashFlow::TYPE_INCOME,
                'amount' => $amount,
                'date' => $request->handover_date,
                'description' => $flowDesc,
                'status' => \App\Models\CashFlow::STATUS_APPROVED,
                'proof_of_payment' => $handover->evidence_path,
                'payment_method' => $flowPaymentMethod,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Berhasil mencatat dan menyimpan serah terima dana (Handover).');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat serah terima dana: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Laporan Pos Multi Outlet') && !Auth::user()->hasRole('Super Admin')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus riwayat serah terima dana.');
        }

        try {
            DB::beginTransaction();

            $handover = OutletHandover::findOrFail($id);

            // Hapus berkas fisik bukti jika ada
            if ($handover->evidence_path) {
                $cleanPath = str_replace('storage/', '', $handover->evidence_path);
                Storage::disk('public')->delete($cleanPath);
            }

            // Hapus otomatis pencatatan arus kas terkait
            $cashFlow = \App\Models\CashFlow::where('description', 'like', '%[Handover ID: ' . $id . ']%')->first();
            if ($cashFlow) {
                $cashFlow->delete();
            }

            $handover->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Berhasil menghapus riwayat serah terima dana.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus riwayat serah terima dana: ' . $e->getMessage());
        }
    }

    public function getPendingAmount(Request $request, $outletId = null)
    {
        if (!Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Pos Kasir') && !Auth::user()->can('Manage Laporan Rugi Laba')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $type = $request->input('type');
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        $startDate = null;
        $endDate = null;
        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->format('Y-m-d');
            $endDate = Carbon::parse($endDateInput)->format('Y-m-d');
        }

        // Skenario 1: Handover Kasir Tunai (Shift Closing)
        if ($type === 'CASHIER' || $request->filled('cashier_id')) {
            $cashierId = $request->input('cashier_id');
            $cashierAdmin = Admin::find($cashierId);

            if (!$cashierAdmin) {
                return response()->json(['status' => 'error', 'message' => 'Kasir tidak ditemukan'], 404);
            }

            $cashierSalesQuery = PointOfSaleTransaction::where('admin_id', $cashierId)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->where('type', PointOfSaleTransaction::TYPE_UMUM)
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });

            $filterOutletId = $request->input('outlet_id') ?: ($outletId && $outletId !== 'all' ? $outletId : null);
            if ($filterOutletId) {
                $cashierSalesQuery->where('outlet_id', $filterOutletId);
            }

            $cashierSales = $cashierSalesQuery->sum('pay_amount');

            $cashierHandovers = OutletHandover::where('cashier_id', $cashierId)
                ->where('handover_type', OutletHandover::TYPE_CASHIER_TO_MANAGEMENT)
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    $q->whereBetween('handover_date', [$startDate, $endDate]);
                })
                ->when($filterOutletId, function($q) use ($filterOutletId) {
                    $q->where('outlet_id', $filterOutletId);
                })
                ->sum('amount');

            $pendingAmount = max(0, $cashierSales - $cashierHandovers);

            return response()->json([
                'status' => 'success',
                'type' => 'CASHIER',
                'cashier_name' => $cashierAdmin->name,
                'system_amount' => $cashierSales,
                'total_handover' => $cashierHandovers,
                'pending_amount' => $pendingAmount,
                'pending_amount_formatted' => number_format($pendingAmount, 0, ',', '.'),
            ]);
        }

        // Skenario 2: Handover Saldo Koperasi ➔ Outlet
        $targetOutletId = $request->input('recipient_outlet_id') ?: $outletId;

        if (!$targetOutletId) {
            return response()->json(['status' => 'error', 'message' => 'Outlet tidak valid'], 400);
        }

        $outlet = Outlet::findOrFail($targetOutletId);
        $koperasiId = \App\Services\OutletContextService::getKoperasiOutletId();
        $isKoperasi = ($outlet->id === $koperasiId) || in_array(strtoupper($outlet->code), ['KPR', 'KOPERASI']) || strtoupper($outlet->name) === 'KOPERASI';

        if ($isKoperasi) {
            // Jika memilih Koperasi, hitung total akumulasi pending semua outlet cabang
            $childOutlets = Outlet::where('id', '!=', $outlet->id)->get();
            $pendingAmount = 0;
            $totalSales = 0;
            $totalReceived = 0;

            foreach ($childOutlets as $child) {
                $childSales = PointOfSaleTransaction::where('outlet_id', $child->id)
                    ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                    ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
                    ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    })
                    ->sum('pay_amount');

                $childReceived = OutletHandover::where('recipient_outlet_id', $child->id)
                    ->where('handover_type', OutletHandover::TYPE_KOPERASI_TO_OUTLET)
                    ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                        $q->whereBetween('handover_date', [$startDate, $endDate]);
                    })
                    ->sum('amount');

                $totalSales += $childSales;
                $totalReceived += $childReceived;
                $pendingAmount += max(0, $childSales - $childReceived);
            }
        } else {
            // Outlet Penerima spesifik
            $totalSales = PointOfSaleTransaction::where('outlet_id', $targetOutletId)
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                })
                ->sum('pay_amount');

            $totalReceived = OutletHandover::where('recipient_outlet_id', $targetOutletId)
                ->where('handover_type', OutletHandover::TYPE_KOPERASI_TO_OUTLET)
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    $q->whereBetween('handover_date', [$startDate, $endDate]);
                })
                ->sum('amount');

            $pendingAmount = max(0, $totalSales - $totalReceived);
        }

        return response()->json([
            'status' => 'success',
            'type' => 'KOPERASI',
            'outlet_name' => $outlet->name,
            'system_amount' => $totalSales,
            'total_handover' => $totalReceived,
            'pending_amount' => $pendingAmount,
            'pending_amount_formatted' => number_format($pendingAmount, 0, ',', '.')
        ]);
    }
}
