<?php

namespace App\Http\Controllers\Admin;

use App\Models\PpdbTrack;
use App\Models\PpdbWaves;
use App\Models\School;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PpdbTrackRequest;
use App\Services\PsbBillingService;
use Illuminate\Support\Facades\DB;

class PpdbTrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PpdbTrackRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Build installment plan JSON from form array
            $data['installment_plan'] = $this->buildInstallmentPlan($request);
            $data['name'] = $request->input('name', $data['registration_type']);

            $track = PpdbTrack::create($data);

            // Sync billing if installment plan exists
            if (!empty($data['installment_plan'])) {
                $billingService = new PsbBillingService();
                $billingService->syncTrackBillType($track);
            }

            DB::commit();
            return redirect()->route('ppdb-waves.show', $data['ppdb_wave_id'])
                ->with('success', 'Track PPDB berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PpdbTrack $ppdbTrack)
    {
        return response()->json([
            'id' => $ppdbTrack->id,
            'school_id' => $ppdbTrack->school_id,
            'registration_type' => $ppdbTrack->registration_type,
            'name' => $ppdbTrack->name,
            'registration_fee' => $ppdbTrack->registration_fee,
            'quota' => $ppdbTrack->quota,
            'is_open' => $ppdbTrack->is_open,
            'close_reason' => $ppdbTrack->close_reason,
            'link_whatsapp_group' => $ppdbTrack->link_whatsapp_group,
            'installment_plan' => $ppdbTrack->installment_plan ?? [],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PpdbTrackRequest $request, PpdbTrack $ppdbTrack)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Build installment plan JSON from form array
            $data['installment_plan'] = $this->buildInstallmentPlan($request);
            $data['name'] = $request->input('name', $data['registration_type']);

            $ppdbTrack->update($data);

            // Sync billing if installment plan exists
            if (!empty($data['installment_plan'])) {
                $billingService = new PsbBillingService();
                $billingService->syncTrackBillType($ppdbTrack);
            }

            DB::commit();
            return redirect()->route('ppdb-waves.show', $ppdbTrack->ppdb_wave_id)
                ->with('success', 'Track PPDB berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PpdbTrack $ppdbTrack)
    {
        $ppdbTrack->delete();
        return redirect()->route('ppdb-waves.show', $ppdbTrack->ppdb_wave_id)
            ->with('success', 'Track PPDB berhasil dihapus');
    }

    /**
     * Build installment plan array from form input.
     */
    private function buildInstallmentPlan(Request $request): ?array
    {
        if (!$request->has('installments') || !is_array($request->installments)) {
            return null;
        }

        $installments = collect($request->installments)
            ->filter(fn($item) => isset($item['month']) && isset($item['amount']) && $item['amount'] > 0)
            ->map(fn($item) => [
                'month' => (int) $item['month'],
                'year_offset' => (int) ($item['year_offset'] ?? 0),
                'amount' => (int) $item['amount'],
            ])
            ->values()
            ->toArray();

        return count($installments) > 0 ? $installments : null;
    }
}
