<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImportData implements ToCollection, WithHeadingRow
{
    use Importable;
    /**
     * @param Collection $collection
     */

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['nis'] !== null && $row['nama'] !== null) {
                // format phone number jika depannya bukan 0 dan lebih dari 1 digit maka tambah 0 di depannya
                $phone = $row['no_wali'] ?? null;
                if (substr($phone, 0, 1) !== '0' && strlen($phone) > 1) {
                    $phone = '0' . $phone;
                }
                // jika depannya 62 maka hilangan 62 dan tambah 0 di depannya
                if (substr($phone, 0, 2) == '62') {
                    $phone = '0' . substr($phone, 2);
                }

                $user = User::where('phone', $phone)->first();
                $classroom = Classroom::where('name', $row['kelas'])->first();
                if ($classroom === null) {
                    dd('Kelas ' . $row['kelas'] . ' tidak ditemukan');
                }

                $birthDate = null;
                if (!empty($row['tanggal_lahir'])) {
                    try {
                        if (is_numeric($row['tanggal_lahir'])) {
                            $birthDate = Date::excelToDateTimeObject($row['tanggal_lahir'])->format('Y-m-d');
                        } else {
                            $birthDate = \Carbon\Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        Log::warning("Gagal parsing tanggal lahir untuk siswa {$row['nama']}: " . $e->getMessage());
                    }
                }

                // Map nickname: if empty, default to first word of name
                $nickname = trim($row['nama_panggilan'] ?? '');
                if (empty($nickname)) {
                    $nickname = explode(' ', trim($row['nama']))[0];
                }

                if ($row['nama'] !== null) {
                    Student::create([
                        'name' => $row['nama'],
                        'nickname' => $nickname,
                        'nis' => $row['nis'],
                        'nisn' => $row['nisn'] ?? null,
                        'born_place' => $row['tempat_lahir'] ?? null,
                        'birth_date' => $birthDate,
                        'address' => $row['alamat'] ?? null,
                        'city' => $row['kota'] ?? null,
                        'province' => $row['provinsi'] ?? null,
                        'user_id' => $user->id ?? null,
                        'gender' => strtoupper($row['jenis_kelamin'] ?? '') ?: null,
                        'classroom_id' => $classroom->id,
                        'status' => Student::STATUS_ACTIVE,
                    ]);
                }
            }
        }
    }
}
