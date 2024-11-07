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

// Fetch the tricycle location based on the tricycle_id
$queryLocation = "SELECT location FROM tricycle WHERE tricycle_id = :tricycle_id"; 
$stmtLocation = $pdo->prepare($queryLocation);
$stmtLocation->bindParam(':tricycle_id', $tricycle_id, PDO::PARAM_INT);
$stmtLocation->execute();

$tricycle = $stmtLocation->fetch(PDO::FETCH_ASSOC);

if (!$tricycle) {
    die('No tricycle found for the given ID.');
}

$tricycle_location = $tricycle['location'];
error_log('Current Tricycle ID: ' . $tricycle_id); // Check your PHP error log for this output

// Fetch waste history with banana type by joining relevant tables
$queryWasteHistory = "
    SELECT w.waste_id, bt.type_name, w.quantity_wasted, w.waste_date, w.reason
    FROM waste w
    JOIN tricycle_inventory ti ON w.tricycle_inventory_id = ti.tric_inventory_id
    JOIN banana_type bt ON ti.banana_type_id = bt.banana_type_id
    WHERE ti.tricycle_id = :tricycle_id
";

// Check if a filter date is set
if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
    $filterDate = $_GET['filter_date'];
    $queryWasteHistory .= " AND w.waste_date = :filter_date"; // Add date filter to the query
}

$queryWasteHistory .= " ORDER BY w.waste_date DESC";

$stmtWasteHistory = $pdo->prepare($queryWasteHistory);
$stmtWasteHistory->bindParam(':tricycle_id', $tricycle_id, PDO::PARAM_INT);

// Bind the filter date parameter if it's set
if (isset($filterDate)) {
    $stmtWasteHistory->bindParam(':filter_date', $filterDate, PDO::PARAM_STR);
}

$stmtWasteHistory->execute();

$wasteHistory = $stmtWasteHistory->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css-pages/style.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Waste History</title>
</head>
<body style="background: #FAFAFA;">
    <?php include 'components/trike-sidebar.php'; ?>
    
    <div class="waste-history-wrapper">
        <div class="waste-history-header">
            <h3>Waste History</h3>
            <form method="GET" action="tric-waste-history.php" class="date-filter-form">
                <input type="date" name="filter_date" value="<?php echo isset($_GET['filter_date']) ? htmlspecialchars($_GET['filter_date']) : ''; ?>">
                <button type="submit" class="button-filter">Filter</button>
            </form>
        </div>
        <span class="location">Vendor Location: <strong><?php echo htmlspecialchars($tricycle_location); ?></strong></span>
        <table class="waste-history-table">
            <thead>
                <tr>
                    <th>Waste ID</th>
                    <th>Banana Type</th>
                    <th>Quantity Wasted</th>
                    <th>Waste Date</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($wasteHistory)): ?>
                    <?php foreach ($wasteHistory as $waste): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($waste['waste_id']); ?></td>
                            <td><?php echo htmlspecialchars($waste['type_name']); ?></td>
                            <td><?php echo htmlspecialchars($waste['quantity_wasted']); ?></td>
                            <td><?php echo htmlspecialchars($waste['waste_date']); ?></td>
                            <td><?php echo htmlspecialchars($waste['reason']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No waste records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
