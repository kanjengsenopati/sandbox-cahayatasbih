<?php

namespace App\Exports;

use App\Models\PointOfSaleTransaction;
use App\Services\MartUserScopeService;
use App\Services\OutletContextService;
use Carbon\Carbon;
use Generator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportMartCashflowExport implements FromGenerator, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle, WithCustomStartCell, WithStyles
{
    protected Request $request;
    private int $rowNumber = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Arus Kas Mart';
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function baseQuery()
    {
        $user = Auth::user();
        $koperasiId = OutletContextService::getKoperasiOutletId();
        $isRestricted = MartUserScopeService::isUserOutletRestricted($user);
        $allowedOutletIds = MartUserScopeService::getAllowedOutletIds($user);

        $query = PointOfSaleTransaction::query()
            ->where('point_of_sale_transactions.type', PointOfSaleTransaction::TYPE_SANTRI)
            ->whereNotNull('point_of_sale_transactions.student_id')
            ->with([
                'student' => function ($q) {
                    $q->withTrashed()->with('classroom');
                },
                'outlet' => function ($q) {
                    $q->withTrashed();
                },
                'pointOfSaleTransactionDetails' => function ($q) {
                    $q->withTrashed()->with('item');
                },
            ]);

        // Outlet Scope Enforcement
        if ($isRestricted) {
            if ($this->request->filled('outlet_id') && in_array($this->request->outlet_id, $allowedOutletIds)) {
                $query->where('point_of_sale_transactions.outlet_id', $this->request->outlet_id);
            } else {
                $query->whereIn('point_of_sale_transactions.outlet_id', $allowedOutletIds);
            }
        } else {
            $source = $this->request->input('source', 'all');
            $outletId = $this->request->input('outlet_id');

            if ($outletId && $outletId !== 'all') {
                $query->where('point_of_sale_transactions.outlet_id', $outletId);
            } elseif ($source === 'koperasi') {
                $query->where('point_of_sale_transactions.outlet_id', $koperasiId);
            } elseif ($source === 'outlet') {
                $query->where('point_of_sale_transactions.outlet_id', '!=', $koperasiId);
            }
        }

        // Date Range
        if ($this->request->filled('start_date') && $this->request->filled('end_date')) {
            $query->whereDate('point_of_sale_transactions.created_at', '>=', $this->request->start_date)
                  ->whereDate('point_of_sale_transactions.created_at', '<=', $this->request->end_date);
        } elseif ($this->request->filled('period') && $this->request->period !== 'all') {
            if ($this->request->period === 'today') {
                $query->whereDate('point_of_sale_transactions.created_at', Carbon::today());
            } elseif ($this->request->period === 'week') {
                $query->whereBetween('point_of_sale_transactions.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($this->request->period === 'month') {
                $query->whereBetween('point_of_sale_transactions.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            }
        }

        if ($this->request->filled('status') && $this->request->status !== 'all') {
            $query->where('point_of_sale_transactions.status', $this->request->status);
        }

        return $query->latest('point_of_sale_transactions.created_at');
    }

    public function generator(): Generator
    {
        $query = $this->baseQuery();
        $page = 1;
        $perPage = 300;

        while (true) {
            $rows = (clone $query)->forPage($page, $perPage)->get();
            if ($rows->isEmpty()) {
                break;
            }

            foreach ($rows as $row) {
                yield $row;
            }

            $page++;
            unset($rows);
        }
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Transaksi',
            'Tanggal & Waktu',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Unit / Outlet',
            'Rincian Belanja',
            'Nominal Belanja (Rp)',
            'Status',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $itemsSummary = $row->pointOfSaleTransactionDetails->map(function ($d) {
            $name = $d->item?->name ?? 'Barang';
            return $d->quantity . 'x ' . $name;
        })->implode(', ');

        $statusText = match ($row->status) {
            PointOfSaleTransaction::STATUS_SUCCESS => 'Sukses',
            PointOfSaleTransaction::STATUS_PENDING => 'Pending',
            default => 'Gagal',
        };

        return [
            $this->rowNumber,
            $row->payment_code ?? '-',
            Carbon::parse($row->created_at)->format('d/m/Y H:i'),
            $row->student->nis ?? '-',
            $row->student->name ?? '-',
            $row->student->classroom?->name ?? '-',
            $row->outlet->name ?? '-',
            $itemsSummary ?: '-',
            $row->pay_amount,
            $statusText,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'REKAP ARUS KAS MART - BELANJA SISWA VIA SALDO');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A2:J2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $lastRow = $this->rowNumber + 2;
        if ($lastRow >= 3) {
            $sheet->getStyle("A2:J{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
            ]);
            $sheet->getStyle("A3:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C3:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J3:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
