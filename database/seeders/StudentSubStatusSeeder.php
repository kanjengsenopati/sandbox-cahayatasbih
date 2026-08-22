<?php

namespace Database\Seeders;

use App\Models\StudentSubStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSubStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Umum',
            'Ndalem',
            'Kuliah'
        ];

        foreach ($statuses as $status) {
            StudentSubStatus::firstOrCreate(['name' => $status]);
        }
    }
}
