<?php
session_start(); // Start the session

// Database connection settings
$host = 'localhost'; // Replace with your database host
$dbname = 'dandg'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the user is logged in and has the Supplier role
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'Supplier') {
    // Fetch the supplier_id based on the user_id
    $stmt = $pdo->prepare("SELECT supplier_id FROM supplier WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($supplier) {
        $supplierId = $supplier['supplier_id']; // Get the supplier_id

        // Check if form data is received via POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Retrieve data from the POST request
            $bananaType = $_POST['banana_type'];
            $costPerUnit = $_POST['cost_per_unit'];

            // 1. Insert into banana_type table
            $stmt = $pdo->prepare("INSERT INTO banana_type (type_name, description) VALUES (:type_name, :description)");
            $stmt->execute(['type_name' => $bananaType, 'description' => 'Description for ' . $bananaType]);

            // Get the last inserted banana_type_id
            $bananaTypeId = $pdo->lastInsertId();

            
            // 2. Insert into supplier_banana table
            $stmt = $pdo->prepare("INSERT INTO supplier_banana (supplier_id, banana_type_id, cost_per_unit) VALUES (:supplier_id, :banana_type_id, :cost_per_unit)");
            $stmt->execute(['supplier_id' => $_SESSION['supplier_id'], 'banana_type_id' => $bananaTypeId, 'cost_per_unit' => $costPerUnit]);


            // Redirect after successful insertion
            header('Location: suppliers-product-page.php');
            exit();
        } else {
            echo "No form data received.";
        }
    } else {
        echo "No supplier found for this user.";
    }
} else {
    echo "Unauthorized access. Please log in as a Supplier.";
}

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
    