<?php 
require 'connections/conx.php'; 
include 'components/header-tricycle.php';
// Start session and validate user and tricycle IDs
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$query = "
    SELECT 
        bt.banana_type_id, 
        bt.type_name, 
        MIN(ti.date_allocated) AS earliest_allocated_date,
        MIN(ti.allocated_unit_expiration) AS earliest_expiration_date,
        SUM(ti.quantity_allocated) AS total_stock,
        (
            SELECT ti2.selling_price_per_kilo 
            FROM tricycle_inventory ti2 
            WHERE ti2.banana_type_id = bt.banana_type_id 
              AND ti2.tricycle_id = :tricycle_id
              AND ti2.quantity_allocated > 0  -- Ensuring non-zero quantity
            ORDER BY ti2.date_allocated DESC  -- Select from the latest allocation
            LIMIT 1
        ) AS last_selling_price
    FROM 
        tricycle_inventory ti
    JOIN 
        banana_type bt ON ti.banana_type_id = bt.banana_type_id
    WHERE 
        ti.tricycle_id = :tricycle_id
        AND ti.quantity_allocated > 0  -- Ensuring non-zero quantity
    GROUP BY 
        bt.banana_type_id, bt.type_name
    ORDER BY 
        earliest_allocated_date ASC;
";




$stmt = $pdo->prepare($query);
$stmt->bindValue(':tricycle_id', $tricycle_id, PDO::PARAM_INT);
$stmt->execute();

$banana_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// // Debugging: Check if banana types were retrieved
// if (empty($banana_types)) {
//     echo 'No banana types found for the selected tricycle.';
// } else {
//     foreach ($banana_types as $type) {
//         echo 'Banana Type ID: ' . htmlspecialchars($type['banana_type_id']) . 
//              ', Name: ' . htmlspecialchars($type['type_name']) . 
//              ', Earliest Allocated Date: ' . htmlspecialchars($type['earliest_allocated_date']) . 
//              ', Total Stock: ' . htmlspecialchars($type['total_stock']) . 
//              ', Last Selling Price: ' . htmlspecialchars($type['last_selling_price']) . '<br>';
//     }
// }

// Initialize variables to avoid warnings
$banana_type_id = null; 
$latestSellingPrice = null; 
$quantity_sold = 0; 
$totalAmountSold = 0;

// Handle form submission (assuming this code is after the HTML form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the posted data
    $banana_type_id = $_POST['banana_type_id'] ?? null;
    $quantity_sold = $_POST['quantity-sold'] ?? 0;

    // Calculate total amount sold (example calculation)
    $latestSellingPrice = // fetch from the database based on the banana_type_id
    $totalAmountSold = $quantity_sold * $latestSellingPrice;

    // Log the information after setting the variables
    error_log("Banana Type ID: $banana_type_id");
    error_log("Price Retrieved: $latestSellingPrice");
    error_log("Inserting sale for Banana Type ID: $banana_type_id, Quantity Sold: $quantity_sold, Total Amount Sold: $totalAmountSold");
}

echo "Session Tricycle ID: " . htmlspecialchars($tricycle_id) . "<br>";
?>

