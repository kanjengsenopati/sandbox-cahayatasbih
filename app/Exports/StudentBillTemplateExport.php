<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\BillType;
use App\Models\AcademicYear;
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
    protected $classroomIds;
    protected $academicYearId;
    protected $billTypeIds;
    private $rowNumber = 0;
    private $columnsCount = 5;

    public function __construct($schoolId = null, $classroomIds = null, $academicYearId = null, $billTypeIds = null)
    {
        $this->schoolId = $schoolId;
        
        if (is_array($classroomIds)) {
            $this->classroomIds = array_values(array_filter($classroomIds));
        } elseif (!empty($classroomIds)) {
            $this->classroomIds = [$classroomIds];
        } else {
            $this->classroomIds = [];
        }

        $this->academicYearId = $academicYearId;

        if (is_array($billTypeIds)) {
            $this->billTypeIds = array_values(array_filter($billTypeIds));
        } elseif (!empty($billTypeIds)) {
            $this->billTypeIds = [$billTypeIds];
        } else {
            $this->billTypeIds = [];
        }
    }

    public function collection()
    {
        $query = Student::where('status', Student::STATUS_ACTIVE);

        if ($this->schoolId) {
            $query->whereHas('classroom', function ($q) {
                $q->where('school_id', $this->schoolId);
            });
        }

        if (!empty($this->classroomIds)) {
            $query->whereIn('classroom_id', $this->classroomIds);
        }

        return $query->with('classroom')->orderBy('name', 'asc')->get();
    }

    public function headings(): array
    {
        $headings = ['No', 'Nama', 'Kelas'];

        $selectedBillTypes = collect();
        if (!empty($this->billTypeIds)) {
            $selectedBillTypes = BillType::whereIn('id', $this->billTypeIds)->get();
        }

        $academicYear = null;
        if ($this->academicYearId) {
            $academicYear = AcademicYear::find($this->academicYearId);
        }

        $startYear = $academicYear?->getStartYearSafe() ?? intval(date('Y'));
        $endYear = $startYear + 1;

        $monthDefs = [
            ['name' => 'Juli', 'month' => 7, 'year' => $startYear],
            ['name' => 'Agustus', 'month' => 8, 'year' => $startYear],
            ['name' => 'September', 'month' => 9, 'year' => $startYear],
            ['name' => 'Oktober', 'month' => 10, 'year' => $startYear],
            ['name' => 'November', 'month' => 11, 'year' => $startYear],
            ['name' => 'Desember', 'month' => 12, 'year' => $startYear],
            ['name' => 'Januari', 'month' => 1, 'year' => $endYear],
            ['name' => 'Februari', 'month' => 2, 'year' => $endYear],
            ['name' => 'Maret', 'month' => 3, 'year' => $endYear],
            ['name' => 'April', 'month' => 4, 'year' => $endYear],
            ['name' => 'Mei', 'month' => 5, 'year' => $endYear],
            ['name' => 'Juni', 'month' => 6, 'year' => $endYear],
        ];

        $monthlyBillTypes = $selectedBillTypes->filter(fn($bt) => $bt->type === BillType::TYPE_MONTHLY);
        $otherBillTypes = $selectedBillTypes->filter(fn($bt) => $bt->type !== BillType::TYPE_MONTHLY);

        if ($monthlyBillTypes->count() === 1) {
            foreach ($monthDefs as $m) {
                $headings[] = "{$m['name']} {$m['year']}";
            }
        } elseif ($monthlyBillTypes->count() > 1) {
            foreach ($monthlyBillTypes as $bt) {
                foreach ($monthDefs as $m) {
                    $headings[] = "[{$bt->name}] {$m['name']} {$m['year']}";
                }
            }
        }

        if ($otherBillTypes->count() > 0) {
            foreach ($otherBillTypes as $bt) {
                $headings[] = "Nominal {$bt->name}";
            }
        }

        if ($monthlyBillTypes->count() === 0 && $otherBillTypes->count() === 0) {
            $headings[] = 'Nominal Bayar';
        }

        $headings[] = 'ID Siswa';
        $this->columnsCount = count($headings);

        return $headings;
    }

    public function map($student): array
    {
        $this->rowNumber++;
        $row = [
            $this->rowNumber,
            $student->name ?? '-',
            $student->classroom?->name ?? '-',
        ];

        $paymentColsCount = max(1, $this->columnsCount - 4); // Columns between Kelas and ID Siswa
        for ($i = 0; $i < $paymentColsCount; $i++) {
            $row[] = '';
        }

        $row[] = $student->id; // ID Siswa (UUID)
        return $row;
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
        $highestColumn = $sheet->getHighestColumn();

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

        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
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
        
        // Hide the ID Siswa column (last column) to make it look cleaner, but still parseable
        $sheet->getColumnDimension($highestColumn)->setVisible(false);
    }
}
