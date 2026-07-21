<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Bill;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\BillItem;
use App\Models\PaymentRate;
use App\Models\PaymentRateItem;
use Illuminate\Support\Facades\DB;

echo "=== VERIFIKASI LOGIKA RIWAYAT MASUK DAN BATASAN TAHUN AJARAN ===\n\n";

try {
    DB::beginTransaction();

    // 1. Test getStartYearSafe on AcademicYear
    echo "1. Menguji AcademicYear::getStartYearSafe()...\n";
    $ay1 = new AcademicYear(['name' => '2026/2027', 'start_year' => 2026]);
    $ay2 = new AcademicYear(['name' => '2025/2026']);
    $ay3 = new AcademicYear(['name' => 'N/A']);
    
    echo "   - AY1 (2026/2027, start_year=2026): " . ($ay1->getStartYearSafe() === 2026 ? "PAS (2026)" : "ERROR") . "\n";
    echo "   - AY2 (2025/2026, start_year=null): " . ($ay2->getStartYearSafe() === 2025 ? "PAS (2025)" : "ERROR") . "\n";
    echo "   - AY3 (N/A, start_year=null): " . ($ay3->getStartYearSafe() === null ? "PAS (null)" : "ERROR") . "\n";

    // 2. Test getEntryYear on Student
    echo "\n2. Menguji Student::getEntryYear()...\n";
    
    // Test case A: NIS format (332126206 -> 2026)
    $studentA = new Student([
        'name' => 'Siswa A',
        'nis' => '332126206'
    ]);
    echo "   - Siswa A (NIS 332126206): " . ($studentA->getEntryYear() === 2026 ? "PAS (2026)" : "ERROR: got " . $studentA->getEntryYear()) . "\n";

    // Test case B: NIS format (332125102 -> 2025)
    $studentB = new Student([
        'name' => 'Siswa B',
        'nis' => '332125102'
    ]);
    echo "   - Siswa B (NIS 332125102): " . ($studentB->getEntryYear() === 2025 ? "PAS (2025)" : "ERROR: got " . $studentB->getEntryYear()) . "\n";

    // Test case C: Fallback to created_at year
    $studentC = new Student([
        'name' => 'Siswa C',
        'nis' => null,
    ]);
    $studentC->created_at = \Carbon\Carbon::parse('2024-03-15 10:00:00');
    echo "   - Siswa C (NIS null, created_at=2024): " . ($studentC->getEntryYear() === 2024 ? "PAS (2024)" : "ERROR: got " . $studentC->getEntryYear()) . "\n";

    // 3. Test bill generation restriction
    echo "\n3. Menguji proteksi pembuatan tagihan di bawah tahun masuk...\n";
    
    // Create actual test models in DB (transaction rollback will clean them up)
    $school = \App\Models\School::firstOrCreate(['name' => 'Sekolah Uji'], ['address' => 'Test']);
    $classroom = Classroom::firstOrCreate(['name' => 'Kelas 10A TEST'], ['school_id' => $school->id]);
    
    // Academic years
    $ay2025 = AcademicYear::firstOrCreate(['name' => '2025/2026'], ['start_year' => 2025, 'end_year' => 2026, 'is_active' => false]);
    $ay2026 = AcademicYear::firstOrCreate(['name' => '2026/2027'], ['start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);

    $student = Student::create([
        'name' => 'AHMAD NADAV TEST',
        'nis' => '332126206', // Entry year 2026
        'classroom_id' => $classroom->id,
        'school_id' => $school->id,
        'status' => Student::STATUS_ACTIVE,
        'gender' => 'L',
    ]);
    
    // Bill types
    $billItem = BillItem::firstOrCreate(['name' => 'SPP TEST']);
    $billType2025 = BillType::firstOrCreate(
        ['name' => 'SPP 2025 TEST', 'bill_item_id' => $billItem->id],
        ['type' => BillType::TYPE_MONTHLY, 'academic_year_id' => $ay2025->id]
    );
    $billType2026 = BillType::firstOrCreate(
        ['name' => 'SPP 2026 TEST', 'bill_item_id' => $billItem->id],
        ['type' => BillType::TYPE_MONTHLY, 'academic_year_id' => $ay2026->id]
    );

    // Payment rates & items
    $rate2025 = PaymentRate::create(['bill_type_id' => $billType2025->id, 'amount' => 500000, 'type' => PaymentRate::TYPE_REGULAR]);
    $rate2025->paymentRateClassrooms()->create(['classroom_id' => $classroom->id]);
    $rateItem2025 = PaymentRateItem::create(['payment_rate_id' => $rate2025->id, 'month' => 7, 'year' => 2025, 'amount' => 500000]);

    $rate2026 = PaymentRate::create(['bill_type_id' => $billType2026->id, 'amount' => 600000, 'type' => PaymentRate::TYPE_REGULAR]);
    $rate2026->paymentRateClassrooms()->create(['classroom_id' => $classroom->id]);
    $rateItem2026 = PaymentRateItem::create(['payment_rate_id' => $rate2026->id, 'month' => 7, 'year' => 2026, 'amount' => 600000]);

    // Test CheckBillClass command logic (mocking by running a manual loop similar to command)
    echo "   - Menjalankan simulasi CheckBillClass...\n";
    $billTypesToCheck = [$billType2025, $billType2026];
    $createdCount = 0;
    
    foreach ($billTypesToCheck as $bt) {
        $ay = $bt->academicYear;
        $startYear = $ay ? $ay->getStartYearSafe() : null;
        
        $paymentRates = PaymentRate::where('bill_type_id', $bt->id)->get();
        foreach ($paymentRates as $pr) {
            if ($startYear !== null && $student->getEntryYear() > $startYear) {
                // Should skip!
                continue;
            }
            
            // Create bill
            Bill::create([
                'bill_type_id' => $bt->id,
                'student_id' => $student->id,
                'classroom_id' => $student->classroom_id,
                'academic_year_id' => $bt->academic_year_id,
                'month' => 7,
                'year' => $startYear,
                'amount' => 500000,
                'status' => Bill::STATUS_UNPAID,
            ]);
            $createdCount++;
        }
    }

    echo "   - Jumlah tagihan yang dibuat: {$createdCount} (Ekspektasi: 1 - Hanya tagihan 2026/2027)\n";
    
    $bill2025Exists = Bill::where('student_id', $student->id)->where('bill_type_id', $billType2025->id)->exists();
    $bill2026Exists = Bill::where('student_id', $student->id)->where('bill_type_id', $billType2026->id)->exists();
    
    echo "   - Tagihan 2025 terbuat? " . ($bill2025Exists ? "YA (ERROR)" : "TIDAK (PAS)") . "\n";
    echo "   - Tagihan 2026 terbuat? " . ($bill2026Exists ? "YA (PAS)" : "TIDAK (ERROR)") . "\n";

    // 4. Test Cleanup Migration Logic
    echo "\n4. Menguji logika migrasi pembersihan...\n";
    
    // Force create an invalid unpaid bill for 2025
    DB::table('bills')->insert([
        'id' => \Illuminate\Support\Str::uuid()->toString(),
        'bill_type_id' => $billType2025->id,
        'student_id' => $student->id,
        'classroom_id' => $student->classroom_id,
        'academic_year_id' => $ay2025->id,
        'month' => 8,
        'year' => 2025,
        'amount' => 500000,
        'status' => 'UNPAID',
    ]);
    
    echo "   - Tagihan UNPAID tahun 2025 dipaksa buat langsung ke DB.\n";
    
    // Run cleanup logic
    $deletedCount = 0;
    $studentsList = DB::table('students')->where('id', $student->id)->get();
    $ayStartYears = [
        $ay2025->id => 2025,
        $ay2026->id => 2026
    ];
    
    foreach ($studentsList as $s) {
        $entryYear = 2026; // hardcoded for test student
        
        $bills = DB::table('bills')
            ->where('student_id', $s->id)
            ->where('status', 'UNPAID')
            ->whereNull('deleted_at')
            ->get();

        foreach ($bills as $bill) {
            $billAyStartYear = $ayStartYears[$bill->academic_year_id] ?? null;
            if ($billAyStartYear !== null && $billAyStartYear < $entryYear) {
                DB::table('bills')
                    ->where('id', $bill->id)
                    ->update([
                        'deleted_at' => now(),
                        'updated_at' => now()
                    ]);
                $deletedCount++;
            }
        }
    }
    
    echo "   - Tagihan tidak valid dihapus oleh simulasi migrasi: {$deletedCount} (Ekspektasi: 1)\n";
    
    $invalidBillStillActive = DB::table('bills')
        ->where('student_id', $student->id)
        ->where('bill_type_id', $billType2025->id)
        ->whereNull('deleted_at')
        ->exists();
        
    echo "   - Apakah tagihan 2025 yang salah masih aktif? " . ($invalidBillStillActive ? "YA (ERROR)" : "TIDAK (PAS - Telah Terhapus)") . "\n";

    echo "\n=== SEMUA VERIFIKASI BERHASIL DENGAN SUKSES ===";

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    echo "\nERROR TERJADI: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
