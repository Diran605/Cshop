<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SalesReceiptsBatchPrintController;
use App\Http\Controllers\SalesReceiptPrintController;
use App\Http\Controllers\StockInReceiptPrintController;
use App\Http\Controllers\StockInReceiptsBatchPrintController;
use App\Livewire\ProductsIndex;
use App\Livewire\ReportsIndex;
use App\Livewire\SalesIndex;
use App\Livewire\StockMovementsIndex;
use App\Livewire\StockInIndex;
use App\Livewire\UsersIndex;
use App\Livewire\Setup\BranchesIndex;
use App\Livewire\Setup\BulkTypesIndex;
use App\Livewire\Setup\BulkUnitsIndex;
use App\Livewire\Setup\CategoriesIndex;
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
    $isSuperAdmin = (bool) ($user?->role === 'super_admin');

    $from = Carbon::today()->startOfMonth();
    $to = Carbon::now();

    $branchId = (int) ($user?->branch_id ?? 0);

    $salesTotal = 0.0;
    $inventoryValue = 0.0;
    $lowStockValue = 0.0;
    $inventoryByCategory = collect();
    $topBranchesBySales = collect();

    if (! $isSuperAdmin) {
        $salesTotal = (float) (DB::table('sales_receipts')
            ->where('branch_id', $branchId)
            ->whereBetween('sold_at', [$from, $to])
            ->sum('grand_total') ?? 0);

        $inventoryValue = (float) (DB::table('product_stocks')
            ->where('branch_id', $branchId)
            ->sum(DB::raw('COALESCE(current_stock, 0) * COALESCE(cost_price, 0)')) ?? 0);

        $lowStockValue = (float) (DB::table('product_stocks')
            ->where('branch_id', $branchId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->sum(DB::raw('COALESCE(current_stock, 0) * COALESCE(cost_price, 0)')) ?? 0);

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
            ->whereBetween('sold_at', [$from, $to])
            ->sum('grand_total') ?? 0);

        $inventoryValue = (float) (DB::table('product_stocks')
            ->sum(DB::raw('COALESCE(current_stock, 0) * COALESCE(cost_price, 0)')) ?? 0);

        $lowStockValue = (float) (DB::table('product_stocks')
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->sum(DB::raw('COALESCE(current_stock, 0) * COALESCE(cost_price, 0)')) ?? 0);

        $topBranchesBySales = DB::table('sales_receipts')
            ->join('branches', 'branches.id', '=', 'sales_receipts.branch_id')
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
        'inventoryByCategory' => $inventoryByCategory,
        'topBranchesBySales' => $topBranchesBySales,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/setup/branches', BranchesIndex::class)->name('setup.branches');

        Route::get('/users', UsersIndex::class)->name('users.index');
        Route::get('/users/create', [RegisteredUserController::class, 'create'])->name('users.create');
        Route::post('/users', [RegisteredUserController::class, 'store'])->name('users.store');
    });

    Route::get('/setup/categories', CategoriesIndex::class)->name('setup.categories');
    Route::get('/setup/bulk-units', BulkUnitsIndex::class)->name('setup.bulk_units');
    Route::get('/setup/bulk-types', BulkTypesIndex::class)->name('setup.bulk_types');

    Route::get('/products', ProductsIndex::class)->name('products.index');
    Route::get('/stock-in', StockInIndex::class)->name('stock_in.index');

    Route::get('/sales', SalesIndex::class)->name('sales.index');
    Route::get('/sales/print', SalesReceiptsBatchPrintController::class)->name('sales.print_batch');
    Route::get('/sales/{sale}/print', SalesReceiptPrintController::class)->name('sales.print');

    Route::get('/stock-in/print', StockInReceiptsBatchPrintController::class)->name('stock_in.print_batch');
    Route::get('/stock-in/{receipt}/print', StockInReceiptPrintController::class)->name('stock_in.print');
    Route::get('/reports', ReportsIndex::class)->name('reports.index');
    Route::get('/stock-movements', StockMovementsIndex::class)->name('stock_movements.index');
});

require __DIR__.'/auth.php';
