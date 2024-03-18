<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\ApplicationSetting;
use App\Models\Transaction;

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
}
