<?php 
//RECORDS ARE REPEATING
require 'connections/conx.php';
include 'components/header-supplier.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

// Check if the user is logged in and has the Supplier role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Supplier') {
    header("Location: login.html"); // Redirect to login if not authorized
    exit();
}

// Get the supplier ID from the session
$supplier_id = $_SESSION['supplier_id']; // Changed this line to retrieve supplier_id instead of user_id
echo "<pre>";
echo "Supplier ID from session: " . $supplier_id;
echo "</pre>";

// Database connection
$conn = new PDO('mysql:host=localhost;dbname=dandg', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error reporting

// Updated SQL query
// Updated SQL query// Updated SQL query to exclude 'Order Complete'
$query = "
    SELECT 
        po.purchase_order_id,
        po.order_date,
        po.quantity_ordered,
        po.total_cost,
        po.order_status,
        po.delivery_date,
        u.name AS staff_name,
        bt.type_name AS banana_type
    FROM 
        purchase_order po
    JOIN 
        banana_type bt ON po.banana_type_id = bt.banana_type_id
    JOIN 
        user u ON po.user_id = u.user_id
    WHERE 
        po.supplier_id = :supplier_id
        AND po.order_status != 'Order Complete'  -- Exclude orders with 'Order Complete' status
    ORDER BY 
        po.order_date";




$stmt = $conn->prepare($query);
$stmt->bindParam(':supplier_id', $supplier_id);

try {
    $stmt->execute();
    $purchase_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<pre>Error: " . $e->getMessage() . "</pre>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Order</title>
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="components/css/style-sidebar.css">
     
    <style>
        .record-row {
            margin-bottom: 20px;
        }
        .divider {
            border-bottom: 1px solid #dee2e6;
            margin: 10px 0;
        }
        .table {
            margin: 20px 0;
        }
        .content {
            margin-left: 250px;
        }
      .main-table-container {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    margin: 50px 0;
    margin-right: 20px; /* Distance from the right side */
    margin-left: 35px; /* Adjust this to control the distance from the left */
    width: calc(100% - 80px); /* Adjust width based on the left margin */
}

        .pending-title{
            display: flex; /* Use flexbox for horizontal alignment */
            justify-content: space-between; /* Space between chart and table */
            gap: 20px; /* Optional: adds space between chart and table */
            margin-bottom: 20px; /* Space below the chart and table */
        }

    </style>
</head>
<body style="background: #FAFAFA;">
    <?php include 'components/sidebar.html'; ?>

    <div class="content">
        
        <div class="main-table-container">
            <h3 class="pending-title">Pending Orders</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Quantity Ordered</th>
                        <th>Total Cost</th>
                        <th>Ordered By</th>
                        <th>Order Status</th>
                        <th>Banana Type</th> <!-- Added column for banana type -->
                        <th>Delivery Date</th> <!-- Added column for delivery date -->
                    </tr>
                </thead>
                <tbody>
    <?php
    foreach ($purchase_orders as $order) {
        echo "<tr class='record-row'>";
        echo "<td>" . $order['purchase_order_id'] . "</td>";
        echo "<td>" . $order['order_date'] . "</td>";
        echo "<td>" . $order['quantity_ordered'] . "</td>";
        echo "<td>" . $order['total_cost'] . "</td>";
        echo "<td>" . $order['staff_name'] . "</td>";
        echo "<td>" . $order['order_status'] . "</td>";
        echo "<td>" . $order['banana_type'] . "</td>"; // Show single banana type for the order
        echo "<td>" . $order['delivery_date'] . "</td>";
        echo "<td style='text-align: right;'>";
        echo "<form action='supplier-order-back.php' method='POST' style='display: inline;'>";
        echo "<input type='hidden' name='order_id' value='" . $order['purchase_order_id'] . "' />";
        echo "<select class='form-select' name='status' onchange='this.form.submit()' style='width: 165px; border: 2px solid #DAA520; color: #DAA520;'>";
        echo "<option value='Order Placed' " . ($order['order_status'] == 'Order Placed' ? 'selected' : '') . ">Order Placed</option>";
        echo "<option value='Preparing Order' " . ($order['order_status'] == 'Preparing Order' ? 'selected' : '') . ">Preparing Order</option>";
        echo "<option value='Order Loaded' " . ($order['order_status'] == 'Order Loaded' ? 'selected' : '') . ">Order Loaded</option>";
        echo "<option value='Order Shipped' " . ($order['order_status'] == 'Order Shipped' ? 'selected' : '') . ">Order Shipped</option>";
        echo "<option value='Order Dropped Off' " . ($order['order_status'] == 'Order Dropped Off' ? 'selected' : '') . ">Order Dropped Off</option>";
        echo "</select>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
        echo "<tr class='divider'><td colspan='8'></td></tr>";
    }
    ?>
</tbody>

            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>   
</body>
</html>
