<?php
// Include database connection
require 'connections/conx.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Check what data is posted
    var_dump($_POST); 

    // Get form values
    $banana_type_id = $_POST['banana_type']; // Renamed variable to reflect the banana_type_id
    $supplier_id = $_POST['supplier_id']; // Correct name here
    $quantity_ordered = $_POST['quantity_ordered'];
    $user_id = $_SESSION['user_id']; // Get the user ID from the session

    // Fetch the cost per unit for the selected banana type and supplier
    $stmt = $pdo->prepare("SELECT cost_per_unit FROM supplier_banana WHERE supplier_id = ? AND banana_type_id = ?");
    $stmt->execute([$supplier_id, $banana_type_id]);
    $banana = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the combination of supplier and banana type was found
    if ($banana && isset($banana['cost_per_unit'])) {
        $cost_per_unit = $banana['cost_per_unit'];

        // Calculate the total cost
        $total_cost = $quantity_ordered * $cost_per_unit;

        // Insert the purchase order into the database, including the banana_type_id
        $stmt = $pdo->prepare("INSERT INTO purchase_order (user_id, supplier_id, banana_type_id, order_date, quantity_ordered, total_cost, order_status) 
                               VALUES (?, ?, ?, NOW(), ?, ?, 'Order Placed')");
        if (!$stmt->execute([$user_id, $supplier_id, $banana_type_id, $quantity_ordered, $total_cost])) {
            die('Error inserting purchase order: ' . implode(', ', $stmt->errorInfo())); // Show error details
        }

        // Get the ID of the last inserted order
        $order_id = $pdo->lastInsertId();

        // Calculate the delivery date by adding 1-2 days to the order date
        $order_date = new DateTime(); // Use the current date for the order date
        $delivery_days = rand(1, 2); // Randomly choose to add 1 or 2 days for the delivery date
        $order_date->modify("+{$delivery_days} days");
        $delivery_date = $order_date->format('Y-m-d H:i:s'); // Include time in the format

        // Prepare the SQL statement to update the order status and delivery date
        $update_stmt = $pdo->prepare("UPDATE purchase_order 
                                       SET delivery_date = :delivery_date
                                       WHERE purchase_order_id = :order_id");
        // Bind parameters and execute the update statement
        $update_stmt->execute([
            ':delivery_date' => $delivery_date,
            ':order_id' => $order_id,
        ]);

        // Provide feedback to the user
        $_SESSION['feedback'] = "Purchase order created successfully!";
        header('Location: purchase-order-front.php');
        exit();
    } else {
        // Feedback if the cost per unit is not found
        $_SESSION['feedback'] = "Error: Cost per unit not found for the selected banana type and supplier.";
        header('Location: purchase-order-front.php');
        exit();
    }
}
?>
