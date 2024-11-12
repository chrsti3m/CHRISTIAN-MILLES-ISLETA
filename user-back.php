<?php
require 'connections/conx.php'; // Adjust the path to your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $user_name = $_POST['user_name'];
    $location = $_POST['location']; // Get the location from the form
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact_no = $_POST['number'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Begin transaction to ensure atomic operations across both tables
    $pdo->beginTransaction();

    try {
        // Insert new user into the `user` table
        $query = "INSERT INTO user (name, role, email, password, contact_info) VALUES (?, 'Tricycle Operator', ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(1, $user_name);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $hashed_password);
        $stmt->bindParam(4, $contact_no);

        if ($stmt->execute()) {
            // Get the last inserted user ID
            $user_id = $pdo->lastInsertId();

            // Insert the tricycle data into the `tricycle` table
            $query_tricycle = "INSERT INTO tricycle (user_id, location) VALUES (?, ?)";
            $stmt_tricycle = $pdo->prepare($query_tricycle);
            $stmt_tricycle->bindParam(1, $user_id);
            $stmt_tricycle->bindParam(2, $location);

            if ($stmt_tricycle->execute()) {
                // Commit the transaction if both inserts succeed
                $pdo->commit();

                // Redirect back to the user management page or display a success message
                header('Location: user-front.php'); // Adjust the redirect as needed
                exit();
            } else {
                // If the tricycle insertion fails, rollback the transaction
                $pdo->rollBack();
                echo "Error: " . $stmt_tricycle->errorInfo()[2];
            }
        } else {
            // If the user insertion fails, rollback the transaction
            $pdo->rollBack();
            echo "Error: " . $stmt->errorInfo()[2];
        }
    } catch (PDOException $e) {
        // Rollback the transaction on any error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    // If the request method is not POST, redirect or show an error
    header('Location: user-management.php'); // Adjust the redirect as needed
    exit();
}
?>
