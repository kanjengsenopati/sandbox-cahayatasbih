<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure permissions exist
        $permissions = [
            'Manage Santri',
            'Lapor Kartu Santri',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // Target roles for Koordinator
        $roleNames = [
            'Koordinator Cahaya Mart',
            'KOORDINATOR CAHAYA MART',
            'Koordinator Pondok Mart',
            'KOORDINATOR PONDOK MART',
        ];

        foreach ($roleNames as $rName) {
            $role = Role::firstOrCreate([
                'name' => $rName,
                'guard_name' => 'web'
            ]);
            $role->givePermissionTo(['Manage Santri', 'Lapor Kartu Santri']);
        }

        // Ensure Super Admin also has the permissions
        $superAdmin = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(['Manage Santri', 'Lapor Kartu Santri']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $roleNames = [
            'Koordinator Cahaya Mart',
            'KOORDINATOR CAHAYA MART',
            'Koordinator Pondok Mart',
            'KOORDINATOR PONDOK MART',
        ];

        foreach ($roleNames as $rName) {
            $role = Role::where('name', $rName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->revokePermissionTo(['Lapor Kartu Santri']);
            }
        }
    }
};
