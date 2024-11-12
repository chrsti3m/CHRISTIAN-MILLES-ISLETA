<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Website</title>
    <link rel="stylesheet" type="text/css" href="components/css/style-sidebar.css">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <a href="#"><img src="/DNGB/components/images/nana.png" alt="nana Icon"></a>
            <h2>D&G</h2>
        </div>
        <div class="links">
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="admin-inventory-page.php">Inventory</a>
            <a href="purchase-order-front.php">Purchase Orders</a>
            <a href="admin-purchase-history.php">Order History</a>
            <a href="suppliers-front.php">Suppliers</a>
            <a href="user-front.php">Tricycle Operator</a>

            <!-- Reports Dropdown -->
            <div class="dropdown">
                <a href="#" class="dropbtn">
                    Reports 
                    <span class="arrow">&#9660;</span> <!-- Down arrow symbol -->
                </a>
                <div class="dropdown-content" style="display: none;">
                    <a href="sales_reports.php">Sales Report</a>
                    <a href="inventory_reports.php">Inventory Report</a>
                    <a href="tricycle_inventory_reports.php">Tricycle Inventory Report</a>
                    <a href="waste_reports.php">Waste Report</a>
                    <a href="purchase_reports.php">Purchase Report</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to toggle dropdown and arrow direction
        document.querySelector('.dropbtn').addEventListener('click', function(event) {
            event.preventDefault();
            const dropdownContent = document.querySelector('.dropdown-content');
            const arrow = document.querySelector('.arrow');

            // Toggle dropdown visibility
            if (dropdownContent.style.display === 'block') {
                dropdownContent.style.display = 'none';
                arrow.innerHTML = '&#9660;'; // Down arrow
            } else {
                dropdownContent.style.display = 'block';
                arrow.innerHTML = '&#9650;'; // Up arrow
            }
        });

        // Close the dropdown if the user clicks outside of it
        window.addEventListener('click', function(event) {
            if (!event.target.matches('.dropbtn')) {
                const dropdownContent = document.querySelector('.dropdown-content');
                const arrow = document.querySelector('.arrow');
                if (dropdownContent.style.display === 'block') {
                    dropdownContent.style.display = 'none';
                    arrow.innerHTML = '&#9660;'; // Reset to down arrow
                }
            }
        });
    </script>

</body>
</html>
