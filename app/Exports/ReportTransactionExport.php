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
use Illuminate\Support\Facades\DB;
use App\Traits\CanonicalBillTypeTrait;

class ReportTransactionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle, WithCustomStartCell, WithStyles
{
    use CanonicalBillTypeTrait;
    private $rowNumber = 0;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Transaction::where('status', Transaction::STATUS_PAID)
            ->with('student', 'student.classroom', 'paymentMethod', 'admin', 'transactionDetails.bill.billType')
            ->when(request()->filled('start_date'), function ($query) {
                $query->whereDate('created_at', '>=', request()->start_date);
            })
            ->when(request()->filled('end_date'), function ($query) {
                $query->whereDate('created_at', '<=', request()->end_date);
            })
            ->when(request()->filled('admin_id'), function ($query) {
                $query->where('admin_id', request()->admin_id);
            })
            ->when(request()->filled('student_name'), function ($query) {
                $searchName = strtolower(trim(request()->student_name));
                $query->whereHas('student', function ($sQ) use ($searchName) {
                    $sQ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchName . '%'])
                       ->orWhereRaw('LOWER(nis) LIKE ?', ['%' . $searchName . '%'])
                       ->orWhereRaw('LOWER(nisn) LIKE ?', ['%' . $searchName . '%']);
                });
            })
            ->schoolFilter('school_id', request()->school_id)
            ->classroomFilter('classroom_id', request()->classroom_id)
            ->filter('type', request()->type_data)
            ->when(request()->filled('bill_type_id'), function ($query) {
                $val = request()->input('bill_type_id');
                $matchingInfo = $this->getMatchingBillTypeInfo($val);
                $matchedIds = $matchingInfo['ids'];
                $matchedNames = $matchingInfo['names'];

                $query->where('type', \App\Models\Transaction::TYPE_BILL)
                    ->whereExists(function ($subQuery) use ($val, $matchedIds, $matchedNames) {
                        $subQuery->select(DB::raw(1))
                            ->from('transaction_details')
                            ->join('bills', 'transaction_details.bill_id', '=', 'bills.id')
                            ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
                            ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                            ->where(function($q) use ($val, $matchedIds, $matchedNames) {
                                if (!empty($matchedIds)) {
                                    $q->whereIn('bill_types.id', $matchedIds)
                                      ->orWhereIn('bill_types.name', $matchedNames);
                                } else {
                                    if (is_array($val)) {
                                        $q->whereIn('bill_types.id', $val)
                                          ->orWhereIn('bill_types.name', $val);
                                    } else {
                                        $q->where('bill_types.id', $val)
                                          ->orWhere('bill_types.name', $val);
                                    }
                                }
                            });
                    });
            })
            ->hasSchool()
            ->latest()
            ->get();
    }

    public function map($data): array
    {
        $this->rowNumber++;
        $filterTagihan = request()->input('bill_type_id');
        $matchingInfo = $filterTagihan ? $this->getMatchingBillTypeInfo($filterTagihan) : ['ids' => [], 'names' => []];
        $matchedIds = $matchingInfo['ids'];
        $matchedNames = $matchingInfo['names'];

        // Filter transaction details jika filter jenis tagihan aktif (sterilisasi data multi-item)
        $details = $data->transactionDetails ?? collect();
        if (!empty($filterTagihan) && $data->type === Transaction::TYPE_BILL) {
            $details = $details->filter(function ($detail) use ($filterTagihan, $matchedIds, $matchedNames) {
                $bt = $detail->bill?->billType;
                $btId = $detail->bill?->bill_type_id;
                $btName = $bt?->name;

                if (!empty($matchedIds)) {
                    return in_array($btId, $matchedIds) || in_array($btName, $matchedNames);
                }

                if (is_array($filterTagihan)) {
                    return in_array($btName, $filterTagihan) || in_array($btId, $filterTagihan);
                }
                return $btName === $filterTagihan || $btId === $filterTagihan;
            });
        }

        // Tentukan teks kolom Item Tagihan
        $itemTagihan = '-';
        if ($data->type === Transaction::TYPE_BILL) {
            $names = $details->map(fn($d) => $d->bill?->billType?->name)->filter()->unique()->values();
            $itemTagihan = $names->isNotEmpty() ? $names->implode(', ') : ($filterTagihan ?: '-');
        } elseif ($data->type === Transaction::TYPE_SALDO) {
            $itemTagihan = 'Saldo';
        } elseif ($data->type === Transaction::TYPE_SAVING) {
            $itemTagihan = 'Tabungan';
        }

        // Hitung nominal khusus item yang difilter
        $totalNominal = 0;
        if ($data->type === Transaction::TYPE_BILL) {
            if (!empty($filterTagihan)) {
                foreach ($details as $detail) {
                    $amt = $detail->amount ?: ($detail->bill?->amount ?? 0);
                    $totalNominal += (int) $amt;
                }
            } else {
                $totalNominal = $data->pay_amount;
            }
        } else {
            $totalNominal = $data->pay_amount;
        }

        $baseRow = [
            $this->rowNumber,
            Carbon::parse($data->created_at)->translatedFormat('l, d F Y H:i:s'),
            $data->student?->name ?? '-',
            $data->student?->classroom?->name ?? '-',
            $data->student?->classroom?->school?->name ?? '-',
            $this->getTransactionTypeBadge($data->type),
            $itemTagihan,
            'Rp ' . number_format($totalNominal, 0, ',', '.'),
            $data->paymentMethod?->name ?? '-',
            $data->admin?->name ?? '-',
        ];

        $monthlyColumns = $this->getMonthlyBreakdown($data, $details);

        return array_merge($baseRow, $monthlyColumns);
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

    /**
     * Breakdown nominal pembayaran ke 12 kolom bulan (Juli s/d Juni).
     * Jika terbayar, muncul nominal per bulan. Jika tidak terbayar, muncul Rp 0.
     */
    private function getMonthlyBreakdown($data, $details): array
    {
        // Urutan bulan kalender akademik: Juli (7) s/d Juni (6)
        $monthMap = [
            7  => 0, // Juli
            8  => 1, // Agustus
            9  => 2, // September
            10 => 3, // Oktober
            11 => 4, // November
            12 => 5, // Desember
            1  => 6, // Januari
            2  => 7, // Februari
            3  => 8, // Maret
            4  => 9, // April
            5  => 10, // Mei
            6  => 11, // Juni
        ];

        $monthlyTotals = array_fill(0, 12, 0);

        if ($data->type === Transaction::TYPE_BILL) {
            $detailCount = $details->count();

            if ($detailCount > 0) {
                foreach ($details as $detail) {
                    $bill = $detail->bill;
                    $m = (int) ($bill?->month ?? 0);

                    // Tentukan nominal item: detail->amount -> bill->amount -> (pay_amount / detailCount)
                    $amount = 0;
                    if (!empty($detail->amount) && (int)$detail->amount > 0) {
                        $amount = (int) $detail->amount;
                    } elseif ($bill && !empty($bill->amount) && (int)$bill->amount > 0) {
                        $amount = (int) $bill->amount;
                    } elseif ($detailCount > 0 && !empty($data->pay_amount)) {
                        $amount = (int) round($data->pay_amount / $detailCount);
                    }

                    if (isset($monthMap[$m])) {
                        $idx = $monthMap[$m];
                        $monthlyTotals[$idx] += $amount;
                    }
                }
            }
        }

        return array_map(function ($amount) {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }, $monthlyTotals);
    }

    /**
     * Dapatkan tahun mulai dan tahun selesai untuk kalender akademik (Juli - Juni)
     */
    protected function getAcademicYearYears(): array
    {
        // 1. Dari filter tanggal jika ada
        if (request()->filled('start_date')) {
            $date = Carbon::parse(request()->start_date);
            $month = (int) $date->format('n');
            $year = (int) $date->format('Y');
            $startYear = $month >= 7 ? $year : $year - 1;
            $endYear = $startYear + 1;
            return [$startYear, $endYear];
        } elseif (request()->filled('end_date')) {
            $date = Carbon::parse(request()->end_date);
            $month = (int) $date->format('n');
            $year = (int) $date->format('Y');
            $startYear = $month >= 7 ? $year : $year - 1;
            $endYear = $startYear + 1;
            return [$startYear, $endYear];
        }

        // 2. Dari Tahun Ajaran aktif
        $activeAy = \App\Models\AcademicYear::where('is_active', 1)->first();
        if ($activeAy) {
            $startYear = $activeAy->getStartYearSafe() ?? (int) ($activeAy->start_year ?? date('Y'));
            $endYear = (int) ($activeAy->end_year ?? ($startYear + 1));
            return [$startYear, $endYear];
        }

        // 3. Fallback kalender
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $startYear = $currentMonth >= 7 ? $currentYear : $currentYear - 1;
        $endYear = $startYear + 1;
        return [$startYear, $endYear];
    }

    public function headings(): array
    {
        [$startYear, $endYear] = $this->getAcademicYearYears();

        return [
            'No',
            'Tanggal',
            'Nama',
            'Kelas',
            'UPT',
            'Jenis Transaksi',
            'Item Tagihan',
            'Nominal',
            'Metode Pembayaran',
            'Petugas',
            "Juli {$startYear}",
            "Agustus {$startYear}",
            "September {$startYear}",
            "Oktober {$startYear}",
            "November {$startYear}",
            "Desember {$startYear}",
            "Januari {$endYear}",
            "Februari {$endYear}",
            "Maret {$endYear}",
            "April {$endYear}",
            "Mei {$endYear}",
            "Juni {$endYear}",
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

    /**
     * Generate TSV (Tab-Separated Values) format for direct clipboard copy and paste into Google Sheets.
     */
    public function generateTsv(): array
    {
        $headings = $this->headings();
        $collection = $this->collection();

        $tsvLines = [];

        // Header row
        $cleanHeadings = array_map(function ($h) {
            return str_replace(["\t", "\r", "\n"], ' ', (string) $h);
        }, $headings);
        $tsvLines[] = implode("\t", $cleanHeadings);

        // Data rows
        foreach ($collection as $item) {
            $mapped = $this->map($item);
            $cleanCells = array_map(function ($cell) {
                return str_replace(["\t", "\r", "\n"], ' ', (string) $cell);
            }, $mapped);
            $tsvLines[] = implode("\t", $cleanCells);
        }

        return [
            'tsv' => implode("\r\n", $tsvLines),
            'count' => $collection->count(),
        ];
    }
}
