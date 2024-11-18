<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory</title>
</head>
<body>
    <h1>Add New Inventory</h1>

    <form method="POST" action="{{ route('inventory.store') }}">
    @csrf
    <label for="banana_type_id">Banana Type:</label>
    <select name="banana_type_id" required>
        <option value="">Select a Banana Type</option>
        @foreach ($bananaTypes as $bananaType)
            <option value="{{ $bananaType->banana_type_id }}">{{ $bananaType->type_name }}</option>
        @endforeach
    </select>

    <label for="quantity_in_stock">Quantity:</label>
    <input type="number" name="quantity_in_stock" required>

    <label for="receive_date">Receive Date:</label>
    <input type="date" name="receive_date" required>

    <button type="submit">Add Inventory</button>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <a href="{{ route('inventory.index') }}">Back to Inventory List</a>
</form>

</body>
</html>
