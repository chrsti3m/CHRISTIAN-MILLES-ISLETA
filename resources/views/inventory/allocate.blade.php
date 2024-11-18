<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tricycle Inventory Allocation</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Allocate Stocks to Tricycle</h1>

        <!-- Tricycle Selection Form -->
        <form method="GET" action="{{ route('tricycle_inventory.allocation_form') }}">
            <label for="tricycle_id">Select Tricycle:</label>
            <select name="tricycle_id" id="tricycle_id" onchange="this.form.submit()">
                <option value="">-- Select a Tricycle --</option>
                @foreach($tricycles as $tricycle)
                    <option value="{{ $tricycle->tricycle_id }}" 
                        {{ $selectedTricycleId == $tricycle->tricycle_id ? 'selected' : '' }}>
                        Tricycle ID {{ $tricycle->tricycle_id }} - {{ $tricycle->location }}
                    </option>
                @endforeach
            </select>
        </form>

        <!-- Stock Details Display -->
        @if($selectedTricycleId)
            <h3>Stock Details for Tricycle ID: {{ $selectedTricycleId }}</h3>
            @if($stocks->isEmpty())
                <p>No stocks available for this tricycle.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Banana Type</th>
                            <th>Total Quantity (kg)</th>
                            <th>Available Stock (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{ $stock->type_name }}</td>
                                <td>{{ $stock->total_quantity }}</td>
                                <td>{{ $stock->quantity_in_stock }}</td> <!-- Show the available stock -->
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif

        <!-- Allocation Form -->
        <!-- Allocation Form -->
@if($selectedTricycleId)
    <h2>Allocate Stocks</h2>
    <form method="POST" action="{{ route('tricycle_inventory.allocate') }}">
        @csrf
        <input type="hidden" name="tricycle_id" value="{{ $selectedTricycleId }}">

        <label for="banana_type_id">Select Banana Type:</label>
        <select name="banana_type_id" id="banana_type_id" required>
            <option value="">-- Select a Banana Type --</option>
            @foreach($stocks as $stock)
                <option value="{{ $stock->banana_type_id }}">{{ $stock->type_name }}</option>
            @endforeach
        </select>

        <label for="quantity_to_allocate">Quantity to Allocate (kg):</label>
        <input type="number" name="quantity_to_allocate" id="quantity_to_allocate" step="0.01" required>

        <label for="selling_price_per_kilo">Selling Price Per Kilo:</label>
        <input type="number" name="selling_price_per_kilo" id="selling_price_per_kilo" step="0.01" required>

        <button type="submit">Allocate</button>
    </form>
@endif


        <!-- Display Success or Error Message -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <a href="{{ route('inventory.index') }}">Back to Inventory List</a>
</body>
</html>
