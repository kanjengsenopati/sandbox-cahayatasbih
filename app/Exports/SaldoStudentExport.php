<?php

namespace App\Exports;

use App\Models\SaldoHistory;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SaldoStudentExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle, WithCustomStartCell, WithStyles
{

    private $rowNumber = 0;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $schoolId = request()->input('school_id');
        $classroomId = request()->input('classroom_id');
        $status = request()->input('status');

        $query = SaldoHistory::query();

        if ($schoolId) {
            $query->whereHas('student', function ($query) use ($schoolId) {
                $query->whereHas('classroom', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                });
            });

            if ($classroomId) {
                $query->whereHas('student', function ($query) use ($classroomId) {
                    $query->where('classroom_id', $classroomId);
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $query->latest();

            return $query->get();
        }
    }

    public function map($saldoStudent): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $saldoStudent->created_at->format('d-m-Y'),
            $saldoStudent->student->nis ?? '-',
            $saldoStudent->student->name ?? '-',
            $saldoStudent->student->nisn ?? '-',
            $saldoStudent->student->classroom?->name ?? '-',
            $saldoStudent->type == SaldoHistory::TYPE_IN ? 'Pemasukan' : 'Pengeluaran',
            $saldoStudent->amount,
            $saldoStudent->status,
            $saldoStudent->description,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'NIS',
            'Nama',
            'NISN',
            'Kelas',
            'Tipe',
            'Jumlah',
            'Status',
            'Keterangan',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Data Saldo Siswa';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
            'font' => [
                'size' => 12,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'padding' => [
                'top' => 5,
                'right' => 5,
                'bottom' => 5,
                'left' => 5,
            ],
        ]);

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'DDDDDD', // Grey background color
                ],
            ],
        ]);
    }
}
