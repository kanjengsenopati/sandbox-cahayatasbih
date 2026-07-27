<?php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// The submenu is named "Advanced Sync Saldo"
$submenu = App\Models\SubMenuNavigation::where('name', 'Advanced Sync Saldo')->first();
$permissionName = $submenu ? $submenu->permission : 'Manage Advanced Sync Saldo';

if (!$permissionName) {
    $permissionName = 'Manage Advanced Sync Saldo';
    if ($submenu) {
        $submenu->update(['permission' => $permissionName]);
    }
}

// 1. Create permission if not exists
$permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
echo "Permission verified: $permissionName\n";

// 2. Assign to Super Admin
$superAdminRole = Role::where('name', 'Super Admin')->orWhere('name', 'superadmin')->first();
if ($superAdminRole) {
    $superAdminRole->givePermissionTo($permission);
    echo "Assigned to role: " . $superAdminRole->name . "\n";
} else {
    echo "Role Super Admin not found!\n";
}

// 3. Assign to KOORDINATOR CAHAYA MART
$koordinatorRole = Role::where('name', 'KOORDINATOR CAHAYA MART')->first();
if ($koordinatorRole) {
    $koordinatorRole->givePermissionTo($permission);
    echo "Assigned to role: " . $koordinatorRole->name . "\n";
} else {
    echo "Role KOORDINATOR CAHAYA MART not found!\n";
}

// 4. Specifically verify the users (Siswanto, Arsito Ari) and the 3 users (Ambar Eka Wati, Maulani Izza Maharani, Maulana Syarif)
$users = App\Models\User::whereIn('name', [
    'Siswanto', 
    'Arsito Ari', 
    'AMBAR EKA WATI', 
    'MAULANI IZZA MAHARANI', 
    'MAULANA SYARIF'
])->get();

foreach ($users as $user) {
    echo "Checking user: " . $user->name . "\n";
    // Usually giving to role is enough, but we can also give directly if they don't have the role
    $user->givePermissionTo($permission);
    echo "  -> Permission assigned directly to user.\n";
}

app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
echo "Done.\n";
