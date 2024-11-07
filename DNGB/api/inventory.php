<?php
header('Content-Type: application/json');

// Include the database connection
require '../connections/conx.php'; // Adjust the path if necessary

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // SQL query to fetch inventory with banana types and suppliers
    $query = "
    SELECT 
        bt.banana_type_id,
        bt.type_name AS banana_type,
        SUM(i.quantity_in_stock) AS total_quantity_in_stock,
        MIN(i.receive_date) AS earliest_receive_date,
        MAX(i.expiration_date) AS latest_expiration_date,
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
        po.order_status = 'Order Complete'
    GROUP BY 
        bt.banana_type_id, bt.type_name, i.storage_location
    HAVING 
        total_quantity_in_stock > 0
    ORDER BY 
        bt.type_name ASC;";

    $stmt = $pdo->query($query);
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log output for debugging
    file_put_contents('debug_log.txt', print_r($inventory, true));

    // Return data as JSON
    echo json_encode($inventory);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
