<?php
 
namespace App\Exports;
 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
 
class ReportBillStudentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
 
    public function __construct($data)
    {
        $this->data = $data;
    }
 
    public function collection()
    {
        return $this->data;
    }
 
    public function headings(): array
    {
        return [
            'Nama Santri',
            'Kelas',
            'Jumlah Tagihan',
            'Total Tagihan (Rp)',
            'Total Terbayar (Rp)',
            'Sisa Tagihan (Rp)',
            'Tunggakan Saat Ini (Rp)',
            'Persentase Pelunasan',
        ];
    }
 
    public function map($row): array
    {
        $percentage = $row->total_bill == 0 ? 0 : ($row->total_paid / $row->total_bill) * 100;
        
        return [
            $row->name,
            $row->classroom_name,
            $row->bill_count . ' tagihan',
            $row->total_bill,
            $row->total_paid,
            $row->total_unpaid,
            $row->current_due_amount,
            round($percentage, 0) . '%',
        ];
    }
 
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
