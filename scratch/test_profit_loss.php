<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\Outlet;
use App\Models\CashFlow;
use App\Models\CashFlowCategory;
use App\Http\Controllers\Admin\ProfitLossReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Get a Super Admin user
$superAdmin = Admin::whereNull('outlet_id')->first();
if (!$superAdmin) {
    $superAdmin = Admin::first();
}

if (!$superAdmin) {
    echo "No admins found in database!\n";
    exit(1);
}

// Log in the user and assign permission
Auth::login($superAdmin);
$superAdmin->givePermissionTo('Manage Laporan Rugi Laba');
echo "Logged in as Admin: {$superAdmin->name} (Outlet ID: " . ($superAdmin->outlet_id ?? 'None') . ")\n";

$outlet = Outlet::first();
if (!$outlet) {
    echo "No outlets found in database!\n";
    exit(1);
}
echo "Using Outlet for test: {$outlet->name} (ID: {$outlet->id})\n";

// Instantiate the request for initial state
$request = Request::create('/report-profit-loss', 'GET', [
    'start_date' => date('Y-m-d', strtotime('-30 days')),
    'end_date' => date('Y-m-d'),
    'outlet_id' => $outlet->id,
]);

$controller = new ProfitLossReportController();

try {
    // 1. Initial State
    $responseInit = $controller->index($request);
    $viewDataInit = $responseInit->getData();
    $initialExpenses = $viewDataInit['totalExpenses'];
    $initialNetProfit = $viewDataInit['netProfit'];
    echo "Initial Expenses: Rp " . number_format($initialExpenses, 0, ',', '.') . "\n";
    echo "Initial Net Profit: Rp " . number_format($initialNetProfit, 0, ',', '.') . "\n";

    // 2. Fetch the "Lainnya" category
    $categoryLainnya = CashFlowCategory::where('name', 'Lainnya')->first();
    if (!$categoryLainnya) {
        // Run index once to seed categories
        $controller->index($request);
        $categoryLainnya = CashFlowCategory::where('name', 'Lainnya')->first();
    }

    if (!$categoryLainnya) {
        throw new \Exception("Category 'Lainnya' could not be found or seeded.");
    }

    // 3. Store new operational expense of Rp 1.500.000 with custom category "Air Mineral"
    $storeRequest = Request::create('/report-profit-loss/store-expense', 'POST', [
        'outlet_id' => $outlet->id,
        'cash_flow_category_id' => $categoryLainnya->id,
        'custom_category' => 'Air Mineral',
        'amount' => '1.500.000',
        'date' => date('Y-m-d'),
        'description' => 'Membeli air mineral galon',
    ]);

    echo "Recording new operational expense...\n";
    $storeResponse = $controller->storeExpense($storeRequest);

    // Verify it created a CashFlow record
    $newExpense = CashFlow::where('type', CashFlow::TYPE_EXPENSE)
        ->where('description', 'like', '[Kustom: Air Mineral]%')
        ->latest()
        ->first();

    if (!$newExpense) {
        throw new \Exception("Failed to store CashFlow record in database.");
    }
    echo "Stored CashFlow transaction successfully. ID: {$newExpense->id}\n";

    // 4. State after creation
    $responseAfter = $controller->index($request);
    $viewDataAfter = $responseAfter->getData();
    $afterExpenses = $viewDataAfter['totalExpenses'];
    $afterNetProfit = $viewDataAfter['netProfit'];

    echo "Expenses After Creation: Rp " . number_format($afterExpenses, 0, ',', '.') . "\n";
    echo "Net Profit After Creation: Rp " . number_format($afterNetProfit, 0, ',', '.') . "\n";

    // Assert calculations
    if ($afterExpenses != $initialExpenses + 1500000) {
        throw new \Exception("Assert failed: Expenses did not increase by 1,500,000.");
    }
    if ($afterNetProfit != $initialNetProfit - 1500000) {
        throw new \Exception("Assert failed: Net Profit did not decrease by 1,500,000.");
    }
    echo "Calculations assertions passed!\n";

    // Verify custom category is parsed in breakdown list
    $foundCustomInBreakdown = false;
    foreach ($viewDataAfter['cashExpensesBreakdownFormatted'] as $item) {
        if ($item->category_name === 'Air Mineral' && $item->total == 1500000) {
            $foundCustomInBreakdown = true;
            break;
        }
    }
    if (!$foundCustomInBreakdown) {
        throw new \Exception("Assert failed: Custom category 'Air Mineral' not found in report breakdown.");
    }
    echo "Breakdown list custom category assertion passed!\n";

    // Verify parsed categories list
    $foundInList = false;
    foreach ($viewDataAfter['expensesList'] as $exp) {
        if ($exp->id === $newExpense->id && $exp->display_category_name === 'Air Mineral') {
            $foundInList = true;
            break;
        }
    }
    if (!$foundInList) {
        throw new \Exception("Assert failed: Custom category 'Air Mineral' not parsed in list view.");
    }
    echo "List view custom category assertion passed!\n";

    // 5. Delete the operational expense
    echo "Deleting the expense...\n";
    $controller->destroyExpense($newExpense->id);

    // 6. State after deletion
    $responseFinal = $controller->index($request);
    $viewDataFinal = $responseFinal->getData();
    $finalExpenses = $viewDataFinal['totalExpenses'];
    $finalNetProfit = $viewDataFinal['netProfit'];

    echo "Expenses After Deletion: Rp " . number_format($finalExpenses, 0, ',', '.') . "\n";
    echo "Net Profit After Deletion: Rp " . number_format($finalNetProfit, 0, ',', '.') . "\n";

    if ($finalExpenses != $initialExpenses || $finalNetProfit != $initialNetProfit) {
        throw new \Exception("Assert failed: State did not revert back after deletion.");
    }
    echo "Deletion and reversion assertions passed!\n";

    // Cleanup permissions
    $superAdmin->revokePermissionTo('Manage Laporan Rugi Laba');
    echo "\n=== INTEGRATION TEST PASSED SUCCESSFULLY ===\n";

} catch (\Exception $e) {
    echo "\n=== INTEGRATION TEST FAILED ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    
    // Attempt cleanup if transaction exists
    if (isset($newExpense) && $newExpense) {
        $newExpense->forceDelete();
    }
    if (isset($superAdmin) && $superAdmin) {
        $superAdmin->revokePermissionTo('Manage Laporan Rugi Laba');
    }
    exit(1);
}
