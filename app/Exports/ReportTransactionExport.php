<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionDetail;
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

class ReportTransactionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle, WithCustomStartCell, WithStyles
{
    private $rowNumber = 0;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Transaction::where('status', Transaction::STATUS_PAID)
            ->with('student', 'student.classroom', 'paymentMethod', 'admin')
            ->when(request()->filled('start_date'), function ($query) {
                $query->whereDate('created_at', '>=', request()->start_date);
            })
            ->when(request()->filled('end_date'), function ($query) {
                $query->whereDate('created_at', '<=', request()->end_date);
            })
            ->when(request()->filled('admin_id'), function ($query) {
                $query->where('admin_id', request()->admin_id);
            })
            ->schoolFilter('school_id', request()->school_id)
            ->classroomFilter('classroom_id', request()->classroom_id)
            ->filter('type', request()->type_data)
            ->when(request()->filled('bill_type_id'), function ($query) {
                $query->whereHas('transactionDetails.bill', function ($query) {
                    $query->where('bill_type_id', request()->bill_type_id);
                });
            })
            ->hasSchool()
            ->latest()
            ->get();
    }

    public function map($data): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            Carbon::parse($data->created_at)->translatedFormat('l, d F Y H:i:s'),
            $data->student?->name ?? '-',
            $data->student?->classroom?->name ?? '-',
            $data->student?->classroom?->school?->name ?? '-',
            $this->getTransactionTypeBadge($data->type),
            'Rp ' . number_format($data->pay_amount, 0, ',', '.'),
            $data->paymentMethod?->name ?? '-',
            $data->admin?->name ?? '-',
            $this->getTransactionDetails($data),
        ];
    }

    private function getTransactionTypeBadge($type): string
    {
        return match ($type) {
            Transaction::TYPE_BILL => 'Tagihan',
            Transaction::TYPE_SALDO => 'Saldo',
            Transaction::TYPE_SAVING => 'Tabungan',
            default => '-',
        };
    }

    private function getTransactionDetails($data): string
    {
        // Ensure a collection is returned (even if empty)
        $transaction_details = TransactionDetail::where('transaction_id', $data->id)->get() ?? collect();
        $item = '';

        if ($data->type == Transaction::TYPE_BILL) {
            foreach ($transaction_details as $index => $detail) {
                $billType = $detail->bill?->billType?->name ?? '-';
                $month = $detail->bill?->month ? Carbon::createFromFormat('m', $detail->bill->month)->translatedFormat('F') : '-';
                $year = $detail->bill?->year ?? '-';
                $item .= ($index + 1) . '. ' . $billType . ' ' . $month . ' ' . $year . "\n";
            }
        } elseif ($data->type == Transaction::TYPE_SALDO) {
            foreach ($transaction_details as $index => $detail) {
                $item .= ($index + 1) . '. ' . ($detail->saldoHistory?->description ?? '-') . "\n";
            }
        } elseif ($data->type == Transaction::TYPE_SAVING) {
            foreach ($transaction_details as $index => $detail) {
                $item .= ($index + 1) . '. ' . ($detail->savingHistory?->description ?? '-') . "\n";
            }
        } else {
            return '-';
        }

        // Trim the trailing newline character
        return trim($item) ?: '-';
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama',
            'Kelas',
            'UPT',
            'Jenis Transaksi',
            'Nominal',
            'Metode Pembayaran',
            'Petugas',
            'Detail Transaksi',
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Data Transaksi';
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
