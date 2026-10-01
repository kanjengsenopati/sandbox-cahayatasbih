<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where('nis', '330200377')->first();
$studentId = $student->id;

\Illuminate\Support\Facades\Gate::before(function ($user, $ability) { return true; });
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::shouldUse('web');
\Illuminate\Support\Facades\Auth::login($user);

$controller = app()->make(\App\Http\Controllers\Admin\BillController::class);

$t_start = microtime(true);
$preloadedRates = \App\Services\TransactionService::getCachedPreloadedRates();
$t_pre = microtime(true);
echo "1. getCachedPreloadedRates: " . round($t_pre - $t_start, 4) . "s\n";

// Emulate getBills for MONTHLY
$t_month_start = microtime(true);
$academicYearId = null; // Emulate 'all'
$studentSchoolName = $student->classroom->school->name ?? '';
$entryYear = $student->getEntryYear() ?? date('Y');

$studentBillTypeIds = \App\Models\Bill::with(['billType.billItem'])
    ->where('student_id', $studentId)
    ->whereNull('deleted_at')
    ->get()
    ->filter(function ($b) use ($studentSchoolName) {
        $bTypeName = $b->billType->name ?? '';
        $bItemName = $b->billType->billItem->name ?? '';
        return \App\Services\TransactionService::isBillTypeMatchingStudentSchoolUnit($bTypeName, $studentSchoolName)
            || \App\Services\TransactionService::isBillTypeMatchingStudentSchoolUnit($bItemName, $studentSchoolName);
    })->pluck('bill_type_id')->unique();

$query = \App\Models\BillType::with(['billItem', 'academicYear', 'bills' => function ($q) use ($studentId) {
        $q->where('student_id', $studentId)->with(['classroom.school']);
    }])
    ->where('type', 'MONTHLY')
    ->whereIn('id', $studentBillTypeIds);

$billTypes = $query->latest()->get();
$allStudentBills = \App\Models\Bill::where('student_id', $studentId)->whereNull('deleted_at')->with('billType')->get();

$t_month_fetch = microtime(true);
echo "2. Fetch MONTHLY BillTypes: " . round($t_month_fetch - $t_month_start, 4) . "s\n";

// Filter
$filtered = $billTypes->filter(function($item) use ($student, $studentSchoolName, $entryYear, $academicYearId, $preloadedRates) {
    if ($item->is_visible === false) return false;
    if (\App\Services\TransactionService::isClass12MA($student)) {
        if (!$academicYearId && \App\Services\TransactionService::isBillBeforeAcademicYear2026($item->academicYear)) return false;
    }
    if (!\App\Services\TransactionService::isBillTypeMatchingStudentSchoolUnit($item->name, $studentSchoolName)) return false;
    if (!$academicYearId && $item->academicYear) {
        $startYear = $item->academicYear->getStartYearSafe();
        if ($startYear !== null && $startYear < $entryYear) return false;
    }
    $hasPaidBills = $item->bills->contains(fn($b) => $b->status === \App\Models\Bill::STATUS_PAID || (int)$b->paid_amount > 0);
    if (!$hasPaidBills && !\App\Services\TransactionService::hasActiveRateForStudent($student, $item, $preloadedRates)) return false;
    return true;
});
$t_month_filter = microtime(true);
echo "3. Filter MONTHLY BillTypes: " . round($t_month_filter - $t_month_fetch, 4) . "s\n";

// Map (calculateBillTotals)
$mapped = $filtered->map(function($item) use ($student, $preloadedRates, $allStudentBills) {
    $bills = $item->bills;
    $totalBill = 0;
    $startYear = $item->academicYear?->start_year ?? date('Y');
    $endYear = $item->academicYear?->end_year ?? ($startYear + 1);
    foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
        $y = ($m >= 7) ? $startYear : $endYear;
        $bDet = $bills->firstWhere('month', (int)$m) ?? $bills->firstWhere('month', (string)$m);
        if ($bDet !== null) {
            $totalBill += $bDet->amount;
        } else {
            $totalBill += \App\Services\TransactionService::resolveStudentRateForBillType($student->id, $item->id, $m, $y, $preloadedRates);
        }
    }
    $item->total_bill = $totalBill;
    $item->total_paid = $bills->sum('paid_amount');
    $item->total_unpaid = max(0, $item->total_bill - $item->total_paid);
    return $item;
});
$t_month_map = microtime(true);
echo "4. Map (Calculate totals) MONTHLY: " . round($t_month_map - $t_month_filter, 4) . "s\n";

// getUngeneratedRatesForStudent
$t_ung_start = microtime(true);
$candidateBillTypes = \App\Models\BillType::with(['billItem', 'academicYear'])
    ->where('type', 'MONTHLY')
    ->whereNull('deleted_at')
    ->where(fn($q) => $q->whereNull('is_visible')->orWhere('is_visible', true))
    ->whereNotIn('id', $filtered->pluck('id')->toArray())
    ->get();

$result = collect();
foreach ($candidateBillTypes as $bt) {
    if (!\App\Services\TransactionService::isBillTypeMatchingStudentSchoolUnit($bt->name, $studentSchoolName)) continue;
    if ($bt->academicYear) {
        $startYear = $bt->academicYear->getStartYearSafe();
        if ($startYear !== null && $startYear < $entryYear) continue;
    }
    if (\App\Services\TransactionService::isClass12MA($student)) {
        if (\App\Services\TransactionService::isBillBeforeAcademicYear2026($bt->academicYear)) continue;
    }
    if (!\App\Services\TransactionService::hasActiveRateForStudent($student, $bt, $preloadedRates)) continue;

    $ratesForBt = $preloadedRates->where('bill_type_id', $bt->id);
    $matchingRate = $ratesForBt->first(function ($r) use ($student) {
        if ($r->type === \App\Models\PaymentRate::TYPE_TRANSFER) {
            return $r->paymentRateStudents->whereNull('deleted_at')->contains('student_id', $student->id);
        }
        if ($r->type === \App\Models\PaymentRate::TYPE_REGULAR) {
            $classMatch = $r->paymentRateClassrooms->whereNull('deleted_at')->contains('classroom_id', $student->classroom_id);
            if (!$classMatch) return false;
            return true;
        }
        return false;
    });
    if ($matchingRate) {
        $result->push($matchingRate);
    }
}
$t_ung_end = microtime(true);
echo "5. getUngeneratedRatesForStudent MONTHLY: " . round($t_ung_end - $t_ung_start, 4) . "s\n";
