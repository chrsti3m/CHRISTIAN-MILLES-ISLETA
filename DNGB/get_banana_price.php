<?php
require 'connections/conx.php'; // Include your DB connection

if (isset($_GET['supplier_id']) && isset($_GET['banana_type_id'])) {
    $supplier_id = intval($_GET['supplier_id']);
    $banana_type_id = intval($_GET['banana_type_id']); // Ensure this is also an integer

    // Fetch the price for the specific banana type and supplier
    $stmt = $pdo->prepare("SELECT supplier_banana.cost_per_unit 
                            FROM supplier_banana 
                            WHERE supplier_banana.supplier_id = ? 
                            AND supplier_banana.banana_type_id = ?");
    $stmt->execute([$supplier_id, $banana_type_id]);

    // Fetch the result
    $bananaPrice = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($bananaPrice) {
        // Return the result as a JSON object
        echo json_encode($bananaPrice);
    } else {
        // If no price found, return an appropriate message
        echo json_encode(['cost_per_unit' => null]);
    }
} else {
    // Handle the case where parameters are missing
    echo json_encode(['error' => 'Invalid parameters']);
}
?>
