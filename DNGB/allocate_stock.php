<?php
session_start(); // Start the session to use session variables
require 'connections/conx.php'; // Connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $tricycle_id = $_POST['tricycle_id'];
    $banana_type_id = $_POST['banana_type_id'];
    $quantity_allocated = $_POST['quantity_allocated'];
    $selling_price_per_kilo = $_POST['selling_price_per_kilo'];
    $user_id = $_SESSION['user_id'];

    // Get the current datetime for allocation
    $date_allocated = date('Y-m-d H:i:s');

    // Calculate the expiration date (7 days from allocation)
    $expiration_date = date('Y-m-d H:i:s', strtotime('+7 days'));

    // Query to select inventory in FIFO order
    $query = "SELECT inventory_id, quantity_in_stock 
              FROM inventory 
              WHERE banana_type_id = :banana_type_id 
                AND quantity_in_stock >= 0 
              ORDER BY receive_date ASC, expiration_date ASC"; // FIFO order

    $stmt = $pdo->prepare($query);
    $stmt->execute([':banana_type_id' => $banana_type_id]);

    // Allocate stock while respecting FIFO
    $remaining_quantity_to_allocate = $quantity_allocated;
    $total_allocated = 0;

    while ($remaining_quantity_to_allocate > 0) {
    $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inventory) {
        // If there's no more inventory, exit the loop
        $_SESSION['alert_message'] = "Error: Not enough inventory available for allocation.";
        break; 
    }

    $inventory_id = $inventory['inventory_id'];
    $available_quantity = $inventory['quantity_in_stock'];

    // Allocate from this inventory item
    if ($available_quantity <= $remaining_quantity_to_allocate) {
        // Use the entire stock from this inventory item
        $allocated_quantity = $available_quantity;
        $remaining_quantity_to_allocate -= $available_quantity;
    } else {
        // Partially allocate from this inventory item
        $allocated_quantity = $remaining_quantity_to_allocate;
        $remaining_quantity_to_allocate = 0; // Allocation complete
    }

    // Only insert if the allocated quantity is greater than zero
    if ($allocated_quantity > 0) {
        // Update inventory to reflect the new remaining quantity
        $new_quantity_in_stock = $available_quantity - $allocated_quantity;
        $updateInventoryQuery = "UPDATE inventory 
                                 SET quantity_in_stock = :remaining_quantity 
                                 WHERE inventory_id = :inventory_id";
        $updateStmt = $pdo->prepare($updateInventoryQuery);
        $updateStmt->execute([
            ':remaining_quantity' => $new_quantity_in_stock,
            ':inventory_id' => $inventory_id
        ]);

        // Insert the allocation records into tricycle_inventory
        $insertQuery = "INSERT INTO tricycle_inventory 
                        (tricycle_id, user_id, quantity_allocated, selling_price_per_kilo, date_allocated, allocated_unit_expiration, banana_type_id, inventory_id)
                        VALUES (:tricycle_id, :user_id, :quantity_allocated, :selling_price_per_kilo, :date_allocated, :expiration_date, :banana_type_id, :inventory_id)";
        
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            ':tricycle_id' => $tricycle_id,
            ':user_id' => $user_id,
            ':quantity_allocated' => $allocated_quantity, // Use the correct allocated amount for this inventory
            ':selling_price_per_kilo' => $selling_price_per_kilo,
            ':date_allocated' => $date_allocated,
            ':expiration_date' => $expiration_date,
            ':banana_type_id' => $banana_type_id,
            ':inventory_id' => $inventory_id
        ]);

        // Track total allocated quantity
        $total_allocated += $allocated_quantity;
    }
    
    // Break the loop once all required quantity has been allocated
    if ($remaining_quantity_to_allocate <= 0) {
        break;
    }
}


    // Check if stock has been successfully allocated
    if ($total_allocated > 0) {
        $_SESSION['alert_message'] = "Stock successfully allocated!";
    } else {
        $_SESSION['alert_message'] = "Error: No stock was allocated.";
    }

    // Check for expired stocks and insert them into the waste table
    checkAndInsertExpiredStocks($pdo);

    // Redirect back to the inventory page
    header("Location: admin-inventory-page.php");
    exit();
}

/**
 * Function to check for expired stocks and insert them into the waste table.
 *
 * @param PDO $pdo The PDO database connection
 */
function checkAndInsertExpiredStocks($pdo) {
    // Get the current date
    $currentDate = date('Y-m-d H:i:s');
    echo "Current Date: $currentDate"; // Debugging line

    // Query to select expired tricycle inventory records
    $query = "
        SELECT 
            ti.tric_inventory_id,
            ti.quantity_allocated
        FROM 
            tricycle_inventory ti
        WHERE 
            ti.allocated_unit_expiration <= :currentDate
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':currentDate' => $currentDate]);
    $expiredStocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Expired Stocks Count: " . count($expiredStocks); // Debugging line

    // Check if any expired stocks exist
    if (!empty($expiredStocks)) {
        // Prepare to insert expired records into the waste table
        // (rest of the code follows)
    } else {
        echo "No expired stocks found."; // Debugging line
    }
}


?>
