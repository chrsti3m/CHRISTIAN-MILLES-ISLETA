<?php
require 'connections/conx.php';

// Connect to the MySQL database
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare a query to fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // After user verification
    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        session_start(); // Start the session here
        $_SESSION['user_id'] = $user['user_id']; // Ensure the user_id is set in session
        $_SESSION['role'] = $user['role']; // Store user role in session
        $_SESSION['name'] = $user['name']; // Optionally store user name

        // Fetch supplier_id if the user is a Supplier
        if ($user['role'] === 'Supplier') {
            $stmt = $pdo->prepare("SELECT supplier_id FROM supplier WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user['user_id']]);
            $supplier = $stmt->fetch();

            if ($supplier) {
                $_SESSION['supplier_id'] = $supplier['supplier_id']; // Store the supplier_id in the session
            } else {
                echo "<div class='alert alert-danger text-center mt-4'>Supplier ID does not exist in the supplier table.</div>";
                exit();
            }
        }

        // Fetch tricycle_id if the user is a Tricycle Operator
        if ($user['role'] === 'Tricycle Operator') {
            $stmt = $pdo->prepare("SELECT tricycle_id FROM tricycle WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user['user_id']]);
            $tricycle = $stmt->fetch();

            if ($tricycle) {
                $_SESSION['tricycle_id'] = $tricycle['tricycle_id']; // Store the tricycle_id in the session
            } else {
                echo "<div class='alert alert-danger text-center mt-4'>Tricycle ID does not exist for this user.</div>";
                exit();
            }
        }

        // Redirect based on user role
        switch ($user['role']) {
            case 'Admin':
                header('Location: admin-inventory-page.php');
                break;
            case 'Tricycle Operator':
                header('Location: tric-inventory-page.php');
                break;
            case 'Supplier':
                header('Location: suppliers-product-page.php');
                break;
            default:
                // Fallback if role is not recognized
                header('Location: landing1.php');
                break;
        }

        // Debugging session variables (optional, remove in production)
        echo "<pre>";
        print_r($_SESSION); // Check session variables
        echo "</pre>";

        exit();
    } else {
        // Invalid credentials
        header('Location: loginerror.html'); // Redirect to login error page
        exit();
    }
}
?>
