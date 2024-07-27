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

                // format phone number jika depannya bukan 0 dan lebih dari 1 digit maka tambah 0 di depannya
                $phone = $row['nomor_handphone'] ?? null;
                if (substr($phone, 0, 1) !== '0' && strlen($phone) > 1) {
                    $phone = '0' . $phone;
                }
                // jika depannya 62 maka hilangan 62 dan tambah 0 di depannya
                if (substr($phone, 0, 2) == '62') {
                    $phone = '0' . substr($phone, 2);
                }

                User::create([
                    'name' => $row['nama'],
                    'email' => $row['email'] ?? null,
                    'password' => bcrypt($row['password']),
                    'phone' => $phone ?? null,
                    'gender' => null,
                ]);
            }
        }
    }
}
