<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bill;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure payment methods exist (in case this seeder runs before PaymentMethodSeeder)
        $cash = PaymentMethod::firstOrCreate(['type' => 'cash'], ['name' => 'Tunai']);
        $balance = PaymentMethod::firstOrCreate(['type' => 'balance'], ['name' => 'Debit Saldo']);
        $other = PaymentMethod::firstOrCreate(['type' => 'other'], ['name' => 'Transfer Aplikasi']);

        // Sample Bills
        $bill1 = Bill::create([
            'name' => 'Zakat Maal',
            'amount' => 500000,
            'target_date' => Carbon::now()->addMonth()->toDateString(),
        ]);

        $bill2 = Bill::create([
            'name' => 'Zakat Fitrah',
            'amount' => 200000,
            'target_date' => Carbon::now()->addWeeks(2)->toDateString(),
        ]);

        // Sample Transactions linked to bills and payment methods
        Transaction::create([
            'bill_id' => $bill1->id,
            'payment_method_id' => $cash->id,
            'nominal' => 500000,
            'date' => Carbon::now()->toDateString(),
        ]);

        Transaction::create([
            'bill_id' => $bill2->id,
            'payment_method_id' => $balance->id,
            'nominal' => 200000,
            'date' => Carbon::now()->toDateString(),
        ]);
    }
}
