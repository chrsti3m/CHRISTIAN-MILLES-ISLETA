<?php
require 'connections/conx.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize a message variable
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture input values
    $banana_type_id = $_POST['banana_type_id'];
    $quantity_sold = (float)$_POST['quantity-sold'];

    // Log inputs
    error_log("Banana Type ID: $banana_type_id, Quantity Sold: $quantity_sold");

    // Validate inputs
    if (empty($banana_type_id) || $quantity_sold <= 0) {
        $message = 'Please select a valid banana type and enter a valid quantity.';
        echo "<script>alert('$message');</script>";
        exit;
    }

    // Retrieve the latest selling price
    $queryPrice = "SELECT selling_price_per_kilo 
                   FROM tricycle_inventory 
                   WHERE banana_type_id = :banana_type_id 
                     AND tricycle_id = :tricycle_id 
                   ORDER BY date_allocated DESC 
                   LIMIT 1";

    $stmtPrice = $pdo->prepare($queryPrice);
    $stmtPrice->bindValue(':banana_type_id', $banana_type_id, PDO::PARAM_INT);
    $stmtPrice->bindValue(':tricycle_id', $_SESSION['tricycle_id'], PDO::PARAM_INT);
    
    // Log the query execution
    error_log("Executing price query: $queryPrice");
    
    if (!$stmtPrice->execute()) {
        error_log("SQL Error: " . implode(", ", $stmtPrice->errorInfo()));
    }

    $latestPriceRecord = $stmtPrice->fetch(PDO::FETCH_ASSOC);

    // Log the latest price record
    error_log("Latest Price Record: " . print_r($latestPriceRecord, true));

    if (!$latestPriceRecord) {
        $message = 'No price available for the selected banana type.';
        echo "<script>alert('$message');</script>";
        exit;
    }

    $latestSellingPrice = $latestPriceRecord['selling_price_per_kilo'];
    
    // Log retrieved selling price
    error_log("Retrieved Selling Price: $latestSellingPrice");

    // Log before calculation
    error_log("Quantity Sold: $quantity_sold, Latest Selling Price: $latestSellingPrice");

    // Retrieve inventory records in FIFO order
    $queryInventory = "SELECT * 
                       FROM tricycle_inventory 
                       WHERE banana_type_id = :banana_type_id 
                         AND tricycle_id = :tricycle_id 
                       ORDER BY date_allocated ASC"; // FIFO order

    $stmtInventory = $pdo->prepare($queryInventory);
    $stmtInventory->bindValue(':banana_type_id', $banana_type_id, PDO::PARAM_INT);
    $stmtInventory->bindValue(':tricycle_id', $_SESSION['tricycle_id'], PDO::PARAM_INT);
    $stmtInventory->execute();

    $tricycleInventories = $stmtInventory->fetchAll(PDO::FETCH_ASSOC);

    if (empty($tricycleInventories)) {
        $message = 'No stock available for the selected banana type.';
        echo "<script>alert('$message');</script>";
        exit;
    }

    $remainingQuantity = $quantity_sold;
    $totalAmountSold = 0;

    $pdo->beginTransaction();

    try {
        foreach ($tricycleInventories as $inventory) {
            $stock = $inventory['quantity_allocated'];
            $inventoryId = $inventory['tric_inventory_id'];

            // Log inventory processing
            error_log("Selected Inventory ID: " . $inventoryId . ", Stock: " . $stock);

            if ($remainingQuantity > 0 && $stock > 0) {
                // Determine how much to sell from this stock
                $quantityToSubtract = min($remainingQuantity, $stock); // Use the smaller of remainingQuantity or stock
                $remainingQuantity -= $quantityToSubtract; // Reduce remaining quantity
                $newStock = $stock - $quantityToSubtract; // Calculate new stock

                // Update tricycle inventory to reflect the stock used
                $updateInventory = "UPDATE tricycle_inventory 
                                    SET quantity_allocated = :new_stock 
                                    WHERE tric_inventory_id = :inventory_id";
                $stmtUpdate = $pdo->prepare($updateInventory);
                $stmtUpdate->bindValue(':new_stock', $newStock, PDO::PARAM_INT); // Use INT for quantity
                $stmtUpdate->bindValue(':inventory_id', $inventoryId, PDO::PARAM_INT);
                $stmtUpdate->execute();

                // Calculate the total for this batch
                $totalForThisBatch = (float)$quantityToSubtract * (float)$latestSellingPrice;
                $totalAmountSold += $totalForThisBatch;

                // Log sale details
                error_log("Selling Quantity: $quantityToSubtract at Price: $latestSellingPrice. Total for this batch: $totalForThisBatch");

                // Insert into sales_transaction
                $insertSale = "INSERT INTO sales_transaction 
                               (tric_inventory_id, sale_date, quantity_sold, inventory_id, total_amount_sold) 
                               VALUES (:inventory_id, :sale_date, :quantity_sold, :inventory_id_ref, :total_amount_sold)";
                $stmtSale = $pdo->prepare($insertSale);
                $stmtSale->bindValue(':inventory_id', $inventoryId, PDO::PARAM_INT);
                $stmtSale->bindValue(':sale_date', date('Y-m-d'), PDO::PARAM_STR);
                $stmtSale->bindValue(':quantity_sold', $quantityToSubtract, PDO::PARAM_INT); // Use INT for quantity
                $stmtSale->bindValue(':inventory_id_ref', $inventory['inventory_id'], PDO::PARAM_INT);
                $stmtSale->bindValue(':total_amount_sold', $totalForThisBatch, PDO::PARAM_STR);
                $stmtSale->execute();
            }

            // Exit loop if all quantity is sold
            if ($remainingQuantity <= 0) {
                break; 
            }
        }

        // Commit transaction
        $pdo->commit();
        $message = "Sale transaction recorded successfully! Total Amount: ₱" . number_format($totalAmountSold, 2);
        echo "<script>
                alert('$message');
                window.location.href = 'tric-sales-page.php';
              </script>";
        exit; 
    } catch (Exception $e) {
        // Roll back transaction in case of error
        $pdo->rollBack();
        $message = "Error processing the sale: " . $e->getMessage();
        echo "<script>
                alert('$message');
                window.location.href = 'tric-sales-page.php';
              </script>";
        exit;
    }
}
?>
