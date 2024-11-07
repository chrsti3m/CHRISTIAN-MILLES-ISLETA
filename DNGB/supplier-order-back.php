<?php
session_start();
require 'connections/conx.php'; // Adjust the path if necessary

// Check if the user is logged in and has the Supplier role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Supplier') {
    header("Location: login.html"); // Redirect to login if not authorized
    exit();
}

// Check if form data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the order ID and new status from the form submission
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Prepare the SQL statement to update the order status and let MySQL handle the timestamp
    $stmt = $pdo->prepare("UPDATE purchase_order 
                           SET order_status = :status
                           WHERE purchase_order_id = :order_id");
    
    // Bind the parameters
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $order_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect back to the order page after successful update
        header("Location: supplier-order-page.php"); // Change this to your order page
        exit();
    } else {
        echo "Error updating order status.";
    }
}
?>
