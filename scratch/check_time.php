<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use App\Models\PointOfSaleTransaction;

echo "=== TIME AND DATE DIAGNOSTIC ===\n";
echo "PHP Default Timezone: " . date_default_timezone_get() . "\n";
echo "Laravel Config Timezone: " . config('app.timezone') . "\n";
echo "Current Time (PHP): " . date('Y-m-d H:i:s') . "\n";
echo "Current Time (Carbon): " . Carbon::now()->toDateTimeString() . "\n";
echo "Carbon::today(): " . Carbon::today()->toDateTimeString() . "\n";
echo "Carbon::now()->startOfWeek(): " . Carbon::now()->startOfWeek()->toDateTimeString() . "\n";
echo "Carbon::now()->endOfWeek(): " . Carbon::now()->endOfWeek()->toDateTimeString() . "\n";
echo "Carbon::now()->startOfMonth(): " . Carbon::now()->startOfMonth()->toDateTimeString() . "\n";
echo "Carbon::now()->endOfMonth(): " . Carbon::now()->endOfMonth()->toDateTimeString() . "\n";

// Let's run the queries
$todayCount = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
    ->whereDate('created_at', Carbon::today())
    ->count();

$weekCount = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
    ->count();

$monthCount = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
    ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
    ->count();

echo "\n=== TRANSACTION COUNTS IN DB ===\n";
echo "Today Count: " . $todayCount . "\n";
echo "This Week Count: " . $weekCount . "\n";
echo "This Month Count: " . $monthCount . "\n";

// Let's find the max created_at date in the database
$maxDate = PointOfSaleTransaction::max('created_at');
echo "Max created_at in database: " . $maxDate . "\n";

// Let's see if there are any transactions on the max date
if ($maxDate) {
    $maxDateCarbon = Carbon::parse($maxDate);
    $maxDateCount = PointOfSaleTransaction::whereDate('created_at', $maxDateCarbon->toDateString())->count();
    echo "Transactions on max date (" . $maxDateCarbon->toDateString() . "): " . $maxDateCount . "\n";
}
