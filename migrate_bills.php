<?php
$oldTypeId = "091f83b2-fe4b-4773-b2fb-9a6f2a5ffe93";
$newTypeId = "c9e7ff1b-549a-4495-ac11-04c4c72fefd4";
$studentId = "386fcf4e-caab-4494-b005-a3590ace3276";
$oldBills = \App\Models\Bill::where("student_id", $studentId)->where("bill_type_id", $oldTypeId)->get();
$newBills = \App\Models\Bill::where("student_id", $studentId)->where("bill_type_id", $newTypeId)->get();
foreach($oldBills as $old) {
    if ($old->paid_amount > 0) {
        $new = $newBills->firstWhere("month", $old->month);
        if ($new) {
            echo "Migrating Month " . $old->month . " amount " . $old->paid_amount . PHP_EOL;
            \DB::table("transaction_details")->where("bill_id", $old->id)->update(["bill_id" => $new->id]);
            $new->paid_amount = $old->paid_amount;
            $new->status = "PAID";
            $new->save();
            $old->paid_amount = 0;
            $old->status = "UNPAID";
            $old->save();
        }
    }
    $old->delete();
}
echo "Done Migration";

