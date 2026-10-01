<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentId = \App\Models\Student::where('nis', '330200377')->first()->id;

\Illuminate\Support\Facades\Gate::before(fn() => true);
\Illuminate\Support\Facades\Auth::shouldUse('web');
\Illuminate\Support\Facades\Auth::login(\App\Models\User::first());

class ProfilingBillController extends \App\Http\Controllers\Admin\BillController {
    public function getBillsPublic($studentId, $type, $academicYearId = null) {
        $method = new ReflectionMethod(get_parent_class($this), 'getBills');
        $method->setAccessible(true);
        return $method->invoke($this, $studentId, $type, $academicYearId);
    }
    public function getUngeneratedRatesForStudentPublic($student, $type, $excludeBillTypeIds = [], $academicYearId = null, $preloadedRates = null) {
        $method = new ReflectionMethod(get_parent_class($this), 'getUngeneratedRatesForStudent');
        $method->setAccessible(true);
        return $method->invoke($this, $student, $type, $excludeBillTypeIds, $academicYearId, $preloadedRates);
    }
}

$controller = app()->make(ProfilingBillController::class);

$t0 = microtime(true);
$academicYearId = null;
$student = \App\Models\Student::with(['user', 'classroom.school', 'classroomHistories.classroom'])->find($studentId);
$t1 = microtime(true);
echo "Fetch student: " . round($t1 - $t0, 4) . "s\n";

$preloadedRates = \App\Services\TransactionService::getCachedPreloadedRates();
$t2 = microtime(true);
echo "getCachedPreloadedRates: " . round($t2 - $t1, 4) . "s\n";

$allStudentBills = \App\Models\Bill::where('student_id', $studentId)->whereNull('deleted_at')->with('billType')->get();
$t3 = microtime(true);
echo "Fetch allStudentBills: " . round($t3 - $t2, 4) . "s\n";

$billMonth = $controller->getBillsPublic($studentId, 'MONTHLY', $academicYearId);
$t4 = microtime(true);
echo "getBills(MONTHLY): " . round($t4 - $t3, 4) . "s\n";

$billOthers = $controller->getBillsPublic($studentId, 'OTHER', $academicYearId);
$t5 = microtime(true);
echo "getBills(OTHER): " . round($t5 - $t4, 4) . "s\n";

$ungeneratedOtherRates = $controller->getUngeneratedRatesForStudentPublic($student, 'OTHER', $billOthers->pluck('id')->toArray(), $academicYearId, $preloadedRates);
$t6 = microtime(true);
echo "getUngeneratedRates(OTHER): " . round($t6 - $t5, 4) . "s\n";

$ungeneratedMonthlyRates = $controller->getUngeneratedRatesForStudentPublic($student, 'MONTHLY', $billMonth->pluck('id')->toArray(), $academicYearId, $preloadedRates);
$t7 = microtime(true);
echo "getUngeneratedRates(MONTHLY): " . round($t7 - $t6, 4) . "s\n";

echo "Total Profiled Controller Methods: " . round($t7 - $t0, 4) . "s\n";
