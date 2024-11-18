<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\BananaType;
use App\Models\Tricycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // Show all inventory items
    public function index()
    {
        $inventory = Inventory::join('banana_type', 'inventory.banana_type_id', '=', 'banana_type.banana_type_id')
            ->select(
                'banana_type.type_name',
                'banana_type.banana_type_id',
                DB::raw('SUM(inventory.quantity_in_stock) as total_quantity'),
                DB::raw('MIN(inventory.receive_date) as earliest_receive_date')
            )
            ->groupBy('banana_type.banana_type_id', 'banana_type.type_name')
            ->get();

        return view('inventory.index', compact('inventory'));
    }

    // Show form to create new inventory item
    public function create()
    {
        // Fetch all banana types
        $bananaTypes = BananaType::all();

        // Pass the banana types to the view
        return view('inventory.create', compact('bananaTypes'));
    }

    // Store new inventory record
    public function store(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'banana_type_id' => 'required|integer',
            'quantity_in_stock' => 'required|numeric',
            'receive_date' => 'required|date',
        ]);

        // Save new inventory record to the database
        Inventory::create($validated);

        // Redirect back to inventory list
        return redirect()->route('inventory.index')->with('success', 'Inventory added successfully');
    }

    // Show details of a single inventory item
    public function show($id)
    {
        $inventory = Inventory::with('bananaType')->findOrFail($id);
        return view('inventory.show', compact('inventory'));
    }

    // Show form to allocate inventory to tricycles
  // Show form to allocate inventory to tricycles
    public function allocateForm(Request $request)
    {
        // Fetch all tricycles
        $tricycles = Tricycle::all();
        // Get the selected tricycle ID from the query parameter
        $selectedTricycleId = $request->input('tricycle_id');

        // Fetch the available stock for the selected banana type
        if ($selectedTricycleId) {
            // Show the available stocks for the selected tricycle and banana type
            $stocks = DB::table('inventory')
                        ->join('banana_type', 'inventory.banana_type_id', '=', 'banana_type.banana_type_id')
                        ->select('banana_type.type_name', 'inventory.quantity_in_stock', 'banana_type.banana_type_id')
                        ->where('inventory.banana_type_id', '=', $request->input('banana_type_id'))
                        ->get();
        } else {
            $stocks = collect(); // Empty collection if no tricycle is selected
        }

        return view('inventory.allocate', [
            'tricycles' => $tricycles,
            'selectedTricycleId' => $selectedTricycleId,
            'stocks' => $stocks
        ]);
    }

}
