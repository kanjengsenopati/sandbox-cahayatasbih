<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\School;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\StudentBarcodeHistory;
use App\Models\StudentCardReport;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StudentBarcodeAndCardReportTest extends TestCase
{
    protected $admin;
    protected $student1;
    protected $student2;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles & permissions exist
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Manage Santri', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Lapor Kartu Santri', 'guard_name' => 'web']);

        $this->admin = Admin::where('email', 'like', '%superadmin%')->first();
        if (!$this->admin) {
            $this->admin = Admin::firstOrCreate(
                ['email' => 'testadmin@cahayatasbih.or.id'],
                [
                    'name' => 'Test Admin',
                    'phone' => '081234567890',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                ]
            );
        }
        $this->admin->assignRole('Super Admin');

        // Create or find dummy school & classroom
        $school = School::first() ?? School::create(['name' => 'Test School']);
        $classroom = Classroom::first() ?? Classroom::create(['name' => 'Class 1A', 'school_id' => $school->id]);

        $this->student1 = Student::create([
            'name' => 'Santri Test One',
            'nis' => 'TEST001' . rand(100, 999),
            'classroom_id' => $classroom->id,
            'status' => 'ACTIVE',
            'saldo' => 50000,
        ]);

        $this->student2 = Student::create([
            'name' => 'Santri Test Two',
            'nis' => 'TEST002' . rand(100, 999),
            'classroom_id' => $classroom->id,
            'status' => 'ACTIVE',
            'saldo' => 50000,
        ]);
    }

    protected function tearDown(): void
    {
        $testStudentIds = Student::withTrashed()
            ->whereIn('name', ['Santri Test One', 'Santri Test Two'])
            ->pluck('id');

        if ($testStudentIds->isNotEmpty()) {
            StudentCardReport::whereIn('student_id', $testStudentIds)->delete();
            StudentBarcodeHistory::whereIn('student_id', $testStudentIds)->delete();
            Student::withTrashed()->whereIn('id', $testStudentIds)->forceDelete();
        }

        parent::tearDown();
    }

    public function test_barcode_generation_is_unique()
    {
        $barcode1 = Student::generateUniqueBarcode();
        $barcode2 = Student::generateUniqueBarcode();

        $this->assertNotEmpty($barcode1);
        $this->assertNotEmpty($barcode2);
        $this->assertNotEquals($barcode1, $barcode2);
        $this->assertEquals(17, strlen($barcode1));
    }

    public function test_update_barcode_fails_on_duplicate()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-barcode.update-barcode', $this->student1->id), [
                'barcode' => $this->student2->barcode,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['barcode']);
    }

    public function test_update_barcode_success_and_saves_previous_state()
    {
        $oldBarcode = $this->student1->barcode;
        $newBarcode = Student::generateUniqueBarcode();

        $response = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-barcode.update-barcode', $this->student1->id), [
                'barcode' => $newBarcode,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->student1->refresh();
        $this->assertEquals($newBarcode, $this->student1->barcode);
        $this->assertEquals($oldBarcode, $this->student1->previous_barcode);

        // Check history log including admin_id (UUID string)
        $this->assertDatabaseHas('student_barcode_histories', [
            'student_id' => $this->student1->id,
            'old_barcode' => $oldBarcode,
            'new_barcode' => $newBarcode,
            'action_type' => 'manual_edit',
            'admin_id' => (string) $this->admin->id,
        ]);
    }

    public function test_rollback_barcode_reverts_to_previous_state()
    {
        $originalBarcode = $this->student1->barcode;
        $updatedBarcode = Student::generateUniqueBarcode();

        // 1. Update barcode first
        $this->actingAs($this->admin, 'web')
            ->postJson(route('student-barcode.update-barcode', $this->student1->id), [
                'barcode' => $updatedBarcode,
            ]);

        $this->student1->refresh();
        $this->assertEquals($updatedBarcode, $this->student1->barcode);
        $this->assertEquals($originalBarcode, $this->student1->previous_barcode);

        // 2. Perform Rollback
        $response = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-barcode.rollback-barcode', $this->student1->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->student1->refresh();
        $this->assertEquals($originalBarcode, $this->student1->barcode);

        // Check rollback history including admin_id (UUID string)
        $this->assertDatabaseHas('student_barcode_histories', [
            'student_id' => $this->student1->id,
            'new_barcode' => $originalBarcode,
            'action_type' => 'rollback',
            'admin_id' => (string) $this->admin->id,
        ]);
    }

    public function test_lapor_kartu_bulk_store_and_complete()
    {
        // 1. Store bulk report
        $response = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-card-reports.store'), [
                'student_ids' => [$this->student1->id, $this->student2->id],
                'issue_type' => 'rusak',
                'notes' => 'Patah di bagian pojok',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'count' => 2]);

        $this->assertDatabaseHas('student_card_reports', [
            'student_id' => $this->student1->id,
            'issue_type' => 'rusak',
            'status' => 'pending',
            'reported_by' => (string) $this->admin->id,
        ]);

        $report = StudentCardReport::where('student_id', $this->student1->id)->first();
        $this->assertNotNull($report);
        $this->assertEquals((string) $this->admin->id, $report->reported_by);

        // 2. Mark complete
        $completeResponse = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-card-reports.complete', $report->id));

        $completeResponse->assertStatus(200);
        $completeResponse->assertJson(['success' => true]);

        $report->refresh();
        $this->assertEquals('completed', $report->status);
        $this->assertEquals((string) $this->admin->id, $report->processed_by);
        $this->assertNotNull($report->processed_at);
    }

    public function test_barcode_column_rendering_by_issue_type_and_hd_png_download()
    {
        // Report 1: Kartu Rusak -> should show barcode image & download PNG link
        StudentCardReport::create([
            'student_id' => $this->student1->id,
            'reported_by' => (string) $this->admin->id,
            'issue_type' => StudentCardReport::ISSUE_RUSAK,
            'status' => StudentCardReport::STATUS_PENDING,
        ]);

        // Report 2: Tidak Bisa Transaksi -> should show inline edit without download
        StudentCardReport::create([
            'student_id' => $this->student2->id,
            'reported_by' => (string) $this->admin->id,
            'issue_type' => StudentCardReport::ISSUE_TIDAK_BISA_TRANSAKSI,
            'status' => StudentCardReport::STATUS_PENDING,
        ]);

        $dtResponse = $this->actingAs($this->admin, 'web')
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('student-card-reports.index', ['status' => 'pending']));

        $dtResponse->assertStatus(200);
        $rows = collect($dtResponse->json('data'));

        $rusakRow = $rows->first(fn ($r) => str_contains($r['student_info'] ?? '', $this->student1->nis));
        $this->assertNotNull($rusakRow);
        $this->assertStringContainsString('data:image/png;base64,', $rusakRow['barcode']);
        $this->assertStringContainsString('Download PNG (HD)', $rusakRow['barcode']);

        $unreadableRow = $rows->first(fn ($r) => str_contains($r['student_info'] ?? '', $this->student2->nis));
        $this->assertNotNull($unreadableRow);
        $this->assertStringContainsString('inline-barcode-wrapper', $unreadableRow['barcode']);
        $this->assertStringContainsString('inline-barcode-input', $unreadableRow['barcode']);
        $this->assertStringNotContainsString('Download PNG (HD)', $unreadableRow['barcode']);

        // Verify HD PNG Download resolution
        $pngResponse = $this->actingAs($this->admin, 'web')
            ->get(route('student-barcode.download-png', $this->student1->id));

        $pngResponse->assertStatus(200);
        $pngResponse->assertHeader('Content-Type', 'image/png');
        $imageSize = getimagesizefromstring($pngResponse->getContent());
        $this->assertNotFalse($imageSize);
        $this->assertGreaterThanOrEqual(1000, $imageSize[0]); // HD width > 1000px
        $this->assertEquals(220, $imageSize[1]); // HD height = 220px
    }

    public function test_auto_lock_prevents_duplicate_pending_card_report()
    {
        // 1. Report student1 for the first time -> should succeed
        $firstResponse = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-card-reports.store'), [
                'student_ids' => [$this->student1->id],
                'issue_type' => 'rusak',
                'notes' => 'Laporan pertama',
            ]);

        $firstResponse->assertStatus(200);
        $firstResponse->assertJson(['success' => true, 'count' => 1, 'locked_count' => 0]);

        // 2. Attempt to report student1 again while still pending -> should be Auto-Locked (422)
        $duplicateResponse = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-card-reports.store'), [
                'student_ids' => [$this->student1->id],
                'issue_type' => 'hilang',
                'notes' => 'Laporan ganda',
            ]);

        $duplicateResponse->assertStatus(422);
        $duplicateResponse->assertJson([
            'success' => false,
            'locked' => true,
            'count' => 0,
            'locked_count' => 1,
        ]);

        // Ensure only 1 pending report exists for student1
        $this->assertEquals(
            1,
            StudentCardReport::where('student_id', $this->student1->id)->where('status', 'pending')->count()
        );

        // 3. Bulk report both student1 (locked) and student2 (unlocked) -> only student2 should be saved
        $mixedResponse = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-card-reports.store'), [
                'student_ids' => [$this->student1->id, $this->student2->id],
                'issue_type' => 'tidak_bisa_transaksi',
                'notes' => 'Laporan campuran',
            ]);

        $mixedResponse->assertStatus(200);
        $mixedResponse->assertJson([
            'success' => true,
            'count' => 1,
            'locked_count' => 1,
        ]);

        // 4. Complete student1's pending report -> unlocks student1 for future reports
        $report1 = StudentCardReport::where('student_id', $this->student1->id)->where('status', 'pending')->first();
        $this->actingAs($this->admin, 'web')
            ->postJson(route('student-card-reports.complete', $report1->id))
            ->assertStatus(200);

        // 5. Report student1 again after follow-up completed -> should succeed
        $unlockedResponse = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-card-reports.store'), [
                'student_ids' => [$this->student1->id],
                'issue_type' => 'hilang',
                'notes' => 'Laporan baru setelah kartu lama selesai',
            ]);

        $unlockedResponse->assertStatus(200);
        $unlockedResponse->assertJson(['success' => true, 'count' => 1, 'locked_count' => 0]);
    }
}
