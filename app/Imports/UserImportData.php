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

    public $successCount = 0;
    public $skipped = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        $seenPhonesInSheet = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // 1-based index plus header row
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

                if (!empty($phone) && $phone !== '-') {
                    // Check if duplicate within the Excel sheet itself
                    if (isset($seenPhonesInSheet[$phone])) {
                        $this->skipped[] = [
                            'row' => $rowNum,
                            'name' => $row['nama'],
                            'phone' => $phone,
                            'reason' => 'Nomor ganda dalam file Excel (Duplikat dengan Baris ' . $seenPhonesInSheet[$phone] . ')'
                        ];
                        continue;
                    }
                    $seenPhonesInSheet[$phone] = $rowNum;

                    // Check if duplicate in database using the 2 indicators (name similarity & phone match)
                    $duplicate = User::checkDoubleEntry($row['nama'], $phone);
                    if ($duplicate) {
                        $status = 'VERIFICATION';
                    } else {
                        // If phone exists but name is not similar, skip to avoid duplicate phone conflicts
                        $exists = User::where('phone', $phone)->exists();
                        if ($exists) {
                            $this->skipped[] = [
                                'row' => $rowNum,
                                'name' => $row['nama'],
                                'phone' => $phone,
                                'reason' => 'Nomor WA sudah terdaftar di database dengan nama berbeda'
                            ];
                            continue;
                        }
                    }
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

                // Map Jamaah Status (JAMAAH / NON_JAMAAH / UNKNOWN / MUKIMIN)
                $jamaahStatusInput = strtoupper(trim($row['status_jamaah'] ?? ''));
                $jamaahStatusNormalized = str_replace([' ', '-'], '_', $jamaahStatusInput);
                
                $jamaahStatus = 'NON_JAMAAH'; // default
                if ($jamaahStatusNormalized === 'JAMAAH') {
                    $jamaahStatus = 'JAMAAH';
                } elseif (in_array($jamaahStatusNormalized, ['NON_JAMAAH', 'BUKAN_JAMAAH', 'NONJAMAAH'])) {
                    $jamaahStatus = 'NON_JAMAAH';
                } elseif ($jamaahStatusNormalized === 'MUKIMIN') {
                    $jamaahStatus = 'MUKIMIN';
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

                $this->successCount++;
            }
        }
    }
}
