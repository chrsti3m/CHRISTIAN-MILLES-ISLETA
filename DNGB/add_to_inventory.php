<?php
require 'connections/conx.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purchaseOrderId = $_POST['purchase_order_id'];

    // Fetch the details from the purchase order, including banana type ID
    $stmt = $pdo->prepare("
    SELECT 
        po.quantity_ordered, 
        po.delivery_date, 
        po.supplier_id,
        po.banana_type_id  -- Get the banana_type_id from the purchase_order table directly
    FROM 
        purchase_order po
    WHERE 
        po.purchase_order_id = ? 
    ");

    $stmt->execute([$purchaseOrderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        $quantityOrdered = $order['quantity_ordered'];
        $deliveryDate = $order['delivery_date'];
        $supplierId = $order['supplier_id']; // Get the supplier ID
        $bananaTypeId = $order['banana_type_id']; // Get the banana type ID

        
        // Calculate expiration date (21 days from the delivery date)
        $expirationDate = date('Y-m-d H:i:s', strtotime($deliveryDate . ' + 21 days'));


        // Get the user ID from session (assumed you have session started and user logged in)
        session_start();
        $userId = $_SESSION['user_id']; // Adjust based on how you manage user sessions

        // Prepare the INSERT statement to add the inventory
        $stmt = $pdo->prepare("
            INSERT INTO inventory 
            (supplier_id, user_id, purchase_order_id, banana_type_id, quantity_in_stock, receive_date, expiration_date, storage_location) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, 'Kubo')
        ");

        // Execute the statement to insert into inventory
        if ($stmt->execute([$supplierId, $userId, $purchaseOrderId, $bananaTypeId, $quantityOrdered, $deliveryDate, $expirationDate])) {
            // After successful insertion into inventory, update the order status to 'Order Complete'
            $updateStmt = $pdo->prepare("UPDATE purchase_order SET order_status = 'Order Complete' WHERE purchase_order_id = ?");
            $updateStmt->execute([$purchaseOrderId]);

            if ($updateStmt->rowCount() > 0) {
                echo 'New Stock Update! Check Inventory and Order Complete';
            } else {
                echo 'Error updating order status';
            }
        } else {
            echo 'Error updating inventory';
        }
    } else {
        echo 'Invalid purchase order ID';
    }
}
?>