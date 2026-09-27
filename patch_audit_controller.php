<?php
$content = file_get_contents('app/Http/Controllers/Admin/AuditController.php');

$search = <<<EOD
        \$billTypes = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('bill_types')->whereNull('deleted_at')->orderBy('name', 'asc')->get();
EOD;

$replace = <<<EOD
        // Resolve bill types and map them to their corresponding school_id via payment_rates -> payment_rate_classrooms -> classrooms
        \$billTypes = \Illuminate\Support\Facades\DB::connection('mysql_master')->table('bill_types')
            ->leftJoin('payment_rates', 'payment_rates.bill_type_id', '=', 'bill_types.id')
            ->leftJoin('payment_rate_classrooms', 'payment_rate_classrooms.payment_rate_id', '=', 'payment_rates.id')
            ->leftJoin('classrooms', 'classrooms.id', '=', 'payment_rate_classrooms.classroom_id')
            ->whereNull('bill_types.deleted_at')
            ->select('bill_types.id', 'bill_types.name', 'bill_types.academic_year_id', 'classrooms.school_id')
            ->groupBy('bill_types.id', 'bill_types.name', 'bill_types.academic_year_id', 'classrooms.school_id')
            ->orderBy('bill_types.name', 'asc')
            ->get();
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/Admin/AuditController.php', $content);
echo "Patched AuditController\n";
