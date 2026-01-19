<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Student;
use App\Models\BillType;
use App\Models\PaymentRate;
use Illuminate\Console\Command;
use App\Services\PaymentRateService;
use App\Jobs\SendBillWhatsappNotificationJob;

class SendBillInvoiceClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-bill-invoice-class';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send bill invoices to students via WhatsApp for unpaid bills.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $students = Student::whereHas('bills', function ($query) {
            $currentMonth = (int) date('n'); // Bulan saat ini (1-12)
            $currentYear = (int) date('Y');  // Tahun saat ini

            $query->where('status', Bill::STATUS_UNPAID)
                ->where(function ($q) use ($currentMonth, $currentYear) {
                    $q->where('year', '<', $currentYear) // Tagihan di tahun-tahun sebelumnya
                        ->orWhere(function ($q2) use ($currentMonth, $currentYear) {
                            $q2->where('year', $currentYear) // Tagihan di tahun saat ini
                                ->where('month', '<=', $currentMonth); // Dan bulan <= bulan saat ini
                        });
                });
        })->pluck('id'); // Mengambil hanya kolom 'id' dari hasil query

        dispatch(new SendBillWhatsappNotificationJob($students, $billTypes));
    }
}
