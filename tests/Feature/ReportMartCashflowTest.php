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
    protected function createPosTransaction(array $attributes = [], ?Item $customItem = null): PointOfSaleTransaction
    {
        $item = $customItem ?? Item::first() ?? Item::create([
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
        // Verify scripts are actually rendered into HTML via @stack('js')
        $response->assertSee("$('#filter-daterange').daterangepicker", false);
        $response->assertSee("$('#table-mart-cashflow').DataTable", false);
        $response->assertSee("applyCustomDateRange", false);

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

        // Filter via preset period = today
        $resPeriodToday = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'period' => 'today',
            ]));
        $resPeriodToday->assertStatus(200);
        $codesPeriodToday = collect($resPeriodToday->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-TODAY-222', $codesPeriodToday);
        $this->assertStringNotContainsString('POS-OLD-111', $codesPeriodToday);

        // Filter via preset period = week
        $resPeriodWeek = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'period' => 'week',
            ]));
        $resPeriodWeek->assertStatus(200);
        $codesPeriodWeek = collect($resPeriodWeek->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-TODAY-222', $codesPeriodWeek);
        $this->assertStringNotContainsString('POS-OLD-111', $codesPeriodWeek);
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

    /**
     * Test R5: Transactions with outlet_id = NULL (e.g. POS-CHM-...) accurately resolve their Unit / Outlet
     * from the database item's outlet_id (KOPERASI vs OUTLET) across table badges, source filters, and summary cards.
     */
    public function test_r5_unit_outlet_column_and_filters_resolve_null_outlet_transactions_from_database_items()
    {
        $futureDate = '2098-10-11';
        $futureDateTime = '2098-10-11 15:51:07';

        $koperasiItem = Item::whereNull('outlet_id')
            ->orWhere('outlet_id', $this->koperasiOutlet->id)
            ->first();

        $outletItem = Item::where('outlet_id', $this->unitOutlet->id)->first();
        if (!$outletItem) {
            $outletItem = Item::create([
                'id' => (string) Str::uuid(),
                'outlet_id' => $this->unitOutlet->id,
                'category_item_id' => $koperasiItem->category_item_id,
                'name' => 'TAHU BAKSO TEST',
                'code' => 'TBT_' . Str::random(4),
                'price' => 6000,
                'selling_price' => 8000,
                'profit' => 2000,
                'stock' => 20,
                'is_active' => true,
            ]);
        }

        // 1. Transaction with outlet_id = NULL but item belongs to OUTLET (like POS-CHM-20261011155107-8ZLP)
        $this->createPosTransaction([
            'payment_code' => 'POS-CHM-OUTLET-NULL',
            'outlet_id' => null,
            'pay_amount' => 8000,
            'created_at' => $futureDateTime,
        ], $outletItem);

        // 2. Transaction with outlet_id = NULL and item belongs to KOPERASI
        $this->createPosTransaction([
            'payment_code' => 'POS-CHM-KOPERASI-NULL',
            'outlet_id' => null,
            'pay_amount' => 10000,
            'created_at' => $futureDateTime,
        ], $koperasiItem);

        // Verify Table Badges for Super Admin (source = all)
        $resAll = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'start_date' => $futureDate,
                'end_date' => $futureDate,
            ]));
        $resAll->assertStatus(200);
        $rowsAll = collect($resAll->json()['data'] ?? []);
        $this->assertCount(2, $rowsAll);

        $outletRow = $rowsAll->first(fn ($r) => str_contains($r['payment_code'], 'POS-CHM-OUTLET-NULL'));
        $koperasiRow = $rowsAll->first(fn ($r) => str_contains($r['payment_code'], 'POS-CHM-KOPERASI-NULL'));

        $this->assertNotNull($outletRow);
        $this->assertNotNull($koperasiRow);

        // Database unit names: OUTLET vs KOPERASI (Pondok Mart)
        $this->assertStringContainsString($this->unitOutlet->name, $outletRow['outlet']);
        $this->assertStringNotContainsString('Pondok Mart', $outletRow['outlet']);
        $this->assertStringContainsString($this->koperasiOutlet->name . ' (Pondok Mart)', $koperasiRow['outlet']);

        // Verify Filter source = outlet includes POS-CHM-OUTLET-NULL and excludes POS-CHM-KOPERASI-NULL
        $resOutlet = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'source' => 'outlet',
                'start_date' => $futureDate,
                'end_date' => $futureDate,
            ]));
        $codesOutlet = collect($resOutlet->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-CHM-OUTLET-NULL', $codesOutlet);
        $this->assertStringNotContainsString('POS-CHM-KOPERASI-NULL', $codesOutlet);

        // Verify Filter source = koperasi includes POS-CHM-KOPERASI-NULL and excludes POS-CHM-OUTLET-NULL
        $resKoperasi = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'source' => 'koperasi',
                'start_date' => $futureDate,
                'end_date' => $futureDate,
            ]));
        $codesKoperasi = collect($resKoperasi->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-CHM-KOPERASI-NULL', $codesKoperasi);
        $this->assertStringNotContainsString('POS-CHM-OUTLET-NULL', $codesKoperasi);

        // Verify Summary Cards include both null-outlet transactions in their respective unit totals
        $resSummary = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $futureDate,
                'end_date' => $futureDate,
            ]));
        $summary = $resSummary->json();
        $this->assertEquals('Rp 18.000', $summary['total_amount']);
        $this->assertEquals('2 Transaksi', $summary['total_count']);
        $this->assertEquals('Rp 8.000', $summary['outlet_amount']);
        $this->assertEquals('1 Transaksi', $summary['outlet_count']);
        $this->assertEquals('Rp 10.000', $summary['koperasi_amount']);
        $this->assertEquals('1 Transaksi', $summary['koperasi_count']);

        // Verify Restricted Outlet Staff (Gus Maulana Rifqi) sees POS-CHM-OUTLET-NULL and NOT POS-CHM-KOPERASI-NULL
        $resRestricted = $this->actingAs($this->outletStaffAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'table',
                'start_date' => $futureDate,
                'end_date' => $futureDate,
            ]));
        $codesRestricted = collect($resRestricted->json()['data'] ?? [])->pluck('payment_code')->implode(' ');
        $this->assertStringContainsString('POS-CHM-OUTLET-NULL', $codesRestricted);
        $this->assertStringNotContainsString('POS-CHM-KOPERASI-NULL', $codesRestricted);
    }

    /**
     * Test R6: Handover Tab UI, 3 summary cards (Total Transaksi Outlet, Dana Sudah Diserahkan, Dana Belum Diserahkan),
     * storeHandover (nominal, pemberi, penerima, bukti transfer, keterangan), handover_table DataTables, and destroyHandover.
     */
    public function test_r6_handover_tab_summary_store_table_and_destroy_workflow()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $testDate = '2097-11-05';
        $testDateTime = '2097-11-05 10:00:00';

        // 1. Verify HTML renders Top Tabs & Handover Form elements
        $pageRes = $this->actingAs($this->superAdmin, 'web')->get(route('report-mart-cashflow.index', ['tab' => 'handover']));
        $pageRes->assertStatus(200);
        $pageRes->assertSee('Serah Terima Dana (Handover Outlet)');
        $pageRes->assertSee('Total Transaksi Outlet');
        $pageRes->assertSee('Dana Sudah Diserahkan');
        $pageRes->assertSee('Dana Belum Diserahkan');
        $pageRes->assertSee('Formulir Serah Terima Dana');
        $pageRes->assertSee('Nama Pemberi (Koperasi)');
        $pageRes->assertSee('Nama Penerima (Outlet)');
        $pageRes->assertSee('Upload Bukti Transfer / Serah Terima');

        // 2. Create 2 Outlet student Saldo transactions on $testDate totaling Rp 150.000
        $this->createPosTransaction([
            'payment_code' => 'POS-HDV-OTL-01',
            'outlet_id' => $this->unitOutlet->id,
            'pay_amount' => 90000,
            'created_at' => $testDateTime,
        ]);
        $this->createPosTransaction([
            'payment_code' => 'POS-HDV-OTL-02',
            'outlet_id' => $this->unitOutlet->id,
            'pay_amount' => 60000,
            'created_at' => $testDateTime,
        ]);

        // 3. Verify Summary before handover: Total Outlet = Rp 150.000, Diserahkan = Rp 0, Belum Diserahkan = Rp 150.000
        $summaryBefore = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        $this->assertEquals('Rp 150.000', $summaryBefore['handover_total_amount']);
        $this->assertEquals('2 Transaksi', $summaryBefore['handover_total_count']);
        $this->assertEquals('Rp 0', $summaryBefore['handover_submitted_amount']);
        $this->assertEquals('Rp 150.000', $summaryBefore['handover_pending_amount']);
        $this->assertEquals(150000, $summaryBefore['handover_pending_raw']);

        // 4. Submit Handover of Rp 100.000 from Koordinator (Maulana Syarif) to Outlet Staff (Gus Maulana Rifqi) with evidence & notes
        $fakeEvidence = \Illuminate\Http\UploadedFile::fake()->create('bukti-transfer-mandiri.jpg', 120, 'image/jpeg');

        $storeRes = $this->actingAs($this->superAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.store'), [
                'handover_date' => $testDate,
                'amount' => '100.000',
                'sender_id' => $this->koordinatorAdmin->id,
                'recipient_id' => $this->outletStaffAdmin->id,
                'recipient_outlet_id' => $this->unitOutlet->id,
                'evidence' => $fakeEvidence,
                'notes' => 'Transfer pencairan saldo Outlet tahap 1 via Bank Mandiri',
            ]);

        $storeRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $handoverId = $storeRes->json('data.id');
        $this->assertNotNull($handoverId);

        // Verify database records in outlet_handovers and cash_flows
        $this->assertDatabaseHas('outlet_handovers', [
            'id' => $handoverId,
            'handover_type' => \App\Models\OutletHandover::TYPE_KOPERASI_TO_OUTLET,
            'outlet_id' => $this->koperasiOutlet->id,
            'recipient_outlet_id' => $this->unitOutlet->id,
            'cashier_id' => $this->koordinatorAdmin->id,
            'created_by' => $this->koordinatorAdmin->id,
            'recipient_id' => $this->outletStaffAdmin->id,
            'amount' => 100000,
            'status' => \App\Models\OutletHandover::STATUS_PENDING,
            'notes' => 'Transfer pencairan saldo Outlet tahap 1 via Bank Mandiri',
        ]);

        $this->assertDatabaseHas('cash_flows', [
            'sender_id' => $this->koordinatorAdmin->id,
            'receiver_id' => $this->outletStaffAdmin->id,
            'outlet_id' => $this->unitOutlet->id,
            'amount' => 100000,
            'status' => \App\Models\CashFlow::STATUS_PENDING,
        ]);

        // Prior to confirmation: submitted is still Rp 0, pending is Rp 150.000
        $summaryPending = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        $this->assertEquals('Rp 0', $summaryPending['handover_submitted_amount']);
        $this->assertEquals('Rp 150.000', $summaryPending['handover_pending_amount']);

        // Recipient confirms handover
        $confirmRes = $this->actingAs($this->outletStaffAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.confirm', $handoverId));
        $confirmRes->assertStatus(200)->assertJsonPath('success', true);

        $this->assertDatabaseHas('outlet_handovers', [
            'id' => $handoverId,
            'status' => \App\Models\OutletHandover::STATUS_APPROVED,
            'verified_by' => $this->outletStaffAdmin->id,
        ]);

        // 5. Verify Summary after Rp 100.000 handover is approved: Diserahkan = Rp 100.000, Belum Diserahkan = Rp 50.000
        $summaryAfter = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        $this->assertEquals('Rp 150.000', $summaryAfter['handover_total_amount']);
        $this->assertEquals('Rp 100.000', $summaryAfter['handover_submitted_amount']);
        $this->assertEquals('1x Penyerahan', $summaryAfter['handover_submitted_count']);
        $this->assertEquals('Rp 50.000', $summaryAfter['handover_pending_amount']);
        $this->assertEquals(50000, $summaryAfter['handover_pending_raw']);

        // 6. Verify Handover DataTable returns the row with Pemberi, Penerima, Nominal, Notes, and Bukti Transfer
        $tableRes = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'handover_table',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200);

        $rows = collect($tableRes->json('data') ?? []);
        $this->assertCount(1, $rows);
        $firstRow = $rows->first();
        $this->assertStringContainsString($this->koordinatorAdmin->name, $firstRow['sender']);
        $this->assertStringContainsString($this->outletStaffAdmin->name, $firstRow['recipient']);
        $this->assertStringContainsString('Rp 100.000', $firstRow['amount']);
        $this->assertStringContainsString('Transfer pencairan saldo Outlet tahap 1 via Bank Mandiri', $firstRow['notes']);
        $this->assertStringContainsString('Lihat Bukti', $firstRow['evidence']);

        // 7. Delete Handover and verify metrics return to Rp 0 submitted / Rp 150.000 pending
        $delRes = $this->actingAs($this->superAdmin, 'web')
            ->deleteJson(route('report-mart-cashflow.handover.destroy', $handoverId));
        $delRes->assertStatus(200)->assertJsonPath('success', true);

        $this->assertSoftDeleted('outlet_handovers', ['id' => $handoverId]);

        $summaryRestored = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        $this->assertEquals('Rp 0', $summaryRestored['handover_submitted_amount']);
        $this->assertEquals('Rp 150.000', $summaryRestored['handover_pending_amount']);
    }

    /**
     * Test R7: Specific Pemberi (Maulana Syarif, Rani) & Penerima (Gus Rifqi) options,
     * exclusion of dummy 'Test' & 'oke' data, and Audit Log on OutletHandover.
     */
    public function test_r7_handover_koperasi_pemberi_and_outlet_penerima_filter_and_dummy_cleanup()
    {
        // 1. Verify view data contains only Maulana Syarif & Rani for Pemberi, and Gus Rifqi for Penerima
        $response = $this->actingAs($this->superAdmin, 'web')
            ->get(route('report-mart-cashflow.index', ['tab' => 'handover']));

        $response->assertStatus(200);

        $koperasiAdmins = $response->viewData('koperasiAdmins');
        $outletAdmins = $response->viewData('outletAdmins');

        $this->assertNotNull($koperasiAdmins);
        $this->assertNotNull($outletAdmins);

        $pemberiEmails = $koperasiAdmins->pluck('email')->toArray();
        $this->assertContains('maulanasyarif@gmail.com', $pemberiEmails);
        $this->assertContains('maulani@gmail.com', $pemberiEmails);
        $this->assertNotContains('bendaharasmp@gmail.com', $pemberiEmails);
        $this->assertNotContains('superadmin@gmail.com', $pemberiEmails);

        $penerimaEmails = $outletAdmins->pluck('email')->toArray();
        $this->assertContains('gusrifqi@email.com', $penerimaEmails);
        $this->assertNotContains('maulanasyarif@gmail.com', $penerimaEmails);

        // 2. Dummy handovers ('Test' / 'oke') are filtered out from summary and table
        $testDate = '2096-10-10';
        $dummyHandover = \App\Models\OutletHandover::create([
            'handover_type' => \App\Models\OutletHandover::TYPE_KOPERASI_TO_OUTLET,
            'outlet_id' => $this->koperasiOutlet->id,
            'recipient_outlet_id' => $this->unitOutlet->id,
            'recipient_id' => $this->outletStaffAdmin->id,
            'recipient_name' => $this->outletStaffAdmin->name,
            'cashier_id' => $this->superAdmin->id,
            'created_by' => $this->superAdmin->id,
            'amount' => 546000,
            'system_amount' => 546000,
            'discrepancy' => 0,
            'status' => \App\Models\OutletHandover::STATUS_APPROVED,
            'handover_date' => $testDate,
            'notes' => 'oke',
        ]);

        $summaryRes = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        // Must NOT count dummy handover in submitted amount
        $this->assertEquals('Rp 0', $summaryRes['handover_submitted_amount']);

        $tableRes = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'handover_table',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200);

        $rows = collect($tableRes->json('data') ?? []);
        $this->assertCount(0, $rows);

        // 3. Verify Activity Log integration on OutletHandover model
        $validHandover = \App\Models\OutletHandover::create([
            'handover_type' => \App\Models\OutletHandover::TYPE_KOPERASI_TO_OUTLET,
            'outlet_id' => $this->koperasiOutlet->id,
            'recipient_outlet_id' => $this->unitOutlet->id,
            'recipient_id' => $this->outletStaffAdmin->id,
            'recipient_name' => $this->outletStaffAdmin->name,
            'cashier_id' => $this->koordinatorAdmin->id,
            'created_by' => $this->koordinatorAdmin->id,
            'amount' => 250000,
            'system_amount' => 250000,
            'discrepancy' => 0,
            'status' => \App\Models\OutletHandover::STATUS_APPROVED,
            'handover_date' => $testDate,
            'notes' => 'Serah terima resmi audit',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => \App\Models\OutletHandover::class,
            'subject_id' => $validHandover->id,
            'event' => 'created',
        ]);
    }

    /**
     * Test R8: Handover Business Workflow:
     * - Created with status PENDING and CashFlow PENDING.
     * - Only recipient (or Super Admin) can confirm -> status becomes APPROVED, CashFlow APPROVED.
     * - Other users cannot confirm (403).
     * - Rejection / Cancellation with required reason -> status becomes REJECTED, CashFlow REJECTED.
     * - Diserahkan amount stays at Rp 0 and pending amount rolls back to original value.
     */
    public function test_r8_handover_pending_confirmation_workflow_and_rollback()
    {
        $testDate = '2097-01-15';

        // 1. Create POS sales at unit outlet: Rp 200.000
        $this->createPosTransaction([
            'payment_code' => 'POS-R8-001',
            'outlet_id' => $this->unitOutlet->id,
            'student_id' => $this->studentA->id,
            'admin_id' => $this->outletStaffAdmin->id,
            'pay_amount' => 200000,
            'created_at' => Carbon::parse($testDate . ' 10:00:00'),
        ]);

        // Initial summary: Total = 200.000, Submitted = 0, Pending = 200.000
        $summaryInit = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        $this->assertEquals('Rp 200.000', $summaryInit['handover_total_amount']);
        $this->assertEquals('Rp 0', $summaryInit['handover_submitted_amount']);
        $this->assertEquals('Rp 200.000', $summaryInit['handover_pending_amount']);

        // 2. Koperasi officer (Maulana Syarif) submits Handover of Rp 120.000
        $storeRes = $this->actingAs($this->koordinatorAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.store'), [
                'handover_date' => $testDate,
                'amount' => '120.000',
                'sender_id' => $this->koordinatorAdmin->id,
                'recipient_id' => $this->outletStaffAdmin->id,
                'recipient_outlet_id' => $this->unitOutlet->id,
                'notes' => 'Pencairan sebagian saldo outlet ke Gus Rifqi',
            ]);

        $storeRes->assertStatus(200)->assertJsonPath('success', true);
        $handoverId = $storeRes->json('data.id');

        // Check DB: PENDING status
        $this->assertDatabaseHas('outlet_handovers', [
            'id' => $handoverId,
            'status' => \App\Models\OutletHandover::STATUS_PENDING,
            'amount' => 120000,
        ]);
        $this->assertDatabaseHas('cash_flows', [
            'amount' => 120000,
            'status' => \App\Models\CashFlow::STATUS_PENDING,
        ]);

        // While pending: submitted amount is still Rp 0, pending amount is still Rp 200.000!
        $summaryPending = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        $this->assertEquals('Rp 0', $summaryPending['handover_submitted_amount']);
        $this->assertEquals('Rp 200.000', $summaryPending['handover_pending_amount']);

        // 3. Unauthorized user (sender or random staff who is NOT recipient and not Super Admin) tries to confirm -> 403
        $unauthorizedConfirm = $this->actingAs($this->koordinatorAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.confirm', $handoverId));
        $unauthorizedConfirm->assertStatus(403);

        // 4. Designated recipient confirms -> Success 200
        $confirmRes = $this->actingAs($this->outletStaffAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.confirm', $handoverId));
        $confirmRes->assertStatus(200)->assertJsonPath('success', true);

        // Verify status is now APPROVED and verified_by is set
        $this->assertDatabaseHas('outlet_handovers', [
            'id' => $handoverId,
            'status' => \App\Models\OutletHandover::STATUS_APPROVED,
            'verified_by' => $this->outletStaffAdmin->id,
        ]);
        $this->assertDatabaseHas('cash_flows', [
            'amount' => 120000,
            'status' => \App\Models\CashFlow::STATUS_APPROVED,
        ]);

        // Summary is now updated: submitted = 120.000, pending = 80.000
        $summaryConfirmed = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        $this->assertEquals('Rp 120.000', $summaryConfirmed['handover_submitted_amount']);
        $this->assertEquals('Rp 80.000', $summaryConfirmed['handover_pending_amount']);

        // Cannot confirm again -> 422
        $duplicateConfirm = $this->actingAs($this->outletStaffAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.confirm', $handoverId));
        $duplicateConfirm->assertStatus(422);

        // 5. Test Reject / Cancel (Rollback) flow on a second handover
        $storeRes2 = $this->actingAs($this->koordinatorAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.store'), [
                'handover_date' => $testDate,
                'amount' => '80.000',
                'sender_id' => $this->koordinatorAdmin->id,
                'recipient_id' => $this->outletStaffAdmin->id,
                'recipient_outlet_id' => $this->unitOutlet->id,
                'notes' => 'Pencairan sisa dana tahap 2',
            ]);

        $storeRes2->assertStatus(200);
        $handoverId2 = $storeRes2->json('data.id');

        // Reject without reason fails validation -> 422
        $rejectNoReason = $this->actingAs($this->outletStaffAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.reject', $handoverId2), [
                'reason' => '',
            ]);
        $rejectNoReason->assertStatus(422);

        // Reject with valid reason -> 200
        $rejectRes = $this->actingAs($this->outletStaffAdmin, 'web')
            ->postJson(route('report-mart-cashflow.handover.reject', $handoverId2), [
                'reason' => 'Nominal transfer bank belum masuk ke rekening outlet',
            ]);
        $rejectRes->assertStatus(200)->assertJsonPath('success', true);

        // Verify status becomes REJECTED and CashFlow is REJECTED
        $this->assertDatabaseHas('outlet_handovers', [
            'id' => $handoverId2,
            'status' => \App\Models\OutletHandover::STATUS_REJECTED,
        ]);
        $this->assertDatabaseHas('cash_flows', [
            'amount' => 80000,
            'status' => \App\Models\CashFlow::STATUS_REJECTED,
        ]);

        // Summary metrics: rejected handover is rolled back, so remaining pending is still Rp 80.000!
        $summaryRollback = $this->actingAs($this->superAdmin, 'web')
            ->getJson(route('report-mart-cashflow.index', [
                'data' => 'summary',
                'start_date' => $testDate,
                'end_date' => $testDate,
            ]))->assertStatus(200)->json();

        $this->assertEquals('Rp 120.000', $summaryRollback['handover_submitted_amount']);
        $this->assertEquals('Rp 80.000', $summaryRollback['handover_pending_amount']);
    }
}

