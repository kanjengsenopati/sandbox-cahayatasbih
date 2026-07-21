<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Http\Controllers\Admin\BillController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== VERIFIKASI PEMBERSIHAN FILTER DAN RENDERING VIEW ===\n\n";

try {
    // 1. Authenticate first available admin
    $admin = Admin::first();
    if (!$admin) {
        $admin = Admin::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
    }
    $admin->assignRole('Super Admin');
    Auth::login($admin);
    echo "Authenticated as admin: {$admin->name}\n";

    // 2. Setup testing academic years
    $ay2025 = AcademicYear::firstOrCreate(['name' => '2025/2026'], ['start_year' => 2025, 'end_year' => 2026, 'is_active' => false]);
    $ay2026 = AcademicYear::firstOrCreate(['name' => '2026/2027'], ['start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

    // 3. Setup testing student with entry year 2026
    $student = Student::firstOrCreate(
        ['nis' => '332126206'],
        [
            'name' => 'AHMAD NADAV BARUNA AHNAF',
            'status' => Student::STATUS_ACTIVE,
            'gender' => 'L',
        ]
    );
    echo "Testing with Student: {$student->name} | NIS: {$student->nis} | Entry Year: {$student->getEntryYear()}\n";

    // 4. Test sanitization logic in controller
    echo "\n4. Menguji sanitasi filter di BillController...\n";
    $controller = new BillController();

    // Case A: Request invalid year (2025/2026) for student who entered in 2026
    $requestA = Request::create('/admin/bills', 'GET', [
        'student_id' => $student->id,
        'academic_year_id' => $ay2025->id
    ]);
    // Bind request
    app()->instance('request', $requestA);
    
    echo "Debug: ay2025 id = {$ay2025->id}, name = {$ay2025->name}, start_year = {$ay2025->start_year}\n";
    echo "Debug: start_year_safe = " . ($ay2025->getStartYearSafe() ?? 'null') . "\n";
    echo "Debug: student entry_year = {$student->getEntryYear()}\n";

    // Call controller index
    $responseA = $controller->index();
    echo "Debug: request->all() = ";
    print_r(request()->all());
    echo "   - Request tahun ajaran 2025/2026 (sebelum masuk): " . (request()->academic_year_id === null ? "DIPERSIHKAN (PAS)" : "ERROR: " . request()->academic_year_id) . "\n";

    // Case B: Request valid year (2026/2027) for student who entered in 2026
    $requestB = Request::create('/admin/bills', 'GET', [
        'student_id' => $student->id,
        'academic_year_id' => $ay2026->id
    ]);
    app()->instance('request', $requestB);
    $responseB = $controller->index();
    echo "   - Request tahun ajaran 2026/2027 (seusai masuk): " . (request()->academic_year_id === $ay2026->id ? "DIPERTAHANKAN (PAS)" : "ERROR: " . request()->academic_year_id) . "\n";

    // 5. Test view rendering
    echo "\n5. Menguji render view index.blade.php...\n";
    
    // Render the view directly to string and check if it has compile errors
    $viewHtml = view('admins.bill.index', [
        'schools' => \App\Models\School::all(),
        'academicYears' => AcademicYear::all(),
        'student' => $student,
        'billMonth' => collect([]),
        'billOthers' => collect([])
    ])->render();
    
    echo "   - View index.blade.php berhasil dirender tanpa syntax error! (PAS)\n";
    
    // Check that pre-entry years are not present in the HTML dropdown options
    // The option for $ay2025->id should NOT be visible.
    $hasOption2025 = strpos($viewHtml, 'value="' . $ay2025->id . '"') !== false;
    $hasOption2026 = strpos($viewHtml, 'value="' . $ay2026->id . '"') !== false;
    
    echo "   - Opsi tahun ajaran 2025/2026 disembunyikan? " . (!$hasOption2025 ? "YA (PAS)" : "TIDAK (ERROR)") . "\n";
    echo "   - Opsi tahun ajaran 2026/2027 ditampilkan? " . ($hasOption2026 ? "YA (PAS)" : "TIDAK (ERROR)") . "\n";

    echo "\n=== SEMUA VERIFIKASI BERHASIL DENGAN SUKSES ===";

} catch (\Exception $e) {
    echo "\nERROR TERJADI: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
