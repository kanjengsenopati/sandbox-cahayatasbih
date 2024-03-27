<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\WaliPpdbRequest;
use App\Models\Ppdb;
use App\Models\PpdbRegistration;
use App\Models\PpdbType;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB; // Add this line at the top
use Illuminate\Support\Facades\Log;

class WaliPpdbController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ppdbs = Ppdb::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('is_active', true)
            ->latest()
            ->get();
        return view('users.ppdb.index', compact('ppdbs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ppdb = Ppdb::findOrFail(request()->ppdb_id);
        return view('users.ppdb.create-edit', compact('ppdb'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(WaliPpdbRequest $request)
    {
        try {
            DB::beginTransaction();

            $ppdb = Ppdb::findOrFail($request->ppdb_id);
            $data = $request->validated();
            $data['no_reg'] = 'PPDB-' . date('YmdHis') . '-' . rand(1000, 9999);
            $data['register_fee'] = $ppdb->register_fee ?? 0;
            $data['status'] = 'PENDING';
            $data['user_id'] = auth('wali')->user()->id;
            $data['payment_status'] = 'UNPAID';

            if ($request->hasFile('photo_card')) {
                $data['photo_card'] = 'storage/' . $request->file('photo_card')->store('images/photo_card', 'public');
            }

            $data['is_member'] = $ppdb->ppdbType->type == PpdbType::TYPE_JAMAAH ? true : false;

            $ppdbRegistration = PpdbRegistration::create($data);
            $ppdbRegistration->ppdbStudents()->create($data);
            $ppdbRegistration->ppdbParents()->create($data);

            DB::commit();

            return redirect()->route('wali.ppdb.index')
                ->with('success', 'Berhasil menambahkan pendaftaran PPDB');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan pendaftaran PPDB');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ppdb = Ppdb::findOrFail($id);
        return view('users.ppdb.show', compact('ppdb'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
