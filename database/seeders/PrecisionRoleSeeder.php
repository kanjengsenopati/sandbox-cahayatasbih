<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Outlet;
use App\Models\AdminOutlet;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PrecisionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds for precise user roles & outlet scope assignments.
     */
    public function run(): void
    {
        // 1. Ensure core roles exist
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $nonKasirRole = Role::firstOrCreate(['name' => 'Karyawan Outlet ( Non Kasir )', 'guard_name' => 'web']);
        $kasirOutletRole = Role::firstOrCreate(['name' => 'Kasir Karyawan Outlet', 'guard_name' => 'web']);
        $kasirKoperasiRole = Role::firstOrCreate(['name' => 'Kasir Koperasi', 'guard_name' => 'web']);

        // 2. Ensure Outlets Exist (Borealis Eatery, Angkringan, Koperasi)
        $borealis = Outlet::firstOrCreate(
            ['code' => 'BE'],
            ['id' => (string) Str::uuid(), 'name' => 'Borealis Eatery', 'address' => 'Pondok', 'is_active' => true]
        );

        $angkringan = Outlet::firstOrCreate(
            ['code' => 'AK'],
            ['id' => (string) Str::uuid(), 'name' => 'Angkringan', 'address' => 'Pondok', 'is_active' => true]
        );

        $koperasi = Outlet::firstOrCreate(
            ['code' => 'KPR'],
            ['id' => (string) Str::uuid(), 'name' => 'Koperasi', 'address' => 'Pondok', 'is_active' => true]
        );

        // 3. Define Super Admins
        $superAdmins = [
            ['name' => 'SISWANTO', 'email' => 'siswanto@cahayatasbih.or.id', 'phone' => '081200000001'],
            ['name' => 'ARSITO ARI K', 'email' => 'arsito@cahayatasbih.or.id', 'phone' => '081200000002'],
            ['name' => 'MAULANA RIFQI', 'email' => 'maulana@cahayatasbih.or.id', 'phone' => '081200000003'],
        ];

        $superAdminIds = [];

        foreach ($superAdmins as $saData) {
            $saUser = Admin::where('name', 'like', '%' . $saData['name'] . '%')
                ->orWhere('email', $saData['email'])
                ->first();

            if (!$saUser) {
                $saUser = Admin::create([
                    'name' => $saData['name'],
                    'email' => $saData['email'],
                    'phone' => $saData['phone'],
                    'avatar' => 'assets/media/avatars/blank.png',
                    'access_scope' => 'all',
                    'role_id' => $superAdminRole->id,
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]);
            } else {
                $saUser->update([
                    'is_active' => true,
                    'role_id' => $superAdminRole->id,
                ]);
            }

            $saUser->syncRoles([$superAdminRole]);
            $superAdminIds[] = $saUser->id;
        }

        // Revoke Super Admin from anyone else
        $allSuperAdmins = Admin::role('Super Admin')->get();
        foreach ($allSuperAdmins as $saUser) {
            if (!in_array($saUser->id, $superAdminIds)) {
                $saUser->removeRole('Super Admin');
            }
        }

        // 4. Update specific user roles
        // INDAH -> Karyawan Outlet ( Non Kasir )
        $indah = Admin::where('name', 'like', '%INDAH%')->first();
        if ($indah) {
            $indah->update(['role_id' => $nonKasirRole->id]);
            $indah->syncRoles([$nonKasirRole]);
        }

        // KHUROTU AINI -> Kasir Karyawan Outlet
        $aini = Admin::where('name', 'like', '%KHUROTU AINI%')->first();
        if ($aini) {
            $aini->update(['role_id' => $kasirOutletRole->id]);
            $aini->syncRoles([$kasirOutletRole]);
        }

        // Ensure permissions for Kasir Karyawan Outlet role
        $itemPermissions = [
            'View Barang', 'Create Barang', 'Edit Barang', 'Delete Barang',
            'View Kategori Barang', 'Create Kategori Barang', 'Edit Kategori Barang', 'Delete Kategori Barang',
            'View Stock History', 'Create Stock History', 'Edit Stock History', 'Delete Stock History',
            'POS Outlet', 'Laporan', 'Manage Pos Kasir', 'Create Pos Kasir', 'Manage Barang'
        ];

        foreach ($itemPermissions as $pName) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $pName, 'guard_name' => 'web']);
        }

        $kasirOutletRole->givePermissionTo(\Spatie\Permission\Models\Permission::whereIn('name', $itemPermissions)->get());

        // 5. Sync Admin Outlets Scopes
        $outletUsers = Admin::whereNotIn('id', $superAdminIds)->get();

        foreach ($outletUsers as $u) {
            // Assign non-koperasi outlets (Borealis Eatery & Angkringan) to outlet users
            AdminOutlet::firstOrCreate([
                'admin_id' => $u->id,
                'outlet_id' => $borealis->id,
            ]);

            AdminOutlet::firstOrCreate([
                'admin_id' => $u->id,
                'outlet_id' => $angkringan->id,
            ]);
        }
    }
}
