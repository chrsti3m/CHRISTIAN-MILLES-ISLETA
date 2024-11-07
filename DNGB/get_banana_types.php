<?php
require 'connections/conx.php'; // Include your DB connection

if (isset($_GET['supplier_id'])) {
    $supplier_id = intval($_GET['supplier_id']);

    // Fetch banana types for the selected supplier, along with their costs
    $stmt = $pdo->prepare("SELECT supplier_banana.banana_type_id, banana_type.type_name, supplier_banana.cost_per_unit 
                           FROM supplier_banana 
                           JOIN banana_type ON supplier_banana.banana_type_id = banana_type.banana_type_id 
                           WHERE supplier_banana.supplier_id = ?");
    $stmt->execute([$supplier_id]);

    $bananaTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the result as a JSON array
    echo json_encode($bananaTypes);
}
