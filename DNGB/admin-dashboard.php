<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);  
include 'components/sidebar.php';
require 'connections/conx.php'; 
include 'components/header-admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

// Get today's date
$today = date('Y-m-d');

try {
    // Prepare a query to get the total sales for today's date
    $stmt = $pdo->prepare("SELECT SUM(total_amount_sold) AS total_sales FROM sales_transaction WHERE sale_date = :today");
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    
    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If there's no sale today, default to 0
    $todays_sales = $result['total_sales'] ?? 0;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

try {
    // Query to calculate total stock from both inventory tables
    $stmt = $pdo->prepare("
        SELECT 
            IFNULL(SUM(quantity_in_stock), 0) AS total_quantity
        FROM (
            SELECT quantity_in_stock FROM inventory WHERE quantity_in_stock > 0
            UNION ALL
            SELECT quantity_allocated FROM tricycle_inventory WHERE quantity_allocated > 0
        ) AS combined_inventory
    ");

    $stmt->execute();
    $total_quantity = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

try {
    // Prepare the query to select the earliest batch with a positive quantity
    $stmt = $pdo->prepare("
        SELECT 
            allocated_unit_expiration 
        FROM 
            tricycle_inventory 
        WHERE 
            quantity_allocated > 0 
        ORDER BY 
            allocated_unit_expiration ASC 
        LIMIT 1
    ");

    $stmt->execute();
    $earliest_batch = $stmt->fetchColumn();

    // Calculate remaining days
    if ($earliest_batch) {
        $currentDate = new DateTime();
        $expirationDate = new DateTime($earliest_batch);
        $remainingDays = $currentDate->diff($expirationDate)->days;
    } else {
        $remainingDays = 0; // Default to 0 if no batches are found
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}



// Get the count of active purchase orders that are not yet completed
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS pending_orders 
        FROM purchase_order 
        WHERE order_status != 'Order Complete'
    ");
    
    $stmt->execute();
    $pending_orders = $stmt->fetchColumn();

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

//purchase recommendtion analytics
try {
   $stmt = $pdo->prepare("
SELECT 
    bt.type_name AS banana_type,
    COALESCE(sales_data.total_sales, 0) AS total_sales,
    COALESCE(stock_data.current_stock, 0) AS current_stock,
    COALESCE(waste_data.total_waste, 0) AS total_waste,
    GREATEST(0, 
        COALESCE(sales_data.total_sales, 0) - 
        COALESCE(stock_data.current_stock, 0) - 
        COALESCE(waste_data.total_waste, 0)
    ) AS suggested_purchase_quantity
FROM 
    banana_type bt
LEFT JOIN (
    -- Subquery for total current stock (quantity_allocated) from tricycle_inventory
    SELECT 
        ti.banana_type_id, 
        SUM(CASE WHEN ti.allocated_unit_expiration >= CURDATE() THEN ti.quantity_allocated ELSE 0 END) AS current_stock
    FROM 
        tricycle_inventory ti
    GROUP BY 
        ti.banana_type_id
) AS stock_data ON bt.banana_type_id = stock_data.banana_type_id
LEFT JOIN (
    -- Subquery for total sales in the last 4 weeks
    SELECT 
        ti.banana_type_id, 
        SUM(st.quantity_sold) AS total_sales
    FROM 
        tricycle_inventory ti
    LEFT JOIN 
        sales_transaction st ON ti.tric_inventory_id = st.tric_inventory_id 
        AND st.sale_date >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
    GROUP BY 
        ti.banana_type_id
) AS sales_data ON bt.banana_type_id = sales_data.banana_type_id
LEFT JOIN (
    -- Subquery for total waste
    SELECT 
        ti.banana_type_id, 
        SUM(w.quantity_wasted) AS total_waste
    FROM 
        tricycle_inventory ti
    LEFT JOIN 
        waste w ON ti.tric_inventory_id = w.tricycle_inventory_id
    GROUP BY 
        ti.banana_type_id
) AS waste_data ON bt.banana_type_id = waste_data.banana_type_id
ORDER BY 
    suggested_purchase_quantity DESC;


");

    $stmt->execute();
    $bananaData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // // Debugging line to check fetched data
    // var_dump($bananaData); // Line 104 - Add this line here
    
    // Prepare data for the chart
    $categories = [];
    $purchaseRecommendations = [];

    foreach ($bananaData as $row) {
        $categories[] = $row['banana_type'];
        $purchaseRecommendations[] = (float) $row['suggested_purchase_quantity'];
    }
    
    // // Console logging for debugging
    // echo '<script>console.log(' . json_encode($categories) . ');</script>'; // Line 116
    // echo '<script>console.log(' . json_encode($purchaseRecommendations) . ');</script>'; // Line 117
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


//for getting the total amount sold by location
try {
    $stmt = $pdo->prepare("
        SELECT 
            t.location AS tricycle_location,
            SUM(st.total_amount_sold) AS total_sales
        FROM 
            tricycle t
        LEFT JOIN 
            tricycle_inventory ti ON t.tricycle_id = ti.tricycle_id
        LEFT JOIN 
            sales_transaction st ON ti.tric_inventory_id = st.tric_inventory_id
        GROUP BY 
            t.tricycle_id, t.location
    ");

    $stmt->execute();
    $locationData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for the chart
    $locations = [];
    $salesByLocation = [];

    foreach ($locationData as $row) {
        $locations[] = $row['tricycle_location'];
        $salesByLocation[] = (float) $row['total_sales']; // Use total sales
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Debug output to check data
var_dump($locations);
var_dump($salesByLocation);

// Pass PHP arrays to JavaScript for locations and total sales

try {
    // Get total sales by date for the last 7 days
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
    
    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for the chart
    $salesDates = [];
    $totalSalesByDate = [];
    $salesByWeek = [];
    $weeks = [];

    foreach ($salesData as $row) {
        // Calculate week number for each sale_date
        $weekNumber = date('W', strtotime($row['sale_date'])) - date('W', strtotime(date('Y-m-01', strtotime($row['sale_date'])))) + 1;
        $monthYear = date('M Y', strtotime($row['sale_date'])); // Get the month and year for the label

        // Format the week as 'Week X of Month'
        $weekLabel = 'Week ' . $weekNumber . ' of ' . $monthYear;
        
        // Add the week label to the weeks array
        if (!in_array($weekLabel, $weeks)) {
            $weeks[] = $weekLabel;
        }
        
        // Sum up total sales for each week
        if (!isset($salesByWeek[$weekLabel])) {
            $salesByWeek[$weekLabel] = 0;
        }
        $salesByWeek[$weekLabel] += $row['total_sales'];
    }

    // Prepare the data for JavaScript
    $salesDates = array_keys($salesByWeek); // Week labels for x-axis
    $totalSalesByDate = array_values($salesByWeek); // Total sales for each week
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


// Fetch top-performing tricycles by ranking based on total sales without a limit
try {
    $stmt = $pdo->prepare("
        SELECT 
            t.tricycle_id, 
            SUM(st.total_amount_sold) AS total_sales,
            SUM(st.quantity_sold) AS total_quantity_sold -- Add this line to calculate total quantity sold
        FROM 
            tricycle t
        LEFT JOIN 
            tricycle_inventory ti ON t.tricycle_id = ti.tricycle_id
        LEFT JOIN 
            sales_transaction st ON ti.tric_inventory_id = st.tric_inventory_id
        GROUP BY 
            t.tricycle_id
        ORDER BY 
            total_sales DESC
    ");

    $stmt->execute();
    $topTricycles = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


try {
    // SQL query to get total waste by banana type
    $query = "
        SELECT 
            ti.banana_type_id,
            SUM(w.quantity_wasted) AS total_waste
        FROM 
            waste w
        JOIN 
            tricycle_inventory ti ON w.tricycle_inventory_id = ti.tric_inventory_id
        GROUP BY 
            ti.banana_type_id;
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for ApexCharts
    $categories = [];
    $seriesData = [];

    foreach ($results as $row) {
        // Fetch the banana type name from banana_type table using banana_type_id
        $bananaTypeQuery = "SELECT type_name FROM banana_type WHERE banana_type_id = ?";
        $bananaTypeStmt = $pdo->prepare($bananaTypeQuery);
        $bananaTypeStmt->execute([$row['banana_type_id']]);
        $bananaType = $bananaTypeStmt->fetchColumn(); // Fetch the type_name

        $categories[] = $bananaType ? $bananaType : 'Unknown'; // Use actual name from the query
        $seriesData[] = (float)$row['total_waste'];
    }

    // Convert to JSON format for JavaScript
    $categoriesJson = json_encode($categories);
    $seriesDataJson = json_encode($seriesData);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

try {
    // Fetching waste data over time for the trend chart
    $queryTrend = "
        SELECT 
            w.waste_date, 
            SUM(w.quantity_wasted) AS total_waste
        FROM 
            waste w
        GROUP BY 
            w.waste_date
        ORDER BY 
            w.waste_date ASC;
    ";

    $stmtTrend = $pdo->prepare($queryTrend);
    $stmtTrend->execute();
    $trendResults = $stmtTrend->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for the waste trend chart
    $trendCategories = [];
    $trendSeriesData = [];

    foreach ($trendResults as $row) {
        $trendCategories[] = $row['waste_date']; // x-axis (dates)
        $trendSeriesData[] = (float)$row['total_waste']; // y-axis (total waste)
    }

    // Convert to JSON for JavaScript
    $trendCategoriesJson = json_encode($trendCategories);
    $trendSeriesDataJson = json_encode($trendSeriesData);

} catch (PDOException $e) {
    echo "Error fetching waste trend data: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <title>Dashboard</title>


</head>

<body style="background: #FAFAFA;">

<main class="main-container">
    <div class="main-title">
        <p class="font-weight-bold">DASHBOARD</p>
    </div>

    <div class="main-card">
        
        <div class="card">
            <div class="card-inner">
                <p class="text-primary">Today's Sales</p>
                <span class="material-symbols-outlined">attach_money</span>
            </div>
            <span class="text-primary font-weight-bold">
                <!-- Display today's sales amount -->
                <?php echo '₱ ' .  number_format($todays_sales, 2); ?>
            </span>
        </div>

        <div class="card">
            <div class="card-inner">
                <p class="text-primary">Total Stocks</p>
                <span class="material-symbols-outlined">inventory_2</span>
            </div>
            <span class="text-primary font-weight-bold">
                <!-- Display total stocks -->
                <?php echo number_format($total_quantity, 2) . ' Kg'; ?>
            </span>
        </div>

        <div class="card">
            <div class="card-inner">
                <p class="text-primary">Near Expiry Batches</p>
                <span class="material-symbols-outlined">date_range</span>                
            </div>
            <span class="text-primary font-weight-bold">
                <!-- Display remaining days until the earliest batch expires -->
                <?php echo $remainingDays . ' days remaining'; ?>
            </span>
        </div>

        <div class="card">
            <div class="card-inner">
                <p class="text-primary">Upcoming Orders</p>
                <span class="material-symbols-outlined">local_shipping</span>                
            </div>
            <span class="text-primary font-weight-bold">
                    <!-- Display the count of pending orders -->
                    <?php echo $pending_orders; ?> orders pending
                </span>
        </div>
    </div>

  <div class="chart">
    <div class="chart-container">
        <h2 class="chart-title">Optimal Purchase Quantities</h2>
        <div id="bar-chart"></div> <!-- Chart will occupy 60% -->
        <div class="chart-table">
            <table class="table-analytics table-bordered">
                <thead>
                    <tr>
                        <th>Banana Type</th>
                        <th>Total Sales (Kg)</th>
                        <th>Current Stock (Kg)</th>
                        <th>Total Waste (Kg)</th>
                        <th>Suggested Purchase Quantity (Kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bananaData as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['banana_type']); ?></td>
                            <td><?php echo number_format($row['total_sales'], 2); ?></td>
                            <td><?php echo number_format($row['current_stock'], 2); ?></td>
                            <td><?php echo number_format($row['total_waste'], 2); ?></td>
                            <td><?php echo number_format($row['suggested_purchase_quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>

  <div class="chart-second">
    <div class="chart-card-second">
        <p class="chart-title">Sales Performance by Location</p>
        <div id="clustered-bar-chart"></div>
    </div>
    <div class="chart-card-second">
        <p class="chart-title">Weekly Sales</p>
        <div id="salesLineChart"></div>
    </div>
</div>


<div class="table-chart">
    <div class="table-container">
        <p class="chart-title">Top Performing Tricycle</p>
        <table class="performance-table">
            <thead>
                <tr>
                    <th>Ranking</th>
                    <th>Tricycle ID</th> <!-- Changed this column -->
                    <th>Total Sales</th>
                    <th>Total Quantity Sold</th> <!-- Changed this column -->
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($topTricycles)): ?>
                    <?php $rank = 1; ?>
                    <?php foreach ($topTricycles as $tricycle): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rank++); ?></td> <!-- Display the ranking -->
                            <td><?php echo htmlspecialchars("TR - " . $tricycle['tricycle_id']); ?></td> <!-- Display formatted Tricycle ID -->
                            <td><?php echo htmlspecialchars(number_format($tricycle['total_sales'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($tricycle['total_quantity_sold']); ?> Kg</td> <!-- Display Total Quantity Sold -->
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No sales data available.</td> <!-- Adjust colspan based on number of columns -->
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="waste-dashboard">
    <!-- Waste Donut Chart -->
    <div class="waste-chart">
        <div class="waste-card">
            <p class="chart-title">Waste by Banana Type</p>
            <div id="donutChart"></div>
        </div>
    </div>

    <!-- Waste Trend Line Chart -->
    <div class="waste-card">
        <div class="trend-card">
            <p class="chart-title">Waste Trend Over Time</p>
            <div id="trendChart"></div>
        </div>
    </div>
</div>




</main>

<!-- Load ApexCharts library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.54.1/apexcharts.min.js"></script>

<script>
// Pass PHP arrays to JavaScript
var categories = <?php echo json_encode($categories); ?>; // Banana types
var purchaseRecommendations = <?php echo json_encode($purchaseRecommendations); ?>; // Suggested purchase quantities

// Chart options
var barChartOptions = {
    series: [{
        name: 'Suggested Purchase Quantity',
        data: purchaseRecommendations // Use the PHP data for the bar chart
    }],
    chart: {
        type: 'bar',
        height: '100%', // Set height to use the full available container
        toolbar: {
            show: false // Remove the toolbar
        },
    },
    colors: ['#FFBF00', '#f7ff00', '#3357FF', '#5a5a48','#abf9ca '],
    plotOptions: {
        bar: {
            borderRadius: 4,
            borderRadiusApplication: 'end',
            horizontal: false,
            columnWidth: '45%' // Adjust column width to avoid scrolling
        }
    },
    dataLabels: {
        enabled: true // Enable data labels for better visibility
    },
    xaxis: {
        categories: categories, // Use the banana types as categories
        labels: {
            style: {
                fontSize: '12px' // Adjust font size to prevent overlap
            }
        }
    },
    yaxis: {
        title: {
            text: 'Quantity' // Label for the Y-axis
        }
    },
    grid: {
        padding: {
            left: 0, // Remove grid padding to avoid overflow
            right: 0
        }
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return val + " kg"; // Format the tooltip to show quantity in kg
            }
        }
    },
};

// Render the chart
var barChart = new ApexCharts(document.querySelector("#bar-chart"), barChartOptions);
barChart.render();

//second bar chart for sales performance
// Pass data to JavaScript
var locations = <?php echo json_encode($locations); ?>; // Tricycle locations
var salesByLocation = <?php echo json_encode($salesByLocation); ?>; // Total sales by location

// Define colors for the bars
var barColors = ['#FF5733', '#33FF57', '#3357FF']; // Three different colors

var clusteredBarChartOptions = {
    series: [{
        name: 'Total Sales (₱)',
        data: salesByLocation
    }],
    chart: {
        type: 'bar',
        height: '100%', // Adjust height to fit the container
        stacked: false,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        }
    },
    colors: barColors,
    plotOptions: {
        bar: {
            borderRadius: 4,
            horizontal: false,
            columnWidth: '50%', // Adjust column width to avoid scrollbars
            distributed: true
        }
    },
    xaxis: {
        categories: locations,
        labels: {
            style: {
                fontSize: '12px' // Prevent overlap of labels
            }
        }
    },
    yaxis: {
        title: {
            text: 'Total Amount Sold (₱)'
        },
        labels: {
            formatter: function (val) {
                return "₱" + val;
            }
        }
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return "₱" + val;
            }
        }
    },
    grid: {
        padding: {
            left: 10,
            right: 10 // Adjust padding to prevent overflow
        }
    }
};

var clusteredBarChart = new ApexCharts(document.querySelector("#clustered-bar-chart"), clusteredBarChartOptions);
clusteredBarChart.render();



 // Pass PHP arrays to JavaScript
    const salesDates = <?php echo json_encode($salesDates); ?>; // PHP array of week numbers
    const totalSalesByDate = <?php echo json_encode($totalSalesByDate); ?>; // PHP array of total sales by week

    var lineChartOptions = {
    chart: {
        height: '100%', // Adjust height to fit the container
        type: 'line',
        zoom: {
            enabled: false
        },
        toolbar: {
            show: true
        }
    },
    colors: ['#FFBF00'],
    dataLabels: {
        enabled: false
    },
    series: [{
        name: 'Total Sales',
        data: totalSalesByDate
    }],
    xaxis: {
        categories: salesDates,
        labels: {
            style: {
                fontSize: '12px' // Consistent label styling
            }
        }
    },
    yaxis: {
        title: {
            text: 'Total Sales (₱)' // Use PHP for currency label
        },
        min: 0 // Start from 0
    },
    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: function(val) {
                return "₱" + val; // Format with PHP currency
            }
        }
    },
    grid: {
        padding: {
            left: 10,
            right: 10 // Adjust padding to prevent overflow
        }
    }
};

var salesLineChart = new ApexCharts(document.querySelector("#salesLineChart"), lineChartOptions);
salesLineChart.render();



  document.addEventListener("DOMContentLoaded", function() {
    // Waste Donut Chart
    var donutOptions = {
        chart: {
            type: 'donut'
        },
        series: <?php echo $seriesDataJson; ?>,
        labels: <?php echo $categoriesJson; ?>,
        colors: ['#FFBF00', '#A9A9A9'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var donutChart = new ApexCharts(document.querySelector("#donutChart"), donutOptions);
    donutChart.render();

  // Waste Trend Line Chart
var trendOptions = {
    chart: {
        type: 'line',
        height: '100%',
        width: '100%',
        toolbar: {
            show: false // Hide zoom/pan toolbar if enabled
        },
        zoom: {
            enabled: false // Disable zooming to avoid scrollbars
        },
    },
    colors: ['#FFBF00'],
    series: [{
        name: 'Waste Quantity',
        data: <?php echo $trendSeriesDataJson; ?>
    }],
    xaxis: {
        categories: <?php echo $trendCategoriesJson; ?>,
        title: {
            text: 'Date'
        }
    },
    yaxis: {
        title: {
            text: 'Total Waste'
        }
    },
    stroke: {
        curve: 'smooth'
    },
   
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: '100%'  // Keep width at 100% for smaller screens
            }
        }
    }]
};

var trendChart = new ApexCharts(document.querySelector("#trendChart"), trendOptions);
trendChart.render();

});
</script>

</body>
</html>
