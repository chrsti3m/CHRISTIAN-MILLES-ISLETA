<?php
include 'components/sidebar.php';
require 'connections/conx.php'; 
include 'components/header-admin.php';
// Fetch purchase order data with necessary joins
$query = "
    SELECT 
        purchase_order.purchase_order_id,
        purchase_order.order_date,
        purchase_order.quantity_ordered,
        purchase_order.total_cost,
        purchase_order.order_status,
        purchase_order.delivery_date,
        user.name AS user_name,
        supplier.supplier_name,
        banana_type.type_name
    FROM 
        purchase_order
    JOIN 
        user ON purchase_order.user_id = user.user_id
    JOIN 
        supplier ON purchase_order.supplier_id = supplier.supplier_id
    JOIN 
        banana_type ON purchase_order.banana_type_id = banana_type.banana_type_id
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="components/css/headeruser.css">
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap");
        * {
            margin: 0px;
            padding: 0px;
            box-sizing: border-box;
        }
        body {
            font-family: "Poppins", sans-serif;
            overflow-y: auto;
        }
        .leftSide {
            position: fixed;
            top: 0px;
            left: 0px;
            width: 250px;
            height: 100vh;
            background: #DAA520;
            z-index: 1;
        }
        .leftSide .links a {
            display: block;
            padding: 15px 10px;
            text-decoration: none;
            color: #f5f5f5;
            cursor: pointer;
            border-bottom: 1px solid #1b1b1b;
        }
        .leftSide .links a.active,
        .leftSide .links a:hover {
            background: #222;
        }
        .leftSide-header {
            padding: 15px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .leftSide-header img {
            width: 42px;
            border-radius: 50%;
            margin-bottom: 5px;
        }
        .leftSide-header h2 {
            font-family: "Poppins", sans-serif;
            margin: 0;
        }
        .main {
            margin-left: 250px;
            padding: 60px 20px;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 20px;
        }
        .grid-item {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 30px;
            padding: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .grid-item:hover {
            background-color: #e9ecef;
        }
        .grid-item a {
            text-decoration: none;
            color: #000;
            display: block;
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>
    <section class="layout">
        

        <div class="main">
            <div class="container">
                <h2>Purchase Order Report</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Purchase Order ID</th>
                            <th>Order Date</th>
                            <th>Quantity Ordered</th>
                            <th>Total Cost</th>
                            <th>Order Status</th>
                            <th>Delivery Date</th>
                            <th>User</th>
                            <th>Supplier</th>
                            <th>Banana Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($results) > 0) {
                            foreach ($results as $row) {
                                echo "<tr>
                                    <td>{$row['purchase_order_id']}</td>
                                    <td>{$row['order_date']}</td>
                                    <td>{$row['quantity_ordered']}</td>
                                    <td>{$row['total_cost']}</td>
                                    <td>{$row['order_status']}</td>
                                    <td>{$row['delivery_date']}</td>
                                    <td>{$row['user_name']}</td>
                                    <td>{$row['supplier_name']}</td>
                                    <td>{$row['type_name']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
