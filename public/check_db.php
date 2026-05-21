<?php
// Autoload
require __DIR__ . '/../vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain');

echo "--- Checking Officers Database Records ---\n";
try {
    $officers = DB::table('officers')->get();
    foreach ($officers as $officer) {
        echo "ID: {$officer->id}\n";
        echo "Position: {$officer->position}\n";
        echo "Phone: {$officer->phone}\n";
        echo "Photo Field in DB: " . json_encode($officer->photo) . "\n";
        if ($officer->photo) {
            $publicPath = public_path($officer->photo);
            $storagePath = storage_path(str_replace('storage/', 'app/public/', $officer->photo));
            echo " - public_path: $publicPath (exists: " . (file_exists($publicPath) ? 'Yes' : 'No') . ")\n";
            echo " - storage_path: $storagePath (exists: " . (file_exists($storagePath) ? 'Yes' : 'No') . ")\n";
        }
        echo "---------------------------\n";
    }
} catch (\Exception $e) {
    echo "Error querying officers: " . $e->getMessage() . "\n";
}

echo "\n--- Checking Users with Avatars/Photos ---\n";
try {
    $users = DB::table('users')->whereNotNull('photo')->orWhereNotNull('avatar')->get();
    foreach ($users as $user) {
        echo "User ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Photo Field: " . json_encode($user->photo ?? null) . "\n";
        echo "Avatar Field: " . json_encode($user->avatar ?? null) . "\n";
        echo "---------------------------\n";
    }
} catch (\Exception $e) {
    echo "Error querying users: " . $e->getMessage() . "\n";
}
