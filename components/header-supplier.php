<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Title Here</title>
    <link rel="stylesheet" type="text/css" href="components/css/style-sidebar.css">
    <script>
        function confirmLogout(event) {
            event.preventDefault(); // Prevent the default action
            const confirmLogout = confirm("Are you sure you want to log out?");
            if (confirmLogout) {
                window.location.href = "login.html"; // Redirect to login page
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="header-text"></div> <!-- Add a title for context -->
        <ul class="header-links">
            <li><a href="profile.php">Profile</a></li>
            <li><a href="login.html" class="logout-button" onclick="confirmLogout(event)">Log Out</a></li>
        </ul>
    </div>
</body>
</html>
