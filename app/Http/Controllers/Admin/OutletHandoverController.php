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
        if (!Auth::user()->can('Manage Laporan Pos Multi Outlet')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->ajax()) {
            $query = OutletHandover::with(['outlet', 'creator'])
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
                    return $data->recipient_name;
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
        if (!Auth::user()->can('Create Laporan Pos Multi Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk mencatat serah terima dana');
        }

        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'amount' => 'required|numeric|min:1',
            'handover_date' => 'required|date',
            'recipient_name' => 'required|string|max:255',
            'evidence' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'notes' => 'nullable|string',
        ]);

        try {
            $data = $request->only(['outlet_id', 'amount', 'handover_date', 'recipient_name', 'notes']);
            $data['created_by'] = auth()->user()->id;

            if ($request->hasFile('evidence')) {
                $file = $request->file('evidence');
                $path = $file->store('images/handovers', ['disk' => 'public']);
                $data['evidence_path'] = 'storage/' . $path;
            }

            OutletHandover::create($data);

            return redirect()->back()->with('success', 'Berhasil mencatat serah terima dana outlet.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat serah terima dana: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Laporan Pos Multi Outlet')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk menghapus riwayat serah terima dana.');
        }

        try {
            $handover = OutletHandover::findOrFail($id);
            
            // Hapus berkas fisik bukti pembayaran jika ada
            if ($handover->evidence_path) {
                $cleanPath = str_replace('storage/', '', $handover->evidence_path);
                Storage::disk('public')->delete($cleanPath);
            }

            $handover->delete();
            return redirect()->back()->with('success', 'Berhasil menghapus riwayat serah terima dana.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus riwayat serah terima dana: ' . $e->getMessage());
        }
    }

    public function getPendingAmount($outletId)
    {
        if (!Auth::user()->can('Manage Laporan Pos Multi Outlet')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $totalSales = PointOfSaleTransaction::where('outlet_id', $outletId)
            ->where('status', 'SUCCESS')
            ->where('type', PointOfSaleTransaction::TYPE_SANTRI)
            ->sum('pay_amount');

        $totalHandovers = OutletHandover::where('outlet_id', $outletId)
            ->sum('amount');

        $pendingAmount = max(0, $totalSales - $totalHandovers);

        return response()->json([
            'status' => 'success',
            'pending_amount' => $pendingAmount,
            'pending_amount_formatted' => number_format($pendingAmount, 0, ',', '.')
        ]);
    }
}
