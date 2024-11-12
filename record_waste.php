<?php
require 'connections/conx.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if the user is logged in and if the required POST data is available
if (!isset($_SESSION['user_id']) || !isset($_POST['tric_inventory_id']) || !isset($_POST['waste_reason'])) {
    die('Invalid request. Please log in and provide all necessary data.');
}

// Get POST data and sanitize it
$tricycleInventoryId = filter_var($_POST['tric_inventory_id'], FILTER_SANITIZE_NUMBER_INT);
$reason = htmlspecialchars(trim($_POST['waste_reason']), ENT_QUOTES, 'UTF-8'); // Updated line
$currentDate = date('Y-m-d');
$quantityWasted = filter_var(trim($_POST['waste_quantity']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

// Fetch the quantity_allocated for the given tricycle_inventory_id
$queryFetchQuantity = "SELECT quantity_allocated FROM tricycle_inventory WHERE tric_inventory_id = :tric_inventory_id";
$stmtFetchQuantity = $pdo->prepare($queryFetchQuantity);
$stmtFetchQuantity->bindParam(':tric_inventory_id', $tricycleInventoryId, PDO::PARAM_INT);
$stmtFetchQuantity->execute();
$inventoryRecord = $stmtFetchQuantity->fetch(PDO::FETCH_ASSOC);

if (!$inventoryRecord) {
    die('Error: Tricycle inventory record not found.');
}

$currentAllocatedQuantity = $inventoryRecord['quantity_allocated'];

if ($quantityWasted > $currentAllocatedQuantity) {
    die('Error: Wasted quantity cannot exceed allocated quantity.');
}

// Prepare the INSERT statement for waste
$queryInsertWaste = "INSERT INTO waste (sales_transaction_id, waste_date, quantity_wasted, reason, tricycle_inventory_id) 
                     VALUES (:sales_transaction_id, :waste_date, :quantity_wasted, :reason, :tricycle_inventory_id)";

$stmtInsertWaste = $pdo->prepare($queryInsertWaste);
$salesTransactionId = null; // Default to null, or fetch if applicable
$stmtInsertWaste->bindParam(':sales_transaction_id', $salesTransactionId, PDO::PARAM_NULL);
$stmtInsertWaste->bindParam(':waste_date', $currentDate);
$stmtInsertWaste->bindParam(':quantity_wasted', $quantityWasted);
$stmtInsertWaste->bindParam(':reason', $reason);
$stmtInsertWaste->bindParam(':tricycle_inventory_id', $tricycleInventoryId, PDO::PARAM_INT);

// Execute the query to insert waste
if ($stmtInsertWaste->execute()) {
    // Successfully inserted the record
    // Update quantity_allocated in the tricycle_inventory table
    $newQuantityAllocated = $currentAllocatedQuantity - $quantityWasted; // Deduct the wasted quantity
    $queryUpdateInventory = "UPDATE tricycle_inventory SET quantity_allocated = :quantity_allocated WHERE tric_inventory_id = :tric_inventory_id";
    $stmtUpdateInventory = $pdo->prepare($queryUpdateInventory);
    $stmtUpdateInventory->bindParam(':quantity_allocated', $newQuantityAllocated, PDO::PARAM_INT);
    $stmtUpdateInventory->bindParam(':tric_inventory_id', $tricycleInventoryId, PDO::PARAM_INT);

    if ($stmtUpdateInventory->execute()) {
        echo 'Waste recorded successfully.';
    } else {
        echo 'Error updating inventory: ' . implode(", ", $stmtUpdateInventory->errorInfo());
    }
} else {
    // Handle errors
    echo 'Error recording waste: ' . implode(", ", $stmtInsertWaste->errorInfo());
}
?>
