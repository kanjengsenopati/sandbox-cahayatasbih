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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'Manage Kartu Santri',
            'Manage Kartu Ujian',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Otomatis berikan ke semua role saat ini agar tidak lockout
        $roles = Role::where('guard_name', 'web')->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'Manage Kartu Santri',
            'Manage Kartu Ujian',
        ];

        foreach ($permissions as $name) {
            Permission::where(['name' => $name, 'guard_name' => 'web'])->delete();
        }
    }
};
