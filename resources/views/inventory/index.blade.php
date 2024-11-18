<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory List</title>
</head>
<body>
    <h1>Inventory List</h1>

    <table>
    <thead>
        <tr>
            <th>Banana Type</th>
            <th>Quantity in Stock</th>
            <th>Receive Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($inventory as $item)
            <tr>
                <td>{{ $item->type_name }}</td>
                <td>{{ $item->total_quantity }}</td>
                <td>{{ $item->earliest_receive_date ?? 'N/A' }}</td>
                 <td>
                        <!-- Allocation Button -->
                        <a href="{{ route('inventory.allocate_form', ['banana_type_id' => $item->banana_type_id]) }}" 
                           class="btn btn-primary">
                            Allocate Stock
                        </a>
                    </td>
            </tr>

        @endforeach
    </tbody>
</table>



    <a href="{{ route('inventory.create') }}">Add New Inventory</a>
</body>
</html>
