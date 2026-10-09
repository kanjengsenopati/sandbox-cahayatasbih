<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Outlet;
use App\Models\AdminOutlet;
use App\Models\Item;
use App\Models\CategoryItem;
use App\Services\OutletContextService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class MultiOutletArchitectureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        OutletContextService::clearCache();
    }

    public function test_outlet_context_service_resolves_koperasi_dynamically()
    {
        $koperasi = Outlet::firstOrCreate(
            ['code' => 'KPR'],
            ['id' => (string) Str::uuid(), 'name' => 'Koperasi', 'is_active' => true, 'track_inventory' => false]
        );

        $resolvedId = OutletContextService::getKoperasiOutletId();
        $this->assertEquals($koperasi->id, $resolvedId);
    }

    public function test_is_super_admin_resolves_via_role_and_env_fallback()
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $adminWithRole = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Admin Role Test',
            'email' => 'role_test_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
            'avatar' => 'default.png',
            'role_id' => $superAdminRole->id,
            'is_active' => true,
        ]);
        $adminWithRole->assignRole($superAdminRole);

        $this->assertTrue($adminWithRole->isSuperAdmin());

        $regularAdmin = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Regular Admin Test',
            'email' => 'regular_test_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
            'avatar' => 'default.png',
            'role_id' => 99,
            'is_active' => true,
        ]);

        $this->assertFalse($regularAdmin->isSuperAdmin());
    }

    public function test_item_policy_guards_cross_outlet_modification()
    {
        Permission::firstOrCreate(['name' => 'Edit Barang', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'Delete Barang', 'guard_name' => 'web']);

        $outletA = Outlet::create([
            'id' => (string) Str::uuid(),
            'name' => 'Outlet Alpha',
            'code' => 'OA_' . Str::random(3),
            'is_active' => true,
            'track_inventory' => true,
        ]);

        $outletB = Outlet::create([
            'id' => (string) Str::uuid(),
            'name' => 'Outlet Beta',
            'code' => 'OB_' . Str::random(3),
            'is_active' => true,
            'track_inventory' => true,
        ]);

        $category = CategoryItem::firstOrCreate(
            ['name' => 'Snack'],
            ['id' => (string) Str::uuid(), 'code' => 'SNK']
        );

        $itemA = Item::create([
            'id' => (string) Str::uuid(),
            'outlet_id' => $outletA->id,
            'category_item_id' => $category->id,
            'name' => 'Keripik A',
            'code' => 'KRA_' . Str::random(4),
            'price' => 5000,
            'selling_price' => 7000,
            'profit' => 2000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $itemB = Item::create([
            'id' => (string) Str::uuid(),
            'outlet_id' => $outletB->id,
            'category_item_id' => $category->id,
            'name' => 'Keripik B',
            'code' => 'KRB_' . Str::random(4),
            'price' => 5000,
            'selling_price' => 7000,
            'profit' => 2000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $outletAdmin = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Staff Outlet A',
            'email' => 'staff_oa_' . Str::random(5) . '@example.com',
            'password' => bcrypt('password'),
            'avatar' => 'default.png',
            'role_id' => 99,
            'is_active' => true,
        ]);
        $outletAdmin->givePermissionTo(['Edit Barang', 'Delete Barang']);

        AdminOutlet::create([
            'id' => (string) Str::uuid(),
            'admin_id' => $outletAdmin->id,
            'outlet_id' => $outletA->id,
        ]);

        // Outlet Admin can edit Item A (in Outlet A)
        $this->assertTrue(Gate::forUser($outletAdmin)->allows('update', $itemA));

        // Outlet Admin CANNOT edit Item B (in Outlet B)
        $this->assertFalse(Gate::forUser($outletAdmin)->allows('update', $itemB));
    }

    public function test_outlet_track_inventory_attribute_persists()
    {
        $outlet = Outlet::create([
            'id' => (string) Str::uuid(),
            'name' => 'Food Court',
            'code' => 'FC_' . Str::random(3),
            'is_active' => true,
            'track_inventory' => false,
        ]);

        $this->assertFalse($outlet->fresh()->track_inventory);

        $outlet->update(['track_inventory' => true]);
        $this->assertTrue($outlet->fresh()->track_inventory);
    }
}
