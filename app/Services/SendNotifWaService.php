<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Bill;
use GuzzleHttp\Client;
use App\Models\BillType;
use App\Models\Transaction;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Auth;

class SendNotifWaService
{
    public static function sendMessage($number, $message)
    {
        $app_setting = ApplicationSetting::first();
        $url = $app_setting->link_whatsapp;
        $deviceId = $app_setting->device_id;

        $client = new Client();

        try {
            $response = $client->get($url, [
                'query' => [
                    'device_id' => $deviceId,
                    'number' => $number,
                    'message' => $message,
                ],
            ]);

            $result = $response->getBody()->getContents();

            return "<pre>" . print_r($result, true);
        } catch (\Exception $e) {
            // Handle exception
            return $e->getMessage();
        }
    }

    public static function sendMessageBillNotification($transaction)
    {
        $student = $transaction->student;
        $parentStudent = $student->user;

        $message = "Assalamualaikum Bapak/Ibu " . $parentStudent->name . ",\n";
        $message .= "PEMBAYARAN " . $transaction->payment_code . " BERHASIL\n";
        $message .= "--------------------------------\n";
        $message .= "1. NIS : *" . $student->nis . "*\n";
        $message .= "2. Nama Santri : *" . $student->name . "*\n";
        $message .= "3. Kelas : *" . $student->classroom->name . "*\n";
        $message .= "4. Metode Pembayaran : *" . $transaction->getTranslatedPaymentMethod() . "*\n";
        if ($transaction?->admin_id) {
            $message .= "5. Petugas : *" . $transaction->admin?->name . "*\n";
        }

        switch ($transaction->type) {
            case Transaction::TYPE_BILL:
                $totalAmount = 0;
                $billCount = count($transaction->transactionDetails);
                foreach ($transaction->transactionDetails as $detail) {
                    $bill = $detail->bill;
                    $totalAmount += $bill->amount;
                    $message .= "--------------------------------\n";
                    $message .= "Pembayaran : *" . $bill->billType->name .
                        " " . $bill->translated_month . " " . $bill->academicYear->name . "*\n";
                    $message .= "Nominal Pembayaran : *Rp." . number_format($bill->amount, 0, ',', '.') . "*\n";
                }
                if ($billCount > 1) {
                    $message .= "--------------------------------\n";
                    $message .= "Total Pembayaran : *Rp." . number_format($totalAmount, 0, ',', '.') . "*\n";
                }
                break;
            case Transaction::TYPE_SAVING:
                $message .= "4. Pembayaran : *Tabungan Santri*\n";
                $message .= "5. Total Pembayaran : *Rp." . number_format($transaction->pay_amount, 0, ',', '.') . "*\n";
                $message .= "6. Tabungan Terkini : *Rp." . number_format($student->saving, 0, ',', '.') . "*\n";
                break;
            case Transaction::TYPE_SALDO:
                $message .= "4. Transaksi : *Saldo Santri*\n";
                $message .= "5. Nominal : *Rp." . number_format($transaction->pay_amount, 0, ',', '.') . "*\n";
                $message .= "6. Saldo Terkini : *Rp." . number_format($student->saldo, 0, ',', '.') . "*\n";
                break;
            default:
                $message .= "--------------------------------\n";
                $message .= "Pembayaran : *Pembayaran Lainnya*\n";
                $message .= "Total Pembayaran : *Rp." . number_format($transaction->pay_amount, 0, ',', '.') . "*\n";
                break;
        }

        $message .= "--------------------------------\n";
        $message .= "Link Kuitansi Digital : *" . route('transaction.invoice', $transaction->id) . "*\n";
        $message .= "--------------------------------\n";
        $message .= "Note : _Transaksi Berhasil dan Kuitansi digital dapat diakses melalui link di atas_\n";
        $message .= "*Tidak perlu dibalas, Silahkan share / forward ke grup Wali Santri*_\n";
        $message .= "Wassalamualaikum Wr. Wb.\n";
        $message .= "*PPTQ CAHAYA TASBIH*";

        return $message;
    }

