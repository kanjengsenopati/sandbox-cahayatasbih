<?php

$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

// FIX analyzeBillingStatus
$search1 = <<<EOD
        // 2. Bulk query for Local DB bills summary
        \$localBillsGroup = [];
        if (!empty(\$studentIds)) {
            \$lQuery = \$localConn->table('bills')
                ->whereIn('student_id', \$studentIds)
                ->whereNull('deleted_at');

            if (!empty(\$academicYearId)) {
                \$lQuery->where('academic_year_id', \$academicYearId);
            }
            if (!empty(\$billTypeId)) {
                \$lQuery->where('bill_type_id', \$billTypeId);
            }
EOD;

$replace1 = <<<EOD
        // Smart UUID Mapping for Local Query
        \$localBillTypeId = \$billTypeId;
        if (!empty(\$billTypeId)) {
            \$masterBillTypeForAnalyze = \$masterConn->table('bill_types')->where('id', \$billTypeId)->first();
            if (\$masterBillTypeForAnalyze) {
                \$masterCleanName = str_replace(' ', '', \$masterBillTypeForAnalyze->name);
                \$localBillTypeForAnalyze = \$localConn->table('bill_types')
                    ->whereRaw("REPLACE(name, ' ', '') = ?", [\$masterCleanName])
                    ->where('academic_year_id', \$masterBillTypeForAnalyze->academic_year_id)
                    ->first();
                if (\$localBillTypeForAnalyze) {
                    \$localBillTypeId = \$localBillTypeForAnalyze->id;
                }
            }
        }

        // 2. Bulk query for Local DB bills summary
        \$localBillsGroup = [];
        if (!empty(\$studentIds)) {
            \$lQuery = \$localConn->table('bills')
                ->whereIn('student_id', \$studentIds)
                ->whereNull('deleted_at');

            if (!empty(\$academicYearId)) {
                \$lQuery->where('academic_year_id', \$academicYearId);
            }
            if (!empty(\$localBillTypeId)) {
                \$lQuery->where('bill_type_id', \$localBillTypeId);
            }
EOD;

$content = str_replace($search1, $replace1, $content);


// FIX executeVerifiedMerge
$search2 = <<<EOD
                            // Smart UUID Mapping
                            \$masterBillType = \$masterConn->table('bill_types')->where('id', \$mBill->bill_type_id)->first();
                            if (\$masterBillType) {
                                \$localBillType = \$localConn->table('bill_types')
                                    ->where('name', \$masterBillType->name)
                                    ->where('academic_year_id', \$masterBillType->academic_year_id)
                                    ->first();
                                if (\$localBillType) {
                                    \$targetBillTypeId = \$localBillType->id;
                                }
                            }
EOD;

$replace2 = <<<EOD
                            // Smart UUID Mapping
                            \$masterBillType = \$masterConn->table('bill_types')->where('id', \$mBill->bill_type_id)->first();
                            if (\$masterBillType) {
                                \$masterCleanName = str_replace(' ', '', \$masterBillType->name);
                                \$localBillType = \$localConn->table('bill_types')
                                    ->whereRaw("REPLACE(name, ' ', '') = ?", [\$masterCleanName])
                                    ->where('academic_year_id', \$masterBillType->academic_year_id)
                                    ->first();
                                if (\$localBillType) {
                                    \$targetBillTypeId = \$localBillType->id;
                                }
                            }
EOD;

$content = str_replace($search2, $replace2, $content);

file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Updated MasterIngestionBridgeService.php\n";
