<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Outlet;
use Illuminate\Http\Request;
use App\Models\OutletHandover;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PointOfSaleTransaction;
use Illuminate\Support\Facades\Storage;

class OutletHandoverController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Pos Kasir')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->ajax()) {
            $query = OutletHandover::with(['outlet', 'recipientOutlet', 'recipient', 'creator'])
                ->when(auth()->user()->outlet_id, function ($q) {
                    $q->where('outlet_id', auth()->user()->outlet_id);
                })
                ->when(!auth()->user()->outlet_id && $request->filled('outlet_id'), function ($q) use ($request) {
                    $q->where('outlet_id', $request->outlet_id);
                })
                ->latest();

            return DataTables::of($query)
                ->addColumn('date', function ($data) {
                    return Carbon::parse($data->handover_date)->translatedFormat('d F Y');
                })
                ->addColumn('outlet', function ($data) {
                    return $data->outlet?->name ?? '-';
                })
                ->addColumn('amount', function ($data) {
                    return 'Rp ' . number_format($data->amount, 0, ',', '.');
                })
                ->addColumn('recipient', function ($data) {
                    $recipientPerson = $data->recipient?->name ?? $data->recipient_name ?? '-';
                    $recipientOutlet = $data->recipientOutlet?->name ?? '-';
                    return "$recipientPerson ($recipientOutlet)";
                })
                ->addColumn('evidence', function ($data) {
                    if ($data->evidence_path) {
                        $url = asset($data->evidence_path);
                        return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-light-primary d-inline-flex align-items-center gap-1" title="Lihat Bukti">
                            <i class="fas fa-image fs-7"></i> Lihat Bukti
                        </a>';
                    }
                    return '<span class="text-muted italic">Tidak ada bukti</span>';
                })
                ->addColumn('creator', function ($data) {
                    return $data->creator?->name ?? '-';
                })
                ->addColumn('action', function ($data) {
                    if (auth()->user()->can('Delete Laporan Pos Multi Outlet')) {
                        $actionDelete = route('outlet-handover.destroy', $data->id);
                        return "<div class='d-flex justify-content-center'>" .
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Serah Terima Dana']) .
                            "</div>";
                    }
                    return '-';
                })
                ->rawColumns(['evidence', 'action'])
                ->make(true);
        }

        return redirect()->route('pos-transaction.index');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Pos Kasir')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mencatat serah terima dana');
        }

        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'recipient_outlet_id' => 'required|exists:outlets,id|different:outlet_id',
            'recipient_id' => 'required|exists:admins,id',
            'amount' => 'required|numeric|min:1',
            'handover_date' => 'required|date',
            'evidence' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'notes' => 'nullable|string',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $adminRecipient = \App\Models\Admin::findOrFail($request->recipient_id);

            $data = $request->only(['outlet_id', 'recipient_outlet_id', 'recipient_id', 'amount', 'handover_date', 'notes']);
            $data['recipient_name'] = $adminRecipient->name;
            $data['created_by'] = auth()->user()->id;

            if ($request->hasFile('evidence')) {
                $file = $request->file('evidence');
                $path = $file->store('images/handovers', ['disk' => 'public']);
                $data['evidence_path'] = 'storage/' . $path;
            }

            $handover = OutletHandover::create($data);

            // Buat kategori Cashflow secara otomatis jika belum ada
            $category = \App\Models\CashFlowCategory::firstOrCreate(
                ['name' => 'Serah Terima Dana'],
                ['description' => 'Kategori Pemasukan dari Serah Terima Dana/Penarikan Koperasi']
            );

            // Generate payment code for CashFlow
            $cashflowCount = \App\Models\CashFlow::whereDate('created_at', now())->count();
            $paymentCode = 'INC-HDV-' . now()->format('Ymd') . str_pad($cashflowCount + 1, 3, '0', STR_PAD_LEFT);

            // Catat Pemasukan Arus Kas secara otomatis untuk Outlet Penerima
            \App\Models\CashFlow::create([
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->recipient_id,
                'outlet_id' => $request->recipient_outlet_id,
                'cash_flow_category_id' => $category->id,
                'payment_code' => $paymentCode,
                'type' => \App\Models\CashFlow::TYPE_INCOME,
                'amount' => $request->amount,
                'date' => $request->handover_date,
                'description' => "Penerimaan Serah Terima Dana dari Koperasi/Kantin. Catatan: " . ($request->notes ?? '-') . " [Handover ID: {$handover->id}]",
                'status' => \App\Models\CashFlow::STATUS_APPROVED,
                'proof_of_payment' => $handover->evidence_path,
                'payment_method' => 'Transfer',
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', 'Berhasil mencatat serah terima dana outlet.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mencatat serah terima dana: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Laporan Pos Multi Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus riwayat serah terima dana.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $handover = OutletHandover::findOrFail($id);
            
            // Hapus berkas fisik bukti pembayaran jika ada
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

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', 'Berhasil menghapus riwayat serah terima dana.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus riwayat serah terima dana: ' . $e->getMessage());
        }
    }

    public function getPendingAmount($outletId)
    {
        if (!Auth::user()->can('Manage Laporan Pos Multi Outlet') && !Auth::user()->can('Manage Laporan Pos Kasir')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $outlet = Outlet::findOrFail($outletId);
        $isMainOutlet = in_array(strtoupper($outlet->code), ['KPR', 'KOPERASI']) || strtoupper($outlet->name) === 'KOPERASI';

        if ($isMainOutlet) {
            // Main outlet (Koperasi):
            // Pending amount is the sum of child outlets' pending amounts
            $childOutlets = Outlet::whereNotIn('id', [$outletId])->get();
            $pendingAmount = 0;
            foreach ($childOutlets as $child) {
                $childSales = PointOfSaleTransaction::where('outlet_id', $child->id)
                    ->where('status', 'SUCCESS')
                    ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
                    ->sum('pay_amount');
                
                $childReceived = OutletHandover::where('recipient_outlet_id', $child->id)
                    ->sum('amount');

                $pendingAmount += max(0, $childSales - $childReceived);
            }
        } else {
            // Child outlet:
            // Pending amount is its own sales minus handovers received from Koperasi
            $totalSales = PointOfSaleTransaction::where('outlet_id', $outletId)
                ->where('status', 'SUCCESS')
                ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
                ->sum('pay_amount');

            $totalReceived = OutletHandover::where('recipient_outlet_id', $outletId)
                ->sum('amount');

            $pendingAmount = max(0, $totalSales - $totalReceived);
        }

        return response()->json([
            'status' => 'success',
            'pending_amount' => $pendingAmount,
            'pending_amount_formatted' => number_format($pendingAmount, 0, ',', '.')
        ]);
    }
}
