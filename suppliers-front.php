<?php 
include 'components/sidebar.php'; 
require 'connections/conx.php'; // Adjust the path if necessary
include 'components/header-admin.php';
var_dump($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="css-pages/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<title>Suppliers</title>

</head>
<body>
	
	<div class="table-wrapper">
		<h2 class="">Suppliers</h2>
	<div class="supplier-header">
		
		
		<!-- Check if the user is staff or admin to show the "Add New Supplier" button -->
		<?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Staff')): ?>
			<button class="add-button" data-toggle="modal" data-target="#AddSupplier">Add New Supplier</button>
		<?php endif; ?>

		
	<!-- Table part -->
	<div class="supplier-table">
		<table class="table">
			<thead >
				<tr>
					<th scope="col">Supplier ID</th>
					<th scope="col">Supplier Name</th>
					<th scope="col">Supplier Contact No.</th>
					<th scope="col">Supplier Location</th>
				</tr>
			</thead>
			<tbody>
				<?php // Fetch suppliers from the database
				try {
				    $query = "SELECT * FROM supplier";
				    $stmt = $pdo->query($query);
				    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
				    echo "Error: " . $e->getMessage();
				} ?>
				<?php if (!empty($suppliers)): ?>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($supplier['supplier_id']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['contact_info']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['location']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No suppliers found</td>
                    </tr>
                <?php endif; ?>
			</tbody>
		</table>
	</div>

		<!-- Modal part -->
		<div class="modal fade" id="AddSupplier" tabindex="-1" role="dialog" aria-labelledby="AddSupplierLabel" aria-hidden="true">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <h5 class="modal-title" id="AddSupplierLabel">Add New Supplier</h5>
		       
		          
		        </button>
		      </div>
		       <form method="post" action="suppliers-back.php">
		      	<div class="modal-body">
		      		<div class="form-group">
		      		  <label>Supplier Name</label>
		      		  <input type="text" name="supplier_name" required>
		      		</div>
		      		<div class="form-group">
		      		 <label>Contact No.</label>
		      		 <input type="text" name="contact_info" required>
		      		</div>	
		      		<div class="form-group">
		      			<label>Location</label>
		      			<input type="text" name="location" required>
		      		</div> 
		      		 <div class="form-group">
			            <label>Email</label>
			            <input type="email" name="email" required>
			        </div>
			        <div class="form-group">
			            <label>Password</label>
			            <input type="password" name="password" required>
			        </div>      		       	
		       </div>
		      <div class="modal-footer">
		        <button type="submit" class="btn btn-primary">Save changes</button>
		        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		      </div>
		       </form>		      
		    </div>
		  </div>
		</div>
	</div>
	</div>


	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>
