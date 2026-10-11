<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\AdminSchool;
use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ReportTransactionScopeAndFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $multiUptAdmin;
    protected School $schoolPondok;
    protected School $schoolMa;
    protected School $schoolSmp;
    protected School $schoolStaff;
    protected BillItem $posPondok;
    protected BillItem $posMa;
    protected BillItem $posSmp;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'Manage Laporan Transaksi', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'KOORDINATOR CAHAYA MART', 'guard_name' => 'web']);
        $role->givePermissionTo('Manage Laporan Transaksi');

        $this->schoolPondok = School::where('type', School::TYPE_PONDOK)->first() ?? School::create([
            'id' => (string) Str::uuid(),
            'name' => 'PPTQ CAHAYA TASBIH',
            'type' => School::TYPE_PONDOK,
            'slug' => 'pptq-cahaya-tasbih',
        ]);

        $this->schoolMa = School::where('type', School::TYPE_MA)->first() ?? School::create([
            'id' => (string) Str::uuid(),
            'name' => 'MADRASAH ALIYAH',
            'type' => School::TYPE_MA,
            'slug' => 'madrasah-aliyah',
        ]);

        $this->schoolSmp = School::where('type', School::TYPE_SMP)->first() ?? School::create([
            'id' => (string) Str::uuid(),
            'name' => 'SMP UNGGULAN',
            'type' => School::TYPE_SMP,
            'slug' => 'smp-unggulan',
        ]);

        $this->schoolStaff = School::where('name', 'STAFF')->first() ?? School::create([
            'id' => (string) Str::uuid(),
            'name' => 'STAFF',
            'type' => '',
            'slug' => 'staff',
        ]);

        $this->posPondok = BillItem::firstOrCreate(['name' => 'PONDOK'], ['id' => (string) Str::uuid(), 'code' => 'PND']);
        $this->posMa = BillItem::firstOrCreate(['name' => 'MADRASAH ALIYAH'], ['id' => (string) Str::uuid(), 'code' => 'MA']);
        $this->posSmp = BillItem::firstOrCreate(['name' => 'SMP'], ['id' => (string) Str::uuid(), 'code' => 'SMP']);

        $this->multiUptAdmin = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Multi UPT Koordinator Test',
            'email' => 'multiupt_' . Str::random(6) . '@example.com',
            'phone' => '081299998888',
            'password' => bcrypt('password'),
            'avatar' => 'default.png',
            'role_id' => $role->id,
            'school_id' => null,
            'access_scope' => 'both',
            'is_active' => true,
        ]);
        $this->multiUptAdmin->assignRole($role);

        foreach ([$this->schoolPondok, $this->schoolMa, $this->schoolSmp] as $sch) {
            AdminSchool::create([
                'id' => (string) Str::uuid(),
                'admin_id' => $this->multiUptAdmin->id,
                'school_id' => $sch->id,
            ]);
        }
    }

    public function test_initial_load_shows_all_3_upt_bill_types_without_truncation_or_demo_leakage()
    {
        $response = $this->actingAs($this->multiUptAdmin, 'web')
            ->get(route('report-transaction.index'));

        $response->assertStatus(200);

        $schools = $response->viewData('schools');
        $schoolIds = $schools->pluck('id')->toArray();

        $this->assertContains($this->schoolPondok->id, $schoolIds);
        $this->assertContains($this->schoolMa->id, $schoolIds);
        $this->assertContains($this->schoolSmp->id, $schoolIds);
        $this->assertNotContains($this->schoolStaff->id, $schoolIds);

        $billTypes = $response->viewData('billTypes')->pluck('name')->toArray();

        // Must contain canonical bill types from ALL 3 UPTs (not truncated to Pondok only)
        $this->assertContains('SYAHRIAH PONDOK', $billTypes);
        $this->assertContains('SYAHRIAH MA', $billTypes);
        $this->assertContains('SYAHRIAH SMP', $billTypes);

        // Must NOT leak DEMO or KOPERASI PONDOK bill types
        $this->assertNotContains('NEW SPP', $billTypes);
        $this->assertNotContains('TOPUP SALDO', $billTypes);
    }

    public function test_get_filters_strictly_isolates_bill_types_per_selected_upt_with_zero_leakage()
    {
        // 1. Select PONDOK
        $resPondok = $this->actingAs($this->multiUptAdmin, 'web')
            ->getJson(route('report-transaction.get-filters', ['school_id' => $this->schoolPondok->id]));
        $resPondok->assertStatus(200);
        $pondokTypes = collect($resPondok->json('bill_types'))->pluck('name')->toArray();

        $this->assertContains('SYAHRIAH PONDOK', $pondokTypes);
        $this->assertContains('ZARKASI PONDOK', $pondokTypes);
        $this->assertNotContains('SYAHRIAH MA', $pondokTypes);
        $this->assertNotContains('SYAHRIAH SMP', $pondokTypes);
        $this->assertNotContains('NEW SPP', $pondokTypes);

        // 2. Select MADRASAH ALIYAH
        $resMa = $this->actingAs($this->multiUptAdmin, 'web')
            ->getJson(route('report-transaction.get-filters', ['school_id' => $this->schoolMa->id]));
        $resMa->assertStatus(200);
        $maTypes = collect($resMa->json('bill_types'))->pluck('name')->toArray();

        $this->assertContains('SYAHRIAH MA', $maTypes);
        $this->assertContains('ZARKASI MA', $maTypes);
        $this->assertContains('LKS SMT 1 - MA', $maTypes);
        $this->assertContains('INFAQ KENAIKAN KELAS - MA', $maTypes);
        $this->assertContains('PENDAFTARAN - MA', $maTypes);
        $this->assertNotContains('SYAHRIAH PONDOK', $maTypes);
        $this->assertNotContains('SYAHRIAH SMP', $maTypes);
        $this->assertNotContains('INFAQ KENAIKAN KELAS - SMP', $maTypes);
        $this->assertNotContains('PENDAFTARAN - SMP', $maTypes);

        // 3. Select SMP UNGGULAN
        $resSmp = $this->actingAs($this->multiUptAdmin, 'web')
            ->getJson(route('report-transaction.get-filters', ['school_id' => $this->schoolSmp->id]));
        $resSmp->assertStatus(200);
        $smpTypes = collect($resSmp->json('bill_types'))->pluck('name')->toArray();

        $this->assertContains('SYAHRIAH SMP', $smpTypes);
        $this->assertContains('ZARKASI SMP', $smpTypes);
        $this->assertContains('LKS SMT 1 - SMP', $smpTypes);
        $this->assertContains('INFAQ KENAIKAN KELAS - SMP', $smpTypes);
        $this->assertContains('PENDAFTARAN - SMP', $smpTypes);
        $this->assertNotContains('SYAHRIAH PONDOK', $smpTypes);
        $this->assertNotContains('SYAHRIAH MA', $smpTypes);
        $this->assertNotContains('INFAQ KENAIKAN KELAS - MA', $smpTypes);
        $this->assertNotContains('PENDAFTARAN - MA', $smpTypes);

        // 4. Select STAFF (unassigned & unmapped Pos Bayar) -> Fail-Closed (0 bill types)
        $resStaff = $this->actingAs($this->multiUptAdmin, 'web')
            ->getJson(route('report-transaction.get-filters', ['school_id' => $this->schoolStaff->id]));
        $resStaff->assertStatus(200);
        $this->assertEmpty($resStaff->json('bill_types'));

        // 5. Switch back to "Semua Lembaga" (school_id = "") -> All 3 UPTs, 0 DEMO/TOPUP SALDO
        $resAll = $this->actingAs($this->multiUptAdmin, 'web')
            ->getJson(route('report-transaction.get-filters', ['school_id' => '']));
        $resAll->assertStatus(200);
        $allTypes = collect($resAll->json('bill_types'))->pluck('name')->toArray();

        $this->assertContains('SYAHRIAH PONDOK', $allTypes);
        $this->assertContains('SYAHRIAH MA', $allTypes);
        $this->assertContains('SYAHRIAH SMP', $allTypes);
        $this->assertNotContains('NEW SPP', $allTypes);
        $this->assertNotContains('TOPUP SALDO', $allTypes);
    }

    public function test_transaction_query_and_totals_have_zero_cross_upt_collision_on_zarkasi_and_student_cross_upt_bills()
    {
        $ay = AcademicYear::first() ?? AcademicYear::create([
            'id' => (string) Str::uuid(),
            'name' => '2026/2027',
            'start_year' => 2026,
            'end_year' => 2027,
            'is_active' => 1,
        ]);

        // Raw name 'ZARKASI' exists in both PONDOK and MA
        $btZarkasiPondok = BillType::create([
            'id' => (string) Str::uuid(),
            'name' => 'ZARKASI',
            'academic_year_id' => $ay->id,
            'bill_item_id' => $this->posPondok->id,
            'amount' => 111000,
            'type' => BillType::TYPE_MONTHLY,
        ]);

        $btZarkasiMa = BillType::create([
            'id' => (string) Str::uuid(),
            'name' => 'ZARKASI',
            'academic_year_id' => $ay->id,
            'bill_item_id' => $this->posMa->id,
            'amount' => 222000,
            'type' => BillType::TYPE_MONTHLY,
        ]);

        $classPondok = Classroom::where('school_id', $this->schoolPondok->id)->first() ?? Classroom::create([
            'id' => (string) Str::uuid(),
            'name' => 'PONDOK TEST 1',
            'school_id' => $this->schoolPondok->id,
        ]);

        $classMa = Classroom::where('school_id', $this->schoolMa->id)->first() ?? Classroom::create([
            'id' => (string) Str::uuid(),
            'name' => '10 MA TEST 1',
            'school_id' => $this->schoolMa->id,
        ]);

        $studentPondok = Student::create([
            'id' => (string) Str::uuid(),
            'name' => 'Santri Pondok Test',
            'nis' => 'NISPND' . rand(1000, 9999),
            'classroom_id' => $classPondok->id,
            'status' => 'ACTIVE',
            'saldo' => 0,
        ]);

        $studentMa = Student::create([
            'id' => (string) Str::uuid(),
            'name' => 'Siswa MA Test',
            'nis' => 'NISMA' . rand(1000, 9999),
            'classroom_id' => $classMa->id,
            'status' => 'ACTIVE',
            'saldo' => 0,
        ]);

        $paymentMethod = PaymentMethod::first() ?? PaymentMethod::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tunai',
            'type' => PaymentMethod::TYPE_CASH,
        ]);

        // Bill 1: Pondok student pays ZARKASI (Pos = PONDOK) -> 111,000
        $billPondok = Bill::create([
            'id' => (string) Str::uuid(),
            'student_id' => $studentPondok->id,
            'classroom_id' => $classPondok->id,
            'academic_year_id' => $ay->id,
            'bill_type_id' => $btZarkasiPondok->id,
            'month' => 7,
            'year' => 2026,
            'amount' => 111000,
            'paid_amount' => 111000,
            'remaining_amount' => 0,
            'status' => Bill::STATUS_PAID,
        ]);

        $txPondok = Transaction::create([
            'id' => (string) Str::uuid(),
            'payment_method_id' => $paymentMethod->id,
            'student_id' => $studentPondok->id,
            'payment_code' => 'TX-PND-' . Str::random(6),
            'pay_amount' => 111000,
            'paid_at' => '2099-07-15 10:00:00',
            'status' => Transaction::STATUS_PAID,
            'type' => Transaction::TYPE_BILL,
            'admin_id' => $this->multiUptAdmin->id,
        ]);
        $txPondok->created_at = '2099-07-15 10:00:00';
        $txPondok->save();

        TransactionDetail::create([
            'id' => (string) Str::uuid(),
            'transaction_id' => $txPondok->id,
            'bill_id' => $billPondok->id,
            'amount' => 111000,
        ]);

        // Bill 2: MA student pays ZARKASI (Pos = MA) -> 222,000
        $billMa = Bill::create([
            'id' => (string) Str::uuid(),
            'student_id' => $studentMa->id,
            'classroom_id' => $classMa->id,
            'academic_year_id' => $ay->id,
            'bill_type_id' => $btZarkasiMa->id,
            'month' => 7,
            'year' => 2026,
            'amount' => 222000,
            'paid_amount' => 222000,
            'remaining_amount' => 0,
            'status' => Bill::STATUS_PAID,
        ]);

        $txMa = Transaction::create([
            'id' => (string) Str::uuid(),
            'payment_method_id' => $paymentMethod->id,
            'student_id' => $studentMa->id,
            'payment_code' => 'TX-MA-' . Str::random(6),
            'pay_amount' => 222000,
            'paid_at' => '2099-07-15 11:00:00',
            'status' => Transaction::STATUS_PAID,
            'type' => Transaction::TYPE_BILL,
            'admin_id' => $this->multiUptAdmin->id,
        ]);
        $txMa->created_at = '2099-07-15 11:00:00';
        $txMa->save();

        TransactionDetail::create([
            'id' => (string) Str::uuid(),
            'transaction_id' => $txMa->id,
            'bill_id' => $billMa->id,
            'amount' => 222000,
        ]);

        // Bill 3 (Cross-UPT Student Bill): Pondok student ALSO has an MA ZARKASI bill (333,000)
        $billCrossMaForPondokStudent = Bill::create([
            'id' => (string) Str::uuid(),
            'student_id' => $studentPondok->id,
            'classroom_id' => $classPondok->id,
            'academic_year_id' => $ay->id,
            'bill_type_id' => $btZarkasiMa->id,
            'month' => 8,
            'year' => 2026,
            'amount' => 333000,
            'paid_amount' => 333000,
            'remaining_amount' => 0,
            'status' => Bill::STATUS_PAID,
        ]);

        $txCrossMa = Transaction::create([
            'id' => (string) Str::uuid(),
            'payment_method_id' => $paymentMethod->id,
            'student_id' => $studentPondok->id,
            'payment_code' => 'TX-CROSS-MA-' . Str::random(6),
            'pay_amount' => 333000,
            'paid_at' => '2099-07-15 12:00:00',
            'status' => Transaction::STATUS_PAID,
            'type' => Transaction::TYPE_BILL,
            'admin_id' => $this->multiUptAdmin->id,
        ]);
        $txCrossMa->created_at = '2099-07-15 12:00:00';
        $txCrossMa->save();

        TransactionDetail::create([
            'id' => (string) Str::uuid(),
            'transaction_id' => $txCrossMa->id,
            'bill_id' => $billCrossMaForPondokStudent->id,
            'amount' => 333000,
        ]);

        // Case A: Filter by "Semua Lembaga" + "ZARKASI MA" -> Must ONLY include $txMa (222,000) + $txCrossMa (333,000) = 555,000, NEVER $txPondok (111,000)!
        $resTotalZarkasiMa = $this->actingAs($this->multiUptAdmin, 'web')
            ->getJson(route('report-transaction.index', [
                'data' => 'total',
                'start_date' => '2099-07-01',
                'end_date' => '2099-07-31',
                'bill_type_id' => 'ZARKASI MA',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $resTotalZarkasiMa->assertStatus(200);
        $this->assertEquals('555.000', $resTotalZarkasiMa->json('total_bill'));

        // Case B: Filter by Lembaga = PONDOK (All bill types) -> Must ONLY include $txPondok (111,000), NEVER $txCrossMa (333,000 MA bill of Pondok student)!
        $resTotalPondokOnly = $this->actingAs($this->multiUptAdmin, 'web')
            ->getJson(route('report-transaction.index', [
                'data' => 'total',
                'start_date' => '2099-07-01',
                'end_date' => '2099-07-31',
                'school_id' => $this->schoolPondok->id,
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $resTotalPondokOnly->assertStatus(200);
        $this->assertEquals('111.000', $resTotalPondokOnly->json('total_bill'));

        // Case C: Filter by Lembaga = PONDOK + bill_type_id = "ZARKASI MA" (mismatched UPT & BillType) -> Fail-Closed (0)
        $resMismatch = $this->actingAs($this->multiUptAdmin, 'web')
            ->getJson(route('report-transaction.index', [
                'data' => 'total',
                'start_date' => '2099-07-01',
                'end_date' => '2099-07-31',
                'school_id' => $this->schoolPondok->id,
                'bill_type_id' => 'ZARKASI MA',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $resMismatch->assertStatus(200);
        $this->assertEquals('0', $resMismatch->json('total_bill'));
    }
}
