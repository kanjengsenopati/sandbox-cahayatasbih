<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\School;
use App\Models\BillType;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Http\Requests\Admin\PaymentRateRequest;

class PaymentRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $billType = BillType::findOrFail(request()->get('bill_type_id'));
        $schools = School::orderBy('name')->get();
        $classroomValue = [];
        return view('admins.payment-rate.create-edit', compact('billType', 'schools', 'classroomValue'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(PaymentRateRequest $request)
    {
        try {
            DB::beginTransaction();

            $billType = BillType::findOrFail($request->bill_type_id);

            $paymentRate = $billType->paymentRates()->create([
                'amount' => $request->price,
            ]);

            // create payment rate classroom
            foreach ($request->classrooms as $classroom) {
                $paymentRate->paymentRateClassrooms()->create([
                    'classroom_id' => $classroom,
                ]);
            }

            // create bill to all student where classroom_id in classrooms
            $classrooms = Classroom::whereIn('id', $request->classrooms)->get();
            $months = [];
            if ($billType->type == BillType::TYPE_MONTHLY) {
                $months = range(1, 12);
                foreach ($months as $month) {
                    foreach ($classrooms as $classroom) {
                        foreach ($classroom->students as $student) {
                            $billAmount = $request->{"bulan_$month"}; // Get amount for current month
                            $billYear = $request->{"tahun_$month"}; // Get year for current month
                            $student->bills()->create([
                                'bill_type_id' => $billType->id,
                                'classroom_id' => $classroom->id,
                                'academic_year_id' => $billType->academic_year_id,
                                'month' => $month,
                                'year' => $billYear,
                                'amount' => $billAmount,
                                'status' => 'UNPAID',
                            ]);
                        }
                    }
                }
            } else {
                $months = $request->months;
                foreach ($months as $month) {
                    foreach ($classrooms as $classroom) {
                        foreach ($classroom->students as $student) {
                            $billAmount = $request->price; // Get amount for current month
                            $billYear = $request->year;
                            $student->bills()->create([
                                'bill_type_id' => $billType->id,
                                'classroom_id' => $classroom->id,
                                'academic_year_id' => $billType->academic_year_id,
                                'month' => $month,
                                'year' => $billYear,
                                'amount' => $billAmount,
                                'status' => 'UNPAID',
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('bill-type.index')->with('success', 'Tarif pembayaran berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
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

    public function getClassroom(Request $request)
    {
        $school = School::findOrFail($request->school_id);
        $classrooms = Classroom::where('school_id', $school->id)->orderBy('name')->get();
        return response()->json($classrooms);
    }
}
