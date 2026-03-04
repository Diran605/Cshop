<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SalesReceiptsBatchPrintController;
use App\Http\Controllers\SalesReceiptPrintController;
use App\Http\Controllers\StockInReceiptPrintController;
use App\Http\Controllers\StockInReceiptsBatchPrintController;
use App\Livewire\ActivityLogsIndex;
use App\Livewire\ProductsIndex;
use App\Livewire\ReportsIndex;
use App\Livewire\ReportsProfitIndex;
use App\Livewire\ReportsStockIndex;
use App\Livewire\ReportsExpensesIndex;
use App\Livewire\ReportsExpiryIndex;
use App\Livewire\SalesIndex;
use App\Livewire\ExpensesIndex;
use App\Livewire\StockMovementsIndex;
use App\Livewire\StockInIndex;
use App\Livewire\UsersIndex;
use App\Livewire\Setup\BranchesIndex;
use App\Livewire\Setup\BulkTypesIndex;
use App\Livewire\Setup\BulkUnitsIndex;
use App\Livewire\Setup\CategoriesIndex;
use App\Livewire\Setup\RolesIndex;
use App\Livewire\Setup\UnitTypesIndex;
use App\Livewire\BranchDashboard;
use App\Livewire\NotificationsIndex;
use App\Livewire\Setup\UserRolesIndex;
use App\Support\Alerts\AlertGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user) {
        AlertGenerator::generateExpiryAlertsForUser($user);
    }

    $isSuperAdmin = (bool) ($user?->role === 'super_admin');

    // Branch users get the new BranchDashboard Livewire component
    if (! $isSuperAdmin) {
        return view('dashboard-branch');
    }

    // Super admin keeps the original dashboard
    $from = Carbon::today()->startOfMonth();
    $to = Carbon::now();

    $branchId = (int) ($user?->branch_id ?? 0);

    $salesTotal = 0.0;
    $inventoryValue = 0.0;
    $lowStockValue = 0.0;
    $lowStockCount = 0;
    $expiringCount = 0;
    $inventoryByCategory = collect();
    $topBranchesBySales = collect();

    if (! $isSuperAdmin) {
        $salesTotal = (float) (DB::table('sales_receipts')
            ->where('branch_id', $branchId)
            ->whereNull('voided_at')
            ->whereBetween('sold_at', [$from, $to])
            ->sum('grand_total') ?? 0);

        $inventoryValue = (float) (DB::table('product_stocks')
            ->where('branch_id', $branchId)
            ->sum(DB::raw('COALESCE(current_stock, 0) * COALESCE(cost_price, 0)')) ?? 0);

        $lowStockCount = (int) DB::table('product_stocks')
            ->where('branch_id', $branchId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('current_stock', '>', 0)
            ->count();

        $expiringCount = (int) DB::table('stock_in_items')
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->where('stock_in_receipts.branch_id', $branchId)
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->whereNotNull('stock_in_items.expiry_date')
            ->where('stock_in_items.expiry_date', '<=', Carbon::today()->addDays(7))
            ->count();

        $inventoryByCategory = DB::table('product_stocks')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('product_stocks.branch_id', $branchId)
            ->groupBy(DB::raw('COALESCE(categories.name, "Uncategorized")'))
            ->orderBy(DB::raw('COALESCE(categories.name, "Uncategorized")'))
            ->get([
                DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'),
                DB::raw('SUM(COALESCE(product_stocks.current_stock, 0) * COALESCE(product_stocks.cost_price, 0)) as inventory_value'),
            ]);
    } else {
        $salesTotal = (float) (DB::table('sales_receipts')
            ->whereNull('voided_at')
            ->whereBetween('sold_at', [$from, $to])
            ->sum('grand_total') ?? 0);

        $inventoryValue = (float) (DB::table('product_stocks')
            ->sum(DB::raw('COALESCE(current_stock, 0) * COALESCE(cost_price, 0)')) ?? 0);

        $lowStockCount = (int) DB::table('product_stocks')
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('current_stock', '>', 0)
            ->count();

        $expiringCount = (int) DB::table('stock_in_items')
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->whereNotNull('stock_in_items.expiry_date')
            ->where('stock_in_items.expiry_date', '<=', Carbon::today()->addDays(7))
            ->count();

        $topBranchesBySales = DB::table('sales_receipts')
            ->join('branches', 'branches.id', '=', 'sales_receipts.branch_id')
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$from, $to])
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc(DB::raw('SUM(sales_receipts.grand_total)'))
            ->limit(5)
            ->get([
                'branches.id as branch_id',
                'branches.name as branch_name',
                DB::raw('SUM(sales_receipts.grand_total) as sales_total'),
            ]);
    }

    return view('dashboard', [
        'isSuperAdmin' => $isSuperAdmin,
        'monthFrom' => $from,
        'monthTo' => $to,
        'salesTotal' => $salesTotal,
        'inventoryValue' => $inventoryValue,
        'lowStockValue' => $lowStockValue,
        'lowStockCount' => $lowStockCount,
        'expiringCount' => $expiringCount,
        'inventoryByCategory' => $inventoryByCategory,
        'topBranchesBySales' => $topBranchesBySales,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('legacy_role:super_admin')->group(function () {
        Route::get('/setup/branches', BranchesIndex::class)->name('setup.branches');

        Route::get('/setup/roles', RolesIndex::class)->name('setup.roles');
        Route::get('/setup/user-roles', UserRolesIndex::class)->name('setup.user_roles');

        Route::get('/users', UsersIndex::class)->name('users.index');
        Route::get('/users/create', [RegisteredUserController::class, 'create'])->name('users.create');
        Route::post('/users', [RegisteredUserController::class, 'store'])->name('users.store');
    });

    Route::get('/setup/categories', CategoriesIndex::class)->name('setup.categories');
    Route::get('/setup/unit-types', UnitTypesIndex::class)->name('setup.unit_types');
    Route::get('/setup/bulk-units', BulkUnitsIndex::class)->name('setup.bulk_units');
    Route::get('/setup/bulk-types', BulkTypesIndex::class)->name('setup.bulk_types');

    Route::get('/products/{mode?}', ProductsIndex::class)
        ->where('mode', 'add|manage|expired')
        ->name('products.index');
    Route::get('/products/download-template', [ProductsIndex::class, 'downloadTemplate'])->name('products.download-template');

    Route::get('/stock-in/{mode?}', StockInIndex::class)
        ->where('mode', 'add|manage')
        ->name('stock_in.index');
    Route::get('/stock-in/download-template', [StockInIndex::class, 'downloadTemplate'])->name('stock_in.download-template');

    Route::get('/sales/add', SalesIndex::class)
        ->defaults('mode', 'add')
        ->name('sales.add');

    Route::get('/sales/manage', SalesIndex::class)
        ->defaults('mode', 'manage')
        ->name('sales.manage');

    Route::get('/sales/{mode?}', SalesIndex::class)
        ->where('mode', 'add|manage')
        ->name('sales.index');

    Route::get('/notifications', NotificationsIndex::class)->name('notifications.index');
    Route::get('/sales/download-template', [SalesIndex::class, 'downloadTemplate'])->name('sales.download-template');
    Route::get('/sales/print', SalesReceiptsBatchPrintController::class)->name('sales.print_batch');
    Route::get('/sales/{sale}/print', SalesReceiptPrintController::class)->name('sales.print');

    Route::get('/expenses/{mode?}', ExpensesIndex::class)
        ->where('mode', 'add|manage')
        ->name('expenses.index');

    Route::get('/stock-in/print', StockInReceiptsBatchPrintController::class)->name('stock_in.print_batch');
    Route::get('/stock-in/{receipt}/print', StockInReceiptPrintController::class)->name('stock_in.print');
    Route::get('/reports', ReportsIndex::class)->name('reports.index');
    Route::get('/reports/profit', ReportsProfitIndex::class)->name('reports.profit');
    Route::get('/reports/stock', ReportsStockIndex::class)->name('reports.stock');
    Route::get('/reports/expenses', ReportsExpensesIndex::class)->name('reports.expenses');
    Route::get('/reports/expiry', ReportsExpiryIndex::class)->name('reports.expiry');
    Route::get('/stock-movements', StockMovementsIndex::class)->name('stock_movements.index');
    Route::get('/activity-logs', ActivityLogsIndex::class)->name('activity_logs.index');
});

require __DIR__.'/auth.php';
