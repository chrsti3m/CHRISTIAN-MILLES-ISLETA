<?php 
require 'connections/conx.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

var_dump($_SESSION['user_id']);

// Check if the user is logged in and has the Supplier role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Supplier') {
    header("Location: login.html"); // Redirect to login if not authorized
    exit();
}
 ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product Modal</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">
  
  
   
</head>
<body style="background: #FAFAFA;">
    <?php include 'components/sidebar.html';
    include 'components/header-supplier.php'; ?>


    <!-- Modal -->
	<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <div class="modal-header">
	                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
	                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	            </div>
	            <div class="modal-body">
	                <form id="addProductForm" method="post" action="suppliers-product-back.php">
	                	<input type="hidden" name="supplier_id" value="<?php echo $_SESSION['user_id']; ?>">
	                    <div class="mb-3">
	                        <label for="bananaType" class="form-label">Banana Type</label>
	                        <input type="text" class="form-control" id="bananaType" name="banana_type" required>
	                    </div>
	                    <div class="mb-3">
	                        <label for="costPerUnit" class="form-label">Cost Per Unit</label>
	                        <input type="number" class="form-control" id="costPerUnit" name="cost_per_unit" step="0.01" required>
	                    </div>
	                    <div class="modal-footer">
	                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
	                        <button type="submit" class="btn btn-primary">Save changes</button> <!-- Change here -->
	                    </div>
	                </form>
	            </div>
	        </div>
	    </div>
	</div>

	<div class="product-wrapper">
    <main class="table">
        <section class="table_header">
            <h2>Product List</h2>
            <button type="button" class="add-product-button" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>
        </section>
        
        <section class="table_body">
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Banana Type</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch banana types and their cost per unit from the database
                     // Fetch banana types and their cost per unit from the database
             try {
				    // Fetch supplier_id based on the logged-in user (user_id from session)
				    $query = "SELECT supplier_id FROM supplier WHERE user_id = :user_id";
				    $stmt = $pdo->prepare($query);
				    $stmt->execute(['user_id' => $_SESSION['user_id']]);
				    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

				    if ($supplier) {
				        // Now fetch banana types for this supplier_id
				        $supplier_id = $supplier['supplier_id'];

				        $query = "
				            SELECT bt.banana_type_id, bt.type_name, sb.cost_per_unit
				            FROM supplier_banana sb
				            INNER JOIN banana_type bt ON sb.banana_type_id = bt.banana_type_id
				            WHERE sb.supplier_id = :supplier_id";

				        $stmt = $pdo->prepare($query);
				        $stmt->execute(['supplier_id' => $supplier_id]);
				        $banana_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

				        if (!$banana_types) {
				            echo "No banana types found for supplier ID: " . htmlspecialchars($supplier_id);
				        }
				    } else {
				        echo "No supplier found for user ID: " . htmlspecialchars($_SESSION['user_id']);
				    }
				} catch (PDOException $e) {
				    echo "Error: " . htmlspecialchars($e->getMessage());
				}
                    ?>
                    <?php if (!empty($banana_types)): ?>
                        <?php foreach ($banana_types as $banana_type): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($banana_type['banana_type_id']); ?></td>
                                <td><?php echo htmlspecialchars($banana_type['type_name']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($banana_type['cost_per_unit'], 2)); ?></td>
                                <td>
                                    <button class="edit-button">Edit</button>
                                    <button class="delete-button">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No banana types found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>




    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>
</html>
