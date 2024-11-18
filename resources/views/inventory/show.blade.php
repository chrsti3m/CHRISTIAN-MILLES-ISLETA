<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inventory</title>
</head>
<body>
    <h1>Inventory Details</h1>

    <p><strong>ID:</strong> {{ $inventory->inventory_id }}</p>
    <p><strong>Banana Type:</strong> {{ $inventory->type_name }}</p>
    <p><strong>Quantity in Stock:</strong> {{ $inventory->quantity_in_stock }}</p>
    <p><strong>Receive Date:</strong> {{ $inventory->receive_date }}</p>

    <a href="{{ route('inventory.index') }}">Back to Inventory List</a>
</body>
</html>     
