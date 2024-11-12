<?php  
// get_tricycle_inventory.php
// Include database connection
include 'connections/conx.php'; // Ensure you include your PDO connection file

if (isset($_GET['tricycle_id'])) {
    $tricycleId = $_GET['tricycle_id'];

    // SQL query to fetch and aggregate inventory data for the selected tricycle
   $query = "
    SELECT 
        bt.banana_type_id AS prod_id,
        bt.type_name AS banana_type,
        SUM(ti.quantity_allocated) AS total_allocated,
        (
            SELECT ti2.selling_price_per_kilo 
            FROM tricycle_inventory ti2 
            WHERE ti2.banana_type_id = bt.banana_type_id 
              AND ti2.tricycle_id = :tricycle_id
              AND ti2.quantity_allocated > 0 -- Ensure the price is from a record with non-zero quantity
            ORDER BY ti2.date_allocated DESC, ti2.tric_inventory_id DESC  
            LIMIT 1
        ) AS selling_price,
        MIN(ti.date_allocated) AS earliest_allocated_date,  
        MAX(ti.allocated_unit_expiration) AS latest_expiration_date
    FROM 
        tricycle_inventory ti
    JOIN 
        inventory i ON ti.inventory_id = i.inventory_id
    JOIN 
        banana_type bt ON i.banana_type_id = bt.banana_type_id
    WHERE 
        ti.tricycle_id = :tricycle_id
        AND ti.quantity_allocated > 0 -- Only consider allocated stocks
    GROUP BY 
        bt.banana_type_id, bt.type_name
    HAVING 
        total_allocated > 0 -- Ensure total allocated quantity is greater than zero
    ORDER BY 
        earliest_allocated_date ASC";


    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':tricycle_id', $tricycleId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Fetch data
        $inventoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debugging: check if data is being fetched correctly
        if (empty($inventoryData)) {
            echo json_encode(['error' => 'No data found']);
        } else {
            echo json_encode($inventoryData);
        }
    } else {
        // Handle query failure
        echo json_encode(['error' => 'Query execution failed']);
    }
} else {
    echo json_encode(['error' => 'Tricycle ID not provided']);
}
?>
