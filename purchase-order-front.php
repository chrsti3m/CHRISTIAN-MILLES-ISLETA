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


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">
    <title></title>
</head>
<body style="background: #FAFAFA;">

<div class="parent-wrapper">
    <div class="wrapper-header">
    <h3>Purchase Order List</h3>

    <!-- Button to trigger the modal -->
    <button type="button" class="button" data-bs-toggle="modal" data-bs-target="#AddPurchaseOrderModal">
        Add New Purchase Order
    </button>
</div>

<div class="container-wrapper">
    <!-- Fetching all purchase orders except 'Order Complete' from the database -->
    <?php
    // Modify the query to include the banana type name from the banana_type table and order by order date
    $orderStmt = $pdo->query("
        SELECT 
            purchase_order.purchase_order_id, 
            purchase_order.order_date, 
            purchase_order.order_status, 
            purchase_order.delivery_date, 
            purchase_order.quantity_ordered, 
            purchase_order.total_cost, 
            banana_type.type_name AS banana_type_name
        FROM 
            purchase_order 
        JOIN 
            banana_type 
        ON 
            purchase_order.banana_type_id = banana_type.banana_type_id
        WHERE 
            purchase_order.order_status != 'Order Complete'
        ORDER BY 
            purchase_order.order_date ASC  -- This orders the records in FIFO manner
    ");
    $orderRecords = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orderRecords as $order) {
        // Get the current order status before using it in the button
        $currentStatus = $order['order_status'];
        ?>
        <div class="order-tracking-container">
            <!-- Order Details -->
            <div class="order-details">
            <p class="purchase-order">
                Purchase Order ID: <strong><?php echo $order['purchase_order_id']; ?></strong>
            </p>
            
            <div class="button-group">
                <button class="view-button"
                    data-toggle="modal"
                    data-target="#orderModal"
                    data-quantity="<?php echo $order['quantity_ordered']; ?>"
                    data-cost="<?php echo $order['total_cost']; ?>"
                    data-banatype="<?php echo $order['banana_type_name']; ?>">
                    View Order
                </button>

                <button class="received-button"
                    <?php echo $currentStatus === 'Order Dropped Off' ? '' : 'disabled'; ?>
                    onclick="markAsReceived(<?php echo $order['purchase_order_id']; ?>)">
                    Receive
                </button>
            </div>
        </div>


            <div class="order-dates">
                <span>Order Date: <strong><?php echo $order['order_date']; ?></strong></span>             
                <span class="delivery-date">Expected Date for Delivery: <strong><?php echo $order['delivery_date']; ?></strong></span>
            </div>

            <div class="steps">
                <?php
                // Define the statuses
                $statuses = [
                    'Order Placed',
                    'Preparing Order',
                    'Order Loaded',
                    'Order Shipped',
                    'Order Dropped Off'
                ];

                // Find the index of the current status in the statuses array
                $currentIndex = array_search($currentStatus, $statuses);
                $totalSteps = count($statuses);

                // Loop through each status and determine if it should be active
                foreach ($statuses as $index => $status) {
                    $isActive = ($index <= $currentIndex) ? 'active' : '';
                    echo "<div class='step-item'>";
                    echo "<span class='circle $isActive'>" . ($index + 1) . "</span>";
                    echo "<span class='status-label'>$status</span>";
                    echo "</div>";
                }
                ?>
                <!-- Progress Bar -->
                <div class="progress-bar">
                    <span class="indicator" style="width: <?php echo ($currentIndex / ($totalSteps - 1)) * 100; ?>%;"></span>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<!-- Modal Structure -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #FFBF00;">
                <h5 class="modal-title" id="orderModalLabel">Order Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </button>
            </div>
            <div class="modal-body">
                <!-- Quantity Ordered -->
                <div class="mb-3">
                    <label for="modalQuantity" class="form-label">Quantity Ordered:</label>
                    <strong id="modalQuantity" class="d-block">0</strong>
                </div>
                <!-- Total Cost -->
                <div class="mb-3">
                    <label for="modalTotalCost" class="form-label">Total Cost:</label>
                    <strong id="modalTotalCost" class="d-block">PHP: 0.00</strong>
                </div>
                <!-- Banana Type -->
                <div class="mb-3">
                    <label for="modalBananaType" class="form-label">Banana Type:</label>
                    <strong id="modalBananaType" class="d-block">N/A</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<?php
// Fetch only one supplier from the database
$supplierStmt = $pdo->query("SELECT supplier_id, supplier_name FROM supplier LIMIT 1");
$supplier = $supplierStmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- Modal Structure -->
<div class="modal fade" id="AddPurchaseOrderModal" tabindex="-1" aria-labelledby="AddPurchaseOrderLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="AddPurchaseOrderLabel">Add Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="purchase-order-back.php">
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Supplier:</label>
                        <div class="col-sm-8">
                            <strong><?php echo htmlspecialchars($supplier['supplier_name']); ?></strong>
                            <input type="hidden" name="supplier_id" value="<?php echo $supplier['supplier_id']; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Banana Type:</label>
                        <div class="col-sm-8">
                            <select class="form-select" name="banana_type" id="banana_type" onchange="updatePrice(this)">
                                <option value="" disabled selected>Select Banana Type</option>
                                <!-- Options will be populated dynamically based on selected supplier -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Banana Price:</label>
                        <div class="col-sm-8">
                            <strong>PHP: </strong><strong id="banana_price">0.00</strong>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Quantity (KL):</label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control" name="quantity_ordered" id="quantity_ordered" oninput="calculateTotalCost()">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label">Total (PHP):</label>
                        <div class="col-sm-8">
                            <strong id="total_cost">0.00</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle (including Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let costPerUnit = 0; // Global variable to store the current cost per unit

// Function to fetch banana types for the selected supplier
function fetchBananaTypes(supplierId) {
    if (supplierId === "") {
        document.getElementById("banana_type").innerHTML = "<option value=''>Select Banana Type</option>";
        // Reset price and total cost when no supplier is selected
        document.getElementById('banana_price').innerText = '0.00 PHP';
        document.getElementById('total_cost').innerText = '0.00 PHP';
        return;
    }

    console.log("Supplier ID: ", supplierId); // Debugging log

    // Send an AJAX request to fetch banana types for the selected supplier
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_banana_types.php?supplier_id=" + supplierId, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var bananaTypes = JSON.parse(xhr.responseText);
            console.log(bananaTypes); // Debugging log
            var bananaTypeSelect = document.getElementById("banana_type");
            bananaTypeSelect.innerHTML = "<option value='' disabled selected>Select Banana Type</option>"; // Reset options

            if (Array.isArray(bananaTypes) && bananaTypes.length > 0) {
                bananaTypes.forEach(function (banana) {
                    var option = document.createElement("option");
                    option.value = banana.banana_type_id; // banana_type_id matches the correct key
                    option.text = banana.type_name; // Correct key to display the name
                    option.setAttribute("data-cost", banana.cost_per_unit); // Set cost as a data attribute
                    bananaTypeSelect.appendChild(option);
                });
            } else {
                bananaTypeSelect.innerHTML = "<option value=''>No Banana Types Available</option>";
            }
        } else if (xhr.readyState == 4) {
            console.error('Error fetching banana types:', xhr.statusText);
            console.error('Response text:', xhr.responseText); // Log the response text if there is an error
        }
    };
    xhr.send();
}
$(document).ready(function() {
    $('.view-button').click(function() {
        var quantity = $(this).data('quantity');
        var cost = $(this).data('cost');
        var bananaType = $(this).data('banatype'); // Fetch banana type name

        // Update modal fields
        $('#modalQuantity').text(quantity);
        $('#modalTotalCost').text('PHP: ' + cost);
        $('#modalBananaType').text(bananaType); // Update banana type

        $('#orderModal').modal('show');
    });
});


