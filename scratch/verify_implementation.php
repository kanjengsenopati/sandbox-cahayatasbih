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
use App\Services\PaymentRateService;
use App\Http\Controllers\Admin\Select2Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== VERIFIKASI FUNGSIONALITAS PEMBATALAN & PROTEKSI TAGIHAN ===\n\n";

try {
    DB::beginTransaction();

    // Authenticate first available admin (or mock one if none exists)
    $admin = \App\Models\Admin::first();
    if (!$admin) {
        // Create a temporary admin
        $admin = \App\Models\Admin::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('Super Admin');
    }
    
    // Log the admin in
    Auth::login($admin);
    echo "Authenticated as admin: {$admin->name}\n";

    // Setup: Ambil atau buat sekolah, kelas, tahun ajaran, dan tipe tagihan
    $academicYear = AcademicYear::firstOrCreate(
        ['name' => '2026/2027'],
        ['start_year' => 2026, 'end_year' => 2027, 'is_active' => true]
    );

    $school = \App\Models\School::firstOrCreate(
        ['name' => 'SMP UNGGULAN TEST'],
        ['address' => 'Test Address']
    );

    // Make sure admin is associated with this school if not a super admin
    if (!$admin->hasRole('Super Admin')) {
        $admin->adminSchool()->firstOrCreate(['school_id' => $school->id]);
    }

    $classroom = Classroom::firstOrCreate(
        ['name' => '7B TEST'],
        ['school_id' => $school->id]
    );

    // Buat tipe tagihan bulanan
    $billItem = BillItem::firstOrCreate(['name' => 'SPP TEST']);
    $billType = BillType::firstOrCreate(
        ['name' => 'SPP Bulanan TEST', 'bill_item_id' => $billItem->id],
        ['type' => BillType::TYPE_MONTHLY, 'academic_year_id' => $academicYear->id]
    );

    // Buat siswa aktif baru untuk testing
    $student = Student::create([
        'name' => 'ABDUL TEST ACTIVE',
        'classroom_id' => $classroom->id,
        'school_id' => $school->id,
        'nis' => '12345678',
        'gender' => 'L',
        'status' => Student::STATUS_ACTIVE,
    ]);

    echo "1. Siswa dibuat: {$student->name} | Status: {$student->status}\n";

    // Buat tagihan belum dibayar (UNPAID) untuk siswa ini
    $bill = Bill::create([
        'student_id' => $student->id,
        'bill_type_id' => $billType->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $academicYear->id,
        'month' => 7,
        'year' => 2026,
        'amount' => 500000,
        'status' => Bill::STATUS_UNPAID,
    ]);

    echo "2. Tagihan UNPAID dibuat untuk siswa: Rp " . number_format($bill->amount, 0, ',', '.') . "\n";

    // Panggil controller select2 untuk verifikasi siswa muncul
    $request = new Request([
        'data_type' => 'STUDENT_BY_SCHOOL',
        'school_id' => $school->id,
        'search' => 'ABDUL TEST'
    ]);
    
    $select2Controller = new Select2Controller();
    $response = $select2Controller->index($request);
    $data = $response->getData();
    
    $found = collect($data)->contains('id', $student->id);
    echo "3. Cek di Select2 (Siswa Aktif): " . ($found ? "MUNCUL (PAS)" : "TIDAK MUNCUL (ERROR)") . "\n";

    // Ubah status siswa menjadi Keluar (DROPPED_OUT)
    $student->status = Student::STATUS_DROPPED_OUT;
    $student->save();
    echo "4. Status siswa diubah menjadi: {$student->status} (Keluar)\n";

    // Siswa Keluar dengan tunggakan HARUS TETAP MUNCUL di Select2
    $response = $select2Controller->index($request);
    $data = $response->getData();
    $found = collect($data)->contains('id', $student->id);
    
    $studentData = collect($data)->firstWhere('id', $student->id);
    $hasDroppedOutStatus = $studentData && $studentData->status === 'DROPPED_OUT';
    echo "5. Cek di Select2 (Keluar + Ada Tunggakan): " . ($found ? "MUNCUL (PAS)" : "TIDAK MUNCUL (ERROR)") . "\n";
    echo "   - Status Keluar di data: " . ($hasDroppedOutStatus ? "YA: '{$studentData->status}' (PAS)" : "TIDAK (ERROR)") . "\n";

    // Lunasin tagihan siswa
    $bill->status = Bill::STATUS_PAID;
    $bill->save();
    echo "6. Tagihan siswa dilunasi (PAID)\n";

    // Siswa Keluar yang SUDAH LUNAS harus TIDAK MUNCUL di Select2
    $response = $select2Controller->index($request);
    $data = $response->getData();
    $found = collect($data)->contains('id', $student->id);
    echo "7. Cek di Select2 (Keluar + Sudah Lunas): " . (!$found ? "TIDAK MUNCUL (PAS)" : "MUNCUL (ERROR)") . "\n";

    // Coba buat tagihan baru menggunakan PaymentRateService untuk kelas siswa ini
    echo "8. Mencoba membuat tagihan baru untuk siswa Keluar...\n";
    $paymentRate = PaymentRate::create([
        'bill_type_id' => $billType->id,
        'amount' => 600000,
    ]);
    
    $paymentRateItem = PaymentRateItem::create([
        'payment_rate_id' => $paymentRate->id,
        'month' => 8,
        'year' => 2026,
        'amount' => 600000,
    ]);

    // Jalankan generator tagihan
    $paymentRateService = new PaymentRateService();
    $dataMock = [
        'classrooms' => [$classroom->id],
        'months' => [8],
        'year' => 2026,
        'price' => 600000,
        'bulan_8' => 600000,
        'tahun_8' => 2026,
    ];
    $paymentRateService->createBillsForStudents($paymentRate, $billType, $dataMock);

    // Cek apakah ada tagihan bulan 8 terbuat untuk siswa Keluar
    $newBillExists = Bill::where('student_id', $student->id)
        ->where('month', 8)
        ->where('year', 2026)
        ->exists();

    echo "   - Tagihan baru terbuat untuk siswa Keluar? " . ($newBillExists ? "YA (ERROR - Proteksi Gagal)" : "TIDAK (PAS - Proteksi Berhasil)") . "\n";

    // Test soft-delete student
    $student->delete();
    echo "9. Siswa dihapus (soft-delete)\n";

    // Cek apakah tagihan lunas/unpaid ikut terhapus
    // Tagihan PAID harus tetap ada (tidak terhapus)
    $paidBillStillExists = Bill::withTrashed()->where('id', $bill->id)->whereNull('deleted_at')->exists();
    echo "   - Tagihan LUNAS tetap dipertahankan? " . ($paidBillStillExists ? "YA (PAS)" : "TIDAK (ERROR)") . "\n";

    // Buat tagihan UNPAID lagi untuk test hapus
    $unpaidBill = Bill::create([
        'student_id' => $student->id,
        'bill_type_id' => $billType->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $academicYear->id,
        'month' => 9,
        'year' => 2026,
        'amount' => 500000,
        'status' => Bill::STATUS_UNPAID,
    ]);

    // Restore siswa lalu hapus lagi untuk memicu event deleted
    $student->restore();
    echo "10. Siswa direstore lalu dihapus ulang untuk testing cascade hook\n";
    $student->delete();
    
    $unpaidBillDeleted = Bill::withTrashed()->where('id', $unpaidBill->id)->whereNotNull('deleted_at')->exists();
    echo "   - Tagihan UNPAID otomatis terhapus saat siswa dihapus? " . ($unpaidBillDeleted ? "YA (PAS - Cascade Hook Sukses)" : "TIDAK (ERROR)") . "\n";

    echo "\n=== VERIFIKASI SELESAI DENGAN SUKSES ===";

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    echo "\nERROR TERJADI: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
