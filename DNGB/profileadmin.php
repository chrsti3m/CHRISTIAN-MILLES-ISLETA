<?php
require 'connections/conx.php';
include 'components/header-admin.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.html");
    exit();
}

// Fetch user details
$stmt = $pdo->prepare("SELECT name, contact_info, email, profile_picture FROM user WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle file upload
$uploadSuccess = false;
$uploadError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'profile_' . $_SESSION['user_id'] . '.' . $ext;
        $targetPath = 'components/images/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("UPDATE user SET profile_picture = ? WHERE user_id = ?");
            $stmt->execute([$targetPath, $_SESSION['user_id']]);
            $user['profile_picture'] = $targetPath;
            $uploadSuccess = true;
        } else {
            $uploadError = true;
        }
    } else {
        $uploadError = true;
    }
}

// Handle password change
$passwordChangeSuccess = false;
$passwordChangeError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Fetch the current password from the database
    $stmt = $pdo->prepare("SELECT password FROM user WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userPassword = $stmt->fetch(PDO::FETCH_ASSOC)['password'];

    // Check if current password matches the one in the database
    if (password_verify($currentPassword, $userPassword)) {
        // Check if new password and confirmation match
        if ($newPassword === $confirmPassword) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $stmt->execute([$hashedPassword, $_SESSION['user_id']]);

            $passwordChangeSuccess = true;
        } else {
            $passwordChangeError = 'New passwords do not match!';
        }
    } else {
        $passwordChangeError = 'Current password is incorrect!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="components/css/headeruser.css">
    <link rel="stylesheet" type="text/css" href="css-pages/style.css">
    <link rel="stylesheet" type="text/css" href="css-pages/style-sidebar.css">

    <style>
        /* Profile Page Styles */
        body {
            background-color: #FAFAFA;
            font-family: "Poppins", sans-serif;
        }

        .profile-container {
            margin: 99px auto;
            width: 73%;
            max-width: 1246px;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-left: 330px;
        }


        .profile-header {
            text-align: center;
        }

        .profile-header h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
        }

        .profile-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-details {
            margin-top: 20px;
        }

        .profile-details table {
            width: 100%;
        }

        .profile-details th,
        .profile-details td {
            padding: 12px 15px;
            text-align: left;
        }

        .profile-details th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .profile-details td {
            background-color: #fff;
            color: #555;
        }

        .profile-details td img {
            width: 100%;
        }

        /* Profile Picture Upload */
        .upload-form {
            margin-top: 20px;
        }

        .upload-form input {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: 100%;
        }

        .upload-form button {
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .upload-form button:hover {
            background-color: #0056b3;
        }

        /* Password Change Form */
        .password-form {
            margin-top: 40px;
        }

        .password-form input {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: 100%;
        }

        .password-form button {
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .password-form button:hover {
            background-color: #0056b3;
        }

        /* Success/Error Messages */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .close-btn {
            cursor: pointer;
            color: #333;
        }

        .close-btn:hover {
            color: red;
        }
    </style>
</head>

<body>
    <?php include 'components/sidebar.php'; ?>
    <div class="profile-container">
        <div class="profile-header">
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>
            <?php if ($user['profile_picture']): ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
            <?php else: ?>
                <p>No profile picture uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="profile-details">
            <h3>Your Details</h3>
            <table class="table table-bordered">
                <tr>
                    <th>Name</th>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                </tr>
                <tr>
                    <th>Contact Info</th>
                    <td><?php echo htmlspecialchars($user['contact_info']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                </tr>
            </table>
        </div>

        <div class="upload-form">
            <h3>Change Profile Picture</h3>
            <form action="profileadmin.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" accept="image/*" required>
                <button type="submit">Upload</button>
            </form>
        </div>

        <!-- Password Change Form -->
        <div class="password-form">
            <h3>Change Password</h3>
            <form action="profileadmin.php" method="POST">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>

    </div>

    <!-- Success Popup Modal -->
    <div id="successPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('successPopup')">&times;</span>
            <p>Profile picture uploaded successfully!</p>
        </div>
    </div>

    <!-- Error Popup Modal -->
    <div id="errorPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('errorPopup')">&times;</span>
            <p>Failed to upload profile picture. Please try again.</p>
        </div>
    </div>

    <script>
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        window.onload = function() {
            <?php if ($uploadSuccess): ?>
                document.getElementById('successPopup').style.display = 'flex';
            <?php elseif ($uploadError): ?>
                document.getElementById('errorPopup').style.display = 'flex';
            <?php elseif ($passwordChangeSuccess): ?>
                document.getElementById('successPopup').style.display = 'flex';
                document.querySelector('.popup-content p').innerText = 'Password changed successfully!';
            <?php elseif ($passwordChangeError): ?>
                document.getElementById('errorPopup').style.display = 'flex';
                document.querySelector('.popup-content p').innerText = '<?php echo $passwordChangeError; ?>';
            <?php endif; ?>
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>

</html>