// Function to update the banana price and set costPerUnit based on the selected banana type
function updatePrice(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];

    // Check if a banana type is selected and it has a data-cost attribute
    if (selectedOption && selectedOption.value) {
        costPerUnit = parseFloat(selectedOption.getAttribute('data-cost')); // Get cost per unit from data attribute
        document.getElementById('banana_price').innerText = costPerUnit.toFixed(2) + ' PHP';
    } else {
        // If no banana type is selected, set price to 0.00 PHP
        costPerUnit = 0; // Reset costPerUnit to 0
        document.getElementById('banana_price').innerText = '0.00 PHP';
    }

    calculateTotalCost(); // Recalculate total cost when price is updated
}

// Function to calculate the total cost based on quantity and costPerUnit
function calculateTotalCost() {
    let quantityInput = document.getElementById('quantity_ordered').value;
    let quantity = parseFloat(quantityInput);
    
    if (!isNaN(quantity) && quantity > 0 && costPerUnit > 0) {
        let totalCost = quantity * costPerUnit;
        document.getElementById('total_cost').innerText = totalCost.toFixed(2) + ' PHP';
    } else {
        document.getElementById('total_cost').innerText = '0.00 PHP';
    }
}

// Add event listener for banana type change
document.getElementById("banana_type").addEventListener("change", function() {
    updatePrice(this); // Pass the current select element
});

// Add event listener for quantity change to recalculate total cost
document.getElementById("quantity_ordered").addEventListener("input", function() {
    calculateTotalCost(); // Recalculate the total cost when the quantity changes
});

// Fetch banana types on modal open
document.addEventListener('DOMContentLoaded', function() {
    const supplierId = '<?php echo $supplier['supplier_id']; ?>'; // Get the supplier ID from PHP
    fetchBananaTypes(supplierId); // Fetch banana types for the fixed supplier
});


function markAsReceived(purchaseOrderId) {
    // Get the necessary data for the inventory
    let quantityOrdered = 0; // You'll need to retrieve this value
    let deliveryDate = ''; // You'll need to retrieve this value
    let expirationDate = ''; // This will be calculated

    // Make an AJAX request to your PHP script
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "add_to_inventory.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                alert('Inventory updated successfully!');
                // Optionally, refresh the page or update the UI accordingly
                location.reload(); // Reloads the page to reflect changes
            } else {
                alert('Error updating inventory: ' + xhr.responseText);
            }
        }
    };

    // Prepare the data to send
    xhr.send("purchase_order_id=" + purchaseOrderId + "&quantity_ordered=" + quantityOrdered + "&delivery_date=" + deliveryDate);
}

function clearModalFields() {
        const bananaTypeSelect = document.getElementById('banana_type');
        if (bananaTypeSelect) {
            bananaTypeSelect.selectedIndex = 0; // Set to the default "Select Banana Type" option
        }

        const bananaPriceDisplay = document.getElementById('banana_price');
        if (bananaPriceDisplay) {
            bananaPriceDisplay.textContent = '0.00'; // Reset to 0.00
        }

        const quantityOrderedInput = document.getElementById('quantity_ordered');
        if (quantityOrderedInput) {
            quantityOrderedInput.value = ''; // Clear the input
        }

        const totalCostDisplay = document.getElementById('total_cost');
        if (totalCostDisplay) {
            totalCostDisplay.textContent = '0.00'; // Reset to 0.00
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('AddPurchaseOrderModal');
        modal.addEventListener('hidden.bs.modal', clearModalFields);
    });



</script>


</body>
</html>
