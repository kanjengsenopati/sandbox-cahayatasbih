<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Requests\Admin\TransactionRequest;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('report-transaction.index');
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
    public function store(TransactionRequest $request)
    {
        if (!auth()->user()?->can('Create Transaksi') && !auth()->user()?->can('Create Tagihan') && !auth()->user()?->hasRole('Super Admin')) {
            return $this->failedResponse("Maaf, Anda tidak memiliki izin untuk melakukan transaksi pembayaran.", 403);
        }

        DB::beginTransaction();
        try {
            // Generate payment code
            $paymentCode = 'CHT-' . Str::random(3) . time();

            // Create transaction
            $transactionData = [
                'payment_code' => $paymentCode,
                'student_id' => $request->student_id,
                'expiry_time' => Carbon::now()->addDay(),
                'status' => $request->payment_method_id == PaymentMethod::CASH_PAYMENT ? Transaction::STATUS_PAID : Transaction::STATUS_PENDING,
                'paid_at' => $request->payment_method_id == PaymentMethod::CASH_PAYMENT ? $request->date : null,
            ];
            $transaction = Transaction::create(array_merge($transactionData, $request->validated()));

            // Create transaction detail
            $transaction->transactionDetails()->create([
                'bill_id' => $request->bill_id,
            ]);


            // Change status to paid if cash payment in bill
            if ($transaction->status == Transaction::STATUS_PAID) {
                TransactionService::changeStatusToPaid($transaction);
            }
            DB::commit();
            return $this->postSuccessResponse("Berhasil melakukan transaksi pembayaran", $transaction);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->failedResponse("Gagal melakukan transaksi pembayaran");
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


    private function normalizeWaPhone(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $raw);
        if ($digits === '' || strlen($digits) < 8) {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }
        return $digits;
    }

    private function resolveUptTreasurerPhones(): array
    {
        $defaultWa = $this->normalizeWaPhone(\App\Models\ApplicationSetting::first()?->number_whatsapp) ?? '6281326368494';

        $uptPhones = [
            'SMP' => null,
            'MA' => null,
            'PONDOK' => null,
            'DEFAULT' => $defaultWa,
        ];

        $treasurerAdmins = \App\Models\Admin::with('roles')
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'like', '%Bendahara%');
            })
            ->get();

        foreach ($treasurerAdmins as $admin) {
            $phone = $this->normalizeWaPhone($admin->phone);
            if (!$phone) {
                continue;
            }
            $roleNamesLower = strtolower($admin->roles->pluck('name')->implode(' '));
            if (str_contains($roleNamesLower, 'smp') && empty($uptPhones['SMP'])) {
                $uptPhones['SMP'] = $phone;
            }
            if (preg_match('/\bma\b/i', $roleNamesLower) && empty($uptPhones['MA'])) {
                $uptPhones['MA'] = $phone;
            }
            if (str_contains($roleNamesLower, 'pondok') && empty($uptPhones['PONDOK'])) {
                $uptPhones['PONDOK'] = $phone;
            }
        }

        return $uptPhones;
    }

    public function invoice($id)
    {
        if (!auth()->user()?->can('Manage Transaksi') && !auth()->user()?->can('Manage Laporan Transaksi') && !auth()->user()?->can('Manage Tagihan') && !auth()->user()?->hasRole('Super Admin')) {
            abort(403, 'Maaf, Anda tidak memiliki izin untuk melihat invoice transaksi.');
        }

        $data = Transaction::with([
            'student.classroom.school',
            'transactionDetails.bill.billType.academicYear',
            'transactionDetails.bill.academicYear',
            'transactionDetails.saldoHistory',
            'transactionDetails.savingHistory',
            'paymentMethod',
            'admin.roles'
        ])->findOrFail($id);

        $transactions = collect([$data]);
        $uptWaNumbers = $this->resolveUptTreasurerPhones();

        $pdf = Pdf::loadView('admins.pdf.transaction-invoice', compact('data', 'transactions', 'uptWaNumbers'))
            ->setPaper('a4', 'portrait');
        return $pdf->stream("Invoice {$data->payment_code}.pdf");
    }

    public function invoiceBatch(Request $request)
    {
        if (!auth()->user()?->can('Manage Transaksi') && !auth()->user()?->can('Manage Laporan Transaksi') && !auth()->user()?->can('Manage Tagihan') && !auth()->user()?->hasRole('Super Admin')) {
            abort(403, 'Maaf, Anda tidak memiliki izin untuk melihat invoice transaksi.');
        }

        $rawIds = $request->input('ids', []);
        if (is_string($rawIds)) {
            $ids = array_values(array_filter(array_map('trim', explode(',', $rawIds))));
        } elseif (is_array($rawIds)) {
            $ids = array_values(array_filter(array_map('trim', $rawIds)));
        } else {
            $ids = [];
        }

        if (empty($ids)) {
            abort(400, 'Silakan pilih minimal 1 transaksi untuk dicetak.');
        }

        $ids = array_slice(array_unique($ids), 0, 60);

        $itemsById = Transaction::with([
            'student.classroom.school',
            'transactionDetails.bill.billType.academicYear',
            'transactionDetails.bill.academicYear',
            'transactionDetails.saldoHistory',
            'transactionDetails.savingHistory',
            'paymentMethod',
            'admin.roles'
        ])->whereIn('id', $ids)->get()->keyBy('id');

        $transactions = collect($ids)
            ->map(fn($id) => $itemsById->get($id))
            ->filter()
            ->values();

        if ($transactions->isEmpty()) {
            abort(404, 'Data transaksi tidak ditemukan.');
        }

        $data = $transactions->first();
        $uptWaNumbers = $this->resolveUptTreasurerPhones();

        $pdf = Pdf::loadView('admins.pdf.transaction-invoice', compact('data', 'transactions', 'uptWaNumbers'))
            ->setPaper('a4', 'portrait');

        $filename = $transactions->count() === 1
            ? "Invoice {$data->payment_code}.pdf"
            : 'Bukti-Bayar-Batch-' . $transactions->count() . '-Transaksi-' . date('Ymd-His') . '.pdf';

        return $pdf->stream($filename);
    }
}
