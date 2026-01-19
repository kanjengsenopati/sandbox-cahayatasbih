<?php

namespace App\Exports;

use App\Models\StudyGrade;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class StudyGradeExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle, WithCustomStartCell, WithStyles
{

    private $rowNumber = 0;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $schoolId = request()->input('school_id');
        $classroomId = request()->input('classroom_id');

        $query = StudyGrade::with(['student', 'academicYear', 'semester', 'study', 'classroom'])
            ->when(request()->school_id, function ($query) use ($schoolId) {
                $query->whereHas('student', function ($query) use ($schoolId) {
                    $query->whereHas('classroom', function ($query) use ($schoolId) {
                        $query->where('school_id', $schoolId);
                    });
                });
            })
            ->when(request()->classroom_id, function ($query) use ($classroomId) {
                $query->whereHas('student', function ($query) use ($classroomId) {
                    $query->where('classroom_id', $classroomId);
                });
            });

        $query->latest();

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tahun Ajaran',
            'Kelas',
            'Sekolah',
            'Semester',
            'NIS',
            'Nama',
            'KKM',
            'Nilai',
            'Nilai Huruf',
        ];
    }

    public function map($studyGrade): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $studyGrade->academicYear?->name ?? 'N/A',
            $studyGrade->student->classroom?->name ?? 'N/A',
            $studyGrade->student->classroom?->school?->name ?? 'N/A',
            $studyGrade->semester?->name ?? 'N/A',
            $studyGrade->student?->nis ?? 'N/A',
            $studyGrade->student?->name ?? 'N/A',
            $studyGrade->kkm ?? 'N/A',
            $studyGrade->grade ?? 'N/A',
            $studyGrade->letter_grade ?? 'N/A',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '0',
        ];
    }

    public function title(): string
    {
        return 'Data Raport Nilai Akademik';
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
