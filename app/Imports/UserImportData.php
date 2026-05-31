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

                // Map Gender (Jenis Kelamin)
                $genderInput = strtolower(trim($row['jenis_kelamin'] ?? ''));
                $gender = null;
                if ($genderInput === 'l' || $genderInput === 'laki-laki' || $genderInput === 'laki laki') {
                    $gender = 'L';
                } elseif ($genderInput === 'p' || $genderInput === 'perempuan') {
                    $gender = 'P';
                }

                // Map Status (ACTIVE / INACTIVE)
                $statusInput = strtolower(trim($row['status'] ?? ''));
                $status = 'ACTIVE'; // default
                if ($statusInput === 'tidak aktif' || $statusInput === 'inactive') {
                    $status = 'INACTIVE';
                }

                // Map Jamaah Status (JAMAAH / NON_JAMAAH / UNKNOWN)
                $jamaahStatusInput = strtolower(trim($row['status_jamaah'] ?? ''));
                $jamaahStatus = 'UNKNOWN'; // default
                if ($jamaahStatusInput === 'jamaah') {
                    $jamaahStatus = 'JAMAAH';
                } elseif ($jamaahStatusInput === 'non jamaah' || $jamaahStatusInput === 'non_jamaah') {
                    $jamaahStatus = 'NON_JAMAAH';
                }

                User::create([
                    'name' => $row['nama'],
                    'email' => $row['email'] ?? null,
                    'password' => bcrypt($row['password']),
                    'phone' => $phone ?? null,
                    'gender' => $gender,
                    'status' => $status,
                    'jamaah_status' => $jamaahStatus,
                ]);
            }
        }
    }
}
