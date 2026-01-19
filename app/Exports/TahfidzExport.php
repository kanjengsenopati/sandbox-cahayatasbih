<?php

namespace App\Exports;

use App\Models\Tahfidz;

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

class TahfidzExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle, WithCustomStartCell, WithStyles
{
    private $rowNumber = 0;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $schoolId = request()->input('school_id');
        $classroomId = request()->input('classroom_id');

        $query = Tahfidz::with('student', 'student.classroom', 'student.school');

        if ($schoolId) {
            $query->whereHas('student', function ($query) use ($schoolId) {
                $query->whereHas('classroom', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                });
            });
        }

        if ($classroomId) {
            $query->whereHas('student', function ($query) use ($classroomId) {
                $query->where('classroom_id', $classroomId);
            });
        }

        $query->latest();

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Setoran',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Sekolah',
            'Jumlah Halaman',
            'Keterangan',
            'Feedback',
            'Link',
        ];
    }

    public function map($tahfidz): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $tahfidz->deposit_date,
            $tahfidz->student?->nis ?? '-',
            $tahfidz->student->name ?? '-',
            $tahfidz->student->classroom?->name ?? '-',
            $tahfidz->student->classroom?->school?->name ?? '-',
            $tahfidz->number_of_pages ? $tahfidz->number_of_pages . ' Halaman' : '-',
            $tahfidz->note ?? '-',
            $tahfidz->feedback ?? '-',
            $tahfidz->link ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => 'dd mmm yyyy',
        ];
    }

    public function title(): string
    {
        return 'Data Setoran Tahfidz';
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