    public static function sendMessageUnpaidNotification($student, $billTypes)
    {
        $parentStudent = $student->user;

        $message = "Assalamualaikum Bapak/Ibu " . $parentStudent->name . ",\n";
        $message .= "Anda memiliki Kewajiban Administrasi Keuangan yang belum terbayar, sebagai berikut:\n";
        $message .= "--------------------------------\n";
        $message .= "1. NIS : *" . $student->nis . "*\n";
        $message .= "2. Nama Santri : *" . $student->name . "*\n";
        $message .= "3. Kelas : *" . $student->classroom->name . "*\n";

        $must_pay = 0;
        foreach ($billTypes as $billType) {
            if ($billType->type == BillType::TYPE_OTHER) {
                $total_unpaid = $billType->bills
                    ->where('student_id', $student->id)->where('status', Bill::STATUS_UNPAID)
                    ->sum('amount');
                $must_pay += $total_unpaid;
                $message .= "--------------------------------\n";
                $message .= "Tagihan : *" . $billType->name . "*\n";
                $message .= "Total Tagihan : *Rp." . number_format($billType->bills->where('student_id', $student->id)
                    ->sum('amount'), 0, ',', '.') . "*\n";
                $message .= "Total Kekurangan: *Rp." . number_format($total_unpaid, 0, ',', '.') . "*\n";
                $message .= "Status Pembayaran : *" . ($total_unpaid > 0 ? 'Belum Lunas' : 'Lunas') . "*\n";
            } else {
                $thisMonthInInteger = intval(date('n'));
                $unpaidBills = $billType->bills()
                    ->where('student_id', $student->id)
                    ->where('status', Bill::STATUS_UNPAID)
                    ->where('month', '<=', $thisMonthInInteger)
                    ->get();

                $totalUnpaid = $unpaidBills->sum('amount');
                $listMonth = $unpaidBills->pluck('translated_month')->toArray();

                $must_pay += $totalUnpaid;
                $message .= "Tagihan : *" . $billType->name . ' ' . $billType->academicYear->name . "*\n";
                $message .= "Bulan : *" . implode(', ', $listMonth) . "*\n";
                $message .= "Jumlah Tagihan: *Rp." . number_format($totalUnpaid, 0, ',', '.') . "*\n";
                $message .= "Status Pembayaran : *" . ($totalUnpaid > 0 ? 'Belum Lunas' : 'Lunas') . "*\n";
            }
        }
        $message .= "\n";
        $message .= "--------------------------------\n";
        $message .= "Total Kekurangan : *Rp." . number_format($must_pay, 0, ',', '.') . "*\n";
        $message .= "--------------------------------\n";
        $message .= "Note : _Jika sudah melakukan pembayaran, abaikan pesan ini_\n";
        $message .= "Wassalamualaikum Wr. Wb.\n";
        $message .= "*PPTQ CAHAYA TASBIH*";

        return $message;
    }


    public static function sendMessageUnpaidPpdb($ppdbRegistration)
    {
        $parentStudent = $ppdbRegistration->user;

        $message = "Assalamualaikum Bapak/Ibu " . $parentStudent->name . ",\n";
        $message .= "Pendaftaran PPDB Putra/Putri Anda Berhasil, Silahkan Melakukan Pembayaran Pendaftaran sebagai berikut:\n";
        $message .= "--------------------------------\n";
        $message .= "1. No Registrasi : *" . $ppdbRegistration->no_reg . "*\n";
        $message .= "2. Nama Santri : *" . $ppdbRegistration->ppdbStudents->first()->name . "*\n";
        $message .= "3. Sekolah : *" . $ppdbRegistration->ppdb->school->name . "*\n";
        $message .= "4. Program : *" . $ppdbRegistration->ppdb->name . "*\n";
        $message .= "5. Tanggal Pendaftaran : *" . $ppdbRegistration->created_at->format('d-m-Y') . "*\n";
        $message .= "6. Biaya Pendaftaran : *Rp." . number_format($ppdbRegistration->register_fee, 0, ',', '.') . "*\n";
        $message .= "7. Status Pembayaran : *Belum Lunas*\n";
        $message .= "--------------------------------\n";
        $message .= "Link Pembayaran : *" . route('wali.ppdb-history.show', $ppdbRegistration->id) . "*\n";
        $message .= "--------------------------------\n";
        $message .= "Note : _Jika sudah melakukan pembayaran, abaikan pesan ini_\n";
        $message .= "Wassalamualaikum Wr. Wb.\n";
        $message .= "*PPTQ CAHAYA TASBIH*";

        return $message;
    }

