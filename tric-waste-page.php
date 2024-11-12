<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if it's not already started
} // Add this to ensure sessions are started before accessing $_SESSION
require 'connections/conx.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1); 

if (!isset($_SESSION['tricycle_id'])) {
    die('Tricycle ID not set in session');
}
$tricycle_id = $_SESSION['tricycle_id']; 
$currentDate = date('Y-m-d');

// Fetch the user ID for the logged-in 'Tricycle Operator'
$queryUser = "SELECT user_id FROM user WHERE role = 'Tricycle Operator' LIMIT 1"; 
$stmtUser = $pdo->prepare($queryUser);
$stmtUser->execute();
$operator = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$operator) {
    die('No Tricycle Operator found for this session.');
}

$user_id = $operator['user_id'];

// Fetch distinct banana types
$queryBananaTypes = "SELECT DISTINCT bt.banana_type_id, bt.type_name 
                     FROM banana_type bt 
                     JOIN tricycle_inventory ti ON bt.banana_type_id = ti.banana_type_id 
                     WHERE ti.tricycle_id = :tricycle_id";

$stmtBananaTypes = $pdo->prepare($queryBananaTypes);
$stmtBananaTypes->bindParam(':tricycle_id', $tricycle_id);
$stmtBananaTypes->execute();
$bananaTypes = $stmtBananaTypes->fetchAll(PDO::FETCH_ASSOC);

// Initialize an empty variable to store unsold bananas
$unsoldBananas = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['banana_type_id'])) {
    $selectedBananaType = $_POST['banana_type_id'];

    if (!empty($selectedBananaType)) {
        // Fetch unsold bananas for the selected banana type
        $query = "SELECT ti.tric_inventory_id, ti.quantity_allocated, DATEDIFF(ti.allocated_unit_expiration, :current_date) AS days_before_expiration 
          FROM tricycle_inventory ti 
          WHERE ti.tricycle_id = :tricycle_id 
          AND ti.banana_type_id = :banana_type_id 
          AND ti.quantity_allocated > 0"; // Only select records with quantity_allocated > 0


        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':tricycle_id', $tricycle_id);
        $stmt->bindParam(':banana_type_id', $selectedBananaType);
        $stmt->bindParam(':current_date', $currentDate);
        $stmt->execute();
        $unsoldBananas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
$currentDateFormatted = date('F j, Y');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css-pages/style.css"> <!-- Ensure this file exists and is correctly linked -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="">
    <script src="https://kit.fontawesome.com/6d226ceedb.js" crossorigin="anonymous"></script>
    <title>Unsold Banana</title>
</head>
<body>
   

    <div class="trike-waste-wrapper">
        <div class="trike-waste-header">
            <h3>Unsold Banana</h3>
            <span class="current-date">Current Date: <strong><?= $currentDateFormatted ?></strong></span>
        </div>

        <!-- Alert Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); // Clear the message after displaying it ?>
        <?php endif; ?>

        <form method="POST" action="">
            <select name="banana_type_id" id="banana_type_id" onchange="this.form.submit()">
                <option value="">Select Banana Type</option>
                <?php foreach ($bananaTypes as $bananaType): ?>
                    <option value="<?= htmlspecialchars($bananaType['banana_type_id']) ?>" 
                        <?= (isset($selectedBananaType) && $selectedBananaType == $bananaType['banana_type_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($bananaType['type_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <table class="unsold-table">
            <thead>
                <th>Batch ID</th>
                <th>Quantity</th>
                <th>Days Before Expiration</th>
                <th>Action</th>
            </thead>
            <tbody id="unsold_table_body">
                <?php if (!empty($unsoldBananas)): ?>
                    <?php foreach ($unsoldBananas as $banana): ?>
                        <tr>
                            <td><?= htmlspecialchars($banana['tric_inventory_id']) ?></td>
                            <td><?= htmlspecialchars($banana['quantity_allocated']) . " Kg" ?></td>
                            <td><?= htmlspecialchars($banana['days_before_expiration']) ?></td>
                            <td>
                                <button type="button" class="btn icon-button" data-bs-toggle="modal" data-bs-target="#wasteModal" 
                                data-id="<?= htmlspecialchars($banana['tric_inventory_id']) ?>"
                                data-quantity="<?= htmlspecialchars($banana['quantity_allocated']) ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No unsold bananas available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

   <!-- Modal -->
<div class="modal fade" id="wasteModal" tabindex="-1" aria-labelledby="wasteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wasteModalLabel">Record Banana Waste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
    <form id="wasteForm">
        <input type="hidden" name="tric_inventory_id" id="tric_inventory_id" value="">
        <div class="mb-3">
            <label for="waste_quantity" class="form-label">Quantity Wasted (Kg)</label>
            <input type="number" class="form-control" id="waste_quantity" name="waste_quantity" min="0" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="waste_reason" class="form-label">Reason for Waste</label>
            <select class="form-select" id="waste_reason" name="waste_reason" required>
                <option value="">Select Reason</option>
                <option value="Spoilage">Spoilage</option>
                <option value="Damage">Damage</option>
                <option value="Overripe">Overripe</option>
            </select>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</div>

        </div>
    </div>
</div>

<<script>
    // Populate modal fields with relevant data
    var wasteModal = document.getElementById('wasteModal');
    wasteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var batchId = button.getAttribute('data-id');
        var quantity = button.getAttribute('data-quantity');

        // Populate the hidden input field with tric_inventory_id
        var modalTricInventoryId = wasteModal.querySelector('#tric_inventory_id');
        modalTricInventoryId.value = batchId;

        // Populate the quantity wasted field with the quantity allocated
        var modalQuantityField = wasteModal.querySelector('#waste_quantity');
        modalQuantityField.value = ""; // Reset the input field
        modalQuantityField.max = quantity; // Set the max attribute to the allocated quantity
    });

    // Handle form submission via AJAX
    document.getElementById('wasteForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Gather form data
        var formData = new FormData(this);

        // Send AJAX request
        fetch('record_waste.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.text())
        .then(data => {
            // Show a success message or handle errors
            alert(data); // Display the server response
            $('#wasteModal').modal('hide'); // Hide the modal
            location.reload(); // Optionally reload the page to refresh data
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
