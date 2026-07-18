<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentBillTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithTitle, WithCustomStartCell, WithStyles
{
    protected $schoolId;
    protected $classroomId;
    private $rowNumber = 0;

    public function __construct($schoolId = null, $classroomId = null)
    {
        $this->schoolId = $schoolId;
        $this->classroomId = $classroomId;
    }

    public function collection()
    {
        $query = Student::where('status', Student::STATUS_ACTIVE);

        if ($this->schoolId) {
            $query->whereHas('classroom', function ($q) {
                $q->where('school_id', $this->schoolId);
            });
        }

        if ($this->classroomId) {
            $query->where('classroom_id', $this->classroomId);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    public function map($student): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $student->name ?? '-',
            $student->classroom?->name ?? '-',
            '', // Nominal Bayar (kosong)
            $student->id, // ID Siswa (UUID)
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Kelas',
            'Nominal Bayar',
            'ID Siswa',
        ];
    }

    public function title(): string
    {
        return 'Template Import Pembayaran';
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
                'name' => 'Inter',
                'size' => 11,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '2563EB', // Blue Accent primary
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ]
        ]);
        
        // Hide the ID Siswa column (Column E) to make it look cleaner, but still parseable
        $sheet->getColumnDimension('E')->setVisible(false);
    }
}