    public static function sendMessageConfirmPpdb($ppdbRegistration)
    {
        $parentStudent = $ppdbRegistration->user;

        $message = "Assalamualaikum Bapak/Ibu " . $parentStudent->name . ",\n";
        $message .= "Pendaftaran PPDB Putra/Putri Anda " . $ppdbRegistration->translated_status . "\n";
        $message .= "--------------------------------\n";
        $message .= "1. No Registrasi : *" . $ppdbRegistration->no_reg . "*\n";
        $message .= "2. Nama Santri : *" . $ppdbRegistration->ppdbStudents->first()->name . "*\n";
        $message .= "3. Sekolah : *" . $ppdbRegistration->ppdb->school->name . "*\n";
        $message .= "4. Program : *" . $ppdbRegistration->ppdb->name . "*\n";
        $message .= "5. Tanggal Pendaftaran : *" . $ppdbRegistration->created_at->format('d-m-Y') . "*\n";
        $message .= "6. Biaya Pendaftaran : *Rp." . number_format($ppdbRegistration->register_fee, 0, ',', '.') . "*\n";
        $message .= "7. Status : *" . $ppdbRegistration->translated_status . "*\n";
        $message .= "8. Catatan : *" . $ppdbRegistration->note . "*\n";
        $message .= "--------------------------------\n";
        $message .= "Wassalamualaikum Wr. Wb.\n";
        $message .= "*PPTQ CAHAYA TASBIH*";

        return $message;
    }

    public static function sendMessagePendingTransferPayment($transaction)
    {
        $student = $transaction->student;
        $parentStudent = $student->user;

        $message = "APLIKASI PPTQ CAHAYA TASBIH\n";
        $message .= "--------------------------------\n";
        if ($transaction->status == Transaction::STATUS_PENDING_CONFIRMATION) {
            $message .= "*-STATUS MENUNGGU VERIFIKASI PETUGAS-*\n";
            $message .= "--------------------------------\n";
            $message .= "Assalamu'alaikum Bapak / Ibu " . $parentStudent->name . ",\n";
            $message .= "Terima kasih sudah melakukan upload bukti pembayaran *" . $transaction->payment_code . "* sebesar *Rp. " . number_format($transaction->pay_amount, 0, ',', '.') . "*.\n";
            $message .= "*Mohon menunggu verifikasi petugas untuk segera diproses.*\n";
        } else {
            $message .= "*-STATUS MENUNGGU UPLOAD BUKTI PEMBAYARAN TRANSFER-*\n";
            $message .= "--------------------------------\n";
            $message .= "Assalamu'alaikum Bapak / Ibu " . $parentStudent->name . ",\n";
            $message .= "Terima kasih sudah melakukan transaksi *" . $transaction->payment_code . "* sebesar *Rp. " . number_format($transaction->pay_amount, 0, ',', '.') . "* dengan cara pembayaran transfer.\n";
            $message .= "*Mohon segera upload bukti pembayaran ke aplikasi untuk segera diproses petugas.*\n";
            $message .= "--------------------------------\n";
            $message .= "*PASTIKAN TRANSFER SESUAI NOMINAL DIATAS, TERMASUK 3 NOMOR TERAKHIR UNTUK KETEPATAN PELAYANAN TRANSAKSI.*\n";
            $message .= "--------------------------------\n";
        }
        // $message .= "italic Tidak perlu dibalas
        $message .= "_*Tidak perlu dibalas, Tidak perlu dibalas, Silahkan share / forward ke grup Wali Santri*_\n";

        return $message;
    }


