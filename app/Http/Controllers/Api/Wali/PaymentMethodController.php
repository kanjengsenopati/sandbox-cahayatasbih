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

        $appSetting = \App\Models\ApplicationSetting::first();
        $allowSaldoWali = !($appSetting && isset($appSetting->allow_pwa_saldo_payment_wali) && !$appSetting->allow_pwa_saldo_payment_wali);
        $disabledSaldoReason = !empty($appSetting->pwa_saldo_payment_disabled_message)
            ? $appSetting->pwa_saldo_payment_disabled_message
            : 'Pembayaran tagihan menggunakan Saldo di PWA Wali Santri sedang dinonaktifkan sementara oleh Pengelola.';

        // Fetch payment methods (Transfer and Balance)
        $methods = PaymentMethod::where('is_active', true)
            ->whereIn('type', [PaymentMethod::TYPE_TRANSFER, PaymentMethod::TYPE_BALANCE])
            ->get()
            ->map(function($m) use ($schoolId, $type, $billIds, $allowSaldoWali, $disabledSaldoReason) {
                $banks = [];
                $isDisabled = false;
                $disabledReason = null;

                if ($m->type === PaymentMethod::TYPE_BALANCE && !$allowSaldoWali) {
                    $isDisabled = true;
                    $disabledReason = $disabledSaldoReason;
                }

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
                    'banks' => $banks,
                    'is_disabled' => $isDisabled,
                    'disabled_reason' => $disabledReason
                ];
            });

        return response()->json($methods);
    }
}
