<?php

namespace App\Http\Controllers\User;

use App\Models\Ppdb;
use App\Models\PpdbType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\PpdbRegistration;

use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Services\TransactionService;
use App\Http\Requests\User\WaliPpdbRequest;
use App\Jobs\SendBillWhatsappNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use Illuminate\Support\Facades\DB; // Add this line at the top

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

            if ($request->hasFile('family_card_image') && $request->hasFile('birth_certificate_image') && $request->hasFile('raport_image') && $request->hasFile('father_identity_image') && $request->hasFile('mother_identity_image')) {
                $ppdbRegistration->ppdbDocument()->create([
                    'family_card_image' => 'storage/' . $request->file('family_card_image')->store('images/ppdb/family_card', 'public'),
                    'birth_certificate_image' => 'storage/' . $request->file('birth_certificate_image')->store('images/ppdb/birth_certificate', 'public'),
                    'raport_image' => 'storage/' . $request->file('raport_image')->store('images/ppdb/raport', 'public'),
                    'father_identity_image' => 'storage/' . $request->file('father_identity_image')->store('images/ppdb/father_identity', 'public'),
                    'mother_identity_image' => 'storage/' . $request->file('mother_identity_image')->store('images/ppdb/mother_identity', 'public'),
                ]);
            } else {
                return redirect()->back()->withInput()
                    ->with('error', 'Dokumen tidak lengkap');
            }

            // create transaction
            $paymentMethodType = PaymentMethod::where('type', PaymentMethod::TYPE_XENDIT)->firstOrFail();

            // get register fee from ppdb
            $registerFee = $ppdb->register_fee ?? 0;

            $transaction = TransactionService::createPaymentPpdb($request, $paymentMethodType, $registerFee, $ppdbRegistration);

            TransactionService::createInvoice($transaction);

            if ($transaction->status == Transaction::STATUS_PAID) {
                TransactionService::dispatchNotifications($transaction);
            }

            // send notification to whatsapp
            $message = SendNotifWaService::sendMessageUnpaidPpdb($ppdbRegistration);

            dispatch(new SendToWhatsappNotificationJob($ppdbRegistration->user->phone, $message));

            DB::commit();

            return redirect()->route('wali.ppdb-history.show', $ppdbRegistration->id)
                ->with('success', 'Berhasil Mendaftar PPDB, silahkan melakukan pembayaran');
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
        // $ppdb = Ppdb::findOrFail($id);
        // return view('users.ppdb.show', compact('ppdb'));
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
