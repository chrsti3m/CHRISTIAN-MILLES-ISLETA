<?php 
require 'connections/conx.php';
error_reporting(E_ALL);
ini_set('display_errors', 1); 

// Start the session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user_id and tricycle_id are set in session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['tricycle_id'])) {
    die('User not logged in or Tricycle ID not set in session');
}

$user_id = $_SESSION['user_id'];
$tricycle_id = $_SESSION['tricycle_id']; // Use the stored tricycle_id in session
$currentDate = date('Y-m-d');

// Query to get banana types for the selector, grouped by banana_type_id
$sql_banana_types = "
    SELECT DISTINCT bt.banana_type_id, bt.type_name
    FROM tricycle_inventory ti
    INNER JOIN banana_type bt ON ti.banana_type_id = bt.banana_type_id
    WHERE ti.tricycle_id = :tricycle_id";

$stmt_banana_types = $pdo->prepare($sql_banana_types);
$stmt_banana_types->execute(['tricycle_id' => $tricycle_id]);
$banana_types = $stmt_banana_types->fetchAll(PDO::FETCH_ASSOC);

// Default query to get inventory based on selected banana type
$banana_type_filter = isset($_GET['banana_type_id']) ? $_GET['banana_type_id'] : '';

$sql = "
    SELECT 
        ti.tric_inventory_id,
        ti.quantity_allocated, 
        ti.date_allocated, 
        ti.allocated_unit_expiration,
        ti.inventory_id
    FROM 
        tricycle_inventory ti
    WHERE 
        ti.tricycle_id = :tricycle_id
        AND ti.quantity_allocated > 0";  // Skip records with zero quantity

// Filter by selected banana type if available
if (!empty($banana_type_filter)) {
    $sql .= " AND ti.banana_type_id = :banana_type_id";
}

$sql .= " ORDER BY ti.date_allocated ASC";  // FIFO sorting by allocation date

// Prepare and execute the query with dynamic filtering
$stmt = $pdo->prepare($sql);
$params = ['tricycle_id' => $tricycle_id];
if (!empty($banana_type_filter)) {
    $params['banana_type_id'] = $banana_type_filter;
}
$stmt->execute($params);

// Fetch results for inventory
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to get total stocks by banana type
$sql_total_stocks = "
    SELECT 
        bt.type_name,
        SUM(ti.quantity_allocated) AS total_quantity,
        (SELECT ti2.selling_price_per_kilo 
         FROM tricycle_inventory ti2 
         WHERE ti2.banana_type_id = ti.banana_type_id 
         AND ti2.tricycle_id = :tricycle_id 
         ORDER BY ti2.date_allocated DESC 
         LIMIT 1) AS last_price
    FROM 
        tricycle_inventory ti
    INNER JOIN banana_type bt ON ti.banana_type_id = bt.banana_type_id
    WHERE 
        ti.tricycle_id = :tricycle_id
        AND ti.quantity_allocated > 0  -- Filter records with positive quantities
    GROUP BY 
        ti.banana_type_id
    ORDER BY 
        bt.type_name";  // Order by banana type name
// Order by banana type name


$stmt_total_stocks = $pdo->prepare($sql_total_stocks);
$stmt_total_stocks->execute(['tricycle_id' => $tricycle_id]);
$total_stocks_results = $stmt_total_stocks->fetchAll(PDO::FETCH_ASSOC);

// Calculate total stocks for footer
$total_stocks = 0;
foreach ($total_stocks_results as $stock) {
    $total_stocks += $stock['total_quantity'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Inventory - FIFO</title>
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body style="background: #FAFAFA;">
<?php include 'components/trike-sidebar.php';
      include 'components/header-tricycle.php';
 ?>
    
    <div class="trike-inventory-wrapper">
        <div class="trike-inventory-header">
            <h3>Allocated Stocks</h3>
            <p>Current Date: <strong id="current-date"></strong></p>
        </div>

        <!-- Banana Type Selector -->
        <label for="banana-type-select">Banana Type: 
            <strong>
                <?php 
                if (!empty($banana_type_filter)) {
                    // Find the selected type name from the $banana_types array
                    foreach ($banana_types as $type) {
                        if ($type['banana_type_id'] == $banana_type_filter) {
                            echo $type['type_name'];
                            break;
                        }
                    }
                } else {
                    echo 'All Types'; // Default text if no type is selected
                }
                ?>
            </strong>
        </label>
        <form method="GET" action="">
            <select id="banana-type-select" name="banana_type_id" onchange="this.form.submit()">
                <option value="">All Types</option>
                <?php if ($banana_types): ?>
                    <?php foreach ($banana_types as $type): ?>
                        <option value="<?php echo $type['banana_type_id']; ?>" <?php echo ($banana_type_filter == $type['banana_type_id']) ? 'selected' : ''; ?>>
                            <?php echo $type['type_name']; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </form>

        <!-- Table for displaying FIFO inventory -->
        <table class="daily-inventory-table">
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Quantity (kg)</th>
                    <th>Allocated Date</th>
                    <th>Days Before Expiration</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): ?>
                    <?php foreach ($results as $row): ?>
                        <?php
                        // Get today's date
                        $current_date = new DateTime($currentDate);
                        $expiration_date = new DateTime($row['allocated_unit_expiration']);

                        // Calculate the remaining days before expiration from today's date
                        $days_remaining = $current_date->diff($expiration_date)->format('%a');

                        // If expiration is today or past, show it as 0 days
                        if ($expiration_date <= $current_date) {
                            $days_remaining = 0;
                        }
                        ?>
                        <tr>
                            <td><?php echo $row['tric_inventory_id']; ?></td>
                            <td><?php echo number_format($row['quantity_allocated'], 2); ?> kg</td>
                            <td><?php echo date('Y-m-d', strtotime($row['date_allocated'])); ?></td>
                            <td><?php echo $days_remaining . ' days'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No records found for today's inventory.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="total-stocks-wrapper">
    <div class="total-stocks-header">
        <h3>Total Stocks</h3>
    </div>
    <table class="table-total-stocks">
        <thead>
            <tr>
                <th>Banana Type</th>
                <th>Total Quantity</th>
                <th>Price/Kg</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($total_stocks_results): ?>
                <?php foreach ($total_stocks_results as $stock): ?>
                    <tr>
                        <td><?php echo $stock['type_name']; ?></td>
                        <td><?php echo number_format($stock['total_quantity'], 2); ?> kg</td>
                        <td><?php echo $stock['last_price'] !== null ? number_format($stock['last_price'], 2) : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">No total stocks available.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right;"><strong>Total Stocks:</strong></td>
                <td><strong><?php echo number_format($total_stocks, 2); ?> kg</strong></td>
            </tr>
        </tfoot>
    </table>
</div>


    <script>
        // Get the current date
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
