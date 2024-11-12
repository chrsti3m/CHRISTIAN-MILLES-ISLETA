<?php
require 'connections/conx.php'; // Adjust the path to your database connection file

// Start the session if it hasn't been started
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['supplier_id'])) {
    $supplier_id = $_POST['supplier_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete all products associated with the supplier
        $delete_products_query = "DELETE FROM supplier_banana WHERE supplier_id = ?";
        $delete_products_stmt = $pdo->prepare($delete_products_query);
        $delete_products_stmt->execute([$supplier_id]);

        // Archive the supplier
        $archive_supplier_query = "UPDATE supplier SET is_archived = 1 WHERE supplier_id = ?";
        $archive_supplier_stmt = $pdo->prepare($archive_supplier_query);
        $archive_supplier_stmt->execute([$supplier_id]);

        // Commit transaction
        $pdo->commit();

        // Redirect back to suppliers front page
        header('Location: suppliers-front.php');
        exit();
    } catch (PDOException $e) {
        // Rollback transaction in case of error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