    public static function sendMessageRejectedPayment($transaction)
    {
        $student = $transaction->student;
        $parentStudent = $student->user;

        $message = "APLIKASI PPTQ CAHAYA TASBIH\n";
        $message .= "--------------------------------\n";
        $message .= "*-STATUS PEMBAYARAN DITOLAK-*\n";
        $message .= "--------------------------------\n";
        $message .= "Assalamu'alaikum Bapak / Ibu " . $parentStudent->name . ",\n";
        $message .= "Mohon maaf, pembayaran *" . $transaction->payment_code . "* sebesar *Rp. " . number_format($transaction->pay_amount, 0, ',', '.') . "* ditolak.\n";
        $message .= "Alasan : " . $transaction?->activeProof?->note . "\n";
        $message .= "*Mohon segera upload ulang bukti pembayaran untuk segera diproses petugas.*\n";
        $message .= "--------------------------------\n";
        $message .= "*PASTIKAN TRANSFER SESUAI NOMINAL DIATAS, TERMASUK 3 NOMOR TERAKHIR UNTUK KETEPATAN PELAYANAN TRANSAKSI.*\n";
        $message .= "--------------------------------\n";
        // $message .= "italic Tidak perlu dibalas
        $message .= "_*Tidak perlu dibalas, Tidak perlu dibalas, Silahkan share / forward ke grup Wali Santri*_\n";

        return $message;
    }

    public static function balanceAdjustment($student, $history, $type)
    {
        $parentStudent = $student->user;
        $title = $type == 'SALDO' ? 'SALDO' : 'TABUNGAN';

        // Format tanggal dengan Carbon
        if ($type == 'SALDO') {
            // Ambil tanggal dari created_at untuk SALDO
            $formattedDate = Carbon::parse($history->created_at)->locale('id')->translatedFormat('d F Y');
        } else {
            // Ambil tanggal dari date untuk SAVING
            $formattedDate = Carbon::parse($history->date)->locale('id')->translatedFormat('d F Y');
        }

        $message = "*-PENYESUAIAN " . strtoupper($title) . " SANTRI-*\n";
        $message .= "--------------------------------\n";
        $message .= "Assalamu'alaikum Bapak / Ibu " . $parentStudent->name . ",\n";
        $message .= "Pemberitahuan bahwa *" . ucfirst($title) . "* atas nama santri : \n";
        $message .= "--------------------------------\n";
        $message .= "1. Nama : *" . $student->name . "*\n";
        $message .= "2. Kelas : *" . $student->classroom?->name . "*\n";
        $message .= $history->type == 'IN' ? "3. Tambah " . ucfirst($title) . " : *Rp. " . number_format($history->amount, 0, ',', '.') . "*\n" : "3. Tarik " . ucfirst($title) . " : *Rp. " . number_format($history->amount, 0, ',', '.') . "*\n";
        $message .= "4. " . ucfirst($title) . " Terkini : *Rp. " . ($type == 'SALDO' ? number_format($student->saldo, 0, ',', '.') : number_format($student->saving, 0, ',', '.')) . "*\n";
        $message .= "5. Tanggal : *" . $formattedDate . "*\n";
        if (Auth::id() != $student->id) {
            $message .= "6. Petugas : *" . Auth::user()->name . "*\n";
        }
        $message .= "--------------------------------\n";
        $message .= "_*Tidak perlu dibalas, Silahkan share / forward ke grup Wali Santri*_\n";

        return $message;
    }
}
