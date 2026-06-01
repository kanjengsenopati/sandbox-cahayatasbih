<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Tunai', 'type' => 'cash'],
            ['name' => 'Debit Saldo', 'type' => 'balance'],
            ['name' => 'Transfer Aplikasi', 'type' => 'other'],
        ];

        foreach ($methods as $data) {
            PaymentMethod::firstOrCreate(
                ['type' => $data['type']],
                ['name' => $data['name']]
            );
        }
    }
}
