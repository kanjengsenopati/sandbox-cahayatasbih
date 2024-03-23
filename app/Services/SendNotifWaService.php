<?php

namespace App\Services;

use App\Models\Bill;
use GuzzleHttp\Client;
use App\Models\Transaction;
use App\Models\ApplicationSetting;

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

        switch ($transaction->type) {
            case Transaction::TYPE_BILL:
                foreach ($transaction->transactionDetails as $detail) {
                    $bill = $detail->bill;
                    $message .= "--------------------------------\n";
                    $message .= "Pembayaran : *" . $bill->billType->name .
                        " " . $bill->translated_month . " " . $bill->academicYear->name . "*\n";
                    $message .= "Total Pembayaran : *Rp." . number_format($bill->amount, 0, ',', '.') . "*\n";
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
        $message .= "Wassalamualaikum Wr. Wb.\n";
        $message .= "*PPTQ CAHAYA TASBIH*";

        return $message;
    }


    public static function sendMessageUnpaidNotification($student, $billTypes)
    {
        $parentStudent = $student->user;

        $message = "Assalamualaikum Bapak/Ibu " . $parentStudent->name . ",\n";
        $message .= "Anda memiliki tagihan yang belum terbayar\n";
        $message .= "--------------------------------\n";
        $message .= "1. NIS : *" . $student->nis . "*\n";
        $message .= "2. NISN : *" . $student->nisn . "*\n";
        $message .= "3. Nama Santri : *" . $student->name . "*\n";
        $message .= "4. Kelas : *" . $student->classroom->name . "*\n";

        $must_pay = 0;
        foreach ($billTypes as $billType) {
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
}
