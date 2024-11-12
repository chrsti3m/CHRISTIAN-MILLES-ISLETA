<?php
include 'components/sidebar.php';
require 'connections/conx.php'; 
include 'components/header-admin.php';
// Fetch inventory data with necessary joins, only including items with quantity in stock greater than zero
$query = "
    SELECT 
        inventory.inventory_id,
        inventory.quantity_in_stock,
        inventory.receive_date,
        inventory.expiration_date,
        inventory.storage_location,
        banana_type.type_name,
        supplier.supplier_name,
        purchase_order.quantity_ordered,
        purchase_order.order_date,
        purchase_order.delivery_date
    FROM 
        inventory
    JOIN 
        banana_type ON inventory.banana_type_id = banana_type.banana_type_id
    JOIN 
        supplier ON inventory.supplier_id = supplier.supplier_id
    JOIN 
        purchase_order ON inventory.purchase_order_id = purchase_order.purchase_order_id
    WHERE 
        inventory.quantity_in_stock > 0  -- Only include records with a quantity in stock
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
    <title>Inventory Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="components/css/headeruser.css">
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap");
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Poppins", sans-serif;
            overflow-y: auto;
        }
        .leftSide {
            position: fixed;
            top: 0;
            left: 0;
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
        .main {
            margin-left: 250px;
            padding: 60px 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <section class="layout">
        

        <div class="main">
            <div class="container">
                <h2>Inventory Report</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Inventory ID</th>
                            <th>Quantity in Stock</th>
                            <th>Receive Date</th>
                            <th>Expiration Date</th>
                            <th>Storage Location</th>
                            <th>Banana Type</th>
                            <th>Supplier</th>
                            <th>Quantity Ordered</th>
                            <th>Order Date</th>
                            <th>Delivery Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($results) > 0) {
                            foreach ($results as $row) {
                                echo "<tr>
                                    <td>{$row['inventory_id']}</td>
                                    <td>{$row['quantity_in_stock']}</td>
                                    <td>{$row['receive_date']}</td>
                                    <td>{$row['expiration_date']}</td>
                                    <td>{$row['storage_location']}</td>
                                    <td>{$row['type_name']}</td>
                                    <td>{$row['supplier_name']}</td>
                                    <td>{$row['quantity_ordered']}</td>
                                    <td>{$row['order_date']}</td>
                                    <td>{$row['delivery_date']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>No records found</td></tr>";
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
