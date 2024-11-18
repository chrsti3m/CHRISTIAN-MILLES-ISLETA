<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TricycleInventoryController;

// Inventory Routes
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');

// Inventory allocation route with banana_type_id
Route::get('/inventory/allocate/{banana_type_id}', [InventoryController::class, 'allocateForm'])->name('inventory.allocate_form');

// Tricycle Inventory Routes
Route::get('/tricycle/inventory/allocate', [TricycleInventoryController::class, 'allocationForm'])->name('tricycle_inventory.allocation_form');
Route::post('/tricycle/inventory/allocate', [TricycleInventoryController::class, 'allocate'])->name('tricycle_inventory.allocate');
use Illuminate\Support\Facades\DB;

Route::get('/test-tricycles', function () {
    $tricycles = DB::table('tricycle')->where('user_id', 103)->get();
    dd($tricycles); // This will dump the result of the query
});
