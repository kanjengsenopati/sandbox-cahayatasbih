<?php
 
namespace App\Http\Controllers\Api\Wali;

use App\Models\Bank;
use App\Models\TopupBank;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends BaseWaliApiController
{
    public function index(Request $request)
    {
        $activeStudent = $this->resolveActiveStudent();
        if (!$activeStudent) {
            return response()->json(['message' => 'Active student not found'], 404);
        }

        $schoolId = $activeStudent->classroom->school_id ?? null;
        $type = $request->get('type', 'SALDO');
        $billIds = $request->get('bill_ids', []);

        // Fetch payment methods (Transfer only)
        $methods = PaymentMethod::where('is_active', true)
            ->where('type', PaymentMethod::TYPE_TRANSFER)
            ->get()
            ->map(function($m) use ($schoolId, $type, $billIds) {
                $banks = [];
                if ($m->type === PaymentMethod::TYPE_TRANSFER) {
                    if ($type === 'BILL' && !empty($billIds)) {
                        // For bills, get banks associated with the bill types
                        $billTypeIds = \App\Models\Bill::whereIn('id', $billIds)->pluck('bill_type_id')->unique();
                        $banks = \App\Models\BillTypeBank::with('bank')
                            ->whereIn('bill_type_id', $billTypeIds)
                            ->get()
                            ->pluck('bank');
                    } else {
                        // For topup, we look at TopupBank records for this school
                        $banks = TopupBank::with('bank')
                            ->where('school_id', $schoolId)
                            ->where('type', $type === 'SAVING' ? TopupBank::TYPE_SAVING : TopupBank::TYPE_SALDO)
                            ->get()
                            ->pluck('bank');
                    }
                }

                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'type' => $m->type,
                    'banks' => $banks
                ];
            });

        return response()->json($methods);
    }
}
