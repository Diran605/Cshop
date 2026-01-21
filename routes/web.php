<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\ProductsIndex;
use App\Livewire\StockInIndex;
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

    Route::get('/setup/categories', CategoriesIndex::class)->name('setup.categories');
    Route::get('/setup/bulk-units', BulkUnitsIndex::class)->name('setup.bulk_units');
    Route::get('/setup/bulk-types', BulkTypesIndex::class)->name('setup.bulk_types');

    Route::get('/products', ProductsIndex::class)->name('products.index');
    Route::get('/stock-in', StockInIndex::class)->name('stock_in.index');
});

require __DIR__.'/auth.php';