<!-- Rest of the HTML... -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css-pages/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Sales</title>
</head>
<body style="background: #FAFAFA;">
    <?php include 'components/trike-sidebar.php'; ?>

   <div class="trike-sales-wrapper">
    <div class="trike-sales-header mb-4">
        <h3>Log Sales</h3>
        <p>Current Date: <strong id="current-date"></strong></p>
    </div>
    <form action="tric-sales-back.php" method="post">
        <!-- Banana Type Selection -->
        <div class="mb-3">
            <label for="banana_type_id" class="form-label">Banana Type:</label>
            <select id="banana_type_id" name="banana_type_id" class="form-select" onchange="updateFIFODate()">
                <option value="">-- Select Banana Type --</option>
                <?php foreach ($banana_types as $banana) : ?>
                    <option value="<?= htmlspecialchars($banana['banana_type_id']); ?>" 
                            data-date="<?= htmlspecialchars($banana['earliest_allocated_date']); ?>" 
                            data-expiration="<?= htmlspecialchars($banana['earliest_expiration_date']); ?>" 
                            data-stock="<?= htmlspecialchars($banana['total_stock']); ?>" 
                            data-price="<?= htmlspecialchars($banana['last_selling_price']); ?>">
                        <?= htmlspecialchars($banana['type_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="mt-2">Date Allocated: <strong id="allocated-date"></strong></p>
            <p>Current Stock: <strong id="current-stock"></strong></p>
            <p>Price/kg: <strong id="price-per-kilo"></strong></p>
        </div>

        <!-- Quantity Sold Input -->
        <div class="mb-3">
            <label for="quantity-sold" class="form-label">Enter Quantity Sold:</label>
            <input type="number" id="quantity-sold" name="quantity-sold" min="0" step="1" class="form-control" oninput="calculateTotal()">
            <label>Total: <strong id="total-price" class="ms-2">₱0.00</strong></label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="button">Submit</button>
    </form>
</div>

<div class="unsold-wrapper">
    <div class="unsold-header">
        <h3>Unsold Bananas</h3>
    </div>
    <table class="unsold-table"></table>
</div>

<?php   include 'tric-waste-page.php' ?>


div.trike-sales-wrapper 

<script>
function updateFIFODate() {
    const select = document.getElementById('banana_type_id');
    const allocatedDateElement = document.getElementById('allocated-date');
    const stockElement = document.getElementById('current-stock');
    const priceElement = document.getElementById('price-per-kilo');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption.value) {
        const allocatedDate = selectedOption.getAttribute('data-date');
        const expirationDate = selectedOption.getAttribute('data-expiration');
        const totalStock = parseFloat(selectedOption.getAttribute('data-stock')) || 0;
        const lastSellingPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;

        // Check if the total stock is zero, and prevent selection
        if (totalStock <= 0) {
            alert('This banana type has zero stock. Please select a different type.');
            select.value = ''; // Reset the selection
            allocatedDateElement.textContent = '';
            stockElement.textContent = '0.00';
            priceElement.textContent = '₱0.00';
            return;
        }

        // Proceed with the calculations if stock is available
        if (allocatedDate && expirationDate) {
            const allocated = new Date(allocatedDate);
            const expiration = new Date(expirationDate);
            const today = new Date();

            // Calculate days until expiration
            const daysToExpiration = Math.round((expiration - today) / (1000 * 60 * 60 * 24));

            allocatedDateElement.textContent = allocated.toLocaleString() + ` (Expires in ${daysToExpiration} days)`;
        } else {
            allocatedDateElement.textContent = allocatedDate ? new Date(allocatedDate).toLocaleString() : '';
        }

        stockElement.textContent = totalStock.toFixed(2);
        priceElement.textContent = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(lastSellingPrice);
    } else {
        // Reset the displayed values when no option is selected
        allocatedDateElement.textContent = '';
        stockElement.textContent = '0.00';
        priceElement.textContent = '₱0.00';
    }

    // Reset total price whenever the banana type is changed
    calculateTotal();
}


function calculateTotal() {
    const quantityInput = document.getElementById('quantity-sold');
    const priceElement = document.getElementById('price-per-kilo');
    const totalPriceElement = document.getElementById('total-price');

    const quantitySold = parseFloat(quantityInput.value) || 0;
    const pricePerKilo = parseFloat(priceElement.textContent.replace(/[^0-9.-]+/g, "")) || 0;

    const totalPrice = quantitySold * pricePerKilo;

    totalPriceElement.textContent = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(totalPrice);
}

// Display current date
const today = new Date();
const formattedDate = today.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});
document.getElementById('current-date').textContent = formattedDate;
</script>
</body>
</html>
