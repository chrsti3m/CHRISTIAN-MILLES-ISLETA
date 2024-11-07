<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);  
include 'components/sidebar.php';
require 'connections/conx.php'; 
include 'components/header-admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

var_dump($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>	</title>
	<link rel="stylesheet" type="text/css" href="css-pages/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<style type="text/css">

	</style>
</head>
<body>

	<div class="inventory-wrapper">
		<div class="inventory-header">
			<h2>Order History</h2>
		</div>
		<table>
			<thead>
				<th>Product ID</th>
                <th>Banana Type</th>
                <th>Supplier</th>
                <th>Stock</th>
                <th>Receive Date</th>
                <th>Expiration Date</th>
                <th>Storage Location</th>
			</thead>
			<tbody>
    <?php
    // SQL query to fetch inventory with banana types and suppliers
    $query = "
    SELECT 
        po.purchase_order_id,  -- Use purchase_order_id instead of inventory_id
        bt.type_name AS banana_type,
        s.supplier_name,
        po.quantity_ordered,  -- Quantity ordered from the purchase order table
        i.receive_date,
        i.expiration_date,
        i.storage_location
    FROM 
        inventory i
    JOIN 
        supplier s ON i.supplier_id = s.supplier_id
    JOIN 
        banana_type bt ON i.banana_type_id = bt.banana_type_id
    JOIN 
        purchase_order po ON i.purchase_order_id = po.purchase_order_id
    WHERE 
        po.order_status = 'Order Complete';";  // Select only "Order Complete" records

    $stmt = $pdo->query($query);
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($inventory as $item) {
        echo "<tr>
                <td>{$item['purchase_order_id']}</td>  <!-- Changed here -->
                <td>{$item['banana_type']}</td>
                <td>{$item['supplier_name']}</td>
                <td>{$item['quantity_ordered']} Kl</td>
                <td>{$item['receive_date']}</td>
                <td>{$item['expiration_date']}</td>
                <td>{$item['storage_location']}</td>
              </tr>";
    }
    ?>
</tbody>

		</table>
	</div>
	


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>