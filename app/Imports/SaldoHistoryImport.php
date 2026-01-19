<?php

namespace App\Imports;

use App\Models\SaldoHistory;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SaldoHistoryImport implements ToCollection, WithHeadingRow
{
    use Importable;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if ($row['nis'] !== null && $row['nama_santri'] !== null) {
                    // Sanitize nominal to ensure it's a valid number
                    $nominal = preg_replace('/[^\d]/', '', $row['nominal']); // Remove non-numeric characters
                    $nominal = (int)$nominal; // Convert to integer

                    // Find the student by NIS
                    $student = Student::where('nis', $row['nis'])->first();

                    if ($student && $nominal > 0) {
                        // Update student's saldo
                        $student->saldo += $nominal;
                        $student->save();

                        // Create saldo history
                        SaldoHistory::create([
                            'student_id' => $student->id,
                            'type' => SaldoHistory::TYPE_IN,
                            'amount' => $nominal,
                            'description' => 'Saldo Telah Ditambahkan Oleh ' . Auth::user()->name . ' Sebesar Rp. ' . number_format($nominal, 0, ',', '.'),
                            'status' => SaldoHistory::STATUS_SUCCESS,
                            'usage' => SaldoHistory::USAGE_TOPUP,
                        ]);
                    }
                }
            }
        });
    }
}
