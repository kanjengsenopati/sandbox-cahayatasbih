<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Bill;
use Illuminate\Support\Facades\Auth;

$user = User::where('email', 'non_jamaah@example.com')->first();
if (!$user) {
    echo "User non_jamaah@example.com not found.\n";
    exit(1);
}

// Log in as the user
Auth::guard('wali')->login($user);

// Resolve active student
$student = Student::where('user_id', $user->id)->first();
session(['active_student_id' => $student->id]);

echo "Logged in as User: {$user->name} (ID: {$user->id})\n";
echo "Active Student: {$student->name} (ID: {$student->id})\n";

// Call DashboardController@index
$controller = new \App\Http\Controllers\Api\Wali\DashboardController();
$response = $controller->index();

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content:\n";
$data = json_decode($response->getContent(), true);
print_r($data);
