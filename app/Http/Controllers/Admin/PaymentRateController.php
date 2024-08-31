<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\User;
use App\Models\School;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentRate;
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
    public function index() {}

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

            // create payment rate item for each month
            $months = [];
            if ($billType->type == BillType::TYPE_MONTHLY) {
                $months = range(1, 12);
                foreach ($months as $month) {
                    $paymentRate->paymentRateItems()->create([
                        'month' => $month,
                        'year' => $request->{"tahun_$month"},
                        'amount' => $request->{"bulan_$month"},
                    ]);
                }
            } else {
                $months = $request->months;
                foreach ($months as $month) {
                    $paymentRate->paymentRateItems()->create([
                        'month' => $month,
                        'year' => $request->year,
                        'amount' => $request->price,
                    ]);
                }
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
                            $paymentRateItem = $paymentRate->paymentRateItems()->where('month', $month)->first();
                            $existingBill = $student->bills()->where('bill_type_id', $billType->id)
                                ->where('month', $month)
                                ->where('year', $billYear)
                                ->first();
                            if (!$existingBill) {
                                $student->bills()->create([
                                    'bill_type_id' => $billType->id,
                                    'classroom_id' => $classroom->id,
                                    'academic_year_id' => $billType->academic_year_id,
                                    'month' => $month,
                                    'year' => $billYear,
                                    'amount' => $billAmount,
                                    'status' => 'UNPAID',
                                    'payment_rate_item_id' => $paymentRateItem?->id ?? null,
                                ]);
                            }
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
                            $paymentRateItem = $paymentRate->paymentRateItems()->where('month', $month)->first();
                            $existingBill = $student->bills()->where('bill_type_id', $billType->id)
                                ->where('month', $month)
                                ->where('year', $billYear)
                                ->first();
                            if (!$existingBill) {
                                $student->bills()->create([
                                    'bill_type_id' => $billType->id,
                                    'classroom_id' => $classroom->id,
                                    'academic_year_id' => $billType->academic_year_id,
                                    'month' => $month,
                                    'year' => $billYear,
                                    'amount' => $billAmount,
                                    'status' => 'UNPAID',
                                    'payment_rate_item_id' => $paymentRateItem?->id ?? null,
                                ]);
                            }
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
    public function edit(PaymentRate $paymentRate)
    {
        $schools = School::orderBy('name')->get();
        $billType = $paymentRate->billType;
        $classrooms = Classroom::orderBy('name')->get();
        return view('admins.payment-rate.create-edit', compact('paymentRate', 'schools', 'billType', 'classrooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRateRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $billType = BillType::findOrFail($request->bill_type_id);
            $paymentRate = PaymentRate::findOrFail($id);

            // Update payment rate amount
            $paymentRate->update([
                'amount' => $request->price,
            ]);

            // Update payment rate items for each month
            if ($billType->type == BillType::TYPE_MONTHLY) {
                for ($month = 1; $month <= 12; $month++) {
                    $paymentRateItem = $paymentRate?->paymentRateItems()?->where('month', $month)?->first();
                    if ($paymentRateItem) {
                        $paymentRateItem->update([
                            'year' => $request->input("tahun_$month"),
                            'amount' => $request->input("bulan_$month"),
                        ]);

                        Bill::where('payment_rate_item_id', $paymentRateItem->id)
                            ->update(['amount' => $request->input("bulan_$month")]);
                    }
                }
            } else {
                foreach ($request->months as $month) {
                    $paymentRateItem = $paymentRate?->paymentRateItems()?->where('month', $month)?->first();
                    if ($paymentRateItem) {
                        $paymentRateItem->update([
                            'year' => $request->year,
                            'amount' => $request->price,
                        ]);

                        // Update corresponding bills
                        Bill::where('payment_rate_item_id', $paymentRateItem->id)
                            ->update(['amount' => $request->price]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('bill-type.index')->with('success', 'Tarif pembayaran berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
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
