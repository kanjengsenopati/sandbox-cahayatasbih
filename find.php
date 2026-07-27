<?php
echo "=== ROLES ===\n";
foreach(Spatie\Permission\Models\Role::all() as $r) {
    echo "Role: " . $r->name . "\n";
}
echo "=== USERS ===\n";
foreach(App\Models\User::all() as $u) {
    $n = strtolower($u->name);
    if(str_contains($n, 'ambar') || str_contains($n, 'maulani') || str_contains($n, 'maulana') || str_contains($n, 'siswanto') || str_contains($n, 'arsito')) {
        echo "User: " . $u->name . "\n";
    }
}
