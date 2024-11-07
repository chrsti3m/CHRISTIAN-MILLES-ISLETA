<?php
require 'connections/conx.php'; // Adjust the path to your database connection file

// Start the session if it hasn't been started
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $supplier_name = $_POST['supplier_name'];
    $contact_info = $_POST['contact_info'];
    $location = $_POST['location'];
    $email = $_POST['email'];  // Add an email field to your supplier form
    $password = $_POST['password']; // Add a password field to your supplier form

    // Check if the user has the privileges to create a supplier
    if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 101 || $_SESSION['user_id'] == 102)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insert supplier login info into user table
            $user_query = "INSERT INTO user (name, role, email, password, contact_info) VALUES (?, 'Supplier', ?, ?, ?)";
            $user_stmt = $pdo->prepare($user_query);
            $user_stmt->execute([$supplier_name, $email, $hashed_password, $contact_info]);

            // Get the user_id of the inserted user
            $user_id = $pdo->lastInsertId();

            // Insert supplier info into supplier table and link with user_id
            $supplier_query = "INSERT INTO supplier (supplier_name, contact_info, location, user_id) VALUES (?, ?, ?, ?)";
            $supplier_stmt = $pdo->prepare($supplier_query);
            $supplier_stmt->execute([$supplier_name, $contact_info, $location, $user_id]);

            // Redirect after successful insertion
            header('Location: suppliers-front.php');
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        // User does not have permission
        echo "You do not have permission to create a supplier.";
    }
}
?>
