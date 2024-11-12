<?php
include 'components/sidebar.php';
require 'connections/conx.php'; 
include 'components/header-admin.php'; 
// Fetch sales data with necessary joins
$query = "
    SELECT 
        sales_transaction.sales_transaction_id,
        sales_transaction.sale_date,
        sales_transaction.quantity_sold,
        sales_transaction.total_amount_sold,
        inventory.storage_location,
        banana_type.type_name,
        user.name AS user_name,
        tricycle.location AS tricycle_location
    FROM 
        sales_transaction
    JOIN 
        inventory ON sales_transaction.inventory_id = inventory.inventory_id
    JOIN 
        banana_type ON inventory.banana_type_id = banana_type.banana_type_id
    JOIN 
        user ON inventory.user_id = user.user_id
    JOIN 
        tricycle_inventory ON sales_transaction.tric_inventory_id = tricycle_inventory.tric_inventory_id
    JOIN 
        tricycle ON tricycle_inventory.tricycle_id = tricycle.tricycle_id
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
    <title>Sales Report</title>
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
            padding:60px 20px;
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
                <h2>Sales Report</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sales Transaction ID</th>
                            <th>Sale Date</th>
                            <th>Quantity Sold</th>
                            <th>Total Amount Sold</th>
                            <th>Storage Location</th>
                            <th>Banana Type</th>
                            <th>User</th>
                            <th>Tricycle Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($results) > 0) {
                            foreach ($results as $row) {
                                echo "<tr>
                                    <td>{$row['sales_transaction_id']}</td>
                                    <td>{$row['sale_date']}</td>
                                    <td>{$row['quantity_sold']}</td>
                                    <td>{$row['total_amount_sold']}</td>
                                    <td>{$row['storage_location']}</td>
                                    <td>{$row['type_name']}</td>
                                    <td>{$row['user_name']}</td>
                                    <td>{$row['tricycle_location']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No records found</td></tr>";
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
