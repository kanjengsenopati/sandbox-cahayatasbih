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
        $newBarcode = '99999888887777711';

        $response = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-barcode.update-barcode', $this->student1->id), [
                'barcode' => $newBarcode,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->student1->refresh();
        $this->assertEquals($newBarcode, $this->student1->barcode);
        $this->assertEquals($oldBarcode, $this->student1->previous_barcode);

        // Check history log
        $this->assertDatabaseHas('student_barcode_histories', [
            'student_id' => $this->student1->id,
            'old_barcode' => $oldBarcode,
            'new_barcode' => $newBarcode,
            'action_type' => 'manual_edit',
        ]);
    }

    public function test_rollback_barcode_reverts_to_previous_state()
    {
        $originalBarcode = $this->student1->barcode;
        $updatedBarcode = '88888777776666622';

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

        // Check rollback history
        $this->assertDatabaseHas('student_barcode_histories', [
            'student_id' => $this->student1->id,
            'new_barcode' => $originalBarcode,
            'action_type' => 'rollback',
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
        ]);

        $report = StudentCardReport::where('student_id', $this->student1->id)->first();
        $this->assertNotNull($report);

        // 2. Mark complete
        $completeResponse = $this->actingAs($this->admin, 'web')
            ->postJson(route('student-card-reports.complete', $report->id));

        $completeResponse->assertStatus(200);
        $completeResponse->assertJson(['success' => true]);

        $report->refresh();
        $this->assertEquals('completed', $report->status);
        $this->assertNotNull($report->processed_at);
    }
}
