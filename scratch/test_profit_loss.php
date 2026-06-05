<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Http\Controllers\Admin\ProfitLossReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Get a Super Admin user
$superAdmin = Admin::whereNull('outlet_id')->first();
if (!$superAdmin) {
    // Fallback to any admin
    $superAdmin = Admin::first();
}

if (!$superAdmin) {
    echo "No admins found in database!\n";
    exit(1);
}

// Log in the user
Auth::login($superAdmin);
$superAdmin->givePermissionTo('Manage Laporan Rugi Laba');
echo "Logged in as Admin: {$superAdmin->name} (Outlet ID: " . ($superAdmin->outlet_id ?? 'None') . ")\n";

// Instantiate the request
$request = Request::create('/report-profit-loss', 'GET', [
    'start_date' => date('Y-m-d', strtotime('-30 days')),
    'end_date' => date('Y-m-d'),
]);

// Call controller
$controller = new ProfitLossReportController();
try {
    $response = $controller->index($request);
    $viewData = $response->getData();
    
    echo "\n=== calculation results ===\n";
    echo "Total Revenues: Rp " . number_format($viewData['totalRevenues'], 0, ',', '.') . "\n";
    echo "  - POS Sales: Rp " . number_format($viewData['posSalesTotal'], 0, ',', '.') . "\n";
    echo "  - Cash Incomes: Rp " . number_format($viewData['cashIncomesTotal'], 0, ',', '.') . "\n";
    echo "Total Cost of Goods Sold (HPP): Rp " . number_format($viewData['totalHpp'], 0, ',', '.') . "\n";
    echo "Gross Profit: Rp " . number_format($viewData['grossProfit'], 0, ',', '.') . "\n";
    echo "Total Expenses: Rp " . number_format($viewData['totalExpenses'], 0, ',', '.') . "\n";
    echo "Net Profit: Rp " . number_format($viewData['netProfit'], 0, ',', '.') . "\n";
    echo "============================\n";
    
    // Revoke the permission to clean up database state
    $superAdmin->revokePermissionTo('Manage Laporan Rugi Laba');
    
    echo "Test passed successfully without exceptions!\n";
} catch (\Exception $e) {
    echo "Test failed with exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
