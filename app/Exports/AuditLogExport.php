<?php

namespace App\Exports;

use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AuditLogExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithCustomStartCell, WithStyles, WithTitle
{
    private $rowNumber = 0;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');

        return Activity::latest()
            ->whereNotNull('causer_id')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->get();
    }

    public function map($activity): array
    {
        $this->rowNumber++;
        $causer = $activity->causer;
        $name = $causer->name ?? 'User Terhapus';
        $role = $causer ? ($causer->roles->first()->name ?? '-') : '-';
        $device = $activity->getExtraProperty('device') ?? 'Desktop';

        return [
            $this->rowNumber,
            $name,
            $role,
            $activity->description,
            $activity->created_at->format('d-m-Y H:i:s'),
            $device,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Peran',
            'Aksi',
            'Waktu (Timestamp)',
            'Perangkat (Device)',
        ];
    }

    public function title(): string
    {
        return 'Laporan Audit Log';
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
        ]);

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'DDDDDD',
                ],
            ],
        ]);
    }
}
