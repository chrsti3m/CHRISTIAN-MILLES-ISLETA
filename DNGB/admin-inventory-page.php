<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);  
include 'components/sidebar.php';
require 'connections/conx.php'; 
include 'components/header-admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

var_dump($_SESSION['user_id']);


// Check if there's an alert message in the session
if (isset($_SESSION['alert_message'])) {
    echo "<script>
        alert('{$_SESSION['alert_message']}');
    </script>";
    // Clear the message after displaying it
    unset($_SESSION['alert_message']);
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>	</title>
	<link rel="stylesheet" type="text/css" href="css-pages/style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<style type="text/css">
		 .custom-modal-width {
            max-width: 60% !important; /* Change this to your desired width */
            width: auto !important; /* Allows for responsive sizing */
        }
        th {
            white-space: nowrap; /* Prevent header text from wrapping */
        } .custom-modal-width {
            max-width: 60	% !important; /* Change this to your desired width */
            width: auto !important; /* Allows for responsive sizing */
        }
        th {
            white-space: nowrap; /* Prevent header text from wrapping */
        }


	</style>
</head>
<body style="background: #FAFAFA;">

	<div class="inventory-wrapper">
		 <div class="inventory-header">
        <h3>Inventory</h3>
        <div class="button-group">
            <!-- Allocate Stock Button -->
            <button type="button" class="allocate-button" data-bs-toggle="modal" data-bs-target="#allocateStockModal">
                Allocate Stock to Tricycle
            </button>
            <button type="button" class="allocated-button" data-bs-toggle="modal" data-bs-target="#allocationHistoryModal">
                Allocation History
            </button>
        </div>
    </div>
		<table>
			<thead>
				<th>Product ID</th>
                <th>Banana Type</th>
                <th>Stock</th>
                <th>Receive Date</th>
                <th>Expiration Date</th>
                <th>Storage Location</th>
			</thead>
			<tbody>
				<?php
            // SQL query to fetch inventory with banana types and suppliers
           $query = "
    SELECT 
        bt.banana_type_id,
        bt.type_name AS banana_type,
        SUM(i.quantity_in_stock) AS total_quantity_in_stock, -- Sum of the stock for same banana type
        MIN(i.receive_date) AS earliest_receive_date, -- Assuming you want the earliest receive date
        MAX(i.expiration_date) AS latest_expiration_date, -- Assuming you want the latest expiration date
        i.storage_location -- You can adjust this to fit how you manage locations for grouped items
    FROM 
        inventory i
    JOIN 
        supplier s ON i.supplier_id = s.supplier_id
    JOIN 
        banana_type bt ON i.banana_type_id = bt.banana_type_id
    JOIN 
        purchase_order po ON i.purchase_order_id = po.purchase_order_id
    WHERE 
        po.order_status = 'Order Complete'
    GROUP BY 
        bt.banana_type_id, bt.type_name, i.storage_location -- Group by the banana type and storage location
        HAVING 
        total_quantity_in_stock > 0 -- Only include banana types with stock
    ORDER BY 
        bt.type_name ASC;";  // You can order by banana type name if needed



           $stmt = $pdo->query($query);
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($inventory as $item) {
        echo "<tr>
                <td>{$item['banana_type_id']}</td>
                <td>{$item['banana_type']}</td>
                <td>{$item['total_quantity_in_stock']} Kg</td>
                <td>{$item['earliest_receive_date']}</td>
                <td>{$item['latest_expiration_date']}</td>
                <td>{$item['storage_location']}</td>
              </tr>";
    }
            ?>
			</tbody>
		</table>
	</div>
	
<!-- Modal for Allocating Stock --><div class="modal fade" id="allocateStockModal" tabindex="-1" aria-labelledby="allocateStockModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-width">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="allocateStockModalLabel">Allocate Stock to Tricycle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="allocate_stock.php" method="POST">
          <!-- Tricycle Selector -->
          <div class="mb-3">
            <label for="tricycle_id" class="form-label">Select Tricycle</label>
            <select class="form-control" id="tricycle_id" name="tricycle_id" required>
              <option value="" disabled selected>Select a Tricycle</option>
              <?php
                // Query to get tricycle data from the tricycle table
                $tricycleQuery = "SELECT tricycle_id, location FROM tricycle";
                $tricycleStmt = $pdo->query($tricycleQuery);
                $tricycles = $tricycleStmt->fetchAll(PDO::FETCH_ASSOC);

                // Loop through the tricycles and create options
                foreach ($tricycles as $tricycle) {
                    $formatted_tricycle_id = 'TR-' . $tricycle['tricycle_id']; // Format the tricycle ID as TR-1
                    echo "<option value='{$tricycle['tricycle_id']}'>{$formatted_tricycle_id}</option>";
                }
              ?>
            </select>
          </div>

          <!-- Banana Type Selector -->
          <div class="mb-3">
            <label for="banana_type_id" class="form-label">Select Banana Type</label>
            <select class="form-control" id="banana_type_id" name="banana_type_id" required>
              <option value="" disabled selected>Select a Banana Type</option>
              <?php
                // Query to fetch banana types from the inventory (join with banana_type)
                $bananaTypeQuery = "
					SELECT DISTINCT bt.banana_type_id, bt.type_name, sb.cost_per_unit
					FROM inventory i
					JOIN banana_type bt ON i.banana_type_id = bt.banana_type_id
					JOIN supplier_banana sb ON bt.banana_type_id = sb.banana_type_id
					WHERE i.quantity_in_stock > 0";  // Only show banana types that have stock

					$bananaTypeStmt = $pdo->query($bananaTypeQuery);
					$bananaTypes = $bananaTypeStmt->fetchAll(PDO::FETCH_ASSOC);

                // Loop through the banana types and create options
                foreach ($bananaTypes as $bananaType) {
                    echo "<option value='{$bananaType['banana_type_id']}' data-cost='{$bananaType['cost_per_unit']}'>{$bananaType['type_name']}</option>";

                    // Add this to check what data-cost values are being rendered
					
                }
              ?>
            </select>
          </div>

          <!-- Cost Per Unit (display only) -->
          <p>Cost Per Unit: <strong id="cost_per_unit_display">N/A</strong></p>

          <!-- Quantity Allocated -->
          <div class="mb-3">
            <label for="quantity_allocated" class="form-label">Quantity Allocated (Kg)</label>
            <input type="number" step="0.01" class="form-control" id="quantity_allocated" name="quantity_allocated" required>
          </div>

          <!-- Selling Price per Kilo -->
          <div class="mb-3">
            <label for="selling_price_per_kilo" class="form-label">Selling Price per Kilo</label>
            <input type="number" step="0.01" class="form-control" id="selling_price_per_kilo" name="selling_price_per_kilo" required>
          </div>

          <!-- Hidden User ID -->
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Allocate Stock</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<?php
// Fetch Allocation History Data
$allocationHistoryQuery = "
    SELECT 
        ti.tric_inventory_id,
        CONCAT('TR-', ti.tricycle_id) AS tricycle_id,
        bt.type_name AS banana_type,
        ti.quantity_allocated,  -- Fetch the current allocated quantity
        ti.selling_price_per_kilo,
        ti.date_allocated
    FROM 
        tricycle_inventory ti
    JOIN 
        inventory i ON ti.inventory_id = i.inventory_id
    JOIN 
        banana_type bt ON i.banana_type_id = bt.banana_type_id
    WHERE 
        ti.date_allocated IS NOT NULL
    ORDER BY 
        ti.date_allocated DESC";  // Fetch the allocations from oldest to newest

$allocationHistoryStmt = $pdo->query($allocationHistoryQuery);
$allocationHistory = $allocationHistoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize an array to store initial quantities (with tric_inventory_id as key)
$initialQuantities = [];
?>

<!-- Modal for Allocation History -->
<div class="modal fade" id="allocationHistoryModal" tabindex="-1" aria-labelledby="allocationHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog custom-modal-width"> <!-- Added custom class for width -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allocationHistoryModalLabel">Allocation History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;"> <!-- Fixed height and scrolling -->
                <table class="table table-bordered" style="width: auto; table-layout: auto;">
                    <thead>
                        <tr>
                            <th>Allocation ID</th>
                            <th>Tricycle</th>
                            <th>Banana Type</th>
                            <th>Quantity Allocated (Kg)</th>
                            <th>Selling Price per Kilo</th>
                            <th>Date Allocated</th>
                        </tr>
                    </thead>
                    <tbody id="allocationHistoryBody">
                        <?php 
                        // Check if there is any allocation history data
                        if (count($allocationHistory) > 0) {
                            foreach ($allocationHistory as $allocation) {
                                // Use the tric_inventory_id as key for initial quantity
                                $tricInventoryId = $allocation['tric_inventory_id'];
                                
                                // Check if this is the first time we're seeing this allocation
                                if (!isset($initialQuantities[$tricInventoryId])) {
                                    // Store the first allocated quantity
                                    $initialQuantities[$tricInventoryId] = $allocation['quantity_allocated'];
                                }

                                // Get the initial quantity from the array
                                $initialQuantity = $initialQuantities[$tricInventoryId];

                                echo "<tr>
                                        <td>{$allocation['tric_inventory_id']}</td>
                                        <td>{$allocation['tricycle_id']}</td>
                                        <td>{$allocation['banana_type']}</td>
                                        <td>{$initialQuantity} Kg</td>
                                        <td>{$allocation['selling_price_per_kilo']}</td>
                                        <td>" . date('Y-m-d', strtotime($allocation['date_allocated'])) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No allocation history available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<?php  
// Query to fetch Tricycle IDs from the tricycle_inventory table
$query = "SELECT DISTINCT tricycle_id FROM tricycle_inventory";
$stmt = $pdo->prepare($query);
$stmt->execute();
$tricycles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="trike-inventory-wrapper">
    <div class="trike-inventory-header">
        <h3>Tricycle Inventory</h3>
        <div class="trike-selector-wrapper">			
            <select class="trike-selector" id="tricycle-selector">
                <option value="">Select Tricycle</option>
                <?php foreach ($tricycles as $tricycle): ?>
                    <option value="<?= $tricycle['tricycle_id'] ?>">TR - <?= $tricycle['tricycle_id'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <table id="inventory-table">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Banana Type</th>
                    <th>Stock (Kilos)</th>
                    <th>Price/Kilo</th>
                    <th>Date Allocated</th>
                    <th>Expiration Date</th>
                </tr>
            </thead>
            <tbody id="inventory-table-body">
                <!-- Data will be populated here -->
            </tbody>
        </table>
</div>

<script>
	

    document.getElementById('tricycle-selector').addEventListener('change', function() {
    var tricycleId = this.value;
    if (tricycleId) {
        // Fetch data from the server based on the selected tricycle ID
        fetch(`get_tricycle_inventory.php?tricycle_id=${tricycleId}`)
            .then(response => response.json())  // Parse JSON response
            .then(data => {
                console.log('Fetched Data:', data);  // Debugging log

                var tableBody = document.getElementById('inventory-table-body');
                tableBody.innerHTML = ''; // Clear previous entries

                if (data.error) {
                    // Handle errors sent from the server
                    var row = document.createElement('tr');
                    row.innerHTML = `<td colspan="6">${data.error}</td>`;
                    tableBody.appendChild(row);
                } else if (data.length === 0) {
                    // If no inventory data is found
                    var row = document.createElement('tr');
                    row.innerHTML = '<td colspan="6">No inventory found for this Tricycle ID.</td>';
                    tableBody.appendChild(row);
                } else {
                    // Populate table with fetched data
                    data.forEach(item => {
                        var row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.prod_id}</td>
                            <td>${item.banana_type}</td>
                            <td>${item.total_allocated} Kg</td>
                            <td>${item.selling_price}</td>
                            <td>${item.earliest_allocated_date}</td>
                            <td>${item.latest_expiration_date}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    } else {
        // Clear the table if no Tricycle ID is selected
        document.getElementById('inventory-table-body').innerHTML = '';
    }
});
   document.getElementById('banana_type_id').addEventListener('change', function () {
    // Get the selected option
    const selectedOption = this.options[this.selectedIndex];
    
    // Get the cost per unit from the data-cost attribute
    const costPerUnit = selectedOption.getAttribute('data-cost');
    
    // Debug log
   
    
    // Display the cost per unit in the cost_per_unit_display element
    document.getElementById('cost_per_unit_display').textContent = costPerUnit ? `${costPerUnit} PHP` : 'N/A';
});


</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>