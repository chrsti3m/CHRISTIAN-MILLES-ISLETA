<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'connections/conx.php'; // Ensure you have the right path

try {
    // Get the selected view (weekly, monthly, or yearly)
    $view = isset($_GET['view']) ? $_GET['view'] : 'weekly';

    switch ($view) {
        case 'weekly':
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(sale_date) AS sale_date, 
                    SUM(total_amount_sold) AS total_sales
                FROM 
                    sales_transaction
                WHERE 
                    sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY 
                    sale_date
                ORDER BY 
                    sale_date ASC
            ");
            break;

        case 'monthly':
            $stmt = $pdo->prepare("
                SELECT 
                    DATE_FORMAT(sale_date, '%Y-%m') AS sale_month, 
                    SUM(total_amount_sold) AS total_sales
                FROM 
                    sales_transaction
                WHERE 
                    sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                GROUP BY 
                    sale_month
                ORDER BY 
                    sale_month ASC
            ");
            break;

        case 'yearly':
            $stmt = $pdo->prepare("
                SELECT 
                    DATE_FORMAT(sale_date, '%Y') AS sale_year, 
                    SUM(total_amount_sold) AS total_sales
                FROM 
                    sales_transaction
                WHERE 
                    sale_date >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)
                GROUP BY 
                    sale_year
                ORDER BY 
                    sale_year ASC
            ");
            break;

        default:
            throw new Exception("Invalid view selected");
    }

    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for the chart
    $salesDates = [];
    $totalSalesByDate = [];

    foreach ($salesData as $row) {
        if ($view == 'weekly') {
            $salesDates[] = $row['sale_date'];
        } elseif ($view == 'monthly') {
            $salesDates[] = $row['sale_month'];
        } elseif ($view == 'yearly') {
            $salesDates[] = $row['sale_year'];
        }

        $totalSalesByDate[] = (float) $row['total_sales'];
    }

    // Return data as JSON
    echo json_encode([
        'salesDates' => $salesDates,
        'totalSalesByDate' => $totalSalesByDate
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
