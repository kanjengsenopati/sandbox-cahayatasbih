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

        // Fetch payment methods (Transfer, Xendit, etc)
        $methods = PaymentMethod::where('is_active', true)
            ->whereIn('type', [PaymentMethod::TYPE_TRANSFER, PaymentMethod::TYPE_XENDIT])
            ->get()
            ->map(function($m) use ($schoolId) {
                $banks = [];
                if ($m->type === PaymentMethod::TYPE_TRANSFER) {
                    // For topup, we look at TopupBank records for this school
                    $banks = TopupBank::with('bank')
                        ->where('school_id', $schoolId)
                        ->where('type', TopupBank::TYPE_SALDO)
                        ->get()
                        ->pluck('bank');
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
