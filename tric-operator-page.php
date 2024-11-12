<?php 
//RECORDS ARE REPEATING
require 'connections/conx.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

// Check if the user is logged in and has the Supplier role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Tricycle Operator') {
    header("Location: login.html"); // Redirect to login if not authorized
    exit();
}

// Get the supplier ID from the session
$supplier_id = $_SESSION['user_id']; // Changed this line to retrieve supplier_id instead of user_id
echo "<pre>";
echo "Tricycle Operator ID: " . $supplier_id;
echo "</pre>";
 ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<title></title>

</head>
<body>
	<?php include 'components/trike-sidebar.php'; ?>
</body>
</html>