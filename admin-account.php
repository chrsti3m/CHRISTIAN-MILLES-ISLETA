<?php
// Database connection
require 'connections/conx.php'; // Adjust the path to your connection script

// Admin details to be inserted
$admin_name = 'Dan';
$admin_role = 'Admin';
$admin_contact = 'admin_contact_info';
$admin_email = 'admin@gmail.com';
$admin_password = 'adminuser'; // Plain text password

// Hash the password using bcrypt
$hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);

try {
    // Prepare the SQL query to insert the admin user
    $sql = "INSERT INTO `user` (name, role, contact_info, password, email) VALUES (:name, :role, :contact_info, :password, :email)";
    
    // Prepare statement
    $stmt = $pdo->prepare($sql);
    
    // Bind the values
    $stmt->bindParam(':name', $admin_name);
    $stmt->bindParam(':role', $admin_role);
    $stmt->bindParam(':contact_info', $admin_contact);
    $stmt->bindParam(':password', $hashed_password); // Store the hashed password
    $stmt->bindParam(':email', $admin_email);
    
    // Execute the query
    $stmt->execute();
    
    // Prompt success message
    echo "Admin successfully inserted!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>
