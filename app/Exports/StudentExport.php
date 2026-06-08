<?php

namespace App\Exports;

use App\Models\Student;
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

class StudentExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle, WithCustomStartCell, WithStyles

{
    private $rowNumber = 0;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $schoolId = request()->input('school_id');
        $classroomId = request()->input('classroom_id');
        $academicYearId = request()->input('academic_year_id');
        $tab = request()->input('tab', 'total');

        $query = Student::hasSchool();

        if ($schoolId) {
            $query->whereHas('classroom', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            });
        }

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        if ($academicYearId) {
            $query->where(function ($sub) use ($academicYearId) {
                $sub->whereHas('classroomHistories', function ($historyQuery) use ($academicYearId) {
                    $historyQuery->where('academic_year_id', $academicYearId);
                })
                ->orWhereHas('bills', function ($billQuery) use ($academicYearId) {
                    $billQuery->where('academic_year_id', $academicYearId);
                });
            });
        }

        if ($tab === 'active') {
            $query->where('status', Student::STATUS_ACTIVE);
        } elseif ($tab === 'graduated') {
            $query->where('status', Student::STATUS_GRADUATED);
        } elseif ($tab === 'dropped_out') {
            $query->where('status', Student::STATUS_DROPPED_OUT);
        }

        $query->orderBy('name', 'asc');

        return $query->get();
    }



    public function map($student): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $student->nis ?? '-',
            $student->name ?? '-',
            $student->nisn ?? '-',
            $student->classroom?->name ?? '-',
            $student->classroom?->school?->name ?? '-',
            $student->born_place ?? '-',
            date('d-m-Y', strtotime($student->birth_date)),
            $student->gender != null ? ($student->gender == 'L' ? 'Laki-laki' : 'Perempuan') : '-',
            $student->address ?? '-',
            $student->saldo ?? 0,
            $student->saving ?? 0,
            $student->daily_limit ?? 'Tidak ada limit',
            $student->translated_status ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama',
            'NISN',
            'Kelas',
            'Sekolah',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Alamat',
            'Saldo',
            'Tabungan',
            'Limit Harian',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Data Santri';
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
