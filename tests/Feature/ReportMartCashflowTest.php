<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminOutlet;
use App\Models\Classroom;
use App\Models\Item;
use App\Models\MenuNavigation;
use App\Models\Outlet;
use App\Models\PointOfSaleTransaction;
use App\Models\PointOfSaleTransactionDetail;
use App\Models\School;
use App\Models\Student;
use App\Models\SubMenuNavigation;
use App\Services\MartUserScopeService;
use App\Services\OutletContextService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportMartCashflowTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $superAdmin;
    protected Admin $koordinatorAdmin;
    protected Admin $outletStaffAdmin;
    protected Admin $unauthorizedAdmin;
    protected Outlet $koperasiOutlet;
    protected Outlet $unitOutlet;
    protected Student $studentA;
    protected Student $studentB;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\DB::statement('PRAGMA busy_timeout = 10000;');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Outlets
        $koperasiId = OutletContextService::getKoperasiOutletId();
        $this->koperasiOutlet = Outlet::find($koperasiId);
        $this->unitOutlet = Outlet::where('name', 'OUTLET')->first();

        // 2. Students
        $this->studentA = Student::first();
        $this->studentB = Student::skip(1)->first();

        // 3. Test Users
        // Super Admin
        $this->superAdmin = Admin::whereHas('roles', function($q) {
            $q->where('name', 'Super Admin');
        })->first();

        // Koordinator Pondok Mart (Maulana Syarif)
        $this->koordinatorAdmin = Admin::where('email', 'maulanasyarif@gmail.com')->first();

        // Outlet Staff (Gus Maulana Rifqi)
        $this->outletStaffAdmin = Admin::where('email', 'gusrifqi@email.com')->first();

        // Unauthorized Admin (Bendahara SMP has no mart/transaction reporting permission)
        $this->unauthorizedAdmin = Admin::where('email', 'bendaharasmp@gmail.com')->first();
    }

    /**
     * Helper to create a POS transaction.
     */
    protected function createPosTransaction(array $attributes = []): PointOfSaleTransaction
    {
        $item = Item::first() ?? Item::create([
            'id' => (string) Str::uuid(),
            'name' => 'Roti Coklat',
            'category_item_id' => (string) Str::uuid(),
            'buy_price' => 2000,
            'sell_price' => 3000,
            'profit' => 1000,
            'is_active' => true,
        ]);

        $customCreatedAt = $attributes['created_at'] ?? null;
        unset($attributes['created_at']);

        $trx = PointOfSaleTransaction::create(array_merge([
            'id' => (string) Str::uuid(),
            'student_id' => $this->studentA->id,
            'admin_id' => $this->superAdmin->id,
            'outlet_id' => $this->koperasiOutlet->id,
            'payment_code' => 'POS-TEST-' . Str::upper(Str::random(6)),
            'pay_amount' => 6000,
            'status' => PointOfSaleTransaction::STATUS_SUCCESS,
            'profit' => 2000,
            'type' => PointOfSaleTransaction::TYPE_SANTRI,
        ], $attributes));

        if ($customCreatedAt) {
            $trx->created_at = $customCreatedAt;
            $trx->save();
        }

        PointOfSaleTransactionDetail::create([
            'id' => (string) Str::uuid(),
            'point_of_sale_transaction_id' => $trx->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'price' => 3000,
            'total' => 6000,
        ]);

        return $trx;
    }

    /**
     * Test R1: Sub menu 'Arus Kas Mart' exists under 'Laporan' and is visible.
     */
    public function test_r1_submenu_arus_kas_mart_exists_in_database_and_sidebar()
    {
        $mainMenu = MenuNavigation::where('name', 'Laporan')->first();
        $this->assertNotNull($mainMenu, 'Menu Laporan must exist');

        $submenu = SubMenuNavigation::where('menu_navigation_id', $mainMenu->id)
            ->where('url', '/report-mart-cashflow')
            ->first();

        $this->assertNotNull($submenu, 'SubMenu Arus Kas Mart must exist in database under Laporan');
        $this->assertEquals('Arus Kas Mart', $submenu->name);
        $this->assertTrue((bool) $submenu->is_active);

        // Access route as Super Admin
        $response = $this->actingAs($this->superAdmin, 'web')->get(route('report-mart-cashflow.index'));
        $response->assertStatus(200);
        $response->assertSee('Arus Kas Mart');
        $response->assertSee('Riwayat Transaksi Belanja Siswa (Saldo)');

        // Access route as Koordinator Pondok Mart
        $responseKoord = $this->actingAs($this->koordinatorAdmin, 'web')->get(route('report-mart-cashflow.index'));
        $responseKoord->assertStatus(200);
        $responseKoord->assertSee('Arus Kas Mart');

        // Access route as Outlet User
        $responseOutlet = $this->actingAs($this->outletStaffAdmin, 'web')->get(route('report-mart-cashflow.index'));
        $responseOutlet->assertStatus(200);
        $responseOutlet->assertSee('Arus Kas Mart');

        // Access route as Unauthorized User
        $responseUnauth = $this->actingAs($this->unauthorizedAdmin, 'web')->get(route('report-mart-cashflow.index'));
        $responseUnauth->assertStatus(302); // Redirect back with error
    }

    /**
     * Test R2: Displays only Saldo student transactions, excludes Umum / non-student transactions.
     */
    public function test_r2_only_saldo_student_transactions_are_returned()
    {
        // 1. Transaction Saldo (Santri)
        $saldoTrx = $this->createPosTransaction([
            'payment_code' => 'POS-SALDO-TEST-001',
            'type' => PointOfSaleTransaction::TYPE_SANTRI,
            'student_id' => $this->studentA->id,
            'pay_amount' => 15000,
        ]);

        // 2. Transaction Umum (Tunai)
        $umumTrx = $this->createPosTransaction([
            'payment_code' => 'POS-UMUM-TEST-002',
            'type' => PointOfSaleTransaction::TYPE_UMUM,
            'student_id' => null,
            'pay_amount' => 20000,
        ]);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', ['data' => 'table']));

        $response->assertStatus(200);
        $json = $response->json();

        // Must see saldo transaction payment code
        $dataRows = collect($json['data'] ?? []);
        $codes = $dataRows->pluck('payment_code')->implode(' ');

        $this->assertStringContainsString('POS-SALDO-TEST-001', $codes);
        $this->assertStringNotContainsString('POS-UMUM-TEST-002', $codes);
    }

    /**
     * Test R3: Filter between Pondok Mart / Koperasi and Outlet.
     */
    public function test_r3_source_filter_isolates_koperasi_and_outlet()
    {
        // Transaction at Koperasi
        $kopTrx = $this->createPosTransaction([
            'payment_code' => 'POS-KOP-991',
            'outlet_id' => $this->koperasiOutlet->id,
            'pay_amount' => 12000,
        ]);

        // Transaction at Outlet
        $outletTrx = $this->createPosTransaction([
            'payment_code' => 'POS-OTL-992',
            'outlet_id' => $this->unitOutlet->id,
            'pay_amount' => 8000,
        ]);

        // Filter: Koperasi only
        $resKoperasi = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', ['data' => 'table', 'source' => 'koperasi']));
        $resKoperasi->assertStatus(200);
        $codesKoperasi = collect($resKoperasi->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-KOP-991', $codesKoperasi);
        $this->assertStringNotContainsString('POS-OTL-992', $codesKoperasi);

        // Filter: Outlet only
        $resOutlet = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', ['data' => 'table', 'source' => 'outlet']));
        $resOutlet->assertStatus(200);
        $codesOutlet = collect($resOutlet->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-OTL-992', $codesOutlet);
        $this->assertStringNotContainsString('POS-KOP-991', $codesOutlet);

        // Filter: Specific Outlet ID
        $resSpecific = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', ['data' => 'table', 'outlet_id' => $this->unitOutlet->id]));
        $resSpecific->assertStatus(200);
        $codesSpecific = collect($resSpecific->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-OTL-992', $codesSpecific);
        $this->assertStringNotContainsString('POS-KOP-991', $codesSpecific);
    }

    /**
     * Test R3: Date range filtering.
     */
    public function test_r3_date_range_filter_works_properly()
    {
        $oldDate = Carbon::now()->subDays(10)->format('Y-m-d H:i:s');
        $oldTrx = $this->createPosTransaction([
            'payment_code' => 'POS-OLD-111',
            'created_at' => $oldDate,
        ]);

        $todayTrx = $this->createPosTransaction([
            'payment_code' => 'POS-TODAY-222',
            'created_at' => now(),
        ]);

        // Filter range for today only
        $todayStr = Carbon::today()->format('Y-m-d');
        $resToday = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'start_date' => $todayStr,
                'end_date' => $todayStr,
            ]));
        $resToday->assertStatus(200);
        $codesToday = collect($resToday->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-TODAY-222', $codesToday);
        $this->assertStringNotContainsString('POS-OLD-111', $codesToday);
    }

    /**
     * Test R4: User scope isolation.
     * Outlet user (e.g. Gus Maulana Rifqi) can only see transactions from their assigned outlet,
     * even if attempting to query Koperasi or other outlets.
     */
    public function test_r4_outlet_user_is_strictly_scoped_to_assigned_outlet()
    {
        $kopTrx = $this->createPosTransaction([
            'payment_code' => 'POS-SEC-KOP-1',
            'outlet_id' => $this->koperasiOutlet->id,
            'pay_amount' => 50000,
        ]);

        $outletTrx = $this->createPosTransaction([
            'payment_code' => 'POS-SEC-OTL-2',
            'outlet_id' => $this->unitOutlet->id,
            'pay_amount' => 25000,
        ]);

        // Acting as Gus Maulana Rifqi (scoped to OUTLET)
        $res = $this->actingAs($this->outletStaffAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'source' => 'koperasi', // Attempt to access Koperasi data!
                'outlet_id' => $this->koperasiOutlet->id, // Attempt forged parameter
            ]));

        $res->assertStatus(200);
        $codes = collect($res->json()['data'] ?? [])->pluck('payment_code')->implode(' ');

        // Must NOT see Koperasi transaction
        $this->assertStringNotContainsString('POS-SEC-KOP-1', $codes);
        // Must see their assigned outlet transaction
        $this->assertStringContainsString('POS-SEC-OTL-2', $codes);

        // Check summary isolation as well
        $summaryRes = $this->actingAs($this->outletStaffAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', ['data' => 'summary']));
        $summaryRes->assertStatus(200);
        $summaryJson = $summaryRes->json();

        // Koperasi amount must be 0 for this outlet user
        $this->assertEquals('Rp 0', $summaryJson['koperasi_amount']);
        $this->assertEquals('0 Transaksi', $summaryJson['koperasi_count']);
    }

    /**
     * Test R4: Target users configuration and role/scope assignment.
     * - Koordinator Pondok Mart: Maulana Syarif, Rani, dan Ambar
     * - Outlet: Gus Maulana Rifqi
     */
    public function test_r4_target_users_configuration_and_role_assignment()
    {
        // Execute syncTargetUsers
        MartUserScopeService::syncTargetUsers();

        // 1. Maulana Syarif
        $syarif = Admin::where('email', 'maulanasyarif@gmail.com')->first();
        $this->assertNotNull($syarif);
        $this->assertTrue($syarif->hasRole('KOORDINATOR PONDOK MART') || $syarif->hasRole('Koordinator Pondok Mart'));
        $this->assertTrue($syarif->isKoordinatorPondokMart());
        $this->assertFalse(MartUserScopeService::isUserOutletRestricted($syarif));

        // 2. Rani (Maulani Izza Maharani)
        $rani = Admin::where('email', 'maulani@gmail.com')->first();
        $this->assertNotNull($rani);
        $this->assertTrue($rani->hasRole('KOORDINATOR PONDOK MART') || $rani->hasRole('Koordinator Pondok Mart'));
        $this->assertTrue($rani->isKoordinatorPondokMart());
        $this->assertFalse(MartUserScopeService::isUserOutletRestricted($rani));

        // 3. Ambar (Ambar Eka Wati)
        $ambar = Admin::where('email', 'ambarsari@gmail.com')->first();
        $this->assertNotNull($ambar);
        $this->assertTrue($ambar->hasRole('KOORDINATOR PONDOK MART') || $ambar->hasRole('Koordinator Pondok Mart'));
        $this->assertTrue($ambar->isKoordinatorPondokMart());
        $this->assertFalse(MartUserScopeService::isUserOutletRestricted($ambar));

        // 4. Gus Maulana Rifqi
        $rifqi = Admin::where('email', 'gusrifqi@email.com')->first();
        $this->assertNotNull($rifqi);
        $this->assertTrue($rifqi->hasRole('Kasir Karyawan Outlet') || $rifqi->hasRole('Outlet'));
        $this->assertFalse($rifqi->hasRole('KOORDINATOR CAHAYA MART'));
        $this->assertFalse($rifqi->hasRole('KOORDINATOR PONDOK MART'));
        $this->assertTrue(MartUserScopeService::isUserOutletRestricted($rifqi));
        $this->assertEquals([$this->unitOutlet->id], MartUserScopeService::getAllowedOutletIds($rifqi));
    }

    /**
     * Test Excel Export functionality.
     */
    public function test_excel_export_returns_download_response()
    {
        $this->createPosTransaction([
            'payment_code' => 'POS-EXPORT-001',
            'pay_amount' => 10000,
        ]);

        $todayStr = Carbon::today()->format('Y-m-d');
        $response = $this->actingAs($this->superAdmin, 'web')
            ->get(route('report-mart-cashflow.export', [
                'source' => 'all',
                'start_date' => $todayStr,
                'end_date' => $todayStr,
            ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition', ''));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition', ''));
    }

    /**
     * Test Edge Case: Empty results when no transactions match the criteria.
     */
    public function test_edge_case_empty_results_and_boundary_filters()
    {
        // 1. Table empty response
        $responseTable = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'start_date' => '2099-01-01',
                'end_date' => '2099-01-02',
            ]));

        $responseTable->assertStatus(200);
        $this->assertCount(0, $responseTable->json()['data'] ?? []);

        // 2. Summary empty response
        $responseSummary = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => '2099-01-01',
                'end_date' => '2099-01-02',
            ]));

        $responseSummary->assertStatus(200);
        $summary = $responseSummary->json();
        $this->assertEquals('Rp 0', $summary['total_amount']);
        $this->assertEquals('0 Transaksi', $summary['total_count']);
        $this->assertEquals('Rp 0', $summary['outlet_amount']);
        $this->assertEquals('0 Transaksi', $summary['outlet_count']);
        $this->assertEquals('Rp 0', $summary['koperasi_amount']);
        $this->assertEquals('0 Transaksi', $summary['koperasi_count']);

        // 3. Export empty result
        $responseExport = $this->actingAs($this->superAdmin, 'web')
            ->get(route('report-mart-cashflow.export', [
                'start_date' => '2099-01-01',
                'end_date' => '2099-01-02',
            ]));

        $responseExport->assertStatus(200);
        $this->assertStringContainsString('.xlsx', $responseExport->headers->get('content-disposition', ''));
    }

    /**
     * Test Adversarial: User with Mart/Outlet permission can access Arus Kas Mart.
     */
    public function test_r1_user_with_mart_report_permission_has_access()
    {
        $role = Role::firstOrCreate(['name' => 'Custom Mart Report Staff', 'guard_name' => 'web']);
        $role->givePermissionTo(['POS Outlet']);

        $transaksiAdmin = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Staff Mart Test',
            'email' => 'staffmart_' . Str::random(5) . '@test.com',
            'password' => bcrypt('password'),
            'avatar' => 'assets/media/avatars/default.png',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $transaksiAdmin->assignRole($role);

        $response = $this->actingAs($transaksiAdmin, 'web')->get(route('report-mart-cashflow.index'));
        $response->assertStatus(200);
        $response->assertSee('Arus Kas Mart');
    }

    /**
     * Test Adversarial: Summary response must include avg_amount and calculate correct average.
     */
    public function test_r2_summary_includes_avg_amount()
    {
        $this->createPosTransaction([
            'payment_code' => 'POS-AVG-01',
            'pay_amount' => 10000,
            'created_at' => now(),
        ]);
        $this->createPosTransaction([
            'payment_code' => 'POS-AVG-02',
            'pay_amount' => 20000,
            'created_at' => now(),
        ]);

        $todayStr = Carbon::today()->format('Y-m-d');
        $response = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $todayStr,
                'end_date' => $todayStr,
            ]));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('avg_amount', $json);
        $this->assertNotEquals('Rp 0', $json['avg_amount']);
    }

    /**
     * Test Adversarial: Single boundary date filtering (start_date only or end_date only).
     */
    public function test_r3_single_boundary_date_filtering()
    {
        $pastDate = Carbon::now()->subDays(5)->format('Y-m-d H:i:s');
        $this->createPosTransaction([
            'payment_code' => 'POS-PAST-001',
            'created_at' => $pastDate,
        ]);

        $futureDate = Carbon::now()->addDays(5)->format('Y-m-d H:i:s');
        $this->createPosTransaction([
            'payment_code' => 'POS-FUT-002',
            'created_at' => $futureDate,
        ]);

        // Filter start_date only
        $startStr = Carbon::now()->addDays(2)->format('Y-m-d');
        $resStart = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'start_date' => $startStr,
            ]));
        $resStart->assertStatus(200);
        $codesStart = collect($resStart->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-FUT-002', $codesStart);
        $this->assertStringNotContainsString('POS-PAST-001', $codesStart);

        // Filter end_date only
        $endStr = Carbon::now()->subDays(2)->format('Y-m-d');
        $resEnd = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'end_date' => $endStr,
            ]));
        $resEnd->assertStatus(200);
        $codesEnd = collect($resEnd->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-PAST-001', $codesEnd);
        $this->assertStringNotContainsString('POS-FUT-002', $codesEnd);
    }

    /**
     * Test Adversarial: User assigned to multiple outlets can switch between them and view combined data.
     */
    public function test_r4_restricted_user_with_multiple_outlets()
    {
        $outletA = $this->unitOutlet; // OUTLET
        $outletB = Outlet::where('id', '!=', $this->koperasiOutlet->id)
            ->where('id', '!=', $outletA->id)
            ->first();

        $outletRole = Role::firstOrCreate(['name' => 'Outlet', 'guard_name' => 'web']);

        // Create test user assigned to Outlet A and Outlet B
        $multiAdmin = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Multi Outlet Staff Test',
            'email' => 'multioutlet_' . Str::random(5) . '@test.com',
            'password' => bcrypt('password'),
            'avatar' => 'assets/media/avatars/default.png',
            'role_id' => $outletRole->id,
            'outlet_id' => $outletA->id,
            'is_active' => true,
        ]);
        $multiAdmin->assignRole('Outlet');

        AdminOutlet::create([
            'id' => (string) Str::uuid(),
            'admin_id' => $multiAdmin->id,
            'outlet_id' => $outletA->id,
        ]);
        AdminOutlet::create([
            'id' => (string) Str::uuid(),
            'admin_id' => $multiAdmin->id,
            'outlet_id' => $outletB->id,
        ]);

        $this->assertTrue(MartUserScopeService::isUserOutletRestricted($multiAdmin));
        $allowed = MartUserScopeService::getAllowedOutletIds($multiAdmin);
        $this->assertCount(2, $allowed);
        $this->assertContains($outletA->id, $allowed);
        $this->assertContains($outletB->id, $allowed);

        // Transactions at Outlet A and Outlet B
        $trxA = $this->createPosTransaction([
            'payment_code' => 'POS-MULT-A-01',
            'outlet_id' => $outletA->id,
        ]);
        $trxB = $this->createPosTransaction([
            'payment_code' => 'POS-MULT-B-02',
            'outlet_id' => $outletB->id,
        ]);
        $trxKop = $this->createPosTransaction([
            'payment_code' => 'POS-MULT-KOP-03',
            'outlet_id' => $this->koperasiOutlet->id,
        ]);

        // Combined query (outlet_id = 'all')
        $resAll = $this->actingAs($multiAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', ['data' => 'table', 'outlet_id' => 'all']));
        $resAll->assertStatus(200);
        $codesAll = collect($resAll->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-MULT-A-01', $codesAll);
        $this->assertStringContainsString('POS-MULT-B-02', $codesAll);
        $this->assertStringNotContainsString('POS-MULT-KOP-03', $codesAll);

        // Filter Outlet A only
        $resA = $this->actingAs($multiAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', ['data' => 'table', 'outlet_id' => $outletA->id]));
        $resA->assertStatus(200);
        $codesA = collect($resA->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-MULT-A-01', $codesA);
        $this->assertStringNotContainsString('POS-MULT-B-02', $codesA);

        // Verify HTML page includes 'Scope Khusus Outlet' badge and assigned outlet names without redundant dropdown
        $pageRes = $this->actingAs($multiAdmin, 'web')->get(route('report-mart-cashflow.index'));
        $pageRes->assertStatus(200);
        $pageRes->assertSee('Scope Khusus Outlet');
        $pageRes->assertSee($outletA->name);
        $pageRes->assertDontSee('Pilih Unit / Outlet');
    }

    /**
     * Test Adversarial: Staff user with 0 assigned outlets is still restricted and sees 0 rows, preventing cross-outlet leak.
     */
    public function test_r4_restricted_user_with_no_assigned_outlets_sees_nothing_preventing_leak()
    {
        $outletRole = Role::firstOrCreate(['name' => 'Outlet', 'guard_name' => 'web']);

        $unassignedAdmin = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Unassigned Staff Test',
            'email' => 'unassigned_' . Str::random(5) . '@test.com',
            'password' => bcrypt('password'),
            'avatar' => 'assets/media/avatars/default.png',
            'role_id' => $outletRole->id,
            'outlet_id' => null,
            'is_active' => true,
        ]);
        $unassignedAdmin->assignRole('Outlet');

        $this->assertTrue(MartUserScopeService::isUserOutletRestricted($unassignedAdmin));
        $this->assertEmpty(MartUserScopeService::getAllowedOutletIds($unassignedAdmin));

        $res = $this->actingAs($unassignedAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', ['data' => 'table']));
        $res->assertStatus(200);
        $this->assertCount(0, $res->json()['data'] ?? []);
    }
}

