<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\User;

class UserImportData implements ToCollection, WithHeadingRow
{
    use Importable;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['nama'] !== null) {
                User::create([
                    'name' => $row['nama'],
                    'email' => $row['email'],
                    'password' => bcrypt($row['password']),
                    'phone' => $row['nomor_handphone'] ?? null,
                    'gender' => strtoupper($row['jenis_kelamin']),
                ]);
            }
        }
    }
}
