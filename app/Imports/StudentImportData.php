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
        DB::beginTransaction(); // Memulai transaksi basis data

        try {
            foreach ($rows as $row) {
                if ($row['nis'] !== null && $row['nama'] !== null) {
                    $user = User::where('phone', $row['no_wali'])->first();
                    $classroom = Classroom::where('name', $row['kelas'])->first();
                    if ($row['nis'] !== null && $row['nama'] !== null && $user !== null && $classroom !== null) {
                        Student::create([
                            'name' => $row['nama'],
                            'nis' => $row['nis'],
                            'nisn' => $row['nisn'] ?? null,
                            'birth_place' => $row['tempat_lahir'],
                            'birth_date' => Date::excelToDateTimeObject($row['tanggal_lahir'])->format('Y-m-d') ?? null,
                            'address' => $row['alamat'] ?? null,
                            'user_id' => $user->id ?? null,
                            'gender' => strtoupper($row['jenis_kelamin']) ?? null,
                            'classroom_id' => $classroom->id ?? null,
                        ]);
                    } else {
                        // Gulir kembali (rollback) transaksi dan kembalikan dengan pesan error
                        DB::rollBack();
                        return Redirect::back()->with('error', 'Maaf Data Tidak Sesuai Format');
                    }
                }
            }

            DB::commit(); // Komit transaksi jika berhasil

            return Redirect::back()->with('success', 'Data berhasil diimpor'); // Kembali dengan pesan sukses
        } catch (\Exception $e) {
            // Gulir kembali (rollback) transaksi jika terjadi pengecualian (exception)
            DB::rollBack();
            Log::error($e); // Catat pengecualian (exception) ke log
            return Redirect::back()->with('error', 'Terjadi kesalahan dalam memproses data');
        }
    }
}
