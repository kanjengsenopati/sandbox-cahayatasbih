<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReportBillPerClassSheet implements FromView, ShouldAutoSize, WithStyles, WithTitle, WithColumnFormatting
{
    protected $title;
    protected $students;
    protected $periods;
    protected $billTypeId;

    public function __construct($title, $students, $periods, $billTypeId)
    {
        $this->title = $title;
        $this->students = $students;
        $this->periods = $periods;
        $this->billTypeId = $billTypeId;
    }

    public function view(): View
    {
        return view('exports.report-bill-detail', [
            'students' => $this->students,
            'periods' => $this->periods,
            'billTypeId' => $this->billTypeId
        ]);
    }

    public function title(): string
    {
        return $this->title;
    }



    public function styles(Worksheet $sheet)
    {
        $styles = [];
        
        // 1. Styling Data (Rows)
        foreach ($this->students as $index => $student) {
            if ($student->total_unpaid > 0) {
                $row = $index + 2;
                $styles[$row] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFFE066'], // Gold
                    ],
                ];
            }
        }

        // 2. Styling Global (Borders & Bold Header)
        // Header
        $styles[1] = ['font' => ['bold' => true]];
        
        // Borders for ALL cells
        $lastRow = count($this->students) + 1;
        $startColIndex = 1; 
        
        // Calculate Total Columns:
        // No(1) + Kelas(1) + Nama(1) + Months(count) + Tagihan_Berjalan(1) + Total_Kek (1) + Status(1)
        // = 6 + count($periods)
        $totalCols = 6 + count($this->periods);
        $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);
        
        $range = "A1:{$lastColLetter}{$lastRow}";
        
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        return $styles;
    }

    public function columnFormats(): array
    {
        $startColIndex = 4; // Column D (No, Kelas, Nama, [Months...])
        // Months columns count
        $monthsCount = count($this->periods);
        // Formatted Cols: Months + Current Due (1) + Total Deficiency (1)
        // Start from D (4)
        $totalFormattedCols = $monthsCount + 2;
        
        $endColIndex = $startColIndex + $totalFormattedCols - 1;
        
        $startLetter = Coordinate::stringFromColumnIndex($startColIndex);
        $endLetter = Coordinate::stringFromColumnIndex($endColIndex);
        
        return [
            "{$startLetter}:{$endLetter}" => '#,##0',
        ];
    }
}
