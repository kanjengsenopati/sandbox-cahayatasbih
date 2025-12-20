<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportBillDetailExport implements WithMultipleSheets
{
    protected $students;
    protected $periods;
    protected $billTypeId;

    public function __construct($students, $periods, $billTypeId)
    {
        $this->students = $students;
        $this->periods = $periods;
        $this->billTypeId = $billTypeId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Group students by classroom_name
        // The query in Controller must have 'classrooms.name as classroom_name'
        $grouped = $this->students->groupBy('classroom_name');

        foreach ($grouped as $className => $students) {
            $sheets[] = new ReportBillPerClassSheet(
                $className, 
                $students->values(), // Reset keys
                $this->periods, 
                $this->billTypeId
            );
        }

        return $sheets;
    }
}
