<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserDuplicateTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test admin for auth with all necessary fields
        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone' => '081234567890',
            'is_active' => true,
            'avatar' => '',
            'role_id' => 1,
        ]);
    }

    /**
     * Test checkDuplicate endpoint returns false when no duplicate exists.
     */
    public function test_check_duplicate_returns_false_when_clean()
    {
        $response = $this->actingAs($this->admin)->post(route('user.check-duplicate'), [
            'name' => 'Unik Name',
            'phone' => '089999999999',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'duplicate' => false,
        ]);
    }

    /**
     * Test checkDuplicate endpoint detects existing phone number.
     */
    public function test_check_duplicate_detects_existing_phone()
    {
        // Create an existing user
        $existingUser = User::create([
            'name' => 'Wali Asli',
            'email' => 'wali_asli_' . uniqid() . '@example.com',
            'phone' => '081234567891',
            'password' => bcrypt('password'),
            'is_active' => true,
            'jamaah_status' => 'JAMAAH',
            'avatar' => '',
        ]);

        // Check duplicate with same phone
        $response = $this->actingAs($this->admin)->post(route('user.check-duplicate'), [
            'name' => 'Wali Asli Clone',
            'phone' => '081234567891',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'duplicate' => true,
            'user' => [
                'name' => 'Wali Asli',
                'phone' => '081234567891',
            ]
        ]);
    }

    /**
     * Test checkDuplicate endpoint ignores own user ID.
     */
    public function test_check_duplicate_ignores_own_id()
    {
        // Create an existing user
        $existingUser = User::create([
            'name' => 'Wali Asli',
            'email' => 'wali_asli_' . uniqid() . '@example.com',
            'phone' => '081234567892',
            'password' => bcrypt('password'),
            'is_active' => true,
            'jamaah_status' => 'JAMAAH',
            'avatar' => '',
        ]);

        // Check duplicate passing the same user's ID
        $response = $this->actingAs($this->admin)->post(route('user.check-duplicate'), [
            'id' => $existingUser->id,
            'name' => 'Wali Asli',
            'phone' => '081234567892',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'duplicate' => false,
        ]);
    }

    /**
     * Test UserImportData skips duplicate phones in Excel sheet and database.
     */
    public function test_import_skips_duplicates()
    {
        // 1. Create a user already in database
        User::create([
            'name' => 'Existing Database User',
            'email' => 'existing_db_' . uniqid() . '@example.com',
            'phone' => '081234567895',
            'password' => bcrypt('password'),
            'is_active' => true,
            'avatar' => '',
        ]);

        // 2. Prepare mock Excel rows
        $rows = collect([
            // Row 2 in Excel (index 0) - valid new user
            [
                'nama' => 'User Baru Valid',
                'email' => 'baru_valid_' . uniqid() . '@example.com',
                'password' => 'password123',
                'jenis_kelamin' => 'Laki-laki',
                'nomor_handphone' => '081234567896',
                'status' => 'Aktif',
                'status_jamaah' => 'Jamaah',
            ],
            // Row 3 in Excel (index 1) - duplicate with DB
            [
                'nama' => 'User Duplikat DB',
                'email' => 'dup_db_' . uniqid() . '@example.com',
                'password' => 'password123',
                'jenis_kelamin' => 'Perempuan',
                'nomor_handphone' => '081234567895', // duplicate with DB
                'status' => 'Aktif',
                'status_jamaah' => 'Non Jamaah',
            ],
            // Row 4 in Excel (index 2) - duplicate within sheet
            [
                'nama' => 'User Duplikat Sheet',
                'email' => 'dup_sheet_' . uniqid() . '@example.com',
                'password' => 'password123',
                'jenis_kelamin' => 'Laki-laki',
                'nomor_handphone' => '081234567896', // duplicate with row 2
                'status' => 'Aktif',
                'status_jamaah' => 'Jamaah',
            ]
        ]);

        $importer = new \App\Imports\UserImportData();
        $importer->collection($rows);

        // Check if correct data was skipped
        $this->assertEquals(1, $importer->successCount);
        $this->assertCount(2, $importer->skipped);

        // First skip should be the DB duplicate (Row 3)
        $this->assertEquals(3, $importer->skipped[0]['row']);
        $this->assertEquals('User Duplikat DB', $importer->skipped[0]['name']);
        $this->assertStringContainsString('database', $importer->skipped[0]['reason']);

        // Second skip should be the sheet duplicate (Row 4)
        $this->assertEquals(4, $importer->skipped[1]['row']);
        $this->assertEquals('User Duplikat Sheet', $importer->skipped[1]['name']);
        $this->assertStringContainsString('file Excel', $importer->skipped[1]['reason']);
    }
}
