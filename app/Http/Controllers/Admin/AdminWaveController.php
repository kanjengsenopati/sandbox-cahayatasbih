<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbTrack;
use App\Models\PpdbWaves;
use App\Models\School;
use App\Services\PsbBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWaveController extends Controller
{
    /**
     * Display Wave detail with its Tracks.
     */
    public function show($id)
    {
        $wave = PpdbWaves::with([
            'academicYear',
            // 'tracks' => function ($query) {
            //     $query->with('school')->withCount('registrations');
            // }
            'tracks'
        ])->findOrFail($id);

        $schools = School::whereIn('type', ['SMP', 'MA'])->get();

        return view('admins.psb.waves.show', compact('wave', 'schools'));
    }

    /**
     * Store a new Track for a Wave.
     */
    public function storeTrack(Request $request, $waveId)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'school_id' => 'required|exists:schools,id',
            'registration_type' => 'required|in:UMUM,JAMAAH,ALUMNI',
            'quota' => 'required|integer|min:0',
            'registration_fee' => 'nullable|integer|min:0',
            'installments' => 'nullable|array',
            'installments.*.month' => 'required_with:installments|integer|between:1,12',
            'installments.*.year_offset' => 'required_with:installments|integer|between:0,3',
            'installments.*.amount' => 'required_with:installments|integer|min:0',
        ], [
            'name.required' => 'Nama jalur wajib diisi.',
            'school_id.required' => 'Unit pendidikan wajib dipilih.',
            'quota.required' => 'Kuota wajib diisi.',
        ]);

        $wave = PpdbWaves::findOrFail($waveId);

        DB::beginTransaction();
        try {
            // Build installment plan JSON
            $installmentPlan = null;
            if ($request->has('installments') && is_array($request->installments)) {
                $installmentPlan = collect($request->installments)->map(function ($item) {
                    return [
                        'month' => (int) $item['month'],
                        'year_offset' => (int) $item['year_offset'],
                        'amount' => (int) $item['amount'],
                    ];
                })->values()->toArray();
            }

            // Create Track
            $track = PpdbTrack::create([
                'ppdb_wave_id' => $wave->id,
                'school_id' => $request->school_id,
                'registration_type' => $request->registration_type,
                'name' => $request->name,
                'quota' => $request->quota,
                'registration_fee' => $request->registration_fee ?? 0,
                'is_open' => true,
                'installment_plan' => $installmentPlan,
            ]);

            // Sync billing (create BillType & PaymentRate)
            if ($installmentPlan) {
                $billingService = new PsbBillingService();
                $billingService->syncTrackBillType($track);
            }

            DB::commit();

            return redirect()
                ->route('psb.waves.show', $waveId)
                ->with('success', 'Jalur pendaftaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update an existing Track.
     */
    public function updateTrack(Request $request, $waveId, $trackId)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'school_id' => 'required|exists:schools,id',
            'registration_type' => 'required|in:UMUM,JAMAAH,ALUMNI',
            'quota' => 'required|integer|min:0',
            'registration_fee' => 'nullable|integer|min:0',
            'is_open' => 'boolean',
            'installments' => 'nullable|array',
            'installments.*.month' => 'required_with:installments|integer|between:1,12',
            'installments.*.year_offset' => 'required_with:installments|integer|between:0,3',
            'installments.*.amount' => 'required_with:installments|integer|min:0',
        ]);

        $track = PpdbTrack::where('ppdb_wave_id', $waveId)->findOrFail($trackId);

        DB::beginTransaction();
        try {
            // Build installment plan JSON
            $installmentPlan = null;
            if ($request->has('installments') && is_array($request->installments)) {
                $installmentPlan = collect($request->installments)->map(function ($item) {
                    return [
                        'month' => (int) $item['month'],
                        'year_offset' => (int) $item['year_offset'],
                        'amount' => (int) $item['amount'],
                    ];
                })->values()->toArray();
            }

            // Update Track
            $track->update([
                'school_id' => $request->school_id,
                'registration_type' => $request->registration_type,
                'name' => $request->name,
                'quota' => $request->quota,
                'registration_fee' => $request->registration_fee ?? 0,
                'is_open' => $request->boolean('is_open', true),
                'installment_plan' => $installmentPlan,
            ]);

            // Sync billing
            if ($installmentPlan) {
                $billingService = new PsbBillingService();
                $billingService->syncTrackBillType($track);
            }

            DB::commit();

            return redirect()
                ->route('psb.waves.show', $waveId)
                ->with('success', 'Jalur pendaftaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a Track.
     */
    public function destroyTrack($waveId, $trackId)
    {
        $track = PpdbTrack::where('ppdb_wave_id', $waveId)->findOrFail($trackId);

        // Check if there are any registrations
        if ($track->registrations()->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'Tidak dapat menghapus jalur yang sudah memiliki pendaftar.');
        }

        $track->delete();

        return redirect()
            ->route('psb.waves.show', $waveId)
            ->with('success', 'Jalur pendaftaran berhasil dihapus.');
    }

    /**
     * Get Track data for AJAX (for edit modal).
     */
    public function getTrack($waveId, $trackId)
    {
        $track = PpdbTrack::where('ppdb_wave_id', $waveId)->findOrFail($trackId);

        return response()->json([
            'id' => $track->id,
            'name' => $track->name,
            'school_id' => $track->school_id,
            'registration_type' => $track->registration_type,
            'quota' => $track->quota,
            'registration_fee' => $track->registration_fee,
            'is_open' => $track->is_open,
            'installment_plan' => $track->installment_plan ?? [],
        ]);
    }
}
