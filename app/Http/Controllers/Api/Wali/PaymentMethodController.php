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
        $isSaldoVisible = $activeStudent->isPwaSaldoVisible();
        $allowSaldoWali = $activeStudent->isPwaSaldoPaymentAllowed() && $isSaldoVisible;
        $disabledSaldoReason = !empty($appSetting->pwa_saldo_payment_disabled_message)
            ? $appSetting->pwa_saldo_payment_disabled_message
            : 'Pembayaran tagihan menggunakan Saldo di PWA Wali Santri sedang dinonaktifkan untuk rombel / jenjang ini.';

        $allowedTypes = [PaymentMethod::TYPE_TRANSFER];
        if ($isSaldoVisible && $allowSaldoWali) {
            $allowedTypes[] = PaymentMethod::TYPE_BALANCE;
        }

        // Fetch payment methods (Transfer and Balance if allowed)
        $methods = PaymentMethod::where('is_active', true)
            ->whereIn('type', $allowedTypes)
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
