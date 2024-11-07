<?php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is a supplier
if (!isset($_SESSION['supplier_id'])) {
    // Redirect to login page if supplier is not logged in
    header("Location: login.php");
    exit();
}

// Get the supplier ID from the session
$supplier_id = $_SESSION['supplier_id'];

// Include the database connection
require_once 'connections/conx.php';

// Query to fetch the order history for the logged-in supplier
$sql = "SELECT po.purchase_order_id, po.order_date, po.quantity_ordered, po.total_cost, po.order_status, po.delivery_date, bt.type_name AS banana_type 
        FROM purchase_order po 
        JOIN banana_type bt ON po.banana_type_id = bt.banana_type_id
        WHERE po.supplier_id = :supplier_id
        ORDER BY po.order_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['supplier_id' => $supplier_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="css-pages/style.css"> <!-- Ensure this file exists and is correctly linked -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Supplier Order History</title>
    <!-- <style>
        .trike-history-wrapper {
            width: 100%;
            padding: 20px;
        }

        .trike-history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .history-table th, .history-table td {
            padding: 12px;
            text-align: center;
        }

        .history-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .history-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .history-table tbody tr:hover {
            background-color: #e9ecef;
        }
    </style> -->
</head>
<body style="background: #FAFAFA;">

	<?php include 'components/sidebar.html';
	include 'components/header-supplier.php';
	 ?>

<div class="trike-history-wrapper">
    <div class="trike-history-header">
        <h2>Order History</h2>
        <p><?php echo date('Y-m-d'); ?></p>
    </div>

    <table class="history-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Quantity Ordered</th>
                <th>Total Cost</th>
                <th>Status</th>
                <th>Delivery Date</th>
                <th>Banana Type</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['purchase_order_id']; ?></td>
                        <td><?php echo $order['order_date']; ?></td>
                        <td><?php echo number_format($order['quantity_ordered'], 2); ?> kg</td>
                        <td><?php echo number_format($order['total_cost'], 2); ?> PHP</td>
                        <td><?php echo $order['order_status']; ?></td>
                        <td><?php echo $order['delivery_date']; ?></td>
                        <td><?php echo $order['banana_type']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No orders found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
