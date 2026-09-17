<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Models\BillItem;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\AcademicYear;
use App\Models\BillTypeBank;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\Admin\BillTypeRequest;

class BillTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()?->can('Manage Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = BillType::with('billItem', 'academicYear', 'billTypeBank')
                ->when(request()->academic_year_id, function ($query) {
                    $query->where('academic_year_id', request()->academic_year_id);
                })
                ->when(request()->type, function ($query) {
                    $query->where('type', request()->type);
                })
                ->when(request()->bill_item_id, function ($query) {
                    $query->where('bill_item_id', request()->bill_item_id);
                })
                ->latest();

            return DataTables::of($data)
                ->addColumn('checkbox', function ($data) {
                    return '<div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                                <input class="form-check-input select-row" type="checkbox" value="' . $data->id . '" />
                            </div>';
                })
                ->addColumn('payment_rates', function ($data) {
                    // show button to link to payment rate with default academic year parameter
                    $action = route('bill-type.show', $data->id) . '?academic_year_id=' . $data->academic_year_id;
                    return "<i class='fas fa-money-bill-wave'></i> <a href='$action'>Tarif Pembayaran</a>";
                })
                ->editColumn('type', function ($data) {
                    $typeStr = $data->type == BillType::TYPE_MONTHLY ? 'Bulanan' : 'Bebas';
                    $badgeClass = $data->type == BillType::TYPE_MONTHLY ? 'primary' : 'success';
                    $inputStr = $data->payment_input_type == 'FREE' ? ' (Cicilan)' : ' (Fix)';
                    return '<span class="badge badge-light-' . $badgeClass . ' fw-bolder px-2 py-1">' . $typeStr . $inputStr . '</span>';
                })
                ->addColumn('bank', function ($data) {
                    $bank = "";
                    if ($data->billTypeBank->isEmpty()) {
                        return "<span class='badge badge-light-danger m-1'>Belum ada bank</span>";
                    } else {
                        foreach ($data->billTypeBank as $value) {
                            $bank .= "<span class='badge badge-light-info m-1'>{$value->bank?->name} - {$value->bank?->account_number}</span>";
                        }
                    }
                    return $bank;
                })
                ->addColumn('bill_item', function ($data) {
                    return $data->billItem?->name ?? '-';
                })

                ->addColumn('action', function ($data) {
                    $actionEdit = route('bill-type.edit', $data->id);
                    $actionDelete = route('bill-type.destroy', $data->id);
                    $actionToggle = route('bill-type.toggle-visibility', $data->id);
                    $isVisible = $data->is_visible ?? true;
                    
                    $iconClass = $isVisible ? 'fa-eye text-primary' : 'fa-eye-slash text-danger';
                    $btnClass = 'btn btn-icon btn-bg-light btn-active-color-primary btn-sm btn-toggle-visibility';
                    
                    $toggleBtn = "<button class='{$btnClass}' data-action='{$actionToggle}' data-visible='{$isVisible}' title='" . ($isVisible ? 'Sembunyikan' : 'Tampilkan') . "'>
                                    <i class='fas {$iconClass} fs-4'></i>
                                  </button>";

                    return "<div class='d-flex justify-content-center gap-2'>" .
                        $toggleBtn .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Jenis Bayar']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Jenis Bayar']) .
                        "</div>";
                })
                ->rawColumns(['checkbox', 'action', 'type', 'payment_rates', 'bank'])
                ->make(true);
        }
        $academicYears = AcademicYear::orderBy('name', 'DESC')->get();
        $billItems = BillItem::orderBy('name', 'ASC')->get();
        return view('admins.bill-type.index', compact('academicYears', 'billItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()?->can('Create Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $banks = Bank::orderBy('name')->where('is_active', true)->get();
        $bankValue = [];
        $paymentNames = $this->getPaymentNames();
        return view('admins.bill-type.create-edit', compact('banks', 'bankValue', 'paymentNames'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillTypeRequest $request)
    {
        if (!Auth::user()?->can('Create Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Start the database transaction
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            
            if (!Schema::hasColumn('bill_types', 'use_custom_filter')) {
                unset($validated['use_custom_filter']);
            }
            
            $name = $validated['name'] ?? null;

            if (empty($name)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Nama jenis bayar wajib diisi.');
            }

            // Pengecekan eksistensi termasuk data yang telah dihapus (withTrashed)
            $existing = BillType::withTrashed()
                ->where('name', $name)
                ->where('academic_year_id', $validated['academic_year_id'] ?? null)
                ->when(!empty($validated['bill_item_id']), function($q) use ($validated) {
                    $q->where('bill_item_id', $validated['bill_item_id']);
                })
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    // Pulihkan record terhapus dan perbarui nilainya
                    $existing->restore();
                    $existing->update($validated);

                    if ($request->has('bank_ids')) {
                        $existing->billTypeBank()->forceDelete();
                        foreach ($request->bank_ids as $bankId) {
                            $existing->billTypeBank()->create(['bank_id' => $bankId]);
                        }
                    }

                    DB::commit();
                    return redirect()->route('bill-type.show', $existing->id)->with('success', 'Jenis bayar berhasil dipulihkan dan diarahkan ke halaman tarif pembayaran.');
                }

                DB::rollBack();
                return redirect()->route('bill-type.show', $existing->id)->with('info', 'Jenis bayar ini sudah ada. Anda diarahkan ke halaman tarif pembayaran.');
            }

            $billType = BillType::create($validated);
            if ($request->has('bank_ids')) {
                foreach ($request->bank_ids as $bankId) {
                    $billType->billTypeBank()->create(['bank_id' => $bankId]);
                }
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('bill-type.show', $billType->id)->with('success', 'Data jenis bayar berhasil ditambahkan.');
        } catch (\Throwable $e) {
            // Rollback the transaction
            DB::rollBack();

            // Log the error
            Log::error('Error storing bill type: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return redirect()->route('bill-type.index')->with('error', 'Terjadi kesalahan saat menambahkan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($billType)
    {
        try {
            if (!$billType instanceof BillType) {
                $found = BillType::find($billType);
                if (!$found) {
                    return redirect()->route('bill-type.index')->with('error', 'Data tipe pembayaran tidak ditemukan atau telah dihapus.');
                }
                $billType = $found;
            }

            if (!Auth::user()?->can('Manage Jenis Bayar')) {
                return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
            }

            $rawAcademicYearId = request('academic_year_id');
            if ($rawAcademicYearId === 'all') {
                $academicYearIds = [];
            } elseif (is_array($rawAcademicYearId)) {
                $academicYearIds = array_values(array_filter($rawAcademicYearId));
            } elseif (is_string($rawAcademicYearId) && trim($rawAcademicYearId) !== '') {
                $academicYearIds = array_values(array_filter(explode(',', $rawAcademicYearId)));
            } else {
                // Default to target bill type's academic year if no filter explicitly provided
                $academicYearIds = !empty($billType->academic_year_id) ? [$billType->academic_year_id] : [];
            }

            // Get related bill types matching Pos Bayar, Nama Pembayaran, & Tipe Pembayaran for cross-year rate filtering
            $relatedQuery = BillType::query()
                ->where('name', $billType->name);

            if (!empty($billType->bill_item_id)) {
                $relatedQuery->where('bill_item_id', $billType->bill_item_id);
            }
            if (!empty($billType->type)) {
                $relatedQuery->where('type', $billType->type);
            }
            if (!empty($billType->payment_input_type)) {
                $relatedQuery->where('payment_input_type', $billType->payment_input_type);
            }

            $relatedBillTypeIds = $relatedQuery->pluck('id')->toArray();
            if (empty($relatedBillTypeIds)) {
                $relatedBillTypeIds = [$billType->id];
            }

            // Regular Rates (Classroom Based)
            $regularRates = PaymentRate::with(['billType.academicYear', 'paymentRateClassrooms.classroom.school', 'paymentRateItems'])
                ->withExists('bills')
                ->whereHas('paymentRateClassrooms')
                ->whereIn('bill_type_id', $relatedBillTypeIds)
                ->where('type', 'REGULAR')
                ->when(!empty($academicYearIds), function ($query) use ($academicYearIds) {
                    $query->whereHas('billType', function ($q) use ($academicYearIds) {
                        $q->whereIn('academic_year_id', $academicYearIds);
                    });
                })
                ->latest()
                ->get();

            // Mencegah Ilusi Optik Tagihan Ganda (Memfilter Data Historis yang Duplikat)
            $regularRates = $regularRates->unique(function ($item) {
                $classrooms = $item->paymentRateClassrooms->pluck('classroom_id')->sort()->implode('-');
                return $item->bill_type_id . '_' . $item->amount . '_' . $classrooms;
            })->values();

            // Transfer Rates (Student Based)
            $transferRates = PaymentRate::with(['billType.academicYear', 'paymentRateStudents.student.classroom.school', 'paymentRateItems'])
                ->withExists('bills')
                ->whereIn('bill_type_id', $relatedBillTypeIds)
                ->where('type', 'TRANSFER')
                ->when(!empty($academicYearIds), function ($query) use ($academicYearIds) {
                    $query->whereHas('billType', function ($q) use ($academicYearIds) {
                        $q->whereIn('academic_year_id', $academicYearIds);
                    });
                })
                ->latest()
                ->get();

            $academicYears = AcademicYear::orderBy('name', 'DESC')->get();

            return view('admins.bill-type.show', compact('billType', 'academicYears', 'regularRates', 'transferRates', 'academicYearIds'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error in BillTypeController@show: " . $e->getMessage(), [
                'exception' => $e,
                'request' => request()->all()
            ]);
            return redirect()->route('bill-type.index')->with('error', 'Terjadi kesalahan saat membuka data tarif: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BillType $billType)
    {
        if (!Auth::user()?->can('Edit Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $banks = Bank::orderBy('name')->where('is_active', true)->get();
        $bankValue = $billType->billTypeBank->pluck('bank_id')->toArray();
        $paymentNames = $this->getPaymentNames();
        return view('admins.bill-type.create-edit', compact('billType', 'banks', 'bankValue', 'paymentNames'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BillTypeRequest $request, BillType $billType)
    {
        if (!Auth::user()?->can('Edit Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Start the database transaction
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $billType->update($validated);

            if ($request->has('bank_ids')) {
                $billType->billTypeBank()->delete();
                foreach ($request->bank_ids as $bankId) {
                    $billType->billTypeBank()->create(['bank_id' => $bankId]);
                }
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('bill-type.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();

            // Log the error
            Log::error('Error updating bill type: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return redirect()->route('bill-type.index')->with('error', 'Terjadi kesalahan saat mengubah data');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillType $billType)
    {
        if (!Auth::user()?->can('Delete Jenis Bayar')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        try {
            DB::transaction(function () use ($billType) {
                $billType->billTypeBank()->delete();
                
                // Cascade delete to Payment Rates & Pivot Classrooms to prevent orphaned locked data
                $paymentRates = $billType->paymentRates()->get();
                foreach ($paymentRates as $rate) {
                    $rate->paymentRateClassrooms()->update(['deleted_at' => now()]);
                    $rate->paymentRateStudents()->update(['deleted_at' => now()]);
                    $rate->delete();
                }

                $billType->delete();
            });

            return redirect()->route('bill-type.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting bill type: ' . $e->getMessage());
            return redirect()->route('bill-type.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the visibility status of the specified resource.
     */
    public function toggleVisibility($id)
    {
        if (!Auth::user()?->can('Edit Jenis Bayar')) {
            return response()->json(['code' => 403, 'message' => 'Maaf, Anda tidak memiliki akses']);
        }

        try {
            $billType = BillType::findOrFail($id);
            $billType->is_visible = !$billType->is_visible;
            $billType->save();

            $statusText = $billType->is_visible ? 'ditampilkan' : 'disembunyikan';
            return response()->json([
                'code' => 200, 
                'message' => "Jenis bayar berhasil {$statusText}",
                'is_visible' => $billType->is_visible
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling bill type visibility: ' . $e->getMessage());
            return response()->json(['code' => 500, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    /**
     * Bulk toggle visibility status of specified resources.
     */
    public function bulkToggleVisibility(Request $request)
    {
        if (!Auth::user()?->can('Edit Jenis Bayar')) {
            return response()->json(['code' => 403, 'message' => 'Maaf, Anda tidak memiliki akses']);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:bill_types,id',
            'is_visible' => 'required|boolean'
        ]);

        try {
            BillType::whereIn('id', $request->ids)->update(['is_visible' => $request->is_visible]);

            $statusText = $request->is_visible ? 'ditampilkan' : 'disembunyikan';
            $count = count($request->ids);
            return response()->json([
                'code' => 200,
                'message' => "{$count} Jenis bayar berhasil {$statusText}"
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk toggling bill type visibility: ' . $e->getMessage());
            return response()->json(['code' => 500, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    /**
     * Compile dynamic payment names for the create/edit dropdown
     */
    private function getPaymentNames()
    {
        $defaultNames = [
            'SYAHRIAH',
            'LKS SEMESTER 1',
            'LKS SEMESTER 2',
            'ZARKASI',
            'KALENDER',
            'BIAYA APLIKASI',
            'REGISTRASI',
            'BIAYA UJIAN / AKHIR TAHUN',
            'INFAQ KENAIKAN KELAS'
        ];
        
        $existingNames = BillType::select('name')->distinct()->pluck('name')->toArray();
        $paymentNames = array_values(array_unique(array_merge($defaultNames, $existingNames)));
        sort($paymentNames);
        
        return $paymentNames;
    }
}
