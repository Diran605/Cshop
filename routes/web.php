<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SalesReceiptPrintController;
use App\Livewire\ProductsIndex;
use App\Livewire\ReportsIndex;
use App\Livewire\SalesIndex;
use App\Livewire\StockInIndex;
use App\Livewire\UsersIndex;
use App\Livewire\Setup\BranchesIndex;
use App\Livewire\Setup\BulkTypesIndex;
use App\Livewire\Setup\BulkUnitsIndex;
use App\Livewire\Setup\CategoriesIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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
    Route::get('/sales/{sale}/print', SalesReceiptPrintController::class)->name('sales.print');
    Route::get('/reports', ReportsIndex::class)->name('reports.index');
});

require __DIR__.'/auth.php';
