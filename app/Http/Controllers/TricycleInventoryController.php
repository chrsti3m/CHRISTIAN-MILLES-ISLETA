<?php

namespace App\Http\Controllers;

use App\Models\Tricycle;
use App\Models\Inventory;
use App\Models\TricycleInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TricycleInventoryController extends Controller
{
   public function allocationForm(Request $request)
{
    // Fetch all tricycles
    $tricycles = Tricycle::all();

    // Get the selected tricycle ID from the request
    $selectedTricycleId = $request->input('tricycle_id');

    // Fetch stocks allocated to the selected tricycle
    $stocks = TricycleInventory::join('banana_type', 'tricycle_inventory.banana_type_id', '=', 'banana_type.banana_type_id')
        ->where('tricycle_inventory.tricycle_id', $selectedTricycleId)
        ->select(
            'tricycle_inventory.banana_type_id',
            'banana_type.type_name',
            DB::raw('SUM(tricycle_inventory.quantity_allocated) as total_quantity')
        )
        ->groupBy('tricycle_inventory.banana_type_id', 'banana_type.type_name')
        ->get();

    // Update the view path to match the correct location
    return view('inventory.allocate', compact('tricycles', 'selectedTricycleId', 'stocks'));
}


public function allocate(Request $request)
{
    $tricycleId = $request->input('tricycle_id');
    $bananaTypeId = $request->input('banana_type_id');
    $quantityToAllocate = $request->input('quantity_to_allocate');
    $sellingPricePerKilo = $request->input('selling_price_per_kilo');

    // Correct query using 'tricycle' as the table name
    $count = DB::table('tricycle')
                ->where('tricycle_id', $tricycleId)
                ->count();

    // Check if the tricycle exists
    if ($count == 0) {
        return redirect()->back()->with('error', 'Tricycle not found');
    }

    // Insert allocation into 'tricycle_inventory'
    DB::table('tricycle_inventory')->insert([
        'tricycle_id' => $tricycleId,
        'banana_type_id' => $bananaTypeId,
        'quantity_allocated' => $quantityToAllocate,
        'selling_price_per_kilo' => $sellingPricePerKilo,
        'date_allocated' => now(),
    ]);

    // Redirect back with a success message
    return redirect()->back()->with('success', 'Bananas allocated successfully!');
}


}
